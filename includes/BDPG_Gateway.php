<?php
/**
 * BDPG_Gateway Class
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */

namespace ultraDevs\BDPG;

/**
 * BDPG_Gateway Class
 *
 * @package BDPaymentGateways
 * @since 3.0.0
 */
abstract class BDPG_Gateway extends \WC_Payment_Gateway {

    /**
     * Gateway
     *
     * @var string
     */
    public $gateway = '';

    /**
     * Icon
     *
     * @var string
     */
    public $icon = '';

    /**
     * Gateway Key
     *
     * @var string
     */
    public $gateway_key = '';
    
    /**
     * Instructions
     *
     * @var string
     */
    public $instructions = '';

    /**
     * Gateway Charge
     *
     * @var string
     */
    public $gateway_charge = '';

    /**
     * Gateway Charge Details
     *
     * @var string
     */
    public $gateway_charge_details = '';

    /**
     * Gateway Fee
     *
     * @var string
     */
    public $gateway_fee = '';

    /**
     * Dollar Rate
     *
     * @var string
     */
    public $dollar_rate = 117.56;

    /**
     * Gateway Accounts
     *
     * @var null
     */
    public $accounts = null;


    /**
     * Constructor
     */
    public function __construct() {

        $this->id = 'woo_' . $this->gateway;
        $this->has_fields         = true;
        $this->icon = apply_filters( 'bdpg_' . $this->gateway . '_icon', BD_PAYMENT_GATEWAYS_DIR_URL . 'assets/images/' . ucfirst( $this->gateway ) . '.png' );

        $this->method_description = sprintf(
			/* translators: %s: Payment Gateway. */
			__( '%s Payment Gateway Settings.', 'bangladeshi-payment-gateways' ),
			$this->gateway
		);
		$this->method_title       = sprintf(
			/* translators: %s: Payment Gateway. */
			__( '%s', 'bangladeshi-payment-gateways' ),
			$this->gateway
		);

		$this->init_form_fields();

		// Load the Settings.
		$this->init_settings();

		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->enabled              = $this->get_option( 'enabled' );
		$this->instructions         = $this->get_option( 'instructions' );
		$this->gateway_charge         = $this->get_option( $this->gateway . '_charge' );
		$this->gateway_fee            = $this->get_option( $this->gateway . '_fee' );
		$this->gateway_charge_details = $this->get_option( 'gateway_charge_details' );

		$account = array(
			array(
				'type'    => $this->get_option( 'type' ),
				'number'  => $this->get_option( 'number' ),
				'qr_code' => $this->get_option( 'qr_code' ),
			),
		);
		$this->accounts    = get_option( 'bdpg_' . $this->gateway . '_accounts', [] );


		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_accounts' ) );
		add_action( 'woocommerce_thankyou_woo_' . $this->gateway, array( $this, 'bdpg_thankyou' ) );
		add_action( 'woocommerce_email_before_order_table', array( $this, 'customer_email_instructions' ), 10, 3 );

		add_action( 'woocommerce_checkout_process', array( $this, 'payment_process' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'fields_update' ) );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'admin_order_data' ) );

		$settings = get_option( 'woocommerce_woo_' . $this->gateway . '_settings' );

		if ( isset( $settings[ $this->gateway . '_charge' ] ) && 'yes' === $settings[ $this->gateway . '_charge' ] ) {
			add_action( 'woocommerce_cart_calculate_fees', array( $this, 'charge_settings' ), 20, 1 );
		}

		add_action( 'woocommerce_order_details_after_customer_details', array( $this, 'data_order_review_page' ) );
		add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'admin_register_column' ), 20 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'admin_column_value' ), 20, 2 );

    }


    /**
	 * Gateway Fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'              => array(
				'title'       => __( 'Enable/Disable', 'bangladeshi-payment-gateways' ),
				'label'       => sprintf(
					/* translators: %s: Payment Gateway. */
					__( 'Enable %s Gateway', 'bangladeshi-payment-gateways' ),
					$this->gateway
				),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'                => array(
				'title'       => __( 'Title', 'bangladeshi-payment-gateways' ),
				'type'        => 'text',
				'default'     => $this->gateway,
				'description' => __( 'Title', 'bangladeshi-payment-gateways' ),
				'desc_tip'    => true,
			),
			'description'          => array(
				'title'   => __( 'Description', 'bangladeshi-payment-gateways' ),
				'default' => bdpg_get_instruction_by_gateway( $this->gateway ),
				'type'    => 'textarea',
			),
			$this->gateway . '_charge'         => array(
				'title'       => sprintf(
					/* translators: %s: Payment Gateway. */
                    __( '%s Charge?', 'bangladeshi-payment-gateways' ),
                    $this->gateway
                ),
				'type'        => 'checkbox',
				'description' => sprintf(
					/* translators: %s: Payment Gateway. */
                    __( 'Add %s <b>Send Money</b> charge?', 'bangladeshi-payment-gateways' ),
                    $this->gateway
                ),
				'default'     => 'no',
			),

			$this->gateway . '_fee'            => array(
				'title'       => sprintf(
					/* translators: %s: Payment Gateway. */
                    __( '%s Fee', 'bangladeshi-payment-gateways' ),
                    $this->gateway
                ),
				'type'        => 'text',
				'default'     => '1.8',
				'description' => __( 'Don\'t add %.', 'bangladeshi-payment-gateways' ),
			),

			$this->gateway . '_charge_details' => array(
				'title'   => sprintf(
					/* translators: %s: Payment Gateway. */
                    __( '%s Charge Details', 'bangladeshi-payment-gateways' ),
                    $this->gateway
                ),
				'type'    => 'textarea',
				'default' => sprintf(
					/* translators: %s: Payment Gateway. */
                    __( '%s "Send Money" fee will be added with net price.', 'bangladeshi-payment-gateways' ),
                    $this->gateway
                )
			),

			'instructions'         => array(
				'title'       => __( 'Instructions', 'bangladeshi-payment-gateways' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions', 'bangladeshi-payment-gateways' ),
				'default'     => ''
			),
			'accounts'             => array(
				'type' => 'accounts',
			),
		);

	}

	/**
	 * Payment Fields
	 */
	public function payment_fields() {
		global $woocommerce;

		$gateway_charge_details = ( 'yes' === $this->gateway_charge ) ? $this->gateway_charge_details : '';
		echo wpautop( wptexturize( __( $this->description, 'bangladeshi-payment-gateways' ) ) . ' ' . $gateway_charge_details );

		$total_payment=  $woocommerce->cart->total ;
		$symbol = get_woocommerce_currency_symbol();
		if ( get_woocommerce_currency() === 'USD' ){
			$total_payment = $this->dollar_rate * $woocommerce->cart->total;
			$symbol        = get_woocommerce_currency_symbol('BDT');
		}

		$total_amount = sprintf(
			/* translators: %s: Total Payment. */
			__( 'You need to send us <b>%s</b>', 'bangladeshi-payment-gateways' ),
			$symbol . $total_payment
		) . '</br>';
		echo '<div class="bdpg-total-amount">' . $total_amount . '</div>';
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
					<img src="<?php echo $account['qr_code']; ?>" alt="QR Code">
				</div>
				<?php
			}
			?>
				<div class="bdpg-acc_d">
					<p>Account Type: <b><?php echo esc_html( $account['type'] ); ?></b></p>
					<p>Account Number: <b><?php echo esc_html( $account['number'] ); ?></b> </p>
				</div>
			</div>
			<?php
		}
		?>
			<div class="bdpg-user__acc">
				<div class="bdpg-user__field">
					<label for="<?php echo esc_attr( $this->gateway ); ?>_acc_no">
						<?php
                            echo sprintf(
								/* translators: %s: Payment Gateway. */
                                __( 'Your %s Account Number', 'bangladeshi-payment-gateways' ),
                                ucfirst( $this->gateway )
                            );
                        ?>
					</label>
					<input type="text" class="widefat" name="<?php echo esc_attr( $this->gateway ); ?>_acc_no" placeholder="01XXXXXXXXX">
				</div>
				<div class="bdpg-user__field">
					<label for="<?php echo esc_attr( $this->gateway ); ?>_trans_id">
                        <?php
                            echo sprintf(
								/* translators: %s: Payment Gateway. */
                                __( 'Your %s Transaction ID', 'bangladeshi-payment-gateways' ),
                                ucfirst( $this->gateway )
                            );
                        ?>
					</label>
					<input type="text" class="widefat" name="<?php echo esc_attr( $this->gateway ); ?>_trans_id" placeholder="2M7A5">
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
			<th scope="row" class="titledesc"><?php esc_html_e( 'Account Details', 'woocommerce' ); ?>:</th>
			<td class="forminp" id="gateway_accounts">
				<table class="widefat wc_input_table sortable" cellspacing="0">
					<thead>
						<tr>
							<th class="sort">&nbsp;</th>
							<th><?php esc_html_e( 'Account Type', 'woocommerce' ); ?></th>
							<th><?php esc_html_e( 'Account Number', 'woocommerce' ); ?></th>
							<th><?php esc_html_e( 'QR Code', 'woocommerce' ); ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="7">
								<a href="#" class="add button">
								<?php esc_html_e( '+ Add Account', 'woocommerce' ); ?></a>
								<a href="#" class="remove_rows button"><?php esc_html_e( 'Remove selected account(s)', 'woocommerce' );?>
								</a>
							</th>
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
									<td><input type="text" value="' . esc_attr( $account['type'] ) . '" name="' . esc_attr( $this->gateway ) . '_account_type[' . $i . ']" /></td>
									<td><input type="text" value="' . esc_attr( $account['number'] ) . '" name="' . esc_attr( $this->gateway ) . '_account_number[' . $i . ']" /></td><td><input type="hidden" value="' . esc_attr( $account['qr_code'] ) . '" name="' . esc_attr( $this->gateway ) . '_account_qr_code[' . $i . ']" id="bdpg_qr_code-' . $i . '" />
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
						$('#gateway_accounts').on( 'click', 'a.add', function(){

							var size = $('#gateway_accounts').find('tbody .account').length;

							$('<tr class="account">\
									<td class="sort"></td>\
									<td><input type="text" name="<?php echo esc_attr( $this->gateway ); ?>_account_type[' + size + ']" /></td>\
									<td><input type="text" name="<?php echo esc_attr( $this->gateway ); ?>_account_number[' + size + ']" /></td>\
									<td><input type="hidden" id="bdpg_qr_code-' + size + '" name="<?php echo esc_attr( $this->gateway ); ?>_account_qr_code[' + size + ']" /><input type="button" class="button button-primary add_qr_c_img" value="Add Image" data-target="#bdpg_qr_code-' + size + '" data-qr="#bdpg_qr_img-' + size + '"><div id="bdpg_qr_img-' + size + '"></div>\
									</td>\
								</tr>').appendTo('#gateway_accounts table tbody');

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
	 */
	public function save_accounts() {

		if ( isset( $_POST[$this->gateway . '_account_type'] ) ) {
			$accounts = array();

			$type    = array_map( 'wc_clean', $_POST[$this->gateway . '_account_type'] );
			$number  = array_map( 'wc_clean', $_POST[$this->gateway . '_account_number'] );
			$qr_code = array_map( 'wc_clean', $_POST[$this->gateway . '_account_qr_code'] );

			foreach ( $type as $key => $value ) {
				if ( ! isset( $type[ $key ] ) ) {
					continue;
				}

				$accounts[] = array(
					'type'    => $type[ $key ],
					'number'  => $number[ $key ],
					'qr_code' => $qr_code[ $key ],
				);
			}
			update_option( 'bdpg_' . $this->gateway . '_accounts', $accounts );
		}

	}

	/**
	 * Process Payment.
	 *
	 * @param int $order_id Order ID.
	 */
	public function process_payment( $order_id ) {
		global $woocommerce;

		$order = new \WC_Order( $order_id );

		// Mark as on-hold (we're awaiting the cheque).
		$order->update_status( 'on-hold', sprintf(
			/* translators: %s: Payment Gateway. */
			esc_html__( 'Awaiting %s payment.', 'bangladeshi-payment-gateways' ),
			$order->get_payment_method_title() )
		);

		// Reduce stock levels.
		$order->reduce_order_stock();

		// Remove cart.
		$woocommerce->cart->empty_cart();

		do_action('process_payment_bgd',$order_id , $order , $this );
		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Thank You Page.
	 *
	 * @param int $order_id Order ID.
	 */
	public function bdpg_thankyou( $order_id ) {

		$order = new \WC_Order( $order_id );

		if ( $this->id === $order->get_payment_method() ) {
			echo wpautop( $this->instructions );
		} else {
			echo esc_html__( 'Thank you. Your order has been received.', 'bangladeshi-payment-gateways' );
		}
	}

	/**
	 * Customer Email.
	 *
	 * @param Object  $order order.
	 * @param [type]  $sent_to_admin Sent to admin.
	 * @param boolean $plain_text Plain Text.
	 * @return string
	 */
	public function customer_email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->id !== $order->get_payment_method() || $sent_to_admin ) {
			return;
		}

		if ( $this->instructions ) {
			echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
		}
	}


	/**
	 * Field Validation.
	 */
	public function payment_process() {
		if ( 'woo_' . $this->gateway !== $_POST['payment_method'] ) {
			return;
		}

		$number   = sanitize_text_field( $_POST[$this->gateway . '_acc_no'] );
		$trans_id = sanitize_text_field( $_POST[$this->gateway . '_trans_id'] );

		if ( '' === $number ) {
			wc_add_notice( sprintf(
				/* translators: %s: Payment Gateway. */
				esc_html__( 'Please enter your %s account number.', 'bangladeshi-payment-gateways' ),
				$this->gateway
			), 'error' );
		}

		if ( '' === $trans_id ) {
			wc_add_notice( sprintf(
				/* translators: %s: Payment Gateway. */
				esc_html__( 'Please enter your %s transaction ID.', 'bangladeshi-payment-gateways' ),
				$this->gateway
			),'error' );
		}
	}

	/**
	 * Field Update.
	 *
	 * @param int $order_id Order ID.
	 */
	public function fields_update( $order_id ) {

		if ( 'woo_' . $this->gateway !== $_POST['payment_method'] ) {
			return;
		}
		$number   = sanitize_text_field( $_POST[$this->gateway . '_acc_no'] );
		$trans_id = sanitize_text_field( $_POST[$this->gateway . '_trans_id'] );

		update_post_meta( $order_id, 'woo_' . $this->gateway . '_number', $number );
		update_post_meta( $order_id, 'woo_' . $this->gateway . '_trans_id', $trans_id );
	}
	/**
	 * Display Gateway data in admin page.
	 *
	 * @param Object $order Order.
	 */
	public function admin_order_data( $order ) {
		if ( 'woo_' . $this->gateway !== $order->get_payment_method() ) {
			return;
		}

		$order_id = $order->get_id();
		$number   = ( get_post_meta( $order_id, 'woo_' . $this->gateway . '_number', true ) ) ? get_post_meta( $order_id, 'woo_' . $this->gateway . '_number', true ) : '';
		$trans_id = ( get_post_meta( $order_id, 'woo_' . $this->gateway . '_trans_id', true ) ) ? get_post_meta( $order_id, 'woo_' . $this->gateway . '_trans_id', true ) : '';
		?>
		<div class="form-field form-field-wide bdpg-admin-data">
			<img src="<?php echo esc_url( $this->icon ); ?> " alt="<?php echo esc_attr( $this->gateway ); ?>">
			<table class="wp-list-table widefat striped posts">
				<tbody>
					<tr>
						<th>
							<strong>
								<?php echo sprintf(
									/* translators: %s: Payment Gateway. */
									esc_html__( '%s Account Number', 'bangladeshi-payment-gateways' ),
									ucfirst( $this->gateway
									) ); ?>
							</strong>
						</th>
						<td>
		<?php echo esc_attr( $number ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<strong>
								<?php echo __( 'Transaction ID', 'bangladeshi-payment-gateways' ); ?>
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
	 * Check Gateway charge status.
	 *
	 * @param Object $cart Cart.
	 */
	public function charge_settings( $cart ) {
		global $woocommerce;
		$settings = get_option( 'woocommerce_woo_' . $this->gateway . '_settings' );


		$av_gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
		if ( ! empty( $av_gateways ) ) {

			$payment_method = \WC()->session->get( 'chosen_payment_method' );

			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				return;
			}

			if ( 'woo_' . $this->gateway === $payment_method ) {
				$label  = sprintf(
					/* translators: %s: Payment Gateway. */
					esc_html__( '%s Charge', 'bangladeshi-payment-gateways' ),
					ucfirst( $this->gateway )
				);
				$amount = round( $cart->cart_contents_total * ( $settings[ $this->gateway . '_fee'] / 100 ) );
				$cart->add_fee( $label, $amount, true, 'standard' );
			}
		}
	}
	/**
	 * Display Gateway data in order review page
	 *
	 * @param Object $order Order.
	 */
	public function data_order_review_page( $order ) {
		if ( 'woo_' . $this->gateway !== $order->get_payment_method() ) {
			return;
		}
		global $wp;

		if ( isset( $wp->query_vars['order-received'] ) ) {
			$order_id = (int) $wp->query_vars['order-received'];
		} else {
			$order_id = (int) $wp->query_vars['view-order'];
		}

		$number   = ( get_post_meta( $order_id, 'woo_' . $this->gateway . '_number', true ) ) ? get_post_meta( $order_id, 'woo_' . $this->gateway . '_number', true ) : '';
		$trans_id = ( get_post_meta( $order_id, 'woo_' . $this->gateway . '_trans_id', true ) ) ? get_post_meta( $order_id, 'woo_' . $this->gateway . '_trans_id', true ) : '';
		?>
		<div class="bdpg-g-details">
			<img src="<?php echo esc_html( $this->icon ); ?> " alt="<?php echo esc_attr( $this->gateway ); ?>">
			<table class="wp-list-table widefat striped posts">
				<tbody>
					<tr>
						<th>
							<strong>
                            <?php echo sprintf(
								/* translators: %s: Payment Gateway. */
								esc_html__( '%s Account Number', 'bangladeshi-payment-gateways' ),
								ucfirst( $this->gateway )
								); ?>
							</strong>
						</th>
						<td>
		<?php echo esc_attr( $number ); ?>
						</td>
					</tr>
					<tr>
						<th>
							<strong>
                                <?php echo __( 'Transaction ID', 'bangladeshi-payment-gateways' ); ?>
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
	 * Register New Column For Payment Info
	 *
	 * @param array $columns Order.
	 */
	public function admin_register_column( $columns ) {

		$columns = ( is_array( $columns ) ) ? $columns : array();

		$columns['payment_no'] = esc_html__( 'Payment No', 'bangladeshi-payment-gateways' );
		$columns['tran_id']    = esc_html__( 'Tran. ID', 'bangladeshi-payment-gateways' );

		return $columns;

	}

	/**
	 * Load Payment Data in New Column
	 *
	 * @param string $column Column name.
	 */
	public function admin_column_value( $column, $order ) {

		$payment_no = ( get_post_meta( $order->get_id(), 'woo_' . $this->gateway . '_number', true ) ) ? get_post_meta( $order->get_id(), 'woo_' . $this->gateway . '_number', true ) : '';
		$tran_id    = ( get_post_meta( $order->get_id(), 'woo_' . $this->gateway . '_trans_id', true ) ) ? get_post_meta( $order->get_id(), 'woo_' . $this->gateway . '_trans_id', true ) : '';

		if ( 'payment_no' === $column ) {
			echo esc_attr( $payment_no );
		}

		if ( 'tran_id' === $column ) {
			echo esc_attr( $tran_id );
		}
	}
}
