<?php
/**
 * Rocket Payment Gateway
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */

namespace ultraDevs\BDPG\Gateways;

use ultraDevs\BDPG\BDPG_Gateway;
use ultraDevs\BDPG\Traits\Singleton;

/**
 * Rocket Payment Gateway class.
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */
class Rocket extends BDPG_Gateway {
    use Singleton;

    public function __construct() {
        $this->gateway = 'rocket';

        parent::__construct();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_accounts' ) );
		add_action( 'woocommerce_thankyou_woo_' . $this->gateway, array( $this, 'bdpg_thankyou' ) );
		add_action( 'woocommerce_email_before_order_table', array( $this, 'customer_email_instructions' ), 10, 3 );

		add_action( 'woocommerce_checkout_process', array( $this, 'payment_process' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'fields_update' ) );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'admin_order_data' ) );

		$settings = get_option( 'woocommerce_woo_' . $this->gateway . '_settings' );
		if ( isset( $settings['gateway_charge'] ) && 'yes' === $settings['gateway_charge'] ) {
			add_action( 'woocommerce_cart_calculate_fees', array( $this, 'charge_settings' ), 20, 1 );
		}

		add_action( 'woocommerce_order_details_after_customer_details', array( $this, 'data_order_review_page' ) );
		add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'admin_register_column' ), 20 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'admin_column_value' ), 20, 2 );
    }

}
