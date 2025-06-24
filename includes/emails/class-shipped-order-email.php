<?php

/**
 * Shipped order email class.
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

namespace WoodMart\Invoices;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Shipped Order Email class.
 *
 * @since 1.0.0
 * @extends \WC_Email
 */
class WC_Shipped_Order_Email extends \WC_Email {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->id             = 'customer_shipped_order';
		$this->customer_email = true;
		$this->title          = __( 'Shipped order', 'woodmart-invoices' );
		$this->description    = __( 'Shipped order emails are sent to customers when their orders are marked shipped and usually contain their tracking information.', 'woodmart-invoices' );
		$this->template_html  = 'emails/customer-shipped-order.php';
		$this->template_plain = 'emails/plain/customer-shipped-order.php';
		$this->placeholders   = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);

		// Triggers for this email.
		add_action( 'woocommerce_order_status_shipped_notification', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor.
		parent::__construct();

		// Other settings.
		$this->template_base = WOODMART_INVOICES_PLUGIN_DIR . 'templates/';
	}

	/**
	 * Get email subject.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Your order has been shipped', 'woodmart-invoices' );
	}

	/**
	 * Get email heading.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Your order has been shipped!', 'woodmart-invoices' );
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @since 1.0.0
	 * @param int       $order_id The order ID.
	 * @param \WC_Order $order Order object.
	 * @return void
	 */
	public function trigger( $order_id, $order = false ) {
		$this->setup_locale();

		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object                         = $order;
			$this->recipient                      = $this->object->get_billing_email();
			$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
			$this->placeholders['{order_number}'] = $this->object->get_order_number();
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * Get content html.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get content plain.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => true,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Initialize settings form fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'            => array(
				'title'   => __( 'Enable/Disable', 'woodmart-invoices' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'woodmart-invoices' ),
				'default' => 'yes',
			),
			'subject'            => array(
				'title'       => __( 'Subject', 'woodmart-invoices' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => sprintf( __( 'Available placeholders: %s', 'woodmart-invoices' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading'            => array(
				'title'       => __( 'Email heading', 'woodmart-invoices' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => sprintf( __( 'Available placeholders: %s', 'woodmart-invoices' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'additional_content' => array(
				'title'       => __( 'Additional content', 'woodmart-invoices' ),
				'description' => __( 'Text to appear below the main email content.', 'woodmart-invoices' ) . ' ' . sprintf( __( 'Available placeholders: %s', 'woodmart-invoices' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
				'css'         => 'width:400px; height: 75px;',
				'placeholder' => __( 'N/A', 'woodmart-invoices' ),
				'type'        => 'textarea',
				'default'     => $this->get_default_additional_content(),
			),
			'email_type'         => array(
				'title'       => __( 'Email type', 'woodmart-invoices' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'woodmart-invoices' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Get default additional content.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_default_additional_content() {
		return __( 'Thank you for your business! Your order has been shipped and should arrive soon.', 'woodmart-invoices' );
	}

	/**
	 * Get email attachments.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_attachments() {
		$attachments = parent::get_attachments();

		// Apply WooCommerce email attachments filter for this specific email.
		$attachments = apply_filters( 'woocommerce_email_attachments', $attachments, $this->id, $this->object );

		return $attachments;
	}

	/**
	 * Prevent cloning.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
	}

	/**
	 * Prevent unserializing.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
	}
}
