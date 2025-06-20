<?php

/**
 * Invoice generator base class.
 *
 * @package XTS_PLUGIN
 * @since 1.0.0
 */

namespace XTS_PLUGIN;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Invoice generator base class.
 *
 * @since 1.0.0
 */
class Invoices_Invoice_Generator extends Invoices_Singleton {

	/**
	 * Upload directory for invoices.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $upload_dir = '';

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		// Exit if already initialized
		if ( $this->is_initialized() ) {
			return;
		}

		$this->setup_upload_directory();

		// Mark as initialized
		$this->set_initialized();
	}

	/**
	 * Setup upload directory.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function setup_upload_directory() {
		$upload_dir       = wp_upload_dir();
		$this->upload_dir = $upload_dir['basedir'] . '/woodmart-invoices/';

		if ( ! file_exists( $this->upload_dir ) ) {
			wp_mkdir_p( $this->upload_dir );
		}
	}

	/**
	 * Get order data for invoice generation.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID.
	 * @return array|false
	 */
	protected function get_order_data( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}

		$billing_address  = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2();
		$shipping_address = $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2();

		return array(
			'order_id'       => $order->get_id(),
			'order_number'   => $order->get_order_number(),
			'order_date'     => $order->get_date_created()->format( 'Y-m-d H:i:s' ),
			'order_status'   => $order->get_status(),
			'order_total'    => $order->get_total(),
			'order_currency' => $order->get_currency(),
			'billing'        => array(
				'first_name' => $order->get_billing_first_name(),
				'last_name'  => $order->get_billing_last_name(),
				'company'    => $order->get_billing_company(),
				'address_1'  => $order->get_billing_address_1(),
				'address_2'  => $order->get_billing_address_2(),
				'city'       => $order->get_billing_city(),
				'state'      => $order->get_billing_state(),
				'postcode'   => $order->get_billing_postcode(),
				'country'    => $order->get_billing_country(),
				'email'      => $order->get_billing_email(),
				'phone'      => $order->get_billing_phone(),
			),
			'shipping'       => array(
				'first_name' => $order->get_shipping_first_name(),
				'last_name'  => $order->get_shipping_last_name(),
				'company'    => $order->get_shipping_company(),
				'address_1'  => $order->get_shipping_address_1(),
				'address_2'  => $order->get_shipping_address_2(),
				'city'       => $order->get_shipping_city(),
				'state'      => $order->get_shipping_state(),
				'postcode'   => $order->get_shipping_postcode(),
				'country'    => $order->get_shipping_country(),
			),
			'items'          => $this->get_order_items( $order ),
		);
	}

	/**
	 * Get order items.
	 *
	 * @since 1.0.0
	 * @param WC_Order $order Order object.
	 * @return array
	 */
	protected function get_order_items( $order ) {
		$items = array();

		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();

			$items[] = array(
				'name'     => $item->get_name(),
				'quantity' => $item->get_quantity(),
				'price'    => $item->get_total() / $item->get_quantity(),
				'total'    => $item->get_total(),
				'sku'      => $product ? $product->get_sku() : '',
			);
		}

		return $items;
	}

	/**
	 * Get company information.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_company_info() {
		$settings = get_option( 'woodmart_invoices_settings', array() );

		return array(
			'name'    => $settings['company_name'] ?? get_bloginfo( 'name' ),
			'address' => $settings['company_address'] ?? '',
			'email'   => $settings['company_email'] ?? get_option( 'admin_email' ),
			'phone'   => $settings['company_phone'] ?? '',
			'website' => get_site_url(),
		);
	}
}
