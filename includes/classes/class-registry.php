<?php

/**
 * Registry helper class.
 *
 * @package XTS_PLUGIN
 * @since 1.0.0
 */

namespace XTS_PLUGIN;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Object Registry for WoodMart Invoices.
 *
 * @since 1.0.0
 */
class Invoices_Registry {

	/**
	 * Holds an instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Short names of known objects.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $known_objects = array();

	/**
	 * PDF Generator instance.
	 *
	 * @since 1.0.0
	 * @var Invoices_Pdf_Generator
	 */
	public $pdf_generator;

	/**
	 * UBL Generator instance.
	 *
	 * @since 1.0.0
	 * @var Invoices_Ubl_Generator
	 */
	public $ubl_generator;

	/**
	 * Packing Slip Generator instance.
	 *
	 * @since 1.0.0
	 * @var Invoices_Packing_Slip_Generator
	 */
	public $packing_slip_generator;

	/**
	 * Invoice Generator instance.
	 *
	 * @since 1.0.0
	 * @var Invoices_Invoice_Generator
	 */
	public $invoice_generator;

	/**
	 * Admin instance.
	 *
	 * @since 1.0.0
	 * @var Invoices_Admin
	 */
	public $admin;

	/**
	 * WooCommerce integration instance.
	 *
	 * @since 1.0.0
	 * @var Invoices_WooCommerce
	 */
	public $woocommerce;

	/**
	 * Ajax handler instance.
	 *
	 * @since 1.0.0
	 * @var Invoices_Ajax
	 */
	public $ajax;

	/**
	 * Email Attachments instance.
	 *
	 * @since 1.0.0
	 * @var Invoices_Email_Attachments
	 */
	public $email_attachments;

	/**
	 * Restrict direct initialization, use Invoices_Registry::getInstance() instead.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->init_classes();
	}

	/**
	 * Get instance of the object (the singleton method).
	 *
	 * @since 1.0.0
	 * @return Invoices_Registry
	 */
	public static function getInstance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Dynamically load missing object and assign it to the Registry property.
	 *
	 * @since 1.0.0
	 * @param string $obj Object name (first char will be converted to upper case).
	 * @return object
	 */
	public function __get( $obj ) {
		if ( ! isset( $this->known_objects[ $obj ] ) ) {
			try {
				$this->save_object( $obj );
			} catch ( Exception $e ) {
				echo esc_html( $e->getTraceAsString() );
			}
		}

		return $this->known_objects[ $obj ];
	}

	/**
	 * Initialize and save object.
	 *
	 * @since 1.0.0
	 * @param string $obj Object name (first char will be converted to upper case).
	 * @return void
	 */
	private function save_object( $obj ) {
		// Skip if object already exists.
		if ( isset( $this->known_objects[ $obj ] ) ) {
			return;
		}

		// Convert underscore names to proper class names.
		// e.g., 'pdf_generator' -> 'Pdf_Generator'.
		$class_name_parts = explode( '_', $obj );
		$class_name_parts = array_map( 'ucfirst', $class_name_parts );
		$formatted_name   = implode( '_', $class_name_parts );

		$objname = 'XTS_PLUGIN\\Invoices_' . $formatted_name;

		if ( is_string( $obj ) && class_exists( $objname ) ) {
			if ( method_exists( $objname, 'get_instance' ) ) {
				// Use singleton pattern.
				$this->known_objects[ $obj ] = $objname::get_instance();
			} else {
				// Create new instance.
				$this->known_objects[ $obj ] = new $objname();
			}
		}
	}

	/**
	 * Prevent users to clone the instance.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		trigger_error( 'Clone is not allowed.', E_USER_ERROR );
	}

	/**
	 * Initialize all classes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_classes() {
		$this->pdf_generator           = Invoices_Pdf_Generator::get_instance();
		$this->ubl_generator           = Invoices_Ubl_Generator::get_instance();
		$this->packing_slip_generator  = Invoices_Packing_Slip_Generator::get_instance();
		$this->invoice_generator       = Invoices_Invoice_Generator::get_instance();
		$this->admin                   = new Invoices_Admin();
		$this->woocommerce             = new Invoices_WooCommerce();
		$this->ajax                    = new Invoices_Ajax();
		$this->email_attachments       = Invoices_Email_Attachments::get_instance();
	}
}
