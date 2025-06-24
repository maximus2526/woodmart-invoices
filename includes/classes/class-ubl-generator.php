<?php

/**
 * UBL generator class.
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

namespace WoodMart\Invoices;

use Sabre\Xml\Writer;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * UBL generator class.
 *
 * @since 1.0.0
 */
class Invoices_Ubl_Generator extends Invoices_Invoice_Generator {

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
	 * Generate UBL invoice.
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

		$xml = $this->generate_xml( $order_data );

		$ubl_path = $this->save_xml( $xml, $order_id );

		return $ubl_path;
	}

	/**
	 * Generate UBL XML.
	 *
	 * @since 1.0.0
	 * @param array $order_data Order data.
	 * @return string
	 */
	private function generate_xml( $order_data ) {
		if ( ! class_exists( 'Writer' ) ) {
			require_once WOODMART_INVOICES_PLUGIN_DIR . 'vendor/autoload.php';
		}

		$writer = new Writer();
		$writer->openMemory();
		$writer->setIndent( true );
		$writer->startDocument( '1.0', 'UTF-8' );

		$company_info = $this->get_company_info();

		$writer->startElementNS( null, 'Invoice', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2' );

		// UBL Version.
		$writer->writeElement( 'UBLVersionID', '2.1' );

		// Invoice ID.
		$writer->writeElement( 'ID', $order_data['order_number'] );

		// Issue Date.
		$writer->writeElement( 'IssueDate', date( 'Y-m-d', strtotime( $order_data['order_date'] ) ) );

		// Invoice Type Code.
		$writer->writeElement( 'InvoiceTypeCode', '380' );

		// Document Currency Code.
		$writer->writeElement( 'DocumentCurrencyCode', $order_data['order_currency'] );

		// Supplier Party.
		$writer->startElement( 'AccountingSupplierParty' );
		$writer->startElement( 'Party' );
		$writer->startElement( 'PartyName' );
		$writer->writeElement( 'Name', $company_info['name'] );
		$writer->endElement(); // PartyName.
		$writer->endElement(); // Party.
		$writer->endElement(); // AccountingSupplierParty.

		// Customer Party.
		$writer->startElement( 'AccountingCustomerParty' );
		$writer->startElement( 'Party' );
		$writer->startElement( 'PartyName' );
		$writer->writeElement( 'Name', $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'] );
		$writer->endElement(); // PartyName.
		$writer->endElement(); // Party.
		$writer->endElement(); // AccountingCustomerParty.

		// Invoice Lines.
		foreach ( $order_data['items'] as $index => $item ) {
			$writer->startElement( 'InvoiceLine' );
			$writer->writeElement( 'ID', $index + 1 );
			$writer->writeElement( 'InvoicedQuantity', $item['quantity'] );
			$writer->startElement( 'Item' );
			$writer->writeElement( 'Name', $item['name'] );
			$writer->endElement(); // Item.
			$writer->endElement(); // InvoiceLine.
		}

		// Legal Monetary Total.
		$writer->startElement( 'LegalMonetaryTotal' );
		$writer->writeElement( 'TaxExclusiveAmount', $order_data['order_total'] );
		$writer->writeElement( 'PayableAmount', $order_data['order_total'] );
		$writer->endElement(); // LegalMonetaryTotal.

		$writer->endElement(); // Invoice.

		return $writer->outputMemory();
	}

	/**
	 * Save XML to file.
	 *
	 * @since 1.0.0
	 * @param string $xml XML content.
	 * @param int    $order_id Order ID.
	 * @return string|false File path or false on failure.
	 */
	private function save_xml( $xml, $order_id ) {
		$filename  = 'ubl-invoice-' . $order_id . '-' . time() . '.xml';
		$file_path = $this->upload_dir . $filename;

		file_put_contents( $file_path, $xml );

		return file_exists( $file_path ) ? $file_path : false;
	}
}
