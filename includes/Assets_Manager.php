<?php // @codingStandardsIgnoreLine
/**
 * Assets Manager Class
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */

namespace ultraDevs\BDPG;

/**
 * Manage All Assets
 *
 * This class is for managing Assets
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */
class Assets_Manager {

	/**
	 * Admin Assets
	 *
	 * Enqueue Admin Styles and Scripts
	 *
	 * @param string $hook Page slug.
	 */
	public function admin_assets( $hook ) {

		wp_enqueue_style( 'bdpg-admin', BD_PAYMENT_GATEWAYS_ASSETS . 'admin/css/admin.css', '', BD_PAYMENT_GATEWAYS_VERSION );
		wp_enqueue_script( 'bdpg-admin', BD_PAYMENT_GATEWAYS_ASSETS . 'admin/js/admin.js', array( 'jquery', 'wp-util' ), BD_PAYMENT_GATEWAYS_VERSION, false );

		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Frontend Assets
	 *
	 * Enqueue Frontend Styles and Scripts
	 */
	public function frontend_assets() {

		wp_enqueue_style( 'bdpg-frontend', BD_PAYMENT_GATEWAYS_ASSETS . 'public/css/bdpg-public.css', '', BD_PAYMENT_GATEWAYS_VERSION );
		wp_enqueue_script( 'bdpg-frontend', BD_PAYMENT_GATEWAYS_ASSETS . 'public/js/bdpg-public.js', array( 'jquery' ), BD_PAYMENT_GATEWAYS_VERSION, false );

		// Enqueue block assets if using checkout block.
		if ( $this->is_checkout_block() ) {
			$this->block_assets();
		}
	}

	/**
	 * Block Assets
	 *
	 * Enqueue Block styles and scripts for Checkout Block.
	 */
	public function block_assets() {
		$asset_file = BD_PAYMENT_GATEWAYS_DIST_PATH . 'bdpg-blocks.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;

		// Enqueue block JavaScript.
		wp_enqueue_script(
			'bdpg-blocks',
			BD_PAYMENT_GATEWAYS_DIST_URL . 'bdpg-blocks.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		// Enqueue block styles.
		wp_enqueue_style(
			'bdpg-blocks-style',
			BD_PAYMENT_GATEWAYS_DIST_URL . 'style-bdpg-blocks.css',
			array(),
			$asset['version']
		);
	}

	/**
	 * Check if the current page is using the Checkout Block.
	 *
	 * @return bool
	 */
	protected function is_checkout_block() {
		if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils' ) ) {
			return false;
		}

		return \Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_checkout_block_default();
	}
}
