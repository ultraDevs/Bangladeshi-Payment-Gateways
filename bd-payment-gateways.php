<?php
/** بسم الله الرحمن الرحيم  *
 * Main Plugin File
 *
 * @package BDPaymentGateways
 */

/**
 * Plugin Name:       Bangladeshi Payment Gateways
 * Plugin URI:        https://ultradevs.com/plugins/bangladeshi-payment-gateways
 * Description:       Bangladeshi Payment Gateways for WooCommerce.
 * Version:           2.0.2
 * Author:            ultraDevs
 * Author URI:        https://ultradevs.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bd-payment-gateways
 * Domain Path:       /languages
 */

// If this file is called directly, abort!
defined( 'ABSPATH' ) || exit( 'bYe bYe!' );

// Constant.
define( 'BD_PAYMENT_GATEWAYS_VERSION', '2.0.0' );
define( 'BD_PAYMENT_GATEWAYS_NAME', plugin_basename( __FILE__ ) );
define( 'BD_PAYMENT_GATEWAYS_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'BD_PAYMENT_GATEWAYS_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'BD_PAYMENT_GATEWAYS_ASSETS', BD_PAYMENT_GATEWAYS_DIR_URL . 'assets/' );

/**
 * The code that runs during plugin activation.
 */
if ( ! function_exists( 'bd_payment_gateways_activate' ) ) {
	/**
	 * Plugin Activation
	 *
	 * @return void
	 */
	function bd_payment_gateways_activate() {
		flush_rewrite_rules();
	}
}
register_activation_hook( __FILE__, 'bd_payment_gateways_activate' );


/**
 * The code that helps translating
 */
if ( ! function_exists( 'bd_payment_gateways_load_text_domain' ) ) {
	/**
	 * Loads a plugin’s translated strings.
	 *
	 * @return void
	 */
	function bd_payment_gateways_load_text_domain() {
		load_plugin_textdomain( 'bangladeshi-payment-gateways', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}
add_action( 'plugins_loaded', 'bd_payment_gateways_load_text_domain' );

/**
 * The code that runs during plugin deactivation.
 */
if ( ! function_exists( 'bd_payment_gateways_deactivate' ) ) {
	/**
	 * Plugin Deactivation
	 *
	 * @return void
	 */
	function bd_payment_gateways_deactivate() {

	}
}
register_deactivation_hook( __FILE__, 'bd_payment_gateways_deactivate' );

/**
 * Core plugin class
 */
require BD_PAYMENT_GATEWAYS_DIR_PATH . 'includes/Init.php';

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_bangladeshi_payment_gateways() {

	if ( ! class_exists( 'Appsero\Client' ) ) {
		require_once BD_PAYMENT_GATEWAYS_DIR_PATH . 'includes/lib/appsero/src/Client.php';
	}

	$client = new Appsero\Client( 'ea194db4-5e5b-4279-9717-302702dc628d', 'Bangladeshi Payment Gateways', __FILE__ );

	// Active insights.
	$client->insights()->init();

	$client->set_textdomain( 'bangladeshi-payment-gateways' );

}

appsero_init_tracker_bangladeshi_payment_gateways();

/**
 * Begin execution of the plugin
 */
if ( ! function_exists( 'bd_payment_gateways_run' ) ) {
	/**
	 * Let's run the plugin.
	 *
	 * @return void
	 */
	function bd_payment_gateways_run() {
		$plugin = new BDPaymentGateways\Init();
		$plugin->run();
	}
}

bd_payment_gateways_run();
