<?php
/**
 * Bangla QR Payment Gateway
 *
 * @package BDPaymentGateways
 * @since 4.0.5
 */

namespace ultraDevs\BDPG\Gateways;

use ultraDevs\BDPG\BDPG_Gateway;
use ultraDevs\BDPG\Traits\Singleton;

/**
 * Bangla QR Payment Gateway class.
 *
 * @package BDPaymentGateways
 * @since 4.0.5
 */
class Bangla_QR extends BDPG_Gateway {
	use Singleton;

	/**
	 * QR Code image URL.
	 *
	 * @var string
	 */
	public $qr_code = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->gateway = 'bangla_qr';

		parent::__construct();

		$this->qr_code = $this->get_option( 'qr_code' );
	}

	/**
	 * Initialize Gateway Titles
	 */
	public function init_gateway_titles() {
		$this->method_description = __( 'Bangla QR Payment Gateway Settings. Allow customers to make payments by scanning your Bangla QR code.', 'bangladeshi-payment-gateways' );
		$this->method_title       = __( 'Bangla QR', 'bangladeshi-payment-gateways' );
	}

	/**
	 * Gateway Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'      => array(
				'title'       => __( 'Enable/Disable', 'bangladeshi-payment-gateways' ),
				'label'       => __( 'Enable Bangla QR Gateway', 'bangladeshi-payment-gateways' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'        => array(
				'title'       => __( 'Title', 'bangladeshi-payment-gateways' ),
				'type'        => 'text',
				'default'     => __( 'Bangla QR', 'bangladeshi-payment-gateways' ),
				'description' => __( 'Payment method title that the customer will see on checkout.', 'bangladeshi-payment-gateways' ),
				'desc_tip'    => true,
			),
			'description'  => array(
				'title'       => __( 'Description', 'bangladeshi-payment-gateways' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on checkout.', 'bangladeshi-payment-gateways' ),
				'desc_tip'    => true,
				'default'     => bdpg_get_instruction_by_gateway( $this->gateway ),
			),
			'qr_code'      => array(
				'title'       => __( 'Bangla QR Code', 'bangladeshi-payment-gateways' ),
				'type'        => 'qr_code',
				'description' => __( 'Upload your Bangla QR code image.', 'bangladeshi-payment-gateways' ),
				'default'     => '',
			),
			'instructions' => array(
				'title'       => __( 'Instructions', 'bangladeshi-payment-gateways' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page and order emails.', 'bangladeshi-payment-gateways' ),
				'default'     => '',
			),
		);
	}

	/**
	 * Generate QR Code upload field HTML
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @return string
	 */
	public function generate_qr_code_html( $key = '', $data = array() ) {
		$field_key = $this->get_field_key( empty( $key ) ? 'qr_code' : $key );
		$defaults  = array(
			'title'       => __( 'Bangla QR Code', 'bangladeshi-payment-gateways' ),
			'description' => __( 'Upload your Bangla QR code image.', 'bangladeshi-payment-gateways' ),
		);

		$data  = wp_parse_args( $data, $defaults );
		$value = $this->get_option( empty( $key ) ? 'qr_code' : $key );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $data['title'] ); ?>:</label>
			</th>
			<td class="forminp" id="bangla_qr_setting_field">
				<input type="hidden" name="<?php echo esc_attr( $field_key ); ?>" id="bdpg_bangla_qr_code_input" value="<?php echo esc_attr( $value ); ?>" />
				<div id="bdpg_bangla_qr_img" style="margin-bottom: 10px;">
					<?php if ( ! empty( $value ) ) : ?>
						<img src="<?php echo esc_url( $value ); ?>" alt="Bangla QR Code" style="max-width: 160px; height: auto; border: 1px solid #ccd0d4; padding: 6px; border-radius: 6px; background: #fff; display: block;" />
					<?php endif; ?>
				</div>
				<input type="button" class="button button-primary add_qr_c_img" value="<?php echo ! empty( $value ) ? esc_attr__( 'Edit Image', 'bangladeshi-payment-gateways' ) : esc_attr__( 'Add Image', 'bangladeshi-payment-gateways' ); ?>" data-target="#bdpg_bangla_qr_code_input" data-qr="#bdpg_bangla_qr_img" />
				<button type="button" class="button button-link-delete bdpg-remove-qr-btn" data-target="#bdpg_bangla_qr_code_input" data-qr="#bdpg_bangla_qr_img" style="color: #a00; margin-left: 10px; vertical-align: middle; <?php echo empty( $value ) ? 'display: none;' : ''; ?>">
					<?php esc_html_e( 'Remove QR Code', 'bangladeshi-payment-gateways' ); ?>
				</button>
				<?php if ( ! empty( $data['description'] ) ) : ?>
					<p class="description" style="margin-top: 8px;"><?php echo esc_html( $data['description'] ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Payment Fields on Checkout Page
	 */
	public function payment_fields() {
		global $woocommerce;

		echo wpautop( wptexturize( __( $this->description, 'bangladeshi-payment-gateways' ) ) );

		$total_payment   = $woocommerce->cart->total;
		$symbol          = get_woocommerce_currency_symbol();
		$original_amount = $woocommerce->cart->total;
		$original_symbol = get_woocommerce_currency_symbol();
		$show_conversion = false;

		if ( bdpg_is_usd_conversion_enabled() && get_woocommerce_currency() === 'USD' ) {
			$total_payment   = bdpg_get_usd_rate() * $woocommerce->cart->total;
			$symbol          = html_entity_decode( get_woocommerce_currency_symbol( 'BDT' ) );
			$original_symbol = html_entity_decode( $original_symbol );
			$show_conversion = true;
		}

		$total_amount = sprintf(
			/* translators: %s: Total Payment. */
			__( 'You need to send us <b>%s</b>', 'bangladeshi-payment-gateways' ),
			$symbol . $total_payment
		);

		// Add conversion details if enabled.
		if ( $show_conversion && bdpg_show_conversion_details() ) {
			$usd_rate      = bdpg_get_usd_rate();
			$total_amount .= '</br><small>' . sprintf(
				/* translators: 1: Original amount, 2: Exchange rate. */
				__( 'Converted from %1$s at 1 USD = %2$s BDT', 'bangladeshi-payment-gateways' ),
				$original_symbol . $original_amount,
				$usd_rate
			) . '</small>';
		} else {
			$total_amount .= '</br>';
		}

		echo '<div class="bdpg-total-amount">' . $total_amount . '</div>';
		?>
		<div class="bdpg-available-accounts bdpg-bangla-qr-wrapper">
			<?php if ( ! empty( $this->qr_code ) ) : ?>
				<div class="bdpg-s__acc bdpg-bangla-qr-acc">
					<div class="bdpg-acc__qr-code bdpg-bangla-qr-image-wrap">
						<img src="<?php echo esc_url( $this->qr_code ); ?>" alt="Bangla QR Code">
					</div>
					<div class="bdpg-acc_d bdpg-bangla-qr-acc-desc">
						<p><b><?php esc_html_e( 'Scan with any Bank or MFS App', 'bangladeshi-payment-gateways' ); ?></b></p>
					</div>
				</div>
			<?php endif; ?>

			<div class="bdpg-user__acc">
				<div class="bdpg-user__field">
					<label for="<?php echo esc_attr( $this->gateway ); ?>_acc_no">
						<?php esc_html_e( 'Your Phone Number', 'bangladeshi-payment-gateways' ); ?>
					</label>
					<input type="text" class="widefat" name="<?php echo esc_attr( $this->gateway ); ?>_acc_no" placeholder="01XXXXXXXXX">
				</div>
				<div class="bdpg-user__field">
					<label for="<?php echo esc_attr( $this->gateway ); ?>_trans_id">
						<?php esc_html_e( 'Your Transaction ID', 'bangladeshi-payment-gateways' ); ?>
					</label>
					<input type="text" class="widefat" name="<?php echo esc_attr( $this->gateway ); ?>_trans_id" placeholder="XXXXXXXXXX">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Field Validation.
	 */
	public function payment_process() {
		if ( 'woo_' . $this->gateway !== $_POST['payment_method'] ) {
			return;
		}

		$number   = isset( $_POST[ $this->gateway . '_acc_no' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->gateway . '_acc_no' ] ) ) : '';
		$trans_id = isset( $_POST[ $this->gateway . '_trans_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->gateway . '_trans_id' ] ) ) : '';

		if ( '' === $number ) {
			wc_add_notice(
				esc_html__( 'Please enter your phone number.', 'bangladeshi-payment-gateways' ),
				'error'
			);
		}

		// Validate account/phone number - must be numeric only.
		if ( '' !== $number && ! preg_match( '/^[0-9]+$/', $number ) ) {
			wc_add_notice(
				esc_html__( 'Please enter a valid phone number (numbers only).', 'bangladeshi-payment-gateways' ),
				'error'
			);
		}

		if ( '' === $trans_id ) {
			wc_add_notice(
				esc_html__( 'Please enter your transaction ID.', 'bangladeshi-payment-gateways' ),
				'error'
			);
		}
	}

	/**
	 * Display Gateway data in admin order page.
	 *
	 * @param \WC_Order $order Order.
	 */
	public function admin_order_data( $order ) {
		if ( 'woo_' . $this->gateway !== $order->get_payment_method() ) {
			return;
		}

		$number   = $this->bdpg_get_order_meta( $order, 'woo_' . $this->gateway . '_number', true );
		$trans_id = $this->bdpg_get_order_meta( $order, 'woo_' . $this->gateway . '_trans_id', true );
		?>
		<div class="form-field form-field-wide bdpg-admin-data">
			<img src="<?php echo esc_url( $this->icon ); ?> " alt="<?php echo esc_attr( $this->gateway ); ?>">
			<table class="wp-list-table widefat striped posts">
				<tbody>
					<tr>
						<th>
							<strong>
								<?php esc_html_e( 'Phone Number', 'bangladeshi-payment-gateways' ); ?>
							</strong>
						</th>
						<td>
							<?php echo esc_attr( $number ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<strong>
								<?php echo esc_html__( 'Transaction ID', 'bangladeshi-payment-gateways' ); ?>
							</strong>
						</th>
						<td>
							<?php echo esc_attr( $trans_id ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Display Gateway data in customer order review page.
	 *
	 * @param \WC_Order $order Order.
	 */
	public function data_order_review_page( $order ) {
		if ( 'woo_' . $this->gateway !== $order->get_payment_method() ) {
			return;
		}

		$number   = $this->bdpg_get_order_meta( $order, 'woo_' . $this->gateway . '_number', true );
		$trans_id = $this->bdpg_get_order_meta( $order, 'woo_' . $this->gateway . '_trans_id', true );
		?>
		<div class="bdpg-g-details">
			<img src="<?php echo esc_url( $this->icon ); ?> " alt="<?php echo esc_attr( $this->gateway ); ?>">
			<table class="wp-list-table widefat striped posts">
				<tbody>
					<tr>
						<th>
							<strong>
								<?php esc_html_e( 'Phone Number', 'bangladeshi-payment-gateways' ); ?>
							</strong>
						</th>
						<td>
							<?php echo esc_attr( $number ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<strong>
								<?php echo esc_html__( 'Transaction ID', 'bangladeshi-payment-gateways' ); ?>
							</strong>
						</th>
						<td>
							<?php echo esc_attr( $trans_id ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
