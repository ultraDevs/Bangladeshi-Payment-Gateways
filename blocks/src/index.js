/**
 * Bangladeshi Payment Gateways - Checkout Block Integration
 *
 * @package BDPaymentGateways
 */

import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getPaymentMethodData } from '@woocommerce/settings';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';

import { Content } from './payment-method';
import { Label } from './label';

import './style.scss';

// Gateway IDs for all supported payment methods
const GATEWAY_IDS = [ 'woo_bkash', 'woo_rocket', 'woo_nagad', 'woo_upay' ];

// Register each payment method
GATEWAY_IDS.forEach( ( gatewayId ) => {
	const settings = getPaymentMethodData( gatewayId );

	// Debug logging
	if ( window.wcBlockBDPGDebug ) {
		console.log( `BDPG: Registering ${ gatewayId }`, settings );
	}

	if ( ! settings ) {
		return;
	}

	/**
	 * Content component
	 */
	const ContentComponent = ( props ) => {
		return Content( props, settings );
	};

	/**
	 * Label component
	 */
	const LabelComponent = ( props ) => {
		return Label( props, settings );
	};

	/**
	 * Register payment method
	 */
	registerPaymentMethod( {
		name: gatewayId,
		label: <LabelComponent />,
		content: <ContentComponent />,
		edit: <ContentComponent />,
		canMakePayment: () => true,
		ariaLabel: decodeEntities( settings.title || gatewayId ),
		supports: {
			features: settings.supports || [ 'products' ],
		},
	} );
} );
