<?php // @codingStandardsIgnoreLine
/**
 * Activate
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */

namespace ultraDevs\BDPG;

use ultraDevs\BDPG\Helper;

/**
 * Activate Class
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */
class Activate {
	/**
	 * HPOS migration version option key.
	 */
	const HPOS_MIGRATION_VERSION = 'bdpg_hpos_migration_version';

	/**
	 * Current HPOS migration version.
	 */
	const CURRENT_HPOS_MIGRATION_VERSION = '1.0';

	/**
	 * The code that runs during plugin activation.
	 *
	 * @return void
	 */
	public function run() {

		Helper::add_option( 'bdpg_do_activation_redirect', true );

		$this->plugin_data();
		$this->maybe_migrate_hpos_data();
	}

	/**
	 * Save Plugin's Data
	 */
	public function plugin_data() {
		$old_version = Helper::get_option( 'bdpg_version', '1.0.0' );
		Helper::update_option( 'bdpg_version', BD_PAYMENT_GATEWAYS_VERSION );

		$installed_time = Helper::get_option( 'bdpg_installed_datetime', false );
		if ( ! $installed_time ) {
			Helper::update_option( 'bdpg_installed_datetime', current_time( 'timestamp' ) ); // phpcs:ignore
		}

		// Trigger migration if version changed.
		if ( version_compare( $old_version, '4.0.4', '<' ) ) {
			$this->migrate_hpos_data();
		}
	}

	/**
	 * Check if HPOS migration is needed and run it.
	 *
	 * @return void
	 */
	public function maybe_migrate_hpos_data() {
		$migration_version = get_option( self::HPOS_MIGRATION_VERSION, '0' );

		if ( version_compare( $migration_version, self::CURRENT_HPOS_MIGRATION_VERSION, '<' ) ) {
			$this->migrate_hpos_data();
			update_option( self::HPOS_MIGRATION_VERSION, self::CURRENT_HPOS_MIGRATION_VERSION );
		}
	}

	/**
	 * Migrate post meta to order meta for HPOS compatibility.
	 * This ensures all existing payment data is copied to HPOS tables.
	 *
	 * @return void
	 */
	public function migrate_hpos_data() {
		// Use Action Scheduler for background migration if available.
		if ( function_exists( 'as_schedule_single_action' ) ) {
			$this->schedule_migration();
		} else {
			// Fallback to direct migration if Action Scheduler not available.
			$this->process_migration_batch();
		}
	}

	/**
	 * Schedule HPOS migration using Action Scheduler.
	 *
	 * @return void
	 */
	public function schedule_migration() {
		// Check if migration is already scheduled or running.
		if ( $this->is_migration_scheduled() || $this->is_migration_running() ) {
			return;
		}

		// Initialize migration progress.
		$this->initialize_migration_progress();

		// Schedule the migration to run in the background.
		as_schedule_single_action( time() + 10, 'bdpg_hpos_migration_batch', array(), 'bdpg_hpos_migration' );
	}

	/**
	 * Initialize migration progress tracking.
	 *
	 * @return void
	 */
	private function initialize_migration_progress() {
		$gateways = array( 'bkash', 'rocket', 'nagad', 'upay' );
		$total_orders = 0;

		// Calculate total orders to process (only count orders with data to migrate).
		foreach ( $gateways as $gateway ) {
			$args = array(
				'limit'  => -1,
				'type'   => 'shop_order',
				'status' => array( 'any' ),
				'return' => 'ids',
			);

			$order_ids = wc_get_orders( $args );
			foreach ( $order_ids as $order_id ) {
				$order = wc_get_order( $order_id );
				if ( $order && 'woo_' . $gateway === $order->get_payment_method() ) {
					// Only count if there's data to migrate.
					$number = get_post_meta( $order_id, 'woo_' . $gateway . '_number', true );
					$trans_id = get_post_meta( $order_id, 'woo_' . $gateway . '_trans_id', true );
					if ( ! empty( $number ) || ! empty( $trans_id ) ) {
						++$total_orders;
					}
				}
			}
		}

		// Initialize migration status options.
		update_option( 'bdpg_hpos_migration_status', 'pending' );
		update_option( 'bdpg_hpos_migration_total', $total_orders );
		update_option( 'bdpg_hpos_migration_processed', 0 );
		update_option( 'bdpg_hpos_migration_gateway', '' );
		update_option( 'bdpg_hpos_migration_last_offset', 0 );
		update_option( 'bdpg_hpos_migration_start_time', current_time( 'timestamp' ) );
	}

