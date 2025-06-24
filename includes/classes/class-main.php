<?php

/**
 * Main plugin class.
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

namespace WoodMart\Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
class Invoices_Main {

	/**
	 * List with main plugin class names.
	 *
	 * @since 1.0.0
	 * @var string[]
	 */
	private $register_classes = array(
		'admin',
		'woocommerce',
		'ajax',
		'invoice_generator',
		'pdf_generator',
		'ubl_generator',
		'packing_slip_generator',
	);

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->general_files_include();
		$this->register_classes();
		$this->add_hooks();
	}

	/**
	 * Include general files.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function general_files_include() {
		$files = array(
			'enqueue',
			'template-tags',
		);

		$this->enqueue_files( $files );
	}

	/**
	 * Register main plugin classes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_classes() {
		$registry = Invoices_Registry::getInstance();

		foreach ( $this->register_classes as $class ) {
			// Get instances from registry (will create them if not exists)
			$instance = $registry->$class;

			// Initialize only specific classes that need manual init
			if ( 'invoice_generator' === $class && method_exists( $instance, 'init' ) ) {
				$instance->init();
			}
		}
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function add_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'woodmart-invoices',
			false,
			dirname( WOODMART_INVOICES_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Enqueue files.
	 *
	 * @since 1.0.0
	 * @param array $files List with files to include.
	 * @return void
	 */
	private function enqueue_files( $files ) {
		foreach ( $files as $file ) {
			$file_path = WOODMART_INVOICES_PLUGIN_DIR . WOODMART_INVOICES_FRAMEWORK . '/' . $file . '.php';
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
		}
	}

	/**
	 * Init all classes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		// Load includes.
		$this->enqueue_files(
			array(
				'classes/class-singleton',
				'classes/class-registry',
				'classes/class-invoice-generator',
				'classes/class-pdf-generator',
				'classes/class-ubl-generator',
				'classes/class-packing-slip-generator',
				'classes/class-admin',
				'classes/class-woocommerce',
				'classes/class-ajax',
				'classes/class-invoices-email-attachments',
			)
		);

		// Initialize registry.
		$registry = Invoices_Registry::getInstance();

		// Initialize email attachments.
		if ( isset( $registry->email_attachments ) ) {
			$registry->email_attachments->init();
		}
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
