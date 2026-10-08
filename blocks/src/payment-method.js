/**
 * Payment Method Content Component
 *
 * @package BDPaymentGateways
 */

import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

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

	const isBanglaQR = settings.gateway === 'bangla_qr';

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
					message: isBanglaQR
						? __(
							'Please enter your phone number.',
							'bangladeshi-payment-gateways'
						)
						: __(
							'Please enter your account number.',
							'bangladeshi-payment-gateways'
						),
					messageContext: emitResponse.noticeContexts.PAYMENTS,
				};
			}

			// Validate account number - must be numeric only.
			if ( accNo && !/^[0-9]+$/.test( accNo ) ) {
				return {
					type: emitResponse.responseTypes.ERROR,
					message: isBanglaQR
						? __(
							'Please enter a valid phone number (numbers only).',
							'bangladeshi-payment-gateways'
						)
						: __(
							'Please enter a valid account number (numbers only).',
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
	}, [ onPaymentSetup, accNo, transId, settings.gateway, isBanglaQR ] );

	const accLabel = isBanglaQR
		? __( 'Your Phone Number', 'bangladeshi-payment-gateways' )
		: sprintf(
			/* translators: %s: Payment Gateway */
			__( 'Your %s Account Number', 'bangladeshi-payment-gateways' ),
			settings.title || settings.gateway
		);

	const transLabel = isBanglaQR
		? __( 'Your Transaction ID', 'bangladeshi-payment-gateways' )
		: sprintf(
			/* translators: %s: Payment Gateway */
			__( 'Your %s Transaction ID', 'bangladeshi-payment-gateways' ),
			settings.title || settings.gateway
		);

	return (
		<div className="bdpg-payment-method">

			{/* Description */}
			{ settings.description && (
				<p
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

			{/* Bangla QR Code Display */}
			{ isBanglaQR && settings.qr_code && (
				<div className="bdpg-available-accounts bdpg-bangla-qr-checkout">
					<div className="bdpg-s__acc bdpg-bangla-qr-box">
						<div className="bdpg-acc__qr-code">
							<img
								src={ settings.qr_code }
								alt="Bangla QR Code"
							/>
						</div>
						<div className="bdpg-acc_d" style={ { textAlign: 'center' } }>
							<p>
								<strong>
									{ __(
										'Scan with any Bank or MFS App',
										'bangladeshi-payment-gateways'
									) }
								</strong>
							</p>
						</div>
					</div>
				</div>
			) }

			{/* Available merchant accounts for other gateways */}
			{ ! isBanglaQR && settings.accounts && settings.accounts.length > 0 && (
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
				</div>
			) }

			{/* Customer input fields */}
			<div className="bdpg-user__acc">
				<div className="bdpg-user__field">
					<label htmlFor={ `${ settings.gateway }_acc_no` }>
						<strong>{ accLabel }</strong>
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
						<strong>{ transLabel }</strong>
					</label>
					<input
						type="text"
						id={ `${ settings.gateway }_trans_id` }
						className="wc-block-components-text-input"
						value={ transId }
						onChange={ ( e ) =>
							setTransId( e.target.value )
						}
						placeholder={ isBanglaQR ? 'XXXXXXXXXX' : '2M7A5' }
					/>
				</div>
			</div>
		</div>
	);
};
