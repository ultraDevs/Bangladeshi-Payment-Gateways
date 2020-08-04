<?php
/**
 * Rocket Functionality
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Rocket class
 *
 * This class is for enqueuing styles, scripts
 *
 * @package BDPaymentGateways
 * @since 1.0.0
 */
class WC_BD_Rocket_Gateway extends WC_Payment_Gateway {
	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->id = 'woo_rocket';
		$this->icon = apply_filters( 'woocommerce_bdpg_Rocket_icon', BD_PAYMENT_GATEWAYS_DIR_URL . '/assets/images/Rocket.png' );
		$this->has_fields = true;
		$this->method_description = __( 'Rocket Payment Gateway Settings.', 'bd-payment-gateways' );
		$this->method_title = __( 'Rocket', 'bd-payment-gateways' );

		// $this->supports = array(
		// 	'products'
		// );

		$this->init_form_fields();

		// Load the Settings.
		$this->init_settings();
		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->enabled = $this->get_option( 'enabled' );
		$this->instructions = $this->get_option( 'instructions', $this->description );
		$this->rocket_charge = $this->get_option( 'rocket_charge' );
		$this->rocket_fee = $this->get_option( 'rocket_fee' );
		$this->rocket_charge_details = $this->get_option( 'rocket_charge_details' );

