# WoodMart Invoices Plugin

A comprehensive WordPress plugin for generating PDF invoices, UBL invoices, and packing slips for WooCommerce orders with seamless email integration.

## ✨ Features

- 🧾 **PDF Invoice Generation** - High-quality PDF invoices using DOMPDF
- 📄 **UBL Invoice Generation** - XML-based UBL invoices with Sabre XML
- 📦 **Packing Slips** - Customizable packing slips for orders
- 📧 **Email Attachments** - Automatic attachment to WooCommerce emails
- 🎨 **Template System** - Flexible template customization
- 🚚 **Custom Order Status** - "Shipped" status with email notifications
- 🔒 **Security First** - Secure file handling and access control
- 🌐 **Multi-language Ready** - Translation support
- ⚡ **Performance Optimized** - Efficient caching and lazy loading
- 🔧 **Developer Friendly** - Extensive hooks and filters

## 📋 Requirements

- **WordPress:** 5.0+
- **WooCommerce:** 5.0+
- **PHP:** 7.4+ (PHP 8.2+ compatible)
- **Memory:** 128MB+ recommended
- **Composer:** For dependency management

## 🚀 Installation

1. **Download & Upload**
   ```bash
   # Upload to your WordPress plugins directory
   /wp-content/plugins/woodmart-invoices/
   ```

2. **Install Dependencies**
   ```bash
   cd wp-content/plugins/woodmart-invoices
   composer install
   ```

3. **Activate Plugin**
   - Go to WordPress Admin → Plugins
   - Activate "WoodMart Invoices"

4. **Configure Settings**
   - Navigate to WooCommerce → WoodMart Invoices
   - Configure company details and preferences

## 🏗️ Plugin Architecture

```
woodmart-invoices/
├── woodmart-invoices.php              # Main plugin file
├── composer.json                      # Dependencies
├── includes/
│   ├── classes/
│   │   ├── class-main.php            # Main plugin class
│   │   ├── class-registry.php        # Dependency injection
│   │   ├── class-admin.php           # Admin interface
│   │   ├── class-woocommerce.php     # WooCommerce integration
│   │   ├── class-ajax.php            # AJAX handlers
│   │   ├── class-pdf-generator.php   # PDF generation
│   │   ├── class-ubl-generator.php   # UBL generation
│   │   ├── class-packing-slip-generator.php # Packing slips
│   │   ├── class-invoice-generator.php # Base generator
│   │   ├── class-invoices-email-attachments.php # Email system
│   │   └── class-singleton.php       # Singleton pattern
│   ├── emails/
│   │   └── class-shipped-order-email.php # Custom email class
│   ├── functions.php                 # Helper functions
│   └── enqueue.php                   # Asset loading
├── admin/                            # Admin assets
│   ├── css/admin.css
│   └── js/admin.js
├── assets/                           # Frontend assets
│   ├── css/frontend.css
│   └── js/frontend.js
├── templates/                        # Template files
│   ├── pdf/default.php              # PDF invoice template
│   ├── packing-clips/default.php     # Packing slip template
│   └── emails/                       # Email templates
├── languages/                        # Translation files
└── vendor/                          # Composer dependencies
```

## ⚙️ Configuration

### Company Settings
Configure your business information:
- Company Name
- Address
- Email & Phone
- Logo Upload
- Tax Information

### PDF Settings
- Enable/Disable PDF generation
- Invoice numbering format
- Template selection
- Custom CSS styling

### UBL Settings
- Enable/Disable UBL generation
- XML format preferences
- Validation settings

### Email Attachments
Choose which WooCommerce emails should include attachments:
- ✅ Processing Order
- ✅ Completed Order
- ✅ Customer Invoice
- ✅ Shipped Order (custom)
- ✅ Refunded Order

### Packing Slips
- Enable/Disable packing slips
- Template customization
- Item grouping options

## 💻 Usage

### Manual Generation
From the WooCommerce order page:
```php
// Generate PDF invoice
$pdf_path = woodmart_invoices_generate_pdf_invoice( $order_id );

// Generate UBL invoice
$ubl_path = woodmart_invoices_generate_ubl_invoice( $order_id );

// Generate packing slip
$slip_path = woodmart_invoices_generate_packing_slip( $order_id );
```

### Automatic Generation
Documents are automatically generated and attached to emails based on your settings.

### Programmatic Access
```php
// Get Registry instance
$registry = XTS_PLUGIN\Invoices_Registry::getInstance();

// Generate PDF
$pdf_generator = $registry->pdf_generator;
$pdf_path = $pdf_generator->generate( $order_id );

// Generate UBL
$ubl_generator = $registry->ubl_generator;
$ubl_path = $ubl_generator->generate( $order_id );

// Generate Packing Slip
$packing_generator = $registry->packing_slip_generator;
$slip_path = $packing_generator->generate( $order_id );
```

