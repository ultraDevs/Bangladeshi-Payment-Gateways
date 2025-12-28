<?php // @codingStandardsIgnoreLine
/** بسم الله الرحمن الرحيم  **
 * Main Plugin File
 *
 * @package BDPaymentGateways
 */

/**
 * Plugin Name:       Bangladeshi Payment Gateways - Make Payment Using QR Code
 * Plugin URI:        https://ultradevs.com/products/wp-plugin/bangladeshi-payment-gateways/
 * Description:       Bangladeshi Payment Gateways for WooCommerce.
 * Version:           4.0.4
 * Author:            ultraDevs
 * Author URI:        https://ultradevs.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bangladeshi-payment-gateways
 * Domain Path:       /languages
 */

// If this file is called directly, abort!
defined( 'ABSPATH' ) || exit( 'bYe bYe!' );

// Constant.
define( 'BD_PAYMENT_GATEWAYS_VERSION', '4.0.4' );
define( 'BD_PAYMENT_GATEWAYS_NAME', 'Bangladeshi Payment Gateways' );
define( 'BD_PAYMENT_GATEWAYS_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'BD_PAYMENT_GATEWAYS_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'BD_PAYMENT_GATEWAYS_DIST_PATH', BD_PAYMENT_GATEWAYS_DIR_PATH . 'dist/' );
define( 'BD_PAYMENT_GATEWAYS_DIST_URL', BD_PAYMENT_GATEWAYS_DIR_URL . 'dist/' );
define( 'BD_PAYMENT_GATEWAYS_ASSETS', BD_PAYMENT_GATEWAYS_DIR_URL . 'assets/' );
define( 'BD_PAYMENT_GATEWAYS_MENU_SLUG', 'bangladeshi-payment-gateways' );

/**
 * Require Composer Autoload
 */
require_once BD_PAYMENT_GATEWAYS_DIR_PATH . 'vendor/autoload.php';


/**
 * Bangladeshi Payment Gateways class
 */
final class BDPaymentGateways {

	/**
	 * Bkash.
	 *
	 * @var mixed
	 */
	public $bkash = null;

	/**
	 * Rocket.
	 *
	 * @var mixed
	 */
	public $rocket = null;

	/**
	 * Nagad.
	 *
	 * @var mixed
	 */
	public $nagad = null;

	/**
	 * Upay.
	 *
	 * @var mixed
	 */
	public $upay = null;

	/**
	 * Gateways.
	 *
	 * @var array
	 */
	public $gateways = array();


