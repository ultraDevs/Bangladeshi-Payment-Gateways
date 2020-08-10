<?php
namespace BDPaymentGateways;
/**
 * Core file
 *
 * A class with all core functionalities, definition, include classes
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Core Class
 *
 * A class with all core functionalities, definition, include classes
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */
final class Init {
	/**
	 * Run
	 *
	 * @since 1.0.0
	 */
	public function run() {
		spl_autoload_register( [ $this, 'autoload' ]);
		if ( is_admin() ) {
			$this->define_admin_hooks();
		} else {
			$this->define_public_hooks();
		}
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_payment_methods' ) );
		add_action( 'plugins_loaded', array( $this, 'u_payments_gateway_start' ), 0 );
	}

	/**
	 * Autoload Classes
	 */
	public function autoload( $class ) {
		$file = preg_replace(
			[ '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
			[ '', '$1_$2', '_', DIRECTORY_SEPARATOR ],
			$class
		);
		$file = BD_PAYMENT_GATEWAYS_DIR_PATH . 'includes/' . $file . '.php';
		
		if ( file_exists( $file ) ) {
			include( $file );
		}
	}

	/**
	 * Register all of the admin hooks
	 *
	 * @since 1.0.0
	 */
	public function define_admin_hooks() {
		$us_enquequ = new Classes\EnqueueScripts();
		add_action( 'admin_enqueue_scripts', array( $us_enquequ, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $us_enquequ, 'enqueue_admin_scripts' ) );
		add_action( 'plugin_action_links_' . BD_PAYMENT_GATEWAYS_NAME , array( $this, 'bdpg_action_links' ) );
	}

	/**
	 * Register all of the public hooks
	 *
	 * @since 1.0.0
	 */
	public function define_public_hooks() {
		$us_enquequ = new Classes\EnqueueScripts();
		add_action( 'wp_enqueue_scripts', array( $us_enquequ, 'enqueue_public_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $us_enquequ, 'enqueue_public_scripts' ) );
	}

	public function u_payments_gateway_start() {
		if ( ! class_exists('WC_Payment_Gateway')) {
			add_action( 'admin_notices', array( $this, 'u_payments_g_woo_notice' ) );
			return;
		}

		require_once BD_PAYMENT_GATEWAYS_DIR_PATH . 'includes/Gateways/bKash.php';
		require_once BD_PAYMENT_GATEWAYS_DIR_PATH . 'includes/Gateways/Rocket.php';
		require_once BD_PAYMENT_GATEWAYS_DIR_PATH . 'includes/Gateways/Nagad.php';
	}

	/**
	 * WooCommerce Error
	 * 
	 * 
	 */
	public function u_payments_g_woo_notice() {
		$message = sprintf(
			esc_html__(' %1$s requires %2$s to be installed and activated. Please activate %2$s to continue.', 'ultra-addons' ),
			'<strong>' . esc_html__( 'Bangladeshi Payment Gateways', 'bd-payment-gateways' ) . '</strong>',
			'<strong>' . esc_html__( 'WooCommerce', 'bd-payment-gateways' ) .'</strong>'
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Register payments gateway
	 *
	 * @since 1.0.0
     * 
     * @param array $methods Methods
	 */
	public function add_payment_methods( $gateways ) {
		$gateways[] = 'WC_BD_bKash_Gateway';
		$gateways[] = 'WC_BD_Rocket_Gateway';
		$gateways[] = 'WC_BD_Nagad_Gateway';
		return $gateways;
	}
	
	/**
	 * Action Links
	 * 
	 * @param array $links Action links array.
	 */
	public function bdpg_action_links( $links) {
		$settings[] = sprintf(
		'<a href="%s">%s</a>',
		admin_url( 'admin.php?page=wc-settings&tab=checkout' ),
		__( 'Payment Settings', 'woocommerce' )
		);
		
		return array_merge( $settings, $links);
	}
}