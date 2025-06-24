<?php

/**
 * Helper functions for WoodMart Invoices plugin.
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Log messages for debugging.
 *
 * @since 1.0.0
 * @param string $message The message to log.
 * @param string $level The log level (info, warning, error).
 * @return void
 */
if ( ! function_exists( 'woodmart_invoices_log' ) ) {
	function woodmart_invoices_log( $message, $level = 'info' ) {
		// Log function disabled for production
	}
}

/**
 * Get upload directory for invoices.
 *
 * @since 1.0.0
 * @return string The upload directory path.
 */
if ( ! function_exists( 'woodmart_invoices_get_upload_dir' ) ) {
	function woodmart_invoices_get_upload_dir() {
		$upload_dir = wp_upload_dir();
		return trailingslashit( $upload_dir['basedir'] ) . 'woodmart-invoices/';
	}
}

/**
 * Ensure upload directory exists.
 *
 * @since 1.0.0
 * @return bool True if directory exists or was created.
 */
if ( ! function_exists( 'woodmart_invoices_ensure_upload_dir' ) ) {
	function woodmart_invoices_ensure_upload_dir() {
		$upload_dir = woodmart_invoices_get_upload_dir();

		if ( ! file_exists( $upload_dir ) ) {
			wp_mkdir_p( $upload_dir );
		}

		// Create .htaccess file to protect the directory.
		$htaccess_file = $upload_dir . '.htaccess';
		if ( ! file_exists( $htaccess_file ) ) {
			$htaccess_content = "Order deny,allow\nDeny from all\n";
			file_put_contents( $htaccess_file, $htaccess_content );
		}

		return file_exists( $upload_dir );
	}
}

/**
 * Sanitize filename for invoices.
 *
 * @since 1.0.0
 * @param string $filename The filename to sanitize.
 * @return string Sanitized filename.
 */
if ( ! function_exists( 'woodmart_invoices_sanitize_filename' ) ) {
	function woodmart_invoices_sanitize_filename( $filename ) {
		// Remove special characters except dots, hyphens, and underscores.
		$filename = preg_replace( '/[^a-zA-Z0-9\-_\.]/', '', $filename );

		// Remove multiple dots.
		$filename = preg_replace( '/\.+/', '.', $filename );

		// Ensure it doesn't start with a dot.
		$filename = ltrim( $filename, '.' );

		return $filename;
	}
}

/**
 * Get company information for invoices.
 *
 * @since 1.0.0
 * @return array Company information.
 */
if ( ! function_exists( 'woodmart_invoices_get_company_info' ) ) {
	function woodmart_invoices_get_company_info() {
		$settings = get_option( 'woodmart_invoices_settings', array() );
		return array(
			'name'    => isset( $settings['company_name'] ) ? $settings['company_name'] : get_bloginfo( 'name' ),
			'address' => isset( $settings['company_address'] ) ? $settings['company_address'] : '',
			'email'   => isset( $settings['company_email'] ) ? $settings['company_email'] : get_option( 'admin_email' ),
			'phone'   => isset( $settings['company_phone'] ) ? $settings['company_phone'] : '',
			'website' => home_url(),
			'logo'    => '',
		);
	}
}

/**
 * Format order data for invoice generation.
 *
 * @since 1.0.0
 * @param WC_Order $order The WooCommerce order object.
 * @return array Formatted order data.
 */
if ( ! function_exists( 'woodmart_invoices_format_order_data' ) ) {
	function woodmart_invoices_format_order_data( $order ) {
		if ( ! $order ) {
			return array();
		}

		$order_data = array(
			'id'               => $order->get_id(),
			'number'           => $order->get_order_number(),
			'date'             => $order->get_date_created(),
			'status'           => $order->get_status(),
			'currency'         => $order->get_currency(),
			'total'            => $order->get_total(),
			'subtotal'         => $order->get_subtotal(),
			'tax_total'        => $order->get_total_tax(),
			'shipping_total'   => $order->get_shipping_total(),
			'discount_total'   => $order->get_discount_total(),
			'payment_method'   => $order->get_payment_method_title(),
			'billing_address'  => $order->get_formatted_billing_address(),
			'shipping_address' => $order->get_formatted_shipping_address(),
			'customer_note'    => $order->get_customer_note(),
			'items'            => array(),
			'fees'             => array(),
			'shipping'         => array(),
			'taxes'            => array(),
		);

		// Get order items.
		foreach ( $order->get_items() as $item_id => $item ) {
			$product               = $item->get_product();
			$order_data['items'][] = array(
				'id'         => $item_id,
				'name'       => $item->get_name(),
				'sku'        => $product ? $product->get_sku() : '',
				'quantity'   => $item->get_quantity(),
				'subtotal'   => $item->get_subtotal(),
				'total'      => $item->get_total(),
				'tax_total'  => $item->get_total_tax(),
				'unit_price' => $item->get_subtotal() / max( 1, $item->get_quantity() ),
			);
		}

		// Get fees.
		foreach ( $order->get_fees() as $fee_id => $fee ) {
			$order_data['fees'][] = array(
				'id'    => $fee_id,
				'name'  => $fee->get_name(),
				'total' => $fee->get_total(),
			);
		}

		// Get shipping methods.
		foreach ( $order->get_shipping_methods() as $shipping_id => $shipping ) {
			$order_data['shipping'][] = array(
				'id'     => $shipping_id,
				'name'   => $shipping->get_name(),
				'total'  => $shipping->get_total(),
				'method' => $shipping->get_method_title(),
			);
		}

		// Get taxes.
		foreach ( $order->get_tax_totals() as $tax_code => $tax ) {
			$order_data['taxes'][] = array(
				'code'  => $tax_code,
				'label' => $tax->label,
				'total' => $tax->amount,
			);
		}

		return $order_data;
	}
}

