<?php

/**
 * Main plugin class.
 *
 * @package XTS_PLUGIN
 * @since 1.0.0
 */

namespace XTS_PLUGIN;

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
		add_action( 'init', array( $this, 'register_custom_order_statuses' ) );
		add_filter( 'wc_order_statuses', array( $this, 'add_custom_order_statuses' ) );
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
	 * Register custom order statuses.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_custom_order_statuses() {
		register_post_status(
			'wc-shipped',
			array(
				'label'                     => _x( 'Shipped', 'Order status', 'woodmart-invoices' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Shipped (%s)', 'Shipped (%s)', 'woodmart-invoices' ),
			)
		);
	}

	/**
	 * Add custom order statuses to WooCommerce.
	 *
	 * @since 1.0.0
	 * @param array $order_statuses Existing order statuses.
	 * @return array
	 */
	public function add_custom_order_statuses( $order_statuses ) {
		$order_statuses['wc-shipped'] = _x( 'Shipped', 'Order status', 'woodmart-invoices' );
		return $order_statuses;
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
				'emails/class-shipped-order-email',
			)
		);

		// Initialize registry.
		$registry = Invoices_Registry::getInstance();

		// Initialize email attachments.
		if ( isset( $registry->email_attachments ) ) {
			$registry->email_attachments->init();
		}

		// Add WooCommerce related hooks.
		if ( woodmart_woocommerce_installed() ) {
			$this->register_email_classes();
		}

		// Hook into WooCommerce init.
		add_action( 'init', array( $this, 'register_custom_order_statuses' ) );
		add_filter( 'wc_order_statuses', array( $this, 'add_custom_order_statuses' ) );
	}

	/**
	 * Register custom email classes with WooCommerce.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_email_classes() {
		add_filter( 'woocommerce_email_classes', array( $this, 'add_email_classes' ) );
	}

	/**
	 * Add custom email classes to WooCommerce.
	 *
	 * @since 1.0.0
	 * @param array $email_classes Existing email classes.
	 * @return array Modified email classes.
	 */
	public function add_email_classes( $email_classes ) {
		// Add our custom shipped order email class
		$email_classes['WC_Shipped_Order_Email'] = new WC_Shipped_Order_Email();

		return $email_classes;
	}
}
