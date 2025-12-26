<?php
/**
 * Admin Statistics
 *
 * @package BDPaymentGateways
 * @since 4.0.4
 */

namespace ultraDevs\BDPG\Admin;

/**
 * Statistics Class
 *
 * @package BDPaymentGateways
 * @since 4.0.4
 */
class Statistics {

	/**
	 * Gateway types
	 *
	 * @var array
	 */
	private const GATEWAYS = array( 'bkash', 'rocket', 'nagad', 'upay' );

	/**
	 * Constructor
	 */
	public function __construct() {
		// AJAX handlers for dynamic data loading.
		add_action( 'wp_ajax_bdpg_get_stats', array( $this, 'ajax_get_stats' ) );
		add_action( 'wp_ajax_bdpg_get_transactions', array( $this, 'ajax_get_transactions' ) );
		add_action( 'wp_ajax_bdpg_export_transactions', array( $this, 'ajax_export_transactions' ) );
	}

	/**
	 * Get payment statistics
	 *
	 * @param string $date_from Date from.
	 * @param string $date_to   Date to.
	 * @return array
	 */
	public function get_statistics( $date_from = '', $date_to = '' ) {
		$stats = array();

		foreach ( self::GATEWAYS as $gateway ) {
			$stats[ $gateway ] = $this->get_gateway_stats( $gateway, $date_from, $date_to );
		}

		// Calculate totals.
		$stats['total'] = array(
			'count'        => 0,
			'total_amount' => 0,
		);

		foreach ( self::GATEWAYS as $gateway ) {
			$stats['total']['count']        += $stats[ $gateway ]['count'];
			$stats['total']['total_amount'] += $stats[ $gateway ]['total_amount'];
		}

		return $stats;
	}

	/**
	 * Get gateway specific statistics
	 *
	 * @param string $gateway   Gateway name.
	 * @param string $date_from Date from.
	 * @param string $date_to   Date to.
	 * @return array
	 */
	private function get_gateway_stats( $gateway, $date_from = '', $date_to = '' ) {
		$args = array(
			'limit'  => -1,
			'type'   => 'shop_order',
			'status' => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
		);

		// Add date filter if provided.
		if ( ! empty( $date_from ) ) {
			$args['date_after'] = gmdate( 'Y-m-d 00:00:00', strtotime( $date_from ) );
		}
		if ( ! empty( $date_to ) ) {
			$args['date_before'] = gmdate( 'Y-m-d 23:59:59', strtotime( $date_to ) );
		}

		$orders = wc_get_orders( $args );

		$count        = 0;
		$total_amount = 0;

		foreach ( $orders as $order ) {
			if ( 'woo_' . $gateway === $order->get_payment_method() ) {
				++$count;
				$total_amount += $order->get_total();
			}
		}

		return array(
			'count'        => $count,
			'total_amount' => $total_amount,
		);
	}