/**
 * Generate invoice number.
 *
 * @since 1.0.0
 * @param int $order_id The order ID.
 * @return string Generated invoice number.
 */
if ( ! function_exists( 'woodmart_invoices_generate_invoice_number' ) ) {
	function woodmart_invoices_generate_invoice_number( $order_id ) {
		$prefix = get_option( 'woodmart_invoices_number_prefix', 'INV-' );
		$suffix = get_option( 'woodmart_invoices_number_suffix', '' );
		$digits = get_option( 'woodmart_invoices_number_digits', 4 );

		$number = str_pad( $order_id, $digits, '0', STR_PAD_LEFT );

		return $prefix . $number . $suffix;
	}
}

/**
 * Check if WooCommerce is installed and active.
 *
 * @since 1.0.0
 * @return bool True if WooCommerce is active.
 */
if ( ! function_exists( 'woodmart_woocommerce_installed' ) ) {
	function woodmart_woocommerce_installed() {
		return class_exists( 'WooCommerce' );
	}
}

/**
 * Get plugin option with default value.
 *
 * @since 1.0.0
 * @param string $option_name The option name.
 * @param mixed  $default_value The default value.
 * @return mixed The option value.
 */
if ( ! function_exists( 'woodmart_invoices_get_option' ) ) {
	function woodmart_invoices_get_option( $option_name, $default_value = '' ) {
		$settings = get_option( 'woodmart_invoices_settings', array() );
		return isset( $settings[ $option_name ] ) ? $settings[ $option_name ] : $default_value;
	}
}

/**
 * Clean variables using sanitize_text_field.
 *
 * @since 1.0.0
 * @param string|array $var Data to sanitize.
 * @return string|array Sanitized data.
 */
if ( ! function_exists( 'woodmart_clean' ) ) {
	function woodmart_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'woodmart_clean', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}
}

if ( ! function_exists( 'woodmart_invoices_get_upload_url' ) ) {
	/**
	 * Get plugin upload URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	function woodmart_invoices_get_upload_url() {
		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'] . '/woodmart-invoices/';
	}
}

if ( ! function_exists( 'woodmart_invoices_update_option' ) ) {
	/**
	 * Update plugin option.
	 *
	 * @since 1.0.0
	 * @param string $key Option key.
	 * @param mixed  $value Option value.
	 * @return bool
	 */
	function woodmart_invoices_update_option( $key, $value ) {
		$settings         = get_option( 'woodmart_invoices_settings', array() );
		$settings[ $key ] = $value;
		return update_option( 'woodmart_invoices_settings', $settings );
	}
}

if ( ! function_exists( 'woodmart_invoices_get_template' ) ) {
	/**
	 * Get template file.
	 *
	 * @since 1.0.0
	 * @param string $template_name Template name.
	 * @param string $template_type Template type (pdf, udp, emails, packing-clips).
	 * @return string
	 */
	function woodmart_invoices_get_template( $template_name, $template_type = 'pdf' ) {
		$template_path = WOODMART_INVOICES_PLUGIN_DIR . "templates/{$template_type}/{$template_name}";

		// Check if template exists.
		if ( file_exists( $template_path ) ) {
			return $template_path;
		}

		// Fallback to default template.
		$default_template = WOODMART_INVOICES_PLUGIN_DIR . "templates/{$template_type}/default.php";

		if ( file_exists( $default_template ) ) {
			return $default_template;
		}

		return '';
	}
}

if ( ! function_exists( 'woodmart_invoices_render_template' ) ) {
	/**
	 * Render template with data.
	 *
	 * @since 1.0.0
	 * @param string $template_path Template path.
	 * @param array  $data Template data.
	 * @return string
	 */
	function woodmart_invoices_render_template( $template_path, $data = array() ) {
		if ( ! file_exists( $template_path ) ) {
			return '';
		}

		// Extract data to variables.
		if ( ! empty( $data ) ) {
			extract( $data, EXTR_SKIP );
		}

		ob_start();
		include $template_path;
		return ob_get_clean();
	}
}

