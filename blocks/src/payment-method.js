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
	}, [ onPaymentSetup, accNo, transId, settings.gateway, emitResponse ] );

	return (
		<div className="bdpg-payment-method">
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