	/**
	 * Get transactions with filters
	 *
	 * @param string $date_from Date from.
	 * @param string $date_to   Date to.
	 * @param string $gateway   Gateway filter.
	 * @param int    $offset    Offset for pagination.
	 * @param int    $per_page  Items per page.
	 * @return array
	 */
	public function get_transactions( $date_from = '', $date_to = '', $gateway = '', $offset = 0, $per_page = 20 ) {
		$args = array(
			'limit'  => $per_page,
			'offset' => $offset,
			'type'   => 'shop_order',
			'status' => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
		);

		// Add date filter if provided.
		if ( ! empty( $date_from ) ) {
			$args['date_after'] = gmdate( 'Y-m-d 00:00:00', strtotime( $date_from ) );
		}
		if ( ! empty( $date_to ) ) {
			$args['date_before'] = gmdate( 'Y-m-d 23:59:59', strtotime( $date_to ) );
		}

		$orders = wc_get_orders( $args );
		$transactions = array();
		$total_count = 0;

		foreach ( $orders as $order ) {
			$payment_method = $order->get_payment_method();

			// Check if this is our gateway.
			if ( ! in_array( str_replace( 'woo_', '', $payment_method ), self::GATEWAYS, true ) ) {
				continue;
			}

			// Filter by gateway if specified.
			if ( ! empty( $gateway ) && $payment_method !== 'woo_' . $gateway ) {
				continue;
			}

			++$total_count;

			$gateway_key = str_replace( 'woo_', '', $payment_method );
			$acc_no      = get_post_meta( $order->get_id(), 'woo_' . $gateway_key . '_number', true );
			$trans_id    = get_post_meta( $order->get_id(), 'woo_' . $gateway_key . '_trans_id', true );

			$transactions[] = array(
				'order_id'       => $order->get_id(),
				'order_number'   => $order->get_order_number(),
				'date'           => $order->get_date_created()->date_i18n( 'Y-m-d H:i:s' ),
				'gateway'        => $this->get_gateway_title( $gateway_key ),
				'gateway_raw'    => $gateway_key,
				'account_no'     => $acc_no ? $acc_no : '-',
				'transaction_id' => $trans_id ? $trans_id : '-',
				'amount'         => $order->get_total(),
				'currency'       => $order->get_currency(),
				'status'         => $order->get_status(),
				'customer_name'  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'customer_email' => $order->get_billing_email(),
			);
		}

		return array(
			'transactions' => $transactions,
			'total_count'  => $total_count,
		);
	}

	/**
	 * Get gateway title
	 *
	 * @param string $gateway Gateway name.
	 * @return string
	 */
	private function get_gateway_title( $gateway ) {
		$titles = array(
			'bkash'  => __( 'bKash', 'bangladeshi-payment-gateways' ),
			'rocket' => __( 'Rocket', 'bangladeshi-payment-gateways' ),
			'nagad'  => __( 'Nagad', 'bangladeshi-payment-gateways' ),
			'upay'   => __( 'Upay', 'bangladeshi-payment-gateways' ),
		);

		return isset( $titles[ $gateway ] ) ? $titles[ $gateway ] : ucfirst( $gateway );
	}

	/**
	 * AJAX handler for getting statistics
	 *
	 * @return void
	 */
	public function ajax_get_stats() {
		check_ajax_referer( 'bdpg_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'bangladeshi-payment-gateways' ) ) );
		}

		$date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$date_to   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';

		$stats = $this->get_statistics( $date_from, $date_to );

		wp_send_json_success( $stats );
	}

	/**
	 * AJAX handler for getting transactions
	 *
	 * @return void
	 */
	public function ajax_get_transactions() {
		check_ajax_referer( 'bdpg_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'bangladeshi-payment-gateways' ) ) );
		}

		$date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$date_to   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';
		$gateway   = isset( $_POST['gateway'] ) ? sanitize_text_field( wp_unslash( $_POST['gateway'] ) ) : '';
		$page      = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
		$per_page  = 20;

		$offset = ( $page - 1 ) * $per_page;

		$result = $this->get_transactions( $date_from, $date_to, $gateway, $offset, $per_page );

		wp_send_json_success( $result );
	}