if ( ! function_exists( 'woodmart_invoices_add_email_attachments' ) ) {
	/**
	 * Add attachments to WooCommerce emails.
	 *
	 * @since 1.0.0
	 * @param array  $attachments Existing attachments.
	 * @param string $email_id Email ID.
	 * @param mixed  $object Email object (order, customer, etc).
	 * @return array Modified attachments array.
	 */
	function woodmart_invoices_add_email_attachments( $attachments, $email_id, $object ) {
		// Get order object.
		$order = woodmart_invoices_get_order_from_object( $object );

		if ( ! $order ) {
			return $attachments;
		}

		$settings                = get_option( 'woodmart_invoices_settings', array() );
		$attach_invoices_to      = $settings['attach_invoices_to'] ?? array();
		$attach_packing_slips_to = $settings['attach_packing_slips_to'] ?? array();

		// Add PDF invoices.
		if ( in_array( $email_id, $attach_invoices_to, true ) ) {
			$invoice_path = woodmart_invoices_generate_pdf_invoice( $order->get_id() );
			if ( $invoice_path && file_exists( $invoice_path ) ) {
				$attachments[] = $invoice_path;
			}
		}

		// Add packing slips.
		if ( in_array( $email_id, $attach_packing_slips_to, true ) ) {
			$packing_slip_path = woodmart_invoices_generate_packing_slip( $order->get_id() );
			if ( $packing_slip_path && file_exists( $packing_slip_path ) ) {
				$attachments[] = $packing_slip_path;
			}
		}

		return $attachments;
	}
}

if ( ! function_exists( 'woodmart_invoices_get_order_from_object' ) ) {
	/**
	 * Get order object from various email objects.
	 *
	 * @since 1.0.0
	 * @param mixed $object Email object.
	 * @return WC_Order|false Order object or false.
	 */
	function woodmart_invoices_get_order_from_object( $object ) {
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
}

if ( ! function_exists( 'woodmart_invoices_generate_pdf_invoice' ) ) {
	/**
	 * Generate PDF invoice for order.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID.
	 * @return string|false File path or false.
	 */
	function woodmart_invoices_generate_pdf_invoice( $order_id ) {
		// Check if PDF invoices are enabled.
		$pdf_enabled = woodmart_invoices_get_option( 'pdf_enabled', 'yes' );

		if ( 'yes' !== $pdf_enabled ) {
			return false;
		}

		// Use existing PDF generator if available.
		if ( class_exists( 'WoodMart\Invoices\Invoices_Registry' ) ) {
			$registry = WoodMart\Invoices\Invoices_Registry::getInstance();
			if ( isset( $registry->pdf_generator ) ) {
				$pdf_generator = $registry->pdf_generator;

				// Initialize generator if not already done.
				if ( method_exists( $pdf_generator, 'init' ) ) {
					$pdf_generator->init();
				}

				return $pdf_generator->generate( $order_id );
			}
		}

		return false;
	}
}

if ( ! function_exists( 'woodmart_invoices_generate_packing_slip' ) ) {
	/**
	 * Generate packing slip for order.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID.
	 * @return string|false File path or false.
	 */
	function woodmart_invoices_generate_packing_slip( $order_id ) {
		// Check if packing slips are enabled.
		$packing_slips_enabled = woodmart_invoices_get_option( 'packing_slips_enabled', 'yes' );

		if ( 'yes' !== $packing_slips_enabled ) {
			return false;
		}

		// Use existing packing slip generator if available.
		if ( class_exists( 'WoodMart\Invoices\Invoices_Registry' ) ) {
			$registry = WoodMart\Invoices\Invoices_Registry::getInstance();
			if ( isset( $registry->packing_slip_generator ) ) {
				$packing_slip_generator = $registry->packing_slip_generator;

				// Initialize generator if not already done.
				if ( method_exists( $packing_slip_generator, 'init' ) ) {
					$packing_slip_generator->init();
				}

				return $packing_slip_generator->generate( $order_id );
			}
		}

		return false;
	}
}

if ( ! function_exists( 'woodmart_invoices_init_email_attachments' ) ) {
	/**
	 * Initialize email attachments functionality.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function woodmart_invoices_init_email_attachments() {
		// Hook into WooCommerce email attachments.
		add_filter( 'woocommerce_email_attachments', 'woodmart_invoices_add_email_attachments', 10, 3 );
	}
}

// Initialize email attachments on plugins_loaded.
add_action( 'plugins_loaded', 'woodmart_invoices_init_email_attachments', 20 );
