<?php
/**
 * Plugin Uninstallation
 *
 * Fired when the plugin is uninstallad
 *
 * @package BDPaymentGateways
 */

// If this file is called directly, abort!
defined( 'ABSPATH' ) || exit( 'bYe bYe!' );

delete_option( 'woocommerce_woo_bkash_settings' );
delete_option( 'woocommerce_woo_rocket_settings' );
delete_option( 'woocommerce_woo_nagad_settings' );
