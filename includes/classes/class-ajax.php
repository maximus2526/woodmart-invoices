<?php

/**
 * AJAX handler class.
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

namespace WoodMart\Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * AJAX handler class.
 *
 * @since 1.0.0
 */
class Invoices_Ajax {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// PDF Invoice generation.
		add_action( 'wp_ajax_woodmart_generate_pdf_invoice', array( $this, 'generate_pdf_invoice' ) );

		// UBL Invoice generation.
		add_action( 'wp_ajax_woodmart_generate_ubl_invoice', array( $this, 'generate_ubl_invoice' ) );

		// Packing slip generation.
		add_action( 'wp_ajax_woodmart_generate_packing_slip', array( $this, 'generate_packing_slip' ) );
	}

	/**
	 * Generate PDF invoice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function generate_pdf_invoice() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'woodmart-invoices' ) );
		}

		// Check nonce
		if ( ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ?? '' ), 'woodmart_invoices_admin_nonce' ) ) {
			wp_die( esc_html__( 'Invalid request.', 'woodmart-invoices' ) );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;

		if ( ! $order_id ) {
			wp_die( esc_html__( 'Invalid order ID.', 'woodmart-invoices' ) );
		}

		// Check if WooCommerce is active
		if ( ! function_exists( 'wc_get_order' ) ) {
			wp_die( esc_html__( 'WooCommerce is not active.', 'woodmart-invoices' ) );
		}

		try {
			$pdf_generator = Invoices_Registry::getInstance()->pdf_generator;

			// Initialize generator if not already done
			if ( method_exists( $pdf_generator, 'init' ) ) {
				$pdf_generator->init();
			}

			$pdf_path = $pdf_generator->generate( $order_id );

			if ( $pdf_path ) {
				$download_url = $this->get_download_url( $pdf_path );
				wp_send_json_success(
					array(
						'message'      => __( 'PDF invoice generated successfully', 'woodmart-invoices' ),
						'download_url' => $download_url,
					)
				);
			} else {
				wp_send_json_error( __( 'Failed to generate PDF invoice.', 'woodmart-invoices' ) );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( __( 'Error generating PDF: ', 'woodmart-invoices' ) . $e->getMessage() );
		}
	}

	/**
	 * Generate UBL invoice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function generate_ubl_invoice() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'woodmart-invoices' ) );
		}

		// Check nonce
		if ( ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ?? '' ), 'woodmart_invoices_admin_nonce' ) ) {
			wp_die( esc_html__( 'Invalid request.', 'woodmart-invoices' ) );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;

		if ( ! $order_id ) {
			wp_die( esc_html__( 'Invalid order ID.', 'woodmart-invoices' ) );
		}

		$ubl_generator = Invoices_Registry::getInstance()->ubl_generator;

		// Initialize generator if not already done
		if ( method_exists( $ubl_generator, 'init' ) ) {
			$ubl_generator->init();
		}

		$ubl_path = $ubl_generator->generate( $order_id );

		if ( $ubl_path ) {
			$download_url = $this->get_download_url( $ubl_path );
			wp_send_json_success(
				array(
					'message'      => __( 'UBL invoice generated successfully', 'woodmart-invoices' ),
					'download_url' => $download_url,
				)
			);
		} else {
			wp_send_json_error( __( 'Failed to generate UBL invoice.', 'woodmart-invoices' ) );
		}
	}

	/**
	 * Generate packing slip.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function generate_packing_slip() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'woodmart-invoices' ) );
		}

		// Check nonce
		if ( ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ?? '' ), 'woodmart_invoices_admin_nonce' ) ) {
			wp_die( esc_html__( 'Invalid request.', 'woodmart-invoices' ) );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;

		if ( ! $order_id ) {
			wp_die( esc_html__( 'Invalid order ID.', 'woodmart-invoices' ) );
		}

		$packing_slip_generator = Invoices_Registry::getInstance()->packing_slip_generator;

		// Initialize generator if not already done
		if ( method_exists( $packing_slip_generator, 'init' ) ) {
			$packing_slip_generator->init();
		}

		$packing_slip_path = $packing_slip_generator->generate( $order_id );

		if ( $packing_slip_path ) {
			$download_url = $this->get_download_url( $packing_slip_path );
			wp_send_json_success(
				array(
					'message'      => __( 'Packing slip generated successfully', 'woodmart-invoices' ),
					'download_url' => $download_url,
				)
			);
		} else {
			wp_send_json_error( __( 'Failed to generate packing slip.', 'woodmart-invoices' ) );
		}
	}

	/**
	 * Get download URL for file.
	 *
	 * @since 1.0.0
	 * @param string $file_path Path to file.
	 * @return string
	 */
	private function get_download_url( $file_path ) {
		$upload_dir  = wp_upload_dir();
		$upload_path = $upload_dir['basedir'] . '/woodmart-invoices/';
		$upload_url  = $upload_dir['baseurl'] . '/woodmart-invoices/';

		// Convert file path to URL
		$relative_path = str_replace( $upload_path, '', $file_path );
		return $upload_url . $relative_path;
	}
}
