<?php

/**
 * Default PDF invoice template.
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

// Available variables:
// $company - Company information
// $order - Order data
// $invoice_number - Invoice number
// $date - Invoice date
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo esc_html($invoice_number); ?></title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .invoice-header {
            border-bottom: 2px solid #333;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .invoice-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .company-logo {
            max-width: 200px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .clear {
            clear: both;
        }
        .billing-shipping {
            margin: 30px 0;
        }
        .billing-info, .shipping-info {
            float: left;
            width: 48%;
        }
        .shipping-info {
            float: right;
        }
        .address-title {
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .item-column {
            width: 40%;
        }
        .quantity-column {
            width: 10%;
            text-align: center;
        }
        .price-column {
            width: 15%;
            text-align: right;
        }
        .total-column {
            width: 15%;
            text-align: right;
        }
        .totals {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        .totals table {
            margin: 0;
        }
        .totals th, .totals td {
            border: none;
            padding: 5px 10px;
        }
        .totals .total-row {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #333;
        }
    </style>
</head>
<body>
    <!-- Invoice Header -->
    <div class="invoice-header">
        <div class="company-info">
            <?php if (! empty($company_info['logo'])) : ?>
                <img src="<?php echo esc_url($company_info['logo']); ?>" alt="<?php echo esc_attr($company_info['name']); ?>" class="company-logo">
            <?php endif; ?>
            <div class="company-name"><?php echo esc_html($company_info['name'] ?? ''); ?></div>
            <?php if (! empty($company_info['address'])) : ?>
                <div><?php echo nl2br(esc_html($company_info['address'])); ?></div>
            <?php endif; ?>
            <?php if (! empty($company_info['phone'])) : ?>
                <div><?php echo esc_html__('Phone:', 'woodmart-invoices'); ?> <?php echo esc_html($company_info['phone']); ?></div>
            <?php endif; ?>
            <?php if (! empty($company_info['email'])) : ?>
                <div><?php echo esc_html__('Email:', 'woodmart-invoices'); ?> <?php echo esc_html($company_info['email']); ?></div>
            <?php endif; ?>
        </div>
        <div class="invoice-info">
            <div class="invoice-title"><?php echo esc_html__('INVOICE', 'woodmart-invoices'); ?></div>
            <div><strong><?php echo esc_html__('Invoice Number:', 'woodmart-invoices'); ?></strong> <?php echo esc_html($order_data['order_number'] ?? ''); ?></div>
            <div><strong><?php echo esc_html__('Invoice Date:', 'woodmart-invoices'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'))); ?></div>
            <div><strong><?php echo esc_html__('Order Number:', 'woodmart-invoices'); ?></strong> <?php echo esc_html($order_data['order_number'] ?? ''); ?></div>
            <div><strong><?php echo esc_html__('Order Date:', 'woodmart-invoices'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($order_data['order_date'] ?? ''))); ?></div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Billing and Shipping Information -->
    <div class="billing-shipping">
        <div class="billing-info">
            <div class="address-title"><?php echo esc_html__('Bill To:', 'woodmart-invoices'); ?></div>
            <div>
                <?php
                $first_name = $order_data['billing']['first_name'] ?? '';
                $last_name = $order_data['billing']['last_name'] ?? '';
                echo esc_html(trim($first_name . ' ' . $last_name));
                ?>
            </div>
            <?php if (! empty($order_data['billing']['company'])) : ?>
                <div><?php echo esc_html($order_data['billing']['company']); ?></div>
            <?php endif; ?>
            <?php if (! empty($order_data['billing']['address_1'])) : ?>
                <div><?php echo esc_html($order_data['billing']['address_1']); ?></div>
            <?php endif; ?>
            <?php if (! empty($order_data['billing']['address_2'])) : ?>
                <div><?php echo esc_html($order_data['billing']['address_2']); ?></div>
            <?php endif; ?>
            <div>
                <?php
                $city = $order_data['billing']['city'] ?? '';
                $state = $order_data['billing']['state'] ?? '';
                $postcode = $order_data['billing']['postcode'] ?? '';
                $address_line = trim($city . ', ' . $state . ' ' . $postcode);
                if ($address_line && ', ' !== $address_line) {
                    echo esc_html($address_line);
                }
                ?>
            </div>
            <?php if (! empty($order_data['billing']['country'])) : ?>
                <div><?php echo esc_html($order_data['billing']['country']); ?></div>
            <?php endif; ?>
        </div>

        <?php if (! empty($order_data['shipping']['address_1'])) : ?>
        <div class="shipping-info">
            <div class="address-title"><?php echo esc_html__('Ship To:', 'woodmart-invoices'); ?></div>
            <div>
                <?php
                $first_name = $order_data['shipping']['first_name'] ?? '';
                $last_name = $order_data['shipping']['last_name'] ?? '';
                echo esc_html(trim($first_name . ' ' . $last_name));
                ?>
            </div>
            <?php if (! empty($order_data['shipping']['company'])) : ?>
                <div><?php echo esc_html($order_data['shipping']['company']); ?></div>
            <?php endif; ?>
            <?php if (! empty($order_data['shipping']['address_1'])) : ?>
                <div><?php echo esc_html($order_data['shipping']['address_1']); ?></div>
            <?php endif; ?>
            <?php if (! empty($order_data['shipping']['address_2'])) : ?>
                <div><?php echo esc_html($order_data['shipping']['address_2']); ?></div>
            <?php endif; ?>
            <div>
                <?php
                $city = $order_data['shipping']['city'] ?? '';
                $state = $order_data['shipping']['state'] ?? '';
                $postcode = $order_data['shipping']['postcode'] ?? '';
                $address_line = trim($city . ', ' . $state . ' ' . $postcode);
                if ($address_line && ', ' !== $address_line) {
                    echo esc_html($address_line);
                }
                ?>
            </div>
            <?php if (! empty($order_data['shipping']['country'])) : ?>
                <div><?php echo esc_html($order_data['shipping']['country']); ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="clear"></div>
    </div>

    <!-- Order Items -->
    <table>
        <thead>
            <tr>
                <th class="item-column"><?php echo esc_html__('Item', 'woodmart-invoices'); ?></th>
                <th class="quantity-column"><?php echo esc_html__('Qty', 'woodmart-invoices'); ?></th>
                <th class="price-column"><?php echo esc_html__('Price', 'woodmart-invoices'); ?></th>
                <th class="total-column"><?php echo esc_html__('Total', 'woodmart-invoices'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($order_data['items'])) : ?>
                <?php foreach ($order_data['items'] as $item) : ?>
                    <tr>
                        <td class="item-column">
                            <strong><?php echo esc_html($item['name'] ?? ''); ?></strong>
                            <?php if (! empty($item['sku'])) : ?>
                                <br><small>SKU: <?php echo esc_html($item['sku']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="quantity-column"><?php echo esc_html($item['quantity'] ?? 0); ?></td>
                        <td class="price-column"><?php echo wp_kses_post(wc_price($item['price'] ?? 0)); ?></td>
                        <td class="total-column"><?php echo wp_kses_post(wc_price($item['total'] ?? 0)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Order Totals -->
    <div class="totals">
        <table>
            <tr>
                <th><?php echo esc_html__('Subtotal:', 'woodmart-invoices'); ?></th>
                <td><?php echo wp_kses_post(wc_price($order_data['order_total'] ?? 0)); ?></td>
            </tr>
            <tr class="total-row">
                <th><?php echo esc_html__('Total:', 'woodmart-invoices'); ?></th>
                <td><?php echo wp_kses_post(wc_price($order_data['order_total'] ?? 0)); ?></td>
            </tr>
        </table>
    </div>
    <div class="clear"></div>

    <!-- Footer -->
    <div class="footer">
        <div><?php echo esc_html__('Thank you for your business!', 'woodmart-invoices'); ?></div>
    </div>
</body>
</html> 