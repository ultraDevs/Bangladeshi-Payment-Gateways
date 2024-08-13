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
	 * The code that runs during plugin activation.
	 *
	 * @return void
	 */
	public function run() {

		Helper::add_option( 'bdpg_do_activation_redirect', true );

		$this->plugin_data();

	}

	/**
	 * Save Plugin's Data
	 */
	public function plugin_data() {
		Helper::update_option( 'bdpg_version', BD_PAYMENT_GATEWAYS_VERSION );

		$installed_time = Helper::get_option( 'bdpg_installed_datetime', false );
		if ( ! $installed_time ) {
			Helper::update_option( 'bdpg_installed_datetime', current_time( 'timestamp' ) ); // phpcs:ignore
		}
	}

	/**
	 * Activation Redirect
	 */
	public function activation_redirect() {

		if ( get_option( 'bdpg_do_activation_redirect', false ) ) {

			delete_option( 'bdpg_do_activation_redirect' );
			wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
			exit();
		}
	}
}