	/**
	 * Process a batch of HPOS migration.
	 * This method is called by Action Scheduler.
	 *
	 * @return void
	 */
	public function process_migration_batch() {
		// Mark migration as running.
		update_option( 'bdpg_hpos_migration_status', 'running' );

		$gateways = array( 'bkash', 'rocket', 'nagad', 'upay' );
		$batch_size = 50;
		$processed_in_batch = 0;
		$found_gateway_orders = false;

		// Get current migration state.
		$current_gateway = get_option( 'bdpg_hpos_migration_gateway', '' );
		$last_offset = intval( get_option( 'bdpg_hpos_migration_last_offset', 0 ) );
		$total_processed = intval( get_option( 'bdpg_hpos_migration_processed', 0 ) );

		// Determine which gateway to process.
		if ( empty( $current_gateway ) ) {
			$current_gateway = $gateways[0];
			update_option( 'bdpg_hpos_migration_gateway', $current_gateway );
			$last_offset = 0;
		}

		// Process current gateway.
		$args = array(
			'limit'  => $batch_size,
			'offset' => $last_offset,
			'type'   => 'shop_order',
			'status' => array( 'any' ),
			'return' => 'ids',
		);

		$order_ids = wc_get_orders( $args );

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				continue;
			}

			// Check if this order used our gateway.
			if ( 'woo_' . $current_gateway !== $order->get_payment_method() ) {
				continue;
			}

			// Mark that we found at least one gateway order in this batch.
			$found_gateway_orders = true;

			// Get post meta data.
			$number = get_post_meta( $order_id, 'woo_' . $current_gateway . '_number', true );
			$trans_id = get_post_meta( $order_id, 'woo_' . $current_gateway . '_trans_id', true );

			// Skip if no data to migrate.
			if ( empty( $number ) && empty( $trans_id ) ) {
				continue;
			}

			// Migrate to order meta (works for both HPOS and compatibility mode).
			if ( ! empty( $number ) ) {
				$order->update_meta_data( 'woo_' . $current_gateway . '_number', $number );
			}
			if ( ! empty( $trans_id ) ) {
				$order->update_meta_data( 'woo_' . $current_gateway . '_trans_id', $trans_id );
			}

