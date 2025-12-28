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

delete_option( 'bdpg_currency_settings' );

delete_option( 'bdpg_hpos_migration_status' );
delete_option( 'bdpg_hpos_migration_total' );
delete_option( 'bdpg_hpos_migration_processed' );
delete_option( 'bdpg_hpos_migration_gateway' );
delete_option( 'bdpg_hpos_migration_last_offset' );
delete_option( 'bdpg_hpos_migration_start_time' );
delete_option( 'bdpg_hpos_migration_end_time' );
