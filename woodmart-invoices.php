<?php

/**
 * Plugin Name: WoodMart Invoices
 * Plugin URI: https://woodmart.com
 * Description: Generate PDF and UBL invoices, packing slips for WooCommerce orders.
 * Version: 1.0.0
 * Author: WoodMart
 * Author URI: https://woodmart.com
 * Text Domain: woodmart-invoices
 * Domain Path: /languages/
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * Woo: 8.0.0:8.0.0
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'WOODMART_INVOICES_VERSION', '1.0.0' );
define( 'WOODMART_INVOICES_PLUGIN_FILE', __FILE__ );
define( 'WOODMART_INVOICES_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WOODMART_INVOICES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOODMART_INVOICES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WOODMART_INVOICES_FRAMEWORK', 'includes' );
define( 'WOODMART_INVOICES_CLASSES', WOODMART_INVOICES_PLUGIN_DIR . 'includes/classes' );

// Load plugin classes.
function woodmart_invoices_load_classes() {
	$classes = array(
		'class-singleton.php',
		'class-registry.php',
		'class-main.php',
		'class-admin.php',
		'class-woocommerce.php',
		'class-ajax.php',
		'class-invoice-generator.php',
		'class-pdf-generator.php',
		'class-ubl-generator.php',
		'class-packing-slip-generator.php',
		'class-invoices-email-attachments.php',
	);

	foreach ( $classes as $class ) {
		require WOODMART_INVOICES_CLASSES . DIRECTORY_SEPARATOR . $class;
	}
}

// Check if WooCommerce is active.
function woodmart_invoices_check_woocommerce() {
	return class_exists( 'WooCommerce' );
}

// Initialize the plugin.
function woodmart_invoices_init() {
	if ( ! woodmart_invoices_check_woocommerce() ) {
		add_action( 'admin_notices', 'woodmart_invoices_woocommerce_missing_notice' );
		return;
	}

	woodmart_invoices_load_classes();
	require_once WOODMART_INVOICES_PLUGIN_DIR . 'includes/functions.php';
	new WoodMart\Invoices\Invoices_Main();
}

// Show notice if WooCommerce is not active.
function woodmart_invoices_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					__( 'WoodMart Invoices requires %s to be installed and active.', 'woodmart-invoices' ),
					'<a href="' . admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) . '">WooCommerce</a>'
				)
			);
			?>
		</p>
	</div>
	<?php
}

// Initialize plugin.
add_action( 'plugins_loaded', 'woodmart_invoices_init' );

// Declare HPOS compatibility.
add_action( 'before_woocommerce_init', 'woodmart_invoices_declare_hpos_compatibility' );

if ( ! function_exists( 'woodmart_invoices_declare_hpos_compatibility' ) ) {
	/**
	 * Declare HPOS compatibility.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function woodmart_invoices_declare_hpos_compatibility() {
		if ( class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
}

// Plugin activation hook.
register_activation_hook( __FILE__, 'woodmart_invoices_activate' );

if ( ! function_exists( 'woodmart_invoices_activate' ) ) {
	/**
	 * Plugin activation hook.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function woodmart_invoices_activate() {
		// Create upload directory for invoices.
		$upload_dir  = wp_upload_dir();
		$invoice_dir = $upload_dir['basedir'] . '/woodmart-invoices/';

		if ( ! file_exists( $invoice_dir ) ) {
			wp_mkdir_p( $invoice_dir );

			// Create .htaccess file to allow file downloads but prevent directory browsing.
			$htaccess_content  = "# Protect invoice files directory\n";
			$htaccess_content .= "Options -Indexes\n";
			$htaccess_content .= "# Allow PDF and XML downloads\n";
			$htaccess_content .= "<Files ~ '\.(pdf|xml)$'>\n";
			$htaccess_content .= "    Order Allow,Deny\n";
			$htaccess_content .= "    Allow from all\n";
			$htaccess_content .= "</Files>\n";

			file_put_contents( $invoice_dir . '.htaccess', $htaccess_content );
		}

		// Set default options.
		if ( ! get_option( 'woodmart_invoices_settings' ) ) {
			$default_settings = array(
				'pdf_enabled'             => 'yes',
				'ubl_enabled'             => 'no',
				'packing_slips_enabled'   => 'yes',
				'attach_invoices_to'      => array( 'customer_processing_order', 'customer_completed_order' ),
				'attach_packing_slips_to' => array( 'customer_processing_order', 'customer_completed_order' ),
				'company_name'            => get_bloginfo( 'name' ),
				'company_address'         => '',
				'company_email'           => get_option( 'admin_email' ),
				'company_phone'           => '',
				'company_logo'            => '',
				'invoice_template'        => 'default',
				'packing_slip_template'   => 'default',
			);

			update_option( 'woodmart_invoices_settings', $default_settings );
		}
	}
}

// Plugin deactivation hook.
register_deactivation_hook( __FILE__, 'woodmart_invoices_deactivate' );

if ( ! function_exists( 'woodmart_invoices_deactivate' ) ) {
	/**
	 * Plugin deactivation hook.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function woodmart_invoices_deactivate() {
		// Clean up temporary files.
		$upload_dir = wp_upload_dir();
		$temp_dir   = $upload_dir['basedir'] . '/woodmart-invoices/temp/';

		if ( file_exists( $temp_dir ) ) {
			$files = glob( $temp_dir . '*' );
			foreach ( $files as $file ) {
				if ( is_file( $file ) && ( time() - filemtime( $file ) ) > 3600 ) {
					unlink( $file );
				}
			}
		}
	}
}
