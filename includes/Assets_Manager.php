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

	}
}
