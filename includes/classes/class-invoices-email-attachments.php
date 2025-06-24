<?php

/**
 * Email attachments handler class.
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

namespace WoodMart\Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Email attachments handler class.
 *
 * @since 1.0.0
 */
class Invoices_Email_Attachments extends Invoices_Singleton {

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		// Exit if already initialized.
		if ( $this->is_initialized() ) {
			return;
		}

		// Hook into WooCommerce email attachments.
		add_filter( 'woocommerce_email_attachments', array( $this, 'add_attachments' ), 10, 3 );

		// Mark as initialized.
		$this->set_initialized();
	}

	/**
	 * Add attachments to WooCommerce emails.
	 *
	 * @since 1.0.0
	 * @param array  $attachments Existing attachments.
	 * @param string $id Email ID.
	 * @param mixed  $object Email object (order, customer, etc).
	 * @return array Modified attachments array.
	 */
	public function add_attachments( $attachments, $id, $object ) {
		// Ensure we have an order object.
		$order = $this->get_order_from_object( $object );

		if ( ! $order ) {
			return $attachments;
		}

		$settings                = get_option( 'woodmart_invoices_settings', array() );
		$attach_invoices_to      = $settings['attach_invoices_to'] ?? array();
		$attach_packing_slips_to = $settings['attach_packing_slips_to'] ?? array();

		// Add PDF invoices.
		if ( in_array( $id, $attach_invoices_to, true ) ) {
			$invoice_attachment = $this->get_invoice_attachment( $order );
			if ( $invoice_attachment ) {
				$attachments[] = $invoice_attachment;
			}
		}

		// Add packing slips.
		if ( in_array( $id, $attach_packing_slips_to, true ) ) {
			$packing_slip_attachment = $this->get_packing_slip_attachment( $order );
			if ( $packing_slip_attachment ) {
				$attachments[] = $packing_slip_attachment;
			}
		}

		return $attachments;
	}

	/**
	 * Get order object from various email objects.
	 *
	 * @since 1.0.0
	 * @param mixed $object Email object.
	 * @return WC_Order|false Order object or false.
	 */
	private function get_order_from_object( $object ) {
		// If it's already an order object.
		if ( is_a( $object, 'WC_Order' ) ) {
			return $object;
		}

		// If it's an order ID.
		if ( is_numeric( $object ) ) {
			return wc_get_order( $object );
		}

		// If it's an array with order_id key.
		if ( is_array( $object ) && isset( $object['order_id'] ) ) {
			return wc_get_order( $object['order_id'] );
		}

		// If it's an object with get_id method.
		if ( is_object( $object ) && method_exists( $object, 'get_id' ) ) {
			return wc_get_order( $object->get_id() );
		}

		return false;
	}

	/**
	 * Get invoice attachment for order.
	 *
	 * @since 1.0.0
	 * @param WC_Order $order Order object.
	 * @return string|false File path or false.
	 */
	private function get_invoice_attachment( $order ) {
		// Check if PDF invoices are enabled.
		$pdf_enabled = woodmart_invoices_get_option( 'pdf_enabled', 'yes' );

		if ( 'yes' !== $pdf_enabled ) {
			return false;
		}

		$pdf_generator = Invoices_Registry::getInstance()->pdf_generator;

		// Initialize generator if not already done.
		if ( method_exists( $pdf_generator, 'init' ) ) {
			$pdf_generator->init();
		}

		// Generate PDF invoice.
		$pdf_path = $pdf_generator->generate( $order->get_id() );

		if ( $pdf_path && file_exists( $pdf_path ) ) {
			return $pdf_path;
		}

		return false;
	}

	/**
	 * Get packing slip attachment for order.
	 *
	 * @since 1.0.0
	 * @param WC_Order $order Order object.
	 * @return string|false File path or false.
	 */
	private function get_packing_slip_attachment( $order ) {
		// Check if packing slips are enabled.
		$packing_slips_enabled = woodmart_invoices_get_option( 'packing_slips_enabled', 'yes' );

		if ( 'yes' !== $packing_slips_enabled ) {
			return false;
		}

		$packing_slip_generator = Invoices_Registry::getInstance()->packing_slip_generator;

		// Initialize generator if not already done.
		if ( method_exists( $packing_slip_generator, 'init' ) ) {
			$packing_slip_generator->init();
		}

		// Generate packing slip.
		$packing_slip_path = $packing_slip_generator->generate( $order->get_id() );

		if ( $packing_slip_path && file_exists( $packing_slip_path ) ) {
			return $packing_slip_path;
		}

		return false;
	}
}
