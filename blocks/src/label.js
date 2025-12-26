/**
 * Label Component
 *
 * @package BDPaymentGateways
 */

import { PaymentMethodLabel } from '@woocommerce/blocks-components';
import { decodeEntities } from '@wordpress/html-entities';

/**
 * Label component for payment method
 *
 * @param {Object} props Component props.
 * @param {Object} settings Gateway settings.
 * @return {JSX.Element}
 */
export const Label                      = ( props, settings ) => {
	const { PaymentMethodLabel: Label } = props.components;

	return (
		<>
			<Label text = { decodeEntities( settings.title || settings.gateway ) } />
			{ settings.icon && (
				<span className="bdpg-gateway-icon">
					<img src={ settings.icon } alt={ settings.title || settings.gateway } />
				</span>
			) }
		</>
	);
};
