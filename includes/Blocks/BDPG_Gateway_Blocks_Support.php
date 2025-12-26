<?php
/**
 * BDPG Gateway Blocks Support Class
 *
 * Abstract class for WooCommerce Checkout Block integration.
 *
 * @package BDPaymentGateways
 * @since 3.0.6
 */

namespace ultraDevs\BDPG\Blocks;

/**
 * BDPG_Gateway_Blocks_Support Class
 *
 * @package BDPaymentGateways
 * @since 3.0.6
 */
abstract class BDPG_Gateway_Blocks_Support extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {

	/**
	 * Gateway
	 *
	 * @var string
	 */
	protected $gateway = '';

	/**
	 * Payment method name/id.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Settings
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Gateway Accounts
	 *
	 * @var array
	 */
	protected $accounts = array();

	/**
	 * Initialize
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_' . $this->name . '_settings', array() );
		$this->accounts = get_option( 'bdpg_' . $this->gateway . '_accounts', array() );
	}

	/**
	 * Check if the gateway is active
	 *
	 * @return bool
	 */
	public function is_active() {
		return ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
	}

	/**
	 * Get payment method script handles
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_path = BD_PAYMENT_GATEWAYS_DIR_URL . 'dist/bdpg-blocks.js';
		$asset_file  = BD_PAYMENT_GATEWAYS_DIST_PATH . 'bdpg-blocks.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return array();
		}

		$asset = require $asset_file;

		wp_register_script(
			'bdpg-blocks',
			$script_path,
			$asset['dependencies'],
			$asset['version'],
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'bdpg-blocks', 'bangladeshi-payment-gateways' );
		}

		return array( 'bdpg-blocks' );
	}

	/**
	 * Get payment method data to pass to frontend
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$gateway_charge_details = '';

		if ( isset( $this->settings[ $this->gateway . '_charge' ] ) && 'yes' === $this->settings[ $this->gateway . '_charge' ] ) {
			$gateway_charge_details = isset( $this->settings['gateway_charge_details'] ) ? $this->settings['gateway_charge_details'] : '';
		}

		// Get cart subtotal for calculation.
		$cart_subtotal = 0;
		if ( function_exists( 'WC' ) ) {
			$cart = WC()->cart;
			if ( $cart ) {
				$cart_subtotal = $cart->cart_contents_total;
			}
		}

		// Calculate gateway fee if enabled.
		$gateway_fee = 0;
		if ( isset( $this->settings[ $this->gateway . '_charge' ] ) && 'yes' === $this->settings[ $this->gateway . '_charge' ] ) {
			$fee_percent = isset( $this->settings[ $this->gateway . '_fee' ] ) ? floatval( $this->settings[ $this->gateway . '_fee' ] ) : 0;
			$gateway_fee = round( $cart_subtotal * ( $fee_percent / 100 ) );
		}

		// Calculate total payment amount (including gateway fee).
		$total_payment   = $cart_subtotal + $gateway_fee;
		$original_amount = $cart_subtotal + $gateway_fee;
		$show_conversion = false;

		// Check if USD conversion is enabled.
		if ( \bdpg_is_usd_conversion_enabled() && get_woocommerce_currency() === 'USD' ) {
			$total_payment   = \bdpg_get_usd_rate() * ( $cart_subtotal + $gateway_fee );
			$show_conversion = true;
		}

		// Get currency symbols and decode HTML entities.
		$store_currency_symbol = html_entity_decode( get_woocommerce_currency_symbol() );
		$bdt_currency_symbol   = html_entity_decode( get_woocommerce_currency_symbol( 'BDT' ) );
		$original_symbol       = $store_currency_symbol;

		// Format display total.
		if ( $show_conversion ) {
			$formatted_total = $bdt_currency_symbol . number_format( $total_payment, 2, '.', '' );
		} else {
			$formatted_total = $store_currency_symbol . number_format( $total_payment, 2, '.', '' );
		}

		// Format original amount for conversion info.
		$formatted_original = $original_symbol . number_format( $original_amount, 2, '.', '' );

		// Get description and convert newlines to <br> tags for block display.
		$description = isset( $this->settings['description'] ) ? $this->settings['description'] : \bdpg_get_instruction_by_gateway( $this->gateway );
		$description = nl2br( $description );

		return array(
			'title'                   => isset( $this->settings['title'] ) ? $this->settings['title'] : \bdpg_gateway_name_to_title( $this->gateway ),
			'description'             => $description,
			'gateway_charge_details'  => $gateway_charge_details,
			'gateway'                 => $this->gateway,
			'accounts'                => $this->accounts,
			'icon'                    => BD_PAYMENT_GATEWAYS_DIR_URL . 'assets/images/' . ucfirst( $this->gateway ) . '.png',
			'supports'                => array( 'products' ),
			'usd_conversion_enabled'  => \bdpg_is_usd_conversion_enabled(),
			'show_conversion_details' => \bdpg_show_conversion_details(),
			'usd_rate'                => \bdpg_get_usd_rate(),
			'store_currency'          => get_woocommerce_currency(),
			'store_currency_symbol'   => $store_currency_symbol,
			'bdt_currency_symbol'     => $bdt_currency_symbol,
			'cart_total'              => $cart_subtotal + $gateway_fee,
			'formatted_total'         => $formatted_total,
			'original_amount'         => $formatted_original,
			'converted_amount'        => $show_conversion ? $bdt_currency_symbol . number_format( $total_payment, 2, '.', '' ) : null,
		);
	}
}
