/**
 * WoodMart Invoices Frontend JavaScript
 *
 * @package WoodMart_Invoices
 * @since 1.0.0
 */

jQuery(document).ready(function ($) {
    'use strict'

    // Download invoice files
    $(document).on('click', '.woodmart-invoice-downloads a', function (e) {
        const $link = $(this)

        // Add loading state
        $link.addClass('loading')

        // Remove loading state after a short delay (download should start)
        setTimeout(function () {
            $link.removeClass('loading')
        }, 2000)
    })

    // Handle download errors
    $(window).on('beforeunload', function () {
        $('.woodmart-invoice-downloads a.loading').removeClass('loading')
    })
})