		$this->all_account = array(
			array(
				'type' => $this->get_option( 'type' ),
				'number' => $this->get_option( 'number' ),
				'qr_code' => $this->get_option( 'qr_code' ),
			)
		);
		$this->accounts = get_option( 'bdpg_rocket_accounts', $this->all_account );
		
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_accounts' ) );
		add_action( 'woocommerce_thankyou_woo_rocket', array( $this, 'woo_rocket_thankyou' ));
		add_action( 'woocommerce_email_before_order_table', array( $this, 'woo_rocket_customer_email_instructions' ), 10, 3);
	}

	/**
	 * Gateway Fields
	 */
	public function init_form_fields(){
		$this->form_fields = array(
			'enabled' => array(
				'title'       => __( 'Enable/Disable', 'bd-payment-gateways'),
				'label'       => __( 'Enable Rocket Gateway', 'bd-payment-gateways'),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
			'title' => array(
				'title'       => __( 'Title', 'bd-payment-gateways' ),
				'type'        => 'text',
				'default'     => 'Rocket',
				'description' => __( 'Title', 'bd-payment-gateways' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'bd-payment-gateways'),
				'default'     => 'Description here. ',
				'type'        => 'textarea',
			),
			'rocket_charge' => array(
				'title'       => __( 'Rocket Charge?', 'bd-payment-gateways'),
				'type'        => 'checkbox',
				'description' => __( 'Add Rocket <b>Send Money</b> charge.', 'bd-payment-gateways'),
				'default' => 'no'
			),

			'rocket_fee' => array(
				'title'       => __( 'Rocket Fee? (in %)', 'bd-payment-gateways' ),
				'type'        => 'text',
				'default'     => '1.8',
				'description' => __( 'Don\'t add %.', 'bd-payment-gateways' ),
			),

			'rocket_charge_details' => array(
				'title'       => __( 'Rocket Charge Details', 'bd-payment-gateways'),
				'type'        => 'textarea',
				'default' => __( 'Rocket "Send Money" fee will be added with net price.'),
			),

			'instructions' => array(
				'title'       => __( 'Instructions', 'bd-payment-gateways'),
				'type'        => 'textarea',
				'description' => __( 'Instructions', 'bd-payment-gateways'),
				'default'     => 'Instructions',
			),
			'accounts' => array(
				'type' => 'accounts'
			)
		);

	}

	/**
	 * Payment Fields
	 */
	public function payment_fields() {
		global $woocommerce;

		$rocket_charge_details = ( 'yes' == $this->rocket_charge ) ? $this->rocket_charge_details : '';
		echo wpautop( wptexturize( esc_html__( $this->description, 'bd-payment-gateways')) . ' ' . $rocket_charge_details );

		$total_amount = 'You need to send us <b>' . get_woocommerce_currency_symbol() . $woocommerce->cart->total . '</b>';
		echo '<div class="bdpg-total-amount">' .  $total_amount . '</div>';
		?>
		<div class="bdpg-available-accounts">
			<?php
				foreach ( $this->accounts as $account ) {
					?>
					<div class="bdpg-s__acc">
						<?php
							if ( '' !== $account['qr_code'] ) {
								?>
						<div class="bdpg-acc__qr-code">
							<img src="<?php echo $account['qr_code'];?>" alt="QR Code">
						</div>
								<?php
							}
						?>
						<div class="bdpg-acc_d">
							<p>Account Type: <b><?php echo $account['type']; ?></b></p>
							<p>Account Number: <b><?php echo $account['number'];?></b> </p>
						</div>
					</div>
					<?php
				}
			?>
			<div class="bdpg-user__acc">
				<div class="bdpg-user__field">
					<label for="rocket_acc_no">
						Your Rocket Account Number
					</label>
					<input type="text" class="widefat" name="rocket_acc_no" placeholder="01XXXXXXXXX">
				</div>
				<div class="bdpg-user__field">
					<label for="rocket_trans_id">
						Rocket Transaction ID
					</label>
					<input type="text" class="widefat" name="rocket_trans_id" placeholder="2M7A5">
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Accounts Fields
	 */
	public function generate_accounts_html() {
        ob_start();
    ?>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'Account Details', 'woocommerce' ); ?>:</th>
			<td class="forminp" id="rocket_accounts">
				<table class="widefat wc_input_table sortable" cellspacing="0">
					<thead>
						<tr>
							<th class="sort">&nbsp;</th>
							<th><?php _e( 'Account Type', 'woocommerce' ); ?></th>
							<th><?php _e( 'Account Number', 'woocommerce' ); ?></th>
							<th><?php _e( 'QR Code', 'woocommerce' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="7"><a href="#" class="add button"><?php _e( '+ Add Account', 'woocommerce' ); ?></a> <a href="#" class="remove_rows button"><?php _e( 'Remove selected account(s)', 'woocommerce' ); ?></a></th>
						</tr>
					</tfoot>
					<tbody class="accounts ui-sortable">
					<?php
						$i = -1;
						if ( $this->accounts ) {
							foreach ( $this->accounts as $account ) {
								$i++;
								echo '<tr class="account">
								<td class="sort"></td>
								<td><input type="text" value="' . esc_attr( $account['type'] ) . '" name="rocket_account_type[' . $i . ']" /></td>
								<td><input type="text" value="' . esc_attr( $account['number'] ) . '" name="rocket_account_number[' . $i . ']" /></td><td><input type="hidden" value="' . esc_attr( $account['qr_code'] ) . '" name="rocket_account_qr_code[' . $i . ']" id="bdpg_qr_code-' . $i . '" />
								<input type="button" class="button button-primary add_qr_c_img" value="Edit Image" data-target="#bdpg_qr_code-' . $i . '"  data-qr="#bdpg_qr_img-' . $i . '"><div  id="bdpg_qr_img-' . $i . '"><img src="' . esc_attr( $account['qr_code'] ) . '" alt="QR Code" id="qr_code" /></div>
								</td>
								</tr>';
							}
						}
						?>
					</tbody>
				</table>
				<script>
					
					jQuery(function($) {
						$('#rocket_accounts').on( 'click', 'a.add', function(){

							var size = $('#rocket_accounts').find('tbody .account').length;

							$('<tr class="account">\
									<td class="sort"></td>\
									<td><input type="text" name="rocket_account_type[' + size + ']" /></td>\
									<td><input type="text" name="rocket_account_number[' + size + ']" /></td>\
									<td><input type="hidden" id="bdpg_qr_code-' + size + '" name="rocket_account_qr_code[' + size + ']" /><input type="button" class="button button-primary add_qr_c_img" value="Add Image" data-target="#bdpg_qr_code-' + size + '" data-qr="#bdpg_qr_img-' + size + '"><div id="bdpg_qr_img-' + size + '"></div>\
									</td>\
								</tr>').appendTo('#rocket_accounts table tbody');

							return false;
						});
						
					});
				
				</script>
			</td>
		</tr>
<?php
    return ob_get_clean();
	}
	
	/**
	 * Save Accounts
	 * 
	 */
	public function save_accounts() {
		
		if ( isset( $_POST['rocket_account_type' ] ) ) {
			$accounts = array();

			$type = array_map( 'wc_clean', $_POST['rocket_account_type'] );
			$number = array_map( 'wc_clean', $_POST['rocket_account_number'] );
			$qr_code = array_map( 'wc_clean', $_POST['rocket_account_qr_code'] );

			foreach ( $type as $key => $value ) {
				if ( !isset( $type[$key] ) ) continue;

				$accounts[] = array(
					'type' => $type[$key],
					'number' => $number[$key],
					'qr_code' => $qr_code[$key],
				);
			}
			update_option( 'bdpg_rocket_accounts', $accounts );
		}
	
	}

	/**
	 * Process Payment
	 * 
	 * @param int $order_id Order ID
	 */
	public function process_payment( $order_id ){
		global $woocommerce;

		$order = new WC_Order( $order_id);

		// Mark as on-hold (we're awaiting the cheque)
		$order->update_status('on-hold', __( 'Awaiting Rocket payment', 'woocommerce' ));

		// Reduce stock levels.
		$order->reduce_order_stock();
		
		// Remove cart
		$woocommerce->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		);
	}

	/**
	 * Thank You Page
	 */
	public function woo_rocket_thankyou($order_id ) {

		$order = new WC_Order( $order_id);
		
		if ( 'woo_rocket' == $order->get_payment_method() ) {
			echo $this->instructions;
		} else {
			echo esc_html__( 'Thank you. Your order has been received.', "woocommerce" );
		}
	}

	/**
	 * Customer Email
	 */
	public function woo_rocket_customer_email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->id !== $order->get_payment_method() || $sent_to_admin ) return;

		if ( $this->instructions ) {
			echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
		}
	}
}

