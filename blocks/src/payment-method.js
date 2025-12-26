/**
 * Payment Method Content Component
 *
 * @package BDPaymentGateways
 */

import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Payment method content component
 *
 * @param {Object} props Component props.
 * @param {Object} settings Gateway settings.
 * @return {JSX.Element}
 */
export const Content = ( props, settings ) => {
	const { eventRegistration, emitResponse } = props;
	const { onPaymentSetup } = eventRegistration;
	const [accNo, setAccNo] = useState( '' );
	const [transId, setTransId] = useState( '' );

	// Calculate converted amount if USD conversion is enabled
	// Use formatted values from PHP for consistent display
	const getConversionInfo = () => {
		if (
			settings.usd_conversion_enabled &&
			settings.store_currency === 'USD' &&
			settings.converted_amount
		) {
			return {
				original: settings.original_amount,
				converted: settings.converted_amount,
				rate: settings.usd_rate,
			};
		}
		return null;
	};

	const conversionInfo = getConversionInfo();

	// Handle payment processing
	useEffect( () => {
		const unsubscribe = onPaymentSetup( async () => {
			// Validation
			if ( ! accNo ) {
				return {
					type: emitResponse.responseTypes.ERROR,
					message: __(
						'Please enter your account number.',
						'bangladeshi-payment-gateways'
					),
					messageContext: emitResponse.noticeContexts.PAYMENTS,
				};
			}

			if ( ! transId ) {
				return {
					type: emitResponse.responseTypes.ERROR,
					message: __(
						'Please enter your transaction ID.',
						'bangladeshi-payment-gateways'
					),
					messageContext: emitResponse.noticeContexts.PAYMENTS,
				};
			}

			// Success - send payment data
			return {
				type: emitResponse.responseTypes.SUCCESS,
				meta: {
					paymentMethodData: {
						[ `${ settings.gateway }_acc_no` ]: accNo,
						[ `${ settings.gateway }_trans_id` ]: transId,
					},
				},
			};
		} );

		return () => unsubscribe();
	}, [ onPaymentSetup, accNo, transId, settings.gateway ] );

	return (
		<div className="bdpg-payment-method">
			{/* Gateway Icon */}
			{ settings.icon && (
				<div className="bdpg-gateway-icon">
					<img src={ settings.icon } alt={ settings.title || settings.gateway } />
				</div>
			) }

			{/* Description */}
			{ settings.description && (
				<div
					dangerouslySetInnerHTML={ {
						__html: settings.description,
					} }
				/>
			) }

			{/* Gateway charge details */}
			{ settings.gateway_charge_details && (
				<p
					dangerouslySetInnerHTML={ {
						__html: settings.gateway_charge_details,
					} }
				/>
			) }

			{/* Total Amount */}
			<div className="bdpg-total-amount">
				<p>
					<strong>
						{ __(
							'You need to send us',
							'bangladeshi-payment-gateways'
						) }
						{ ' ' }
					</strong>
					{ settings.formatted_total }
				</p>
			</div>

			{/* USD Conversion Info */}
			{ conversionInfo && settings.show_conversion_details && (
				<div className="bdpg-conversion-info">
					<small>
						{ __(
							'Converted from ',
							'bangladeshi-payment-gateways'
						) }
						{ conversionInfo.original }
						{ __(
							' at 1 USD = ',
							'bangladeshi-payment-gateways'
						) }
						{ conversionInfo.rate }
						{ ' BDT' }
					</small>
				</div>
			) }

			{/* Available merchant accounts */}
			{ settings.accounts && settings.accounts.length > 0 && (
				<div className="bdpg-available-accounts">
					{ settings.accounts.map( ( account, index ) => (
						<div key={ index } className="bdpg-s__acc">
							{/* QR Code */}
							{ account.qr_code && (
								<div className="bdpg-acc__qr-code">
									<img
										src={ account.qr_code }
										alt="QR Code"
									/>
								</div>
							) }

							{/* Account details */}
							<div className="bdpg-acc_d">
								<p>
									<strong>
										{ __(
											'Account Type:',
											'bangladeshi-payment-gateways'
										) }{ ' ' }
									</strong>
									{ account.type }
								</p>
								<p>
									<strong>
										{ __(
											'Account Number:',
											'bangladeshi-payment-gateways'
										) }{ ' ' }
									</strong>
									{ account.number }
								</p>
							</div>
						</div>
					) ) }

					{/* Customer input fields */}
					<div className="bdpg-user__acc">
						<div className="bdpg-user__field">
							<label htmlFor={ `${ settings.gateway }_acc_no` }>
								<strong>
									{ __(
										`Your ${ settings.gateway } Account Number`,
										'bangladeshi-payment-gateways'
									) }
								</strong>
							</label>
							<input
								type="text"
								id={ `${ settings.gateway }_acc_no` }
								className="wc-block-components-text-input"
								value={ accNo }
								onChange={ ( e ) =>
									setAccNo( e.target.value )
								}
								placeholder="01XXXXXXXXX"
							/>
						</div>

						<div className="bdpg-user__field">
							<label htmlFor={ `${ settings.gateway }_trans_id` }>
								<strong>
									{ __(
										`Your ${ settings.gateway } Transaction ID`,
										'bangladeshi-payment-gateways'
									) }
								</strong>
							</label>
							<input
								type="text"
								id={ `${ settings.gateway }_trans_id` }
								className="wc-block-components-text-input"
								value={ transId }
								onChange={ ( e ) =>
									setTransId( e.target.value )
								}
								placeholder="2M7A5"
							/>
						</div>
					</div>
				</div>
			) }
		</div>
	);
};
