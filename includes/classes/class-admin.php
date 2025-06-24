<?php

/**
 * Admin class.
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

namespace WoodMart\Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 * Admin class.
 *
 * @since 1.0.0
 */
class Invoices_Admin {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Add admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Add settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add admin menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'WoodMart Invoices', 'woodmart-invoices' ),
			__( 'WoodMart Invoices', 'woodmart-invoices' ),
			'manage_woocommerce',
			'woodmart-invoices',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Admin page callback.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_page() {
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'WoodMart Invoices Settings', 'woodmart-invoices' ); ?></h1>
			
			<nav class="nav-tab-wrapper">
				<a href="?page=woodmart-invoices&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'General', 'woodmart-invoices' ); ?>
				</a>
				<a href="?page=woodmart-invoices&tab=invoices" class="nav-tab <?php echo 'invoices' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'PDF/UBL Invoices', 'woodmart-invoices' ); ?>
				</a>
				<a href="?page=woodmart-invoices&tab=packing-slips" class="nav-tab <?php echo 'packing-slips' === $active_tab ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Packing Slips', 'woodmart-invoices' ); ?>
				</a>
			</nav>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'woodmart_invoices_settings' );

				switch ( $active_tab ) {
					case 'invoices':
						$this->render_invoices_tab();
						break;
					case 'packing-slips':
						$this->render_packing_slips_tab();
						break;
					default:
						$this->render_general_tab();
						break;
				}

				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render general tab.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function render_general_tab() {
		$settings = get_option( 'woodmart_invoices_settings', array() );
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Company Name', 'woodmart-invoices' ); ?></th>
				<td>
					<input type="text" name="woodmart_invoices_settings[company_name]" value="<?php echo esc_attr( $settings['company_name'] ?? '' ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Company Address', 'woodmart-invoices' ); ?></th>
				<td>
					<textarea name="woodmart_invoices_settings[company_address]" rows="4" cols="50"><?php echo esc_textarea( $settings['company_address'] ?? '' ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Company Email', 'woodmart-invoices' ); ?></th>
				<td>
					<input type="email" name="woodmart_invoices_settings[company_email]" value="<?php echo esc_attr( $settings['company_email'] ?? '' ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Company Phone', 'woodmart-invoices' ); ?></th>
				<td>
					<input type="text" name="woodmart_invoices_settings[company_phone]" value="<?php echo esc_attr( $settings['company_phone'] ?? '' ); ?>" class="regular-text" />
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render invoices tab.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function render_invoices_tab() {
		$settings           = get_option( 'woodmart_invoices_settings', array() );
		$available_emails   = $this->get_available_wc_emails();
		$attach_invoices_to = $settings['attach_invoices_to'] ?? array();
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable PDF Invoices', 'woodmart-invoices' ); ?></th>
				<td>
					<label>
						<input type="hidden" name="woodmart_invoices_settings[pdf_enabled]" value="no" />
						<input type="checkbox" name="woodmart_invoices_settings[pdf_enabled]" value="yes" <?php checked( $settings['pdf_enabled'] ?? '', 'yes' ); ?> />
						<?php esc_html_e( 'Generate PDF invoices', 'woodmart-invoices' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable UBL Invoices', 'woodmart-invoices' ); ?></th>
				<td>
					<label>
						<input type="hidden" name="woodmart_invoices_settings[ubl_enabled]" value="no" />
						<input type="checkbox" name="woodmart_invoices_settings[ubl_enabled]" value="yes" <?php checked( $settings['ubl_enabled'] ?? '', 'yes' ); ?> />
						<?php esc_html_e( 'Generate UBL XML invoices', 'woodmart-invoices' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Attach PDF Invoices to Emails', 'woodmart-invoices' ); ?></th>
				<td>
					<?php foreach ( $available_emails as $email_id => $email_data ) : ?>
						<label style="display: block; margin-bottom: 5px;">
							<input type="checkbox" 
									name="woodmart_invoices_settings[attach_invoices_to][]" 
									value="<?php echo esc_attr( $email_id ); ?>" 
									<?php checked( in_array( $email_id, $attach_invoices_to, true ) ); ?> />
							<?php echo esc_html( $email_data['title'] ); ?>
							<small style="color: #666;">(<?php echo esc_html( $email_data['description'] ); ?>)</small>
						</label>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render packing slips tab.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function render_packing_slips_tab() {
		$settings                = get_option( 'woodmart_invoices_settings', array() );
		$available_emails        = $this->get_available_wc_emails();
		$attach_packing_slips_to = $settings['attach_packing_slips_to'] ?? array();
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable Packing Slips', 'woodmart-invoices' ); ?></th>
				<td>
					<label>
						<input type="hidden" name="woodmart_invoices_settings[packing_slips_enabled]" value="no" />
						<input type="checkbox" name="woodmart_invoices_settings[packing_slips_enabled]" value="yes" <?php checked( $settings['packing_slips_enabled'] ?? '', 'yes' ); ?> />
						<?php esc_html_e( 'Generate packing slips', 'woodmart-invoices' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Attach Packing Slips PDF to Emails', 'woodmart-invoices' ); ?></th>
				<td>
					<?php foreach ( $available_emails as $email_id => $email_data ) : ?>
						<label style="display: block; margin-bottom: 5px;">
							<input type="checkbox" 
									name="woodmart_invoices_settings[attach_packing_slips_to][]" 
									value="<?php echo esc_attr( $email_id ); ?>" 
									<?php checked( in_array( $email_id, $attach_packing_slips_to, true ) ); ?> />
							<?php echo esc_html( $email_data['title'] ); ?>
							<small style="color: #666;">(<?php echo esc_html( $email_data['description'] ); ?>)</small>
						</label>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Register settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'woodmart_invoices_settings',
			'woodmart_invoices_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitize settings callback.
	 *
	 * @since 1.0.0
	 * @param array $input Input settings.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// Get existing settings to preserve data across tabs
		$existing_settings = get_option( 'woodmart_invoices_settings', array() );

		// Ensure both arrays are valid
		$existing_settings = is_array( $existing_settings ) ? $existing_settings : array();
		$input             = is_array( $input ) ? $input : array();

		// Merge with existing settings
		$sanitized = array_merge( $existing_settings, $input );

		// Sanitize each field
		if ( isset( $sanitized['company_name'] ) ) {
			$sanitized['company_name'] = sanitize_text_field( $sanitized['company_name'] );
		}

		if ( isset( $sanitized['company_address'] ) ) {
			$sanitized['company_address'] = sanitize_textarea_field( $sanitized['company_address'] );
		}

		if ( isset( $sanitized['company_email'] ) ) {
			$sanitized['company_email'] = sanitize_email( $sanitized['company_email'] );
		}

		if ( isset( $sanitized['company_phone'] ) ) {
			$sanitized['company_phone'] = sanitize_text_field( $sanitized['company_phone'] );
		}

		// Handle checkboxes - now they always have values due to hidden fields
		if ( isset( $sanitized['pdf_enabled'] ) ) {
			$sanitized['pdf_enabled'] = ( 'yes' === $sanitized['pdf_enabled'] ) ? 'yes' : 'no';
		}

		if ( isset( $sanitized['ubl_enabled'] ) ) {
			$sanitized['ubl_enabled'] = ( 'yes' === $sanitized['ubl_enabled'] ) ? 'yes' : 'no';
		}

		if ( isset( $sanitized['packing_slips_enabled'] ) ) {
			$sanitized['packing_slips_enabled'] = ( 'yes' === $sanitized['packing_slips_enabled'] ) ? 'yes' : 'no';
		}

		// Sanitize email attachment settings.
		if ( isset( $input['attach_invoices_to'] ) && is_array( $input['attach_invoices_to'] ) ) {
			$sanitized['attach_invoices_to'] = array_map( 'sanitize_text_field', $input['attach_invoices_to'] );
		} else {
			$sanitized['attach_invoices_to'] = array();
		}

		if ( isset( $input['attach_packing_slips_to'] ) && is_array( $input['attach_packing_slips_to'] ) ) {
			$sanitized['attach_packing_slips_to'] = array_map( 'sanitize_text_field', $input['attach_packing_slips_to'] );
		} else {
			$sanitized['attach_packing_slips_to'] = array();
		}

		return $sanitized;
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only load on our admin pages and WooCommerce pages.
		if ( ! $this->is_woodmart_invoices_page( $hook ) && ! $this->is_woocommerce_page( $hook ) ) {
			return;
		}

		wp_enqueue_style(
			'woodmart-invoices-admin',
			WOODMART_INVOICES_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			WOODMART_INVOICES_VERSION
		);

		wp_enqueue_script(
			'woodmart-invoices-admin',
			WOODMART_INVOICES_PLUGIN_URL . 'admin/js/admin.js',
			array( 'jquery', 'wp-util' ),
			WOODMART_INVOICES_VERSION,
			true
		);

		wp_localize_script(
			'woodmart-invoices-admin',
			'woodmartInvoicesAdmin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'woodmart_invoices_admin_nonce' ),
				'strings'  => array(
					'generating_pdf'     => __( 'Generating PDF...', 'woodmart-invoices' ),
					'generating_ubl'     => __( 'Generating UBL...', 'woodmart-invoices' ),
					'generating_packing' => __( 'Generating Packing Slip...', 'woodmart-invoices' ),
					'error'              => __( 'An error occurred. Please try again.', 'woodmart-invoices' ),
					'success'            => __( 'Generated successfully!', 'woodmart-invoices' ),
				),
			)
		);
	}

	/**
	 * Check if current page is a WoodMart Invoices admin page.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return bool
	 */
	private function is_woodmart_invoices_page( $hook ) {
		return strpos( $hook, 'woodmart-invoices' ) !== false;
	}

	/**
	 * Check if current page is a WooCommerce admin page.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return bool
	 */
	private function is_woocommerce_page( $hook ) {
		$wc_pages = array(
			'edit.php',
			'post.php',
			'post-new.php',
			'woocommerce_page_wc-orders',
			'woocommerce_page_wc-settings',
			'shop_order',
		);

		return in_array( $hook, $wc_pages, true ) ||
				( isset( $_GET['post_type'] ) && 'shop_order' === $_GET['post_type'] );
	}

	/**
	 * Get available WooCommerce emails.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function get_available_wc_emails() {
		$emails = array();

		// Standard WooCommerce emails
		$standard_emails = array(
			'customer_processing_order' => array(
				'title'       => __( 'Processing Order', 'woodmart-invoices' ),
				'description' => __( 'Sent to customers when their orders are marked processing', 'woodmart-invoices' ),
			),
			'customer_completed_order'  => array(
				'title'       => __( 'Completed Order', 'woodmart-invoices' ),
				'description' => __( 'Sent to customers when their orders are marked completed', 'woodmart-invoices' ),
			),
			'customer_on_hold_order'    => array(
				'title'       => __( 'Order On-hold', 'woodmart-invoices' ),
				'description' => __( 'Sent to customers when their orders are put on-hold', 'woodmart-invoices' ),
			),
			'customer_invoice'          => array(
				'title'       => __( 'Customer Invoice', 'woodmart-invoices' ),
				'description' => __( 'Sent to customers manually or when paying for orders', 'woodmart-invoices' ),
			),
			'customer_refunded_order'   => array(
				'title'       => __( 'Refunded Order', 'woodmart-invoices' ),
				'description' => __( 'Sent to customers when their orders are refunded', 'woodmart-invoices' ),
			),
		);

		// Get WooCommerce emails if available
		if ( function_exists( 'WC' ) && WC()->mailer() ) {
			$wc_emails = WC()->mailer()->get_emails();

			foreach ( $wc_emails as $email ) {
				// Skip admin emails by checking if email ID starts with 'customer_'
				if ( strpos( $email->id, 'customer_' ) !== 0 ) {
					continue;
				}

				$emails[ $email->id ] = array(
					'title'       => $email->get_title(),
					'description' => $email->get_description(),
				);
			}
		}

		// Fallback to standard emails if WooCommerce is not loaded
		if ( empty( $emails ) ) {
			$emails = $standard_emails;
		}

		return $emails;
	}
}