## 🔌 Hooks & Filters

### Filters
```php
// Modify invoice data
add_filter( 'woodmart_invoices_invoice_data', function( $data, $order ) {
    $data['custom_field'] = 'Custom Value';
    return $data;
}, 10, 2 );

// Modify company information
add_filter( 'woodmart_invoices_company_info', function( $info ) {
    $info['website'] = 'https://example.com';
    return $info;
});

// Customize PDF template path
add_filter( 'woodmart_invoices_pdf_template', function( $template, $order ) {
    return 'custom-template.php';
}, 10, 2 );

// Modify email attachments
add_filter( 'woodmart_invoices_email_attachments', function( $attachments, $email_id, $order ) {
    // Custom logic here
    return $attachments;
}, 10, 3 );
```

### Actions
```php
// After PDF generation
add_action( 'woodmart_invoices_pdf_generated', function( $pdf_path, $order_id ) {
    // Custom processing
}, 10, 2 );

// After UBL generation
add_action( 'woodmart_invoices_ubl_generated', function( $ubl_path, $order_id ) {
    // Custom processing
}, 10, 2 );

// Before email attachment
add_action( 'woodmart_invoices_before_email_attachment', function( $order_id, $email_id ) {
    // Pre-processing
}, 10, 2 );
```

## 🎨 Template Customization

### Override Templates in Theme
Create custom templates in your theme:
```
your-theme/
└── woodmart-invoices/
    ├── pdf/
    │   └── custom-invoice.php
    ├── packing-clips/
    │   └── custom-packing-slip.php
    └── emails/
        ├── customer-shipped-order.php
        └── plain/
            └── customer-shipped-order.php
```

### Template Variables
Available variables in templates:
```php
$order          // WC_Order object
$invoice_data   // Invoice information
$company_info   // Company details
$items          // Order items
$totals         // Order totals
$billing        // Billing address
$shipping       // Shipping address
```

## 🔐 Security Features

- **File Protection**: All generated files stored in protected directory
- **Access Control**: Nonce verification for all actions
- **Input Sanitization**: All user inputs properly sanitized
- **Permission Checks**: Proper capability checks
- **CSRF Protection**: WordPress nonce system
- **File Validation**: Secure file handling

## 🚀 Performance

- **Lazy Loading**: Classes loaded only when needed
- **Caching**: Generated files cached for performance
- **Optimized Queries**: Efficient database operations
- **Memory Management**: Optimized for large orders
- **Background Processing**: Heavy operations in background

## 🔧 Development

### Composer Dependencies
```json
{
    "require": {
        "dompdf/dompdf": "^2.0",
        "sabre/xml": "^2.2"
    }
}
```

### Class Structure
- **Singleton Pattern**: For main classes
- **Registry Pattern**: For dependency injection
- **Factory Pattern**: For generators
- **Observer Pattern**: For hooks and events

### Coding Standards
- WordPress Coding Standards
- PHPDoc comments required
- YODA conditions
- Proper escaping and sanitization

## 🌐 Internationalization

The plugin is translation-ready. Translation files are located in `/languages/`.

Supported languages:
- English (default)
- Ukrainian
- Ready for more translations

## 📊 System Requirements

### Minimum Requirements
- PHP 7.4+
- WordPress 5.0+
- WooCommerce 5.0+
- MySQL 5.6+
- 128MB PHP memory limit

### Recommended
- PHP 8.1+
- WordPress 6.0+
- WooCommerce 7.0+
- MySQL 8.0+
- 256MB PHP memory limit

## 🐛 Troubleshooting

### Common Issues

**PDF Generation Fails**
- Check PHP memory limit
- Verify DOMPDF installation
- Check file permissions

**Email Attachments Not Working**
- Verify email settings
- Check WooCommerce email configuration
- Review attachment settings

**Permission Errors**
- Check file/directory permissions
- Verify WordPress file constants
- Review security plugins

## 📝 Changelog

### Version 1.0.0
- Initial release
- PDF invoice generation
- UBL invoice generation
- Packing slip functionality
- Email attachment system
- Custom order status
- Admin interface
- PHP 8.2+ compatibility

## 📄 License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

## 👥 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 🆘 Support

For support and questions:
- Documentation: [Plugin Documentation]
- Issues: [GitHub Issues]
- Community: [WordPress.org Plugin Forum]

## 🏢 About

Developed by the WoodMart team for seamless WooCommerce invoice management.

**Author:** WoodMart Team  
**Website:** https://woodmart.com  
**Version:** 1.0.0 