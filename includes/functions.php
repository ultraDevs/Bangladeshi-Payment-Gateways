<?php
/**
 * Functions here.
 *
 * @package BDPaymentGateways
 */

function bdpg_get_instruction_by_gateway( $gateway ) {
    switch ( $gateway ) {
        case 'bkash':
            return __(
                '
                01. Go to your Rocket app or Dial *322#
                02. Choose “Send Money”
                03. Enter below Rocket Account Number
                04. Enter <b>total amount</b>
                06. Now enter your Rocket Account PIN to confirm the transaction
                07. Copy Transaction ID from payment confirmation message and paste that Transaction ID below',
                'bangladeshi-payment-gateways'
            );
        break;

        case 'rocket':
            return __(
                '
                01. Go to your Rocket app or Dial *322#
                02. Choose “Send Money”
                03. Enter below Rocket Account Number
                04. Enter <b>total amount</b>
                06. Now enter your Rocket Account PIN to confirm the transaction
                07. Copy Transaction ID from payment confirmation message and paste that Transaction ID below',
                'bangladeshi-payment-gateways'
            );
        break;

        case 'nagad':
            return __(
                '
                01. Go to your Nagad app or Dial *167#
                02. Choose “Send Money”
                03. Enter below Nagad Account Number
                04. Enter <b>total amount</b>
                06. Now enter your Nagad Account PIN to confirm the transaction
                07. Copy Transaction ID from payment confirmation message and paste that Transaction ID below',
                'bangladeshi-payment-gateways'
            );
        break;

        case 'upay':
            return __(
                '
                01. Go to your Upay app or Dial *268#
                02. Choose “Send Money”
                03. Enter below Upay Account Number
                04. Enter <b>total amount</b>
                06. Now enter your Upay Account PIN to confirm the transaction
                07. Copy Transaction ID from payment confirmation message and paste that Transaction ID below',
                'bangladeshi-payment-gateways'
            );
        break;

        default:
            return '';
    }

}