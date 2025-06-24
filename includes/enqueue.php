<?php

/**
 * Enqueue scripts and styles.
 *
 * @package WoodMart\Invoices
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Enqueue frontend scripts and styles.
 *
 * @since 1.0.0
 * @return void
 */
function woodmart_invoices_enqueue_frontend_scripts() {
	wp_enqueue_style(
		'woodmart-invoices-frontend',
		WOODMART_INVOICES_PLUGIN_URL . 'assets/css/frontend.css',
		array(),
		WOODMART_INVOICES_VERSION
	);

	wp_enqueue_script(
		'woodmart-invoices-frontend',
		WOODMART_INVOICES_PLUGIN_URL . 'assets/js/frontend.js',
		array( 'jquery' ),
		WOODMART_INVOICES_VERSION,
		true
	);

	wp_localize_script(
		'woodmart-invoices-frontend',
		'woodmartInvoices',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'woodmart_invoices_nonce' ),
		)
	);
}

add_action( 'wp_enqueue_scripts', 'woodmart_invoices_enqueue_frontend_scripts' );