/**
 * Field Validation
 */
add_action( 'woocommerce_checkout_process', 'woo_rocket_payment_process' );
if ( ! function_exists( 'woo_rocket_payment_process' ) ) {
	function woo_rocket_payment_process() {
		if ( 'woo_rocket' !== $_POST['payment_method'] ) return;
		$number = sanitize_text_field( $_POST['rocket_acc_no'] );
		$trans_id = sanitize_text_field( $_POST['rocket_trans_id'] );

		if ( '' == $number ) {
			wc_add_notice( __( 'Please enter your Rocket number.', 'bd-payment-gateways'), 'error');
		}

		if ( '' == $trans_id ) {
			wc_add_notice( __( 'Please enter your Rocket transaction ID.', 'bd-payment-gateways'), 'error');
		}
	}
}

/**
 * Rocket Field Update
 */
add_action( 'woocommerce_checkout_update_order_meta', 'woo_Rocket_fields_update' );
if ( ! function_exists( 'woo_Rocket_fields_update' ) ) {
	function woo_Rocket_fields_update( $order_id) {

		if ( 'woo_rocket' !== $_POST['payment_method'] ) return;
		$number = sanitize_text_field( $_POST['rocket_acc_no'] );
		$trans_id = sanitize_text_field( $_POST['rocket_trans_id'] );

		update_post_meta( $order_id, 'woo_rocket_number', $number );
		update_post_meta( $order_id, 'woo_rocket_trans_id', $trans_id );
	}
}

