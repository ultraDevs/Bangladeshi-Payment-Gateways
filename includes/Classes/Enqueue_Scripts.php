<?php
namespace BDPaymentGateways\Classes;
/**
 * Enqueue styles scripts functionality
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue styles scripts class
 *
 * This class is for enqueuing styles, scripts
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */
class EnqueueScripts {
	/**
	 * Register admin stylesheets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_styles() {

		wp_enqueue_style( 'bdpg-admin', BD_PAYMENT_GATEWAYS_ASSETS . 'admin/css/admin.css', array(), BD_PAYMENT_GATEWAYS_VERSION, 'all' );
	}

	/**
	 * Register admin scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {

		wp_enqueue_script( 'bdpg-admin', BD_PAYMENT_GATEWAYS_ASSETS . 'admin/js/admin.js', array( 'jquery' ), BD_PAYMENT_GATEWAYS_VERSION . rand(1,100), false );

		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Register public stylesheets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_public_styles() {

		wp_enqueue_style( 'bdpg-public', BD_PAYMENT_GATEWAYS_ASSETS . 'public/css/bdpg-public.css', array(), BD_PAYMENT_GATEWAYS_VERSION, 'all' );
		
	}

	/**
	 * Register public scripts
	 *
	 * @since 1.0.0
	 */
	public function enqueue_public_scripts() {
		wp_enqueue_script( 'bdpg-public', BD_PAYMENT_GATEWAYS_ASSETS . 'public/js/bdpg-public.js', array('jquery'), BD_PAYMENT_GATEWAYS_VERSION, true );
	}
}
