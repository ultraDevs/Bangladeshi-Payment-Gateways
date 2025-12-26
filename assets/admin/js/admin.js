/**
 * All Admin Javascript Code here
 *
 * Javascript code will be written here
 *
 * @package BDPaymentsGateway
 */

jQuery(document).ready(
    function ($) {

        // QR Code Image Upload
        $(document).on(
            'click', '.add_qr_c_img', function (e) {
                var id = $(this).data('target');
                var qr = $(this).data('qr');
                var image = wp.media(
                    {
                        title: 'Upload Image',
                        multiple: false,
                    }
                ).open().on(
                    'select', function (e) {
                        var uploaded_img = image.state().get('selection').first();
                        var img_url = uploaded_img.toJSON().url;
                        $(id).val(img_url);
                        $(qr).html('<img src="' + img_url + '" alt="QR Code" />');
                        $('.add_qr_c_img').val('Edit Image');
                    }
                );

            }
        );

        // Page detection
        var page = window.location.href;
        var isStatsPage = page.indexOf('bangladeshi-payment-gateways-statistics') > -1;
        var isTransPage = page.indexOf('bangladeshi-payment-gateways-transactions') > -1;

        /**
         * Statistics Page
         */
        if (isStatsPage && typeof bdpgAdmin !== 'undefined') {
            var currentPage = 1;

            // Load stats on page load
            loadStats();

            // Filter button click
            $('#bdpg-stats-filter').on('click', function() {
                loadStats();
            });

            // Reset button click
            $('#bdpg-stats-reset').on('click', function() {
                $('#bdpg-stats-date-from').val('');
                $('#bdpg-stats-date-to').val('');
                loadStats();
            });

            /**
             * Load statistics via AJAX
             */
            function loadStats() {
                var dateFrom = $('#bdpg-stats-date-from').val();
                var dateTo = $('#bdpg-stats-date-to').val();

                // Show loading state
                $('.bdpg-stat-count').html('<span class="bdpg-loading"></span>');
                $('.bdpg-stat-amount').text(bdpgAdmin.strings.loading);

                $.ajax({
                    url: bdpgAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bdpg_get_stats',
                        nonce: bdpgAdmin.nonce,
                        date_from: dateFrom,
                        date_to: dateTo
                    },
                    success: function(response) {
                        if (response.success) {
                            updateStatsCards(response.data);
                        } else {
                            showError(response.data.message || bdpgAdmin.strings.no_data);
                        }
                    },
                    error: function() {
                        showError(bdpgAdmin.strings.no_data);
                    }
                });
            }

            /**
             * Update statistics cards
             */
            function updateStatsCards(stats) {
                var gateways = ['bkash', 'rocket', 'nagad', 'upay'];

                // Update individual gateway cards
                gateways.forEach(function(gateway) {
                    var count = stats[gateway] ? stats[gateway].count : 0;
                    var amount = stats[gateway] ? stats[gateway].total_amount : 0;

                    $('#bdpg-stat-' + gateway + '-count').text(count + ' ' + (count === 1 ? 'Order' : 'Orders'));
                    $('#bdpg-stat-' + gateway + '-amount').text(formatCurrency(amount));
                });

                // Update total card
                var totalCount = stats.total ? stats.total.count : 0;
                var totalAmount = stats.total ? stats.total.total_amount : 0;

                $('#bdpg-stat-total-count').text(totalCount + ' ' + (totalCount === 1 ? 'Order' : 'Orders'));
                $('#bdpg-stat-total-amount').text(formatCurrency(totalAmount));
            }
        }

        /**
         * Transactions Page
         */
        if (isTransPage && typeof bdpgAdmin !== 'undefined') {
            var currentPage = 1;
            var totalPages = 1;
            var totalCount = 0;
            var perPage = 20;

            // Load transactions on page load
            loadTransactions();

            // Filter button click
            $('#bdpg-trans-filter').on('click', function() {
                currentPage = 1;
                loadTransactions();
            });

            // Reset button click
            $('#bdpg-trans-reset').on('click', function() {
                $('#bdpg-trans-date-from').val('');
                $('#bdpg-trans-date-to').val('');
                $('#bdpg-trans-gateway').val('');
                currentPage = 1;
                loadTransactions();
            });

            // Pagination
            $('#bdpg-prev-page').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    loadTransactions();
                }
            });

            $('#bdpg-next-page').on('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    loadTransactions();
                }
            });

            // Export buttons
            $('#bdpg-export-csv').on('click', function() {
                exportTransactions('csv');
            });

            $('#bdpg-export-pdf').on('click', function() {
                exportTransactions('pdf');
            });

            /**
             * Load transactions via AJAX
             */
            function loadTransactions() {
                var dateFrom = $('#bdpg-trans-date-from').val();
                var dateTo = $('#bdpg-trans-date-to').val();
                var gateway = $('#bdpg-trans-gateway').val();

                // Show loading state
                $('#bdpg-transactions-body').html(
                    '<tr><td colspan="8" style="text-align: center;">' +
                    '<span class="bdpg-loading"></span> ' + bdpgAdmin.strings.loading +
                    '</td></tr>'
                );

                $.ajax({
                    url: bdpgAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bdpg_get_transactions',
                        nonce: bdpgAdmin.nonce,
                        date_from: dateFrom,
                        date_to: dateTo,
                        gateway: gateway,
                        page: currentPage
                    },
                    success: function(response) {
                        if (response.success) {
                            totalCount = response.data.total_count;
                            totalPages = Math.ceil(totalCount / perPage);
                            updateTransactionsTable(response.data.transactions);
                            updatePagination();
                        } else {
                            showError(response.data.message || bdpgAdmin.strings.no_data);
                        }
                    },
                    error: function() {
                        showError(bdpgAdmin.strings.no_data);
                    }
                });
            }

            /**
             * Update transactions table
             */
            function updateTransactionsTable(transactions) {
                var tbody = $('#bdpg-transactions-body');

                if (transactions.length === 0) {
                    tbody.html(
                        '<tr><td colspan="8" style="text-align: center; padding: 60px 20px;">' +
                        '<div class="bdpg-empty-state">' +
                        '<span class="dashicons dashicons-money-alt"></span>' +
                        '<p>' + bdpgAdmin.strings.no_data + '</p>' +
                        '</div></td></tr>'
                    );
                    return;
                }

                var html = '';
                transactions.forEach(function(trans) {
                    var orderUrl = bdpgAdmin.ajax_url.replace('admin-ajax.php', 'post.php?post=' + trans.order_id + '&action=edit');
                    html += '<tr>';
                    html += '<td><a href="' + orderUrl + '">#' + trans.order_number + '</a></td>';
                    html += '<td>' + trans.date + '</td>';
                    html += '<td><span class="bdpg-gateway-badge bdpg-gateway-' + trans.gateway_raw + '">' + trans.gateway + '</span></td>';
                    html += '<td>' + trans.account_no + '</td>';
                    html += '<td>' + trans.transaction_id + '</td>';
                    html += '<td>' + formatCurrency(trans.amount) + ' ' + trans.currency + '</td>';
                    html += '<td>' + trans.customer_name + '</td>';
                    html += '<td><span class="bdpg-status-badge bdpg-status-' + trans.status + '">' + trans.status + '</span></td>';
                    html += '</tr>';
                });

                tbody.html(html);
            }

            /**
             * Update pagination
             */
            function updatePagination() {
                var pageInfo = 'Page ' + currentPage + ' of ' + (totalPages || 1);
                $('#bdpg-page-info').text(pageInfo + ' (' + totalCount + ' total)');

                $('#bdpg-prev-page').prop('disabled', currentPage <= 1);
                $('#bdpg-next-page').prop('disabled', currentPage >= totalPages);
            }

            /**
             * Export transactions
             */
            function exportTransactions(format) {
                var dateFrom = $('#bdpg-trans-date-from').val();
                var dateTo = $('#bdpg-trans-date-to').val();
                var gateway = $('#bdpg-trans-gateway').val();

                // Create form and submit
                var form = $('<form>', {
                    'method': 'POST',
                    'action': bdpgAdmin.ajax_url
                });

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'action',
                    'value': 'bdpg_export_transactions'
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'nonce',
                    'value': bdpgAdmin.nonce
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'date_from',
                    'value': dateFrom
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'date_to',
                    'value': dateTo
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'gateway',
                    'value': gateway
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'format',
                    'value': format
                }));

                $('body').append(form);
                form.submit();
                form.remove();
            }
        }

        /**
         * Helper: Format currency
         */
        function formatCurrency(amount) {
            return parseFloat(amount).toFixed(2);
        }

        /**
         * Helper: Show error message
         */
        function showError(message) {
            // For stats page
            if (isStatsPage) {
                $('.bdpg-stat-count').text('-');
                $('.bdpg-stat-amount').text(message);
            }

            // For transactions page
            if (isTransPage) {
                $('#bdpg-transactions-body').html(
                    '<tr><td colspan="8" style="text-align: center; color: #dc3545;">' +
                    message +
                    '</td></tr>'
                );
            }
        }

    }
);