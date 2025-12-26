<?php
/**
 * Functions here.
 *
 * @package BDPaymentGateways
 */
function bdpg_get_instruction_by_gateway( $gateway ) {

	$instructions = array(
		'bkash'  => __(
			'01. Go to your bKash app or Dial *247#
02. Choose "Send Money"
03. Enter below bKash Account Number
04. Enter <b>total amount</b>
05. Now enter your bKash Account PIN to confirm the transaction
06. Copy Transaction ID from payment confirmation message and paste that Transaction ID below',
			'bangladeshi-payment-gateways'
		),
		'rocket' => __(
			'01. Go to your Rocket app or Dial *322#
02. Choose "Send Money"
03. Enter below Rocket Account Number
04. Enter <b>total amount</b>
05. Now enter your Rocket Account PIN to confirm the transaction
06. Copy Transaction ID from payment confirmation message and paste that Transaction ID below',
			'bangladeshi-payment-gateways'
		),
		'nagad'  => __(
			'01. Go to your Nagad app or Dial *167#
02. Choose "Send Money"
03. Enter below Nagad Account Number
04. Enter <b>total amount</b>
05. Now enter your Nagad Account PIN to confirm the transaction
06. Copy Transaction ID from payment confirmation message and paste that Transaction ID below',
			'bangladeshi-payment-gateways'
		),
		'upay'   => __(
			'01. Go to your Upay app or Dial *268#
02. Choose "Send Money"
03. Enter below Upay Account Number
04. Enter <b>total amount</b>
05. Now enter your Upay Account PIN to confirm the transaction
06. Copy Transaction ID from payment confirmation message and paste that Transaction ID below',
			'bangladeshi-payment-gateways'
		),
	);

	return isset( $instructions[ $gateway ] ) ? $instructions[ $gateway ] : '';
}

function bdpg_gateway_name_to_title( $gateway ) {
	switch ( $gateway ) {
		case 'bkash':
			return __( 'bKash', 'bangladeshi-payment-gateways' );
		case 'rocket':
			return __( 'Rocket', 'bangladeshi-payment-gateways' );
		case 'nagad':
			return __( 'Nagad', 'bangladeshi-payment-gateways' );
		case 'upay':
			return __( 'Upay', 'bangladeshi-payment-gateways' );
		default:
			return '';
	}
}

/**
 * Get USD to BDT exchange rate
 *
 * Returns the stored USD to BDT exchange rate from settings,
 * or the default rate if not set.
 *
 * @return float The USD to BDT exchange rate
 */
function bdpg_get_usd_rate() {
	$settings = get_option( 'bdpg_currency_settings', array() );
	return isset( $settings['usd_rate'] ) ? floatval( $settings['usd_rate'] ) : 123.00;
}

/**
 * Check if USD conversion is enabled
 *
 * @return bool True if USD conversion is enabled
 */
function bdpg_is_usd_conversion_enabled() {
	$settings = get_option( 'bdpg_currency_settings', array() );
	return isset( $settings['enable_usd_conversion'] ) && 'yes' === $settings['enable_usd_conversion'];
}

/**
 * Check if conversion details should be shown
 *
 * @return bool True if conversion details should be shown
 */
function bdpg_show_conversion_details() {
	$settings = get_option( 'bdpg_currency_settings', array() );
	return isset( $settings['show_conversion_details'] ) && 'yes' === $settings['show_conversion_details'];
}
