<?php

/**
 * WooCommerce integration class.
 *
 * @package XTS_PLUGIN
 * @since 1.0.0
 */

namespace XTS_PLUGIN;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * WooCommerce integration class.
 *
 * @since 1.0.0
 */
class Invoices_WooCommerce {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Add meta box to order edit page.
		add_action( 'add_meta_boxes', array( $this, 'add_order_meta_box' ) );
	}

	/**
	 * Add meta box to order edit page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_order_meta_box() {
		// Get plugin settings.
		$pdf_enabled           = woodmart_invoices_get_option( 'pdf_enabled', 'yes' );
		$ubl_enabled           = woodmart_invoices_get_option( 'ubl_enabled', 'no' );
		$packing_slips_enabled = woodmart_invoices_get_option( 'packing_slips_enabled', 'yes' );

		if ( 'yes' !== $pdf_enabled && 'yes' !== $ubl_enabled && 'yes' !== $packing_slips_enabled ) {
			return;
		}

		// Add meta box for legacy post-based orders.
		add_meta_box(
			'woodmart-invoices-actions',
			__( 'WoodMart Invoices', 'woodmart-invoices' ),
			array( $this, 'order_meta_box_content' ),
			'shop_order',
			'side',
			'high'
		);

		// Add meta box for HPOS orders if available.
		if ( function_exists( 'wc_get_page_screen_id' ) ) {
			$screen_id = wc_get_page_screen_id( 'shop-order' );
			if ( $screen_id ) {
				add_meta_box(
					'woodmart-invoices-actions',
					__( 'WoodMart Invoices', 'woodmart-invoices' ),
					array( $this, 'order_meta_box_content' ),
					$screen_id,
					'side',
					'high'
				);
			}
		}
	}

	/**
	 * Order meta box content.
	 *
	 * @since 1.0.0
	 * @param WP_Post|WC_Order $post_or_order Order post object or WC_Order object.
	 * @return void
	 */
	public function order_meta_box_content( $post_or_order ) {
		// Get order ID - works for both legacy and HPOS.
		if ( is_a( $post_or_order, 'WC_Order' ) ) {
			$order_id = $post_or_order->get_id();
		} else {
			$order_id = $post_or_order->ID;
		}

		// Get plugin settings.
		$pdf_enabled           = woodmart_invoices_get_option( 'pdf_enabled', 'yes' );
		$ubl_enabled           = woodmart_invoices_get_option( 'ubl_enabled', 'no' );
		$packing_slips_enabled = woodmart_invoices_get_option( 'packing_slips_enabled', 'yes' );
		?>
		<div class="woodmart-invoices-meta-box">
			<?php if ( 'yes' === $pdf_enabled ) : ?>
			<p>
				<button type="button" class="button button-primary woodmart-generate-pdf" data-order-id="<?php echo esc_attr( $order_id ); ?>">
					<?php esc_html_e( 'Generate PDF Invoice', 'woodmart-invoices' ); ?>
				</button>
			</p>
			<?php endif; ?>
			
			<?php if ( 'yes' === $ubl_enabled ) : ?>
			<p>
				<button type="button" class="button woodmart-generate-ubl" data-order-id="<?php echo esc_attr( $order_id ); ?>">
					<?php esc_html_e( 'Generate UBL Invoice', 'woodmart-invoices' ); ?>
				</button>
			</p>
			<?php endif; ?>
			
			<?php if ( 'yes' === $packing_slips_enabled ) : ?>
			<p>
				<button type="button" class="button woodmart-generate-packing-slip" data-order-id="<?php echo esc_attr( $order_id ); ?>">
					<?php esc_html_e( 'Generate Packing Slip', 'woodmart-invoices' ); ?>
				</button>
			</p>
			<?php endif; ?>
		</div>
		<?php
	}
}
