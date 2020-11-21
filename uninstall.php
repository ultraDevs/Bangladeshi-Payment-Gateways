<?php
/**
 * Plugin Uninstallation
 *
 * Fired when the plugin is uninstallad
 *
 * @package BDPaymentGateways
 */

if (! defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}

delete_option('woocommerce_woo_bkash_settings');
delete_option('woocommerce_woo_rocket_settings');
delete_option('woocommerce_woo_nagad_settings');