	/**
	 * Constructor
	 */
	private function __construct() {

		// Load text domain on init hook.
		add_action( 'init', array( $this, 'load_text_domain' ) );

		add_action( 'plugins_loaded', array( $this, 'init' ), 1 );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		do_action( 'bd_payment_gateways_loaded' );

		/**
		 * Declare WooCommerce Compatibility
		 */
		add_action(
			'before_woocommerce_init',
			function () {
				if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
						'cart_checkout_blocks',
						__FILE__,
						true
					);

					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
						'custom_order_tables',
						__FILE__,
						true
					);
				}
			}
		);
	}

	/**
	 * Begin execution of the plugin
	 *
	 * @return \BDPaymentGateways
	 */
	public static function run() {
		/**
		 * Instance
		 *
		 * @var boolean
		 */
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Plugin Init
	 */
	public function init() {

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			add_action( 'admin_notices', array( $this, 'woo_required_notice' ) );
			return;
		}

		$this->appsero_init_tracker();

		// Payment Gateways classes.
		$this->gateways = array(
			ultraDevs\BDPG\Gateways\Bkash::get_instance(),
			ultraDevs\BDPG\Gateways\Rocket::get_instance(),
			ultraDevs\BDPG\Gateways\Nagad::get_instance(),
			ultraDevs\BDPG\Gateways\Upay::get_instance(),
		);

		// Assets Manager Class.
		$assets_manager = new ultraDevs\BDPG\Assets_Manager();

		// Activate.
		$activate = new ultraDevs\BDPG\Activate();
		$activate->register_migration_hook();

		// Review Class.
		$review = new ultraDevs\BDPG\Review();

		// Dashboard Class.
		new ultraDevs\BDPG\Admin\Dashboard();

		// Statistics Class.
		new ultraDevs\BDPG\Admin\Statistics();

		add_action( 'woocommerce_payment_gateways', array( $this, 'add_payment_gateways' ) );

		// Register block support gateways.
		add_action( 'woocommerce_blocks_loaded', array( $this, 'init_block_gateways' ) );

		if ( is_admin() ) {

			// Activation_Redirect.
			add_action( 'admin_init', array( $activate, 'activation_redirect' ) );

			// Admin Assets.
			add_action( 'admin_enqueue_scripts', array( $assets_manager, 'admin_assets' ) );

			// Plugin Action Links.
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

			// Review Notice.
			$review->register();

		} else {
			// Frontend Assets.
			add_action( 'wp_enqueue_scripts', array( $assets_manager, 'frontend_assets' ) );
		}
	}

	/**
	 * The code that runs during plugin activation.
	 */
	/**
	 * Plugin Activation.
	 *
	 * @return void
	 */
	public function activate() {
		$activate = new ultraDevs\BDPG\Activate();
		$activate->run();
	}

	/**
	 * Loads a plugin’s translated strings.
	 *
	 * @return void
	 */
	public function load_text_domain() {
		load_plugin_textdomain( 'bangladeshi-payment-gateways', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add Payment Gateways to WooCommerce.
	 *
	 * @param array $gateways Gateways.
	 * @return array
	 */
	public function add_payment_gateways( $gateways ) {

		foreach ( $this->gateways as $gateway ) {
			$gateways[] = $gateway;
		}

		return $gateways;
	}

	/**
	 * Initialize Block Support Gateways.
	 *
	 * @return void
	 */
	public function init_block_gateways() {
		if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			return;
		}

		// Block gateways.
		$block_gateways = array(
			ultraDevs\BDPG\Blocks\Gateways\Bkash_Blocks::get_instance(),
			ultraDevs\BDPG\Blocks\Gateways\Rocket_Blocks::get_instance(),
			ultraDevs\BDPG\Blocks\Gateways\Nagad_Blocks::get_instance(),
			ultraDevs\BDPG\Blocks\Gateways\Upay_Blocks::get_instance(),
		);

		foreach ( $block_gateways as $block_gateway ) {
			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				function ( $payment_method_registry ) use ( $block_gateway ) {
					$payment_method_registry->register( $block_gateway );
				}
			);
		}
	}

	/**
	 * WooCommerce Required Notice.
	 */
	public function woo_required_notice() {
		$message = sprintf(
			// translators: %1$s Plugin Name, %2$s wooCommerce.
			esc_html__( ' %1$s requires %2$s to be installed and activated. Please activate %2$s to continue.', 'bangladeshi-payment-gateways' ), // @codingStandardsIgnoreLine
			'<strong>' . esc_html__( 'Bangladeshi Payment Gateways', 'bangladeshi-payment-gateways' ) . '</strong>',
			'<strong>' . esc_html__( 'WooCommerce', 'bangladeshi-payment-gateways' ) . '</strong>'
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message ); // @codingStandardsIgnoreLine
	}

	/**
	 * Plugin Action Links
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {

		$links[] = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'admin.php?page=wc-settings&tab=checkout' ),
			__( 'Payment Settings', 'bangladeshi-payment-gateways' )
		);

		$links[] = sprintf(
			'<a href="%s">%s</a>',
			'https://wordpress.org/support/plugin/bangladeshi-payment-gateways/reviews?filter=5#new-post',
			__( '<b style="color: green">Write a Review</b>', 'bangladeshi-payment-gateways' ) // @codingStandardsIgnoreLine
		);

		return $links;
	}

	/**
	 * Initialize the plugin tracker
	 *
	 * @return void
	 */
	public function appsero_init_tracker() {

		if ( ! class_exists( 'Appsero\Client' ) ) {
			require_once BD_PAYMENT_GATEWAYS_DIR_PATH . 'vendor/appsero/src/Client.php';
		}

		$client = new Appsero\Client( 'ea194db4-5e5b-4279-9717-302702dc628d', 'Bangladeshi Payment Gateways', __FILE__ );

		// Active insights.
		$client->insights()->init();
	}
}
BDPaymentGateways::run();
