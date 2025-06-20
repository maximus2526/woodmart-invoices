<?php

/**
 * Default packing slip template.
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
// $packing_slip_date - Packing slip date
// $document_title - Document title
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo esc_html($document_title); ?> - <?php echo esc_html($order['order_number'] ?? ''); ?></title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .packing-slip-header {
            border-bottom: 2px solid #333;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .order-info {
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
        .document-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .clear {
            clear: both;
        }
        .shipping-address {
            margin: 30px 0;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
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
            width: 60%;
        }
        .quantity-column {
            width: 20%;
            text-align: center;
        }
        .packed-column {
            width: 20%;
            text-align: center;
            border-right: 2px solid #333;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #333;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Packing Slip Header -->
    <div class="packing-slip-header">
        <div class="company-info">
            <?php if (! empty($company['logo'])) : ?>
                <img src="<?php echo esc_url($company['logo']); ?>" alt="<?php echo esc_attr($company['name']); ?>" class="company-logo">
            <?php endif; ?>
            <div class="company-name"><?php echo esc_html($company['name']); ?></div>
            <?php if (! empty($company['address'])) : ?>
                <div><?php echo nl2br(esc_html($company['address'])); ?></div>
            <?php endif; ?>
        </div>
        <div class="order-info">
            <div class="document-title"><?php echo esc_html($document_title); ?></div>
            <div><strong><?php echo esc_html__('Order Number:', 'woodmart-invoices'); ?></strong> <?php echo esc_html($order['order_number'] ?? ''); ?></div>
            <div><strong><?php echo esc_html__('Order Date:', 'woodmart-invoices'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($order['order_date'] ?? ''))); ?></div>
            <div><strong><?php echo esc_html__('Packing Date:', 'woodmart-invoices'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($packing_slip_date))); ?></div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Shipping Address -->
    <div class="shipping-address">
        <div class="address-title"><?php echo esc_html__('Ship To:', 'woodmart-invoices'); ?></div>
        <div>
            <?php
            $first_name = $order['shipping']['first_name'] ?? '';
            $last_name = $order['shipping']['last_name'] ?? '';
            echo esc_html(trim($first_name . ' ' . $last_name));
            ?>
        </div>
        <?php if (! empty($order['shipping']['company'])) : ?>
            <div><?php echo esc_html($order['shipping']['company']); ?></div>
        <?php endif; ?>
        <?php if (! empty($order['shipping']['address_1'])) : ?>
            <div><?php echo esc_html($order['shipping']['address_1']); ?></div>
        <?php endif; ?>
        <?php if (! empty($order['shipping']['address_2'])) : ?>
            <div><?php echo esc_html($order['shipping']['address_2']); ?></div>
        <?php endif; ?>
        <div>
            <?php
            $city = $order['shipping']['city'] ?? '';
            $state = $order['shipping']['state'] ?? '';
            $postcode = $order['shipping']['postcode'] ?? '';
            $address_line = trim($city . ', ' . $state . ' ' . $postcode);
            if ($address_line && ', ' !== $address_line) {
                echo esc_html($address_line);
            }
            ?>
        </div>
        <?php if (! empty($order['shipping']['country'])) : ?>
            <div><?php echo esc_html($order['shipping']['country']); ?></div>
        <?php endif; ?>
    </div>

    <!-- Order Items -->
    <table>
        <thead>
            <tr>
                <th class="item-column"><?php echo esc_html__('Item', 'woodmart-invoices'); ?></th>
                <th class="quantity-column"><?php echo esc_html__('Ordered Qty', 'woodmart-invoices'); ?></th>
                <th class="packed-column"><?php echo esc_html__('Packed Qty', 'woodmart-invoices'); ?></th>
            </tr>
        </thead>
        <tbody>
                    <?php if (! empty($order['items'])) : ?>
                        <?php foreach ($order['items'] as $item) : ?>
                <tr>
                    <td class="item-column">
                        <strong><?php echo esc_html($item['name'] ?? ''); ?></strong>
                            <?php if (! empty($item['sku'])) : ?>
                            <br><small><?php echo esc_html__('SKU:', 'woodmart-invoices'); ?> <?php echo esc_html($item['sku']); ?></small>
                            <?php endif; ?>
                    </td>
                    <td class="quantity-column"><?php echo esc_html($item['quantity'] ?? 0); ?></td>
                    <td class="packed-column">_______</td>
                </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
        </tbody>
    </table>

    <!-- Customer Notes -->
    <?php if (! empty($order['customer_note'])) : ?>
    <div class="notes">
        <strong><?php echo esc_html__('Customer Notes:', 'woodmart-invoices'); ?></strong><br>
        <?php echo esc_html($order['customer_note']); ?>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <div><?php echo esc_html__('Please check all items against this packing slip and report any discrepancies immediately.', 'woodmart-invoices'); ?></div>
    </div>
</body>
</html> 