			$order->save_meta_data();
			++$processed_in_batch;
			++$total_processed;
		}

		// Update progress.
		$new_offset = $last_offset + $batch_size;
		update_option( 'bdpg_hpos_migration_last_offset', $new_offset );
		update_option( 'bdpg_hpos_migration_processed', $total_processed );

		// Check if we need to move to next gateway or continue.
		// If we found gateway orders in this batch and got a full batch of orders, there might be more.
		$has_more_orders = $found_gateway_orders && count( $order_ids ) === $batch_size;

		if ( ! $has_more_orders ) {
			// Move to next gateway.
			$gateway_index = array_search( $current_gateway, $gateways, true );
			if ( false !== $gateway_index && isset( $gateways[ $gateway_index + 1 ] ) ) {
				update_option( 'bdpg_hpos_migration_gateway', $gateways[ $gateway_index + 1 ] );
				update_option( 'bdpg_hpos_migration_last_offset', 0 );
				// Schedule next batch.
				as_schedule_single_action( time() + 5, 'bdpg_hpos_migration_batch', array(), 'bdpg_hpos_migration' );
			} else {
				// Migration complete.
				$this->complete_migration();
			}
		} else {
			// Schedule next batch.
			as_schedule_single_action( time() + 5, 'bdpg_hpos_migration_batch', array(), 'bdpg_hpos_migration' );
		}
	}

	/**
	 * Mark migration as complete.
	 *
	 * @return void
	 */
	private function complete_migration() {
		update_option( 'bdpg_hpos_migration_status', 'completed' );
		update_option( 'bdpg_hpos_migration_end_time', current_time( 'timestamp' ) );

		// Clear Action Scheduler group.
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( 'bdpg_hpos_migration_batch', array(), 'bdpg_hpos_migration' );
		}
	}

	/**
	 * Check if migration is scheduled.
	 *
	 * @return bool
	 */
	public function is_migration_scheduled() {
		if ( ! function_exists( 'as_has_scheduled_action' ) ) {
			return false;
		}

		return as_has_scheduled_action( 'bdpg_hpos_migration_batch', array(), 'bdpg_hpos_migration' );
	}

	/**
	 * Check if migration is currently running.
	 *
	 * @return bool
	 */
	public function is_migration_running() {
		$status = get_option( 'bdpg_hpos_migration_status', '' );
		return 'running' === $status;
	}

	/**
	 * Get migration progress data.
	 *
	 * @return array Migration progress data.
	 */
	public function get_migration_progress() {
		$total = intval( get_option( 'bdpg_hpos_migration_total', 0 ) );
		$processed = intval( get_option( 'bdpg_hpos_migration_processed', 0 ) );
		$status = get_option( 'bdpg_hpos_migration_status', '' );
		$current_gateway = get_option( 'bdpg_hpos_migration_gateway', '' );
		$start_time = get_option( 'bdpg_hpos_migration_start_time', 0 );
		$end_time = get_option( 'bdpg_hpos_migration_end_time', 0 );

		$percentage = $total > 0 ? round( ( $processed / $total ) * 100, 2 ) : 0;

		return array(
			'status'          => $status,
			'total'           => $total,
			'processed'       => $processed,
			'percentage'      => $percentage,
			'current_gateway' => $current_gateway,
			'is_scheduled'    => $this->is_migration_scheduled(),
			'is_running'      => $this->is_migration_running(),
			'start_time'      => $start_time ? date_i18n( 'Y-m-d H:i:s', $start_time ) : '-',
			'end_time'        => $end_time ? date_i18n( 'Y-m-d H:i:s', $end_time ) : '-',
		);
	}

	/**
	 * Reset migration data.
	 *
	 * @return void
	 */
	public function reset_migration() {
		delete_option( 'bdpg_hpos_migration_status' );
		delete_option( 'bdpg_hpos_migration_total' );
		delete_option( 'bdpg_hpos_migration_processed' );
		delete_option( 'bdpg_hpos_migration_gateway' );
		delete_option( 'bdpg_hpos_migration_last_offset' );
		delete_option( 'bdpg_hpos_migration_start_time' );
		delete_option( 'bdpg_hpos_migration_end_time' );

		// Clear Action Scheduler group.
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( 'bdpg_hpos_migration_batch', array(), 'bdpg_hpos_migration' );
		}
	}

	/**
	 * Register Action Scheduler hook for migration.
	 *
	 * @return void
	 */
	public function register_migration_hook() {
		add_action( 'bdpg_hpos_migration_batch', array( $this, 'process_migration_batch' ) );
	}

	/**
	 * Activation Redirect
	 */
	public function activation_redirect() {

		if ( get_option( 'bdpg_do_activation_redirect', false ) ) {

			delete_option( 'bdpg_do_activation_redirect' );
			wp_safe_redirect( admin_url( 'admin.php?page=' . BD_PAYMENT_GATEWAYS_MENU_SLUG ) );
			exit();
		}
	}
}