	/**
	 * AJAX handler for exporting transactions
	 *
	 * @return void
	 */
	public function ajax_export_transactions() {
		check_ajax_referer( 'bdpg_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'bangladeshi-payment-gateways' ) ) );
		}

		$date_from = isset( $_POST['date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) : '';
		$date_to   = isset( $_POST['date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) : '';
		$gateway   = isset( $_POST['gateway'] ) ? sanitize_text_field( wp_unslash( $_POST['gateway'] ) ) : '';
		$format    = isset( $_POST['format'] ) ? sanitize_text_field( wp_unslash( $_POST['format'] ) ) : 'csv';

		// Get all transactions (no pagination).
		$result = $this->get_transactions( $date_from, $date_to, $gateway, 0, 999999 );

		if ( 'csv' === $format ) {
			$this->export_csv( $result['transactions'] );
		} else {
			$this->export_pdf( $result['transactions'] );
		}

		exit;
	}

	/**
	 * Export transactions to CSV
	 *
	 * @param array $transactions Transactions data.
	 * @return void
	 */
	private function export_csv( $transactions ) {
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="bdpg-transactions-' . date( 'Y-m-d' ) . '.csv"' );

		$output = fopen( 'php://output', 'w' );

		// CSV headers.
		fputcsv( $output, array(
			__( 'Order ID', 'bangladeshi-payment-gateways' ),
			__( 'Date', 'bangladeshi-payment-gateways' ),
			__( 'Gateway', 'bangladeshi-payment-gateways' ),
			__( 'Account No', 'bangladeshi-payment-gateways' ),
			__( 'Transaction ID', 'bangladeshi-payment-gateways' ),
			__( 'Amount', 'bangladeshi-payment-gateways' ),
			__( 'Customer', 'bangladeshi-payment-gateways' ),
			__( 'Status', 'bangladeshi-payment-gateways' ),
		) );

		foreach ( $transactions as $transaction ) {
			fputcsv( $output, array(
				$transaction['order_number'],
				$transaction['date'],
				$transaction['gateway'],
				$transaction['account_no'],
				$transaction['transaction_id'],
				$transaction['amount'] . ' ' . $transaction['currency'],
				$transaction['customer_name'] . ' (' . $transaction['customer_email'] . ')',
				$transaction['status'],
			) );
		}

		fclose( $output );
	}

	/**
	 * Export transactions to PDF (simple HTML-based PDF)
	 *
	 * @param array $transactions Transactions data.
	 * @return void
	 */
	private function export_pdf( $transactions ) {
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="bdpg-transactions-' . date( 'Y-m-d' ) . '.pdf"' );

		$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . __( 'Transactions Report', 'bangladeshi-payment-gateways' ) . '</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { color: #2271b1; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #2271b1; color: white; padding: 10px; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>' . __( 'Bangladeshi Payment Gateways - Transactions', 'bangladeshi-payment-gateways' ) . '</h1>
    <p>' . __( 'Generated on:', 'bangladeshi-payment-gateways' ) . ' ' . date( 'Y-m-d H:i:s' ) . '</p>
    <table>
        <tr>
            <th>' . __( 'Order ID', 'bangladeshi-payment-gateways' ) . '</th>
            <th>' . __( 'Date', 'bangladeshi-payment-gateways' ) . '</th>
            <th>' . __( 'Gateway', 'bangladeshi-payment-gateways' ) . '</th>
            <th>' . __( 'Account No', 'bangladeshi-payment-gateways' ) . '</th>
            <th>' . __( 'Transaction ID', 'bangladeshi-payment-gateways' ) . '</th>
            <th>' . __( 'Amount', 'bangladeshi-payment-gateways' ) . '</th>
            <th>' . __( 'Customer', 'bangladeshi-payment-gateways' ) . '</th>
        </tr>';

		foreach ( $transactions as $transaction ) {
			$html .= '
        <tr>
            <td>' . esc_html( $transaction['order_number'] ) . '</td>
            <td>' . esc_html( $transaction['date'] ) . '</td>
            <td>' . esc_html( $transaction['gateway'] ) . '</td>
            <td>' . esc_html( $transaction['account_no'] ) . '</td>
            <td>' . esc_html( $transaction['transaction_id'] ) . '</td>
            <td>' . esc_html( $transaction['amount'] . ' ' . $transaction['currency'] ) . '</td>
            <td>' . esc_html( $transaction['customer_name'] ) . '</td>
        </tr>';
		}

		$html .= '
    </table>
</body>
</html>';

		// For simple PDF generation, you might want to use a library like DOMPDF or TCPDF.
		// For now, we'll output as HTML which browsers can print to PDF.
		echo $html;
	}
}
