<?php

/**
 * PDF generator class.
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

namespace WoodMart\Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * PDF generator class.
 *
 * @since 1.0.0
 */
class Invoices_Pdf_Generator extends Invoices_Invoice_Generator {

	/**
	 * Generate PDF invoice.
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

		if ( ! $html ) {
			return false;
		}

		$pdf_path = $this->generate_pdf( $html, $order_id );

		return $pdf_path;
	}

	/**
	 * Generate HTML for PDF.
	 *
	 * @since 1.0.0
	 * @param array $order_data Order data.
	 * @return string
	 */
	private function generate_html( $order_data ) {
		$company_info   = $this->get_company_info();
		$order_data     = $order_data; // Order data for template
		$invoice_number = $order_data['order_number'];
		$date           = date_i18n( get_option( 'date_format' ) );

		ob_start();
		include WOODMART_INVOICES_PLUGIN_DIR . 'templates/pdf/default.php';
		$html = ob_get_clean();

		return $html;
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
		if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
			require_once WOODMART_INVOICES_PLUGIN_DIR . 'vendor/autoload.php';
		}

		if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
			return false;
		}

		try {
			$options = new \Dompdf\Options();
			$options->set( 'defaultFont', 'DejaVu Sans' );
			$options->set( 'isRemoteEnabled', true );

			$dompdf = new \Dompdf\Dompdf( $options );
			$dompdf->loadHtml( $html );
			$dompdf->setPaper( 'A4', 'portrait' );
			$dompdf->render();

			$filename  = 'invoice-' . $order_id . '-' . time() . '.pdf';
			$file_path = $this->upload_dir . $filename;

			// Check if directory exists
			if ( ! file_exists( $this->upload_dir ) ) {
				return false;
			}

			file_put_contents( $file_path, $dompdf->output() );

			return file_exists( $file_path ) ? $file_path : false;
		} catch ( Exception $e ) {
			return false;
		}
	}
}
