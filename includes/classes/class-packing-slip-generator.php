<?php

/**
 * Packing slip generator class.
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

namespace WoodMart\Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Packing slip generator class.
 *
 * @since 1.0.0
 */
class Invoices_Packing_Slip_Generator extends Invoices_Invoice_Generator {

	/**
	 * Initialize the generator.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		parent::init();
	}
	/**
	 * Generate packing slip.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID.
	 * @return string|false File path or false on failure.
	 */
	public function generate( $order_id ) {
		$order_data = $this->get_order_data( $order_id );

		if ( ! $order_data ) {
			return false;
		}

		$html = $this->generate_html( $order_data );

		$pdf_path = $this->generate_pdf( $html, $order_id );

		return $pdf_path;
	}

	/**
	 * Generate HTML for packing slip.
	 *
	 * @since 1.0.0
	 * @param array $order_data Order data.
	 * @return string
	 */
	private function generate_html( $order_data ) {
		$company           = $this->get_company_info();
		$order             = $order_data;
		$packing_slip_date = current_time( 'Y-m-d' );
		$document_title    = __( 'Packing Slip', 'woodmart-invoices' );
		ob_start();
		include WOODMART_INVOICES_PLUGIN_DIR . 'templates/packing-clips/default.php';
		return ob_get_clean();
	}

	/**
	 * Generate PDF from HTML.
	 *
	 * @since 1.0.0
	 * @param string $html HTML content.
	 * @param int    $order_id Order ID.
	 * @return string|false File path or false on failure.
	 */
	private function generate_pdf( $html, $order_id ) {
		if ( ! class_exists( 'Dompdf\Dompdf' ) ) {
			require_once WOODMART_INVOICES_PLUGIN_DIR . 'vendor/autoload.php';
		}

		$options = new \Dompdf\Options();
		$options->set( 'defaultFont', 'DejaVu Sans' );
		$options->set( 'isRemoteEnabled', true );

		$dompdf = new \Dompdf\Dompdf( $options );
		$dompdf->loadHtml( $html );
		$dompdf->setPaper( 'A4', 'portrait' );
		$dompdf->render();

		$filename  = 'packing-slip-' . $order_id . '-' . time() . '.pdf';
		$file_path = $this->upload_dir . $filename;

		file_put_contents( $file_path, $dompdf->output() );

		return file_exists( $file_path ) ? $file_path : false;
	}
}
