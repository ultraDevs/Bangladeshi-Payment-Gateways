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

		return array(
			'title'                  => isset( $this->settings['title'] ) ? $this->settings['title'] : \bdpg_gateway_name_to_title( $this->gateway ),
			'description'            => isset( $this->settings['description'] ) ? $this->settings['description'] : \bdpg_get_instruction_by_gateway( $this->gateway ),
			'gateway_charge_details' => $gateway_charge_details,
			'gateway'                => $this->gateway,
			'accounts'               => $this->accounts,
			'icon'                   => BD_PAYMENT_GATEWAYS_DIR_URL . 'assets/images/' . ucfirst( $this->gateway ) . '.png',
			'supports'               => array( 'products' ),
		);
	}
}