/**
 * Display Rocket data in admin page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'woo_rocket_admin_order_data');

if ( ! function_exists( 'woo_rocket_admin_order_data' ) ) {
	function woo_rocket_admin_order_data( $order) {
		if ( 'woo_rocket' !== $order->get_payment_method() ) return;

		$number = ( get_post_meta( $_GET['post'], 'woo_rocket_number', true) ) ? get_post_meta( $_GET['post'], 'woo_rocket_number', true) : '';
		$trans_id = ( get_post_meta( $_GET['post'], 'woo_rocket_trans_id', true) ) ? get_post_meta( $_GET['post'], 'woo_rocket_trans_id', true) : '';
		?>
		<div class="form-field form-field-wide bdpg-admin-data">
			<img src="<?php echo BD_PAYMENT_GATEWAYS_DIR_URL . '/assets/images/Rocket.png';?> " alt="Rocket">
			<table class="wp-list-table widefat fixed striped posts">
				<tbody>
					<tr>
						<th>
							<strong>
								<?php echo __( 'Rocket Number', 'bd-payment-gateways');?>
							</strong>
						</th>
						<td>
							<?php echo esc_attr( $number );?>
						</td>
					</tr>
					<tr>
						<th>
							<strong>
								<?php echo __( 'Transaction ID', 'bd-payment-gateways');?>
							</strong>
						</th>
						<td>
							<?php echo esc_attr( $trans_id );?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}

$rocket_settings = get_option( 'woocommerce_woo_rocket_settings' );
if ( 'yes' == $rocket_settings['rocket_charge'] ) {
	add_action( 'woocommerce_cart_calculate_fees', 'bdpg_Rocket_charge_settings', 20, 1 );
}

/**
 * Check if Rocket charge status.
 * 
 * @param Object $cart Cart
 */

if ( ! function_exists( 'bdpg_Rocket_charge_settings' ) ) {
	function bdpg_Rocket_charge_settings( $cart ) {
		global $woocommerce;
		$rocket_settings = get_option( 'woocommerce_woo_rocket_settings' );

		$av_gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
		if ( !empty( $av_gateways ) ) {

			$payment_method = WC()->session->get( 'chosen_payment_method' );

			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

			if( $payment_method == 'woo_rocket' ) {
				$label = __( 'Rocket Charge', 'bd-payment-gateways' );
				$amount = round( $cart->cart_contents_total * ( $rocket_settings['rocket_fee'] / 100 ) );
				$cart->add_fee( $label, $amount, true, 'standard' );
			}
		}
	}
}

/**
 * Display Rocket data in order review page
 */
add_action( 'woocommerce_order_details_after_customer_details', 'woo_rocket_data_order_review_page');

if ( ! function_exists( 'woo_rocket_data_order_review_page' ) ) {
	function woo_rocket_data_order_review_page( $order) {
		if ( 'woo_rocket' !== $order->get_payment_method() ) return;
		global $wp;

		if ( $wp->query_vars['order-received'] ) {
			$order_id = (int) $wp->query_vars['order-received'];
		} else {
			$order_id = (int) $wp->query_vars['view-order'];
		}

		$number = ( get_post_meta( $order_id, 'woo_rocket_number', true) ) ? get_post_meta( $order_id, 'woo_rocket_number', true) : '';
		$trans_id = ( get_post_meta( $order_id, 'woo_rocket_trans_id', true) ) ? get_post_meta( $order_id, 'woo_rocket_trans_id', true) : '';
		?>
		<div class="bdpg-g-details">
			<img src="<?php echo BD_PAYMENT_GATEWAYS_DIR_URL . '/assets/images/Rocket.png';?> " alt="Rocket">
			<table class="wp-list-table widefat fixed striped posts">
				<tbody>
					<tr>
						<th>
							<strong>
								<?php echo __( 'Rocket Number', 'bd-payment-gateways');?>
							</strong>
						</th>
						<td>
							<?php echo esc_attr( $number );?>
						</td>
					</tr>
					<tr>
						<th>
							<strong>
								<?php echo __( 'Transaction ID', 'bd-payment-gateways');?>
							</strong>
						</th>
						<td>
							<?php echo esc_attr( $trans_id );?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}