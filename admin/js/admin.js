/**
 * WoodMart Invoices Admin JavaScript
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

jQuery(document).ready(function ($) {
    'use strict'

    // Generate PDF Invoice
    $(document).on('click', '.woodmart-generate-pdf', function (e) {
        e.preventDefault()

        const $button = $(this)
        const orderId = $button.data('order-id')
        const originalText = $button.text()

        if (!orderId) {
            alert(woodmartInvoicesAdmin.strings.error)
            return
        }

        // Disable button and show loading state
        $button.prop('disabled', true).text(woodmartInvoicesAdmin.strings.generating_pdf)

        // Send AJAX request
        $.ajax({
            url: woodmartInvoicesAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'woodmart_generate_pdf_invoice',
                order_id: orderId,
                nonce: woodmartInvoicesAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    // Show success message
                    $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>')
                        .insertAfter('.wrap h1:first')

                    // Open download link
                    if (response.data.download_url) {
                        window.open(response.data.download_url, '_blank')
                    }
                } else {
                    alert(response.data || woodmartInvoicesAdmin.strings.error)
                }
            },
            error: function () {
                alert(woodmartInvoicesAdmin.strings.error)
            },
            complete: function () {
                // Re-enable button
                $button.prop('disabled', false).text(originalText)
            }
        })
    })

    // Generate UBL Invoice
    $(document).on('click', '.woodmart-generate-ubl', function (e) {
        e.preventDefault()

        const $button = $(this)
        const orderId = $button.data('order-id')
        const originalText = $button.text()

        if (!orderId) {
            alert(woodmartInvoicesAdmin.strings.error)
            return
        }

        // Disable button and show loading state
        $button.prop('disabled', true).text(woodmartInvoicesAdmin.strings.generating_ubl)

        // Send AJAX request
        $.ajax({
            url: woodmartInvoicesAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'woodmart_generate_ubl_invoice',
                order_id: orderId,
                nonce: woodmartInvoicesAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    // Show success message
                    $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>')
                        .insertAfter('.wrap h1:first')

                    // Open download link
                    if (response.data.download_url) {
                        window.open(response.data.download_url, '_blank')
                    }
                } else {
                    alert(response.data || woodmartInvoicesAdmin.strings.error)
                }
            },
            error: function () {
                alert(woodmartInvoicesAdmin.strings.error)
            },
            complete: function () {
                // Re-enable button
                $button.prop('disabled', false).text(originalText)
            }
        })
    })

    // Generate Packing Slip
    $(document).on('click', '.woodmart-generate-packing-slip', function (e) {
        e.preventDefault()

        const $button = $(this)
        const orderId = $button.data('order-id')
        const originalText = $button.text()

        if (!orderId) {
            alert(woodmartInvoicesAdmin.strings.error)
            return
        }

        // Disable button and show loading state
        $button.prop('disabled', true).text(woodmartInvoicesAdmin.strings.generating_packing)

        // Send AJAX request
        $.ajax({
            url: woodmartInvoicesAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'woodmart_generate_packing_slip',
                order_id: orderId,
                nonce: woodmartInvoicesAdmin.nonce
            },
            success: function (response) {
                if (response.success) {
                    // Show success message
                    $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>')
                        .insertAfter('.wrap h1:first')

                    // Open download link
                    if (response.data.download_url) {
                        window.open(response.data.download_url, '_blank')
                    }
                } else {
                    alert(response.data || woodmartInvoicesAdmin.strings.error)
                }
            },
            error: function () {
                alert(woodmartInvoicesAdmin.strings.error)
            },
            complete: function () {
                // Re-enable button
                $button.prop('disabled', false).text(originalText)
            }
        })
    })

    // Company logo upload
    $('#upload-logo').on('click', function (e) {
        e.preventDefault()

        const frame = wp.media({
            title: 'Select Company Logo',
            button: {
                text: 'Use as Logo'
            },
            multiple: false
        })

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON()
            $('input[name="woodmart_invoices_settings[company_logo]"]').val(attachment.url)
        })

        frame.open()
    })

    // Auto-dismiss notices
    setTimeout(function () {
        $('.notice.is-dismissible').fadeOut()
    }, 5000)
})
