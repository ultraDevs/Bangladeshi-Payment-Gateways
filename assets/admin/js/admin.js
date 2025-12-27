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
        var isMigrationPage = page.indexOf('bangladeshi-payment-gateways-hpos-migration') > -1;

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
         * HPOS Migration Page
         */
        if (isMigrationPage && typeof bdpgAdmin !== 'undefined') {
            var migrationInterval = null;

            // Load migration status on page load
            loadMigrationStatus();

            // Start migration button
            $('#bdpg-start-migration').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt bdpg-spin"></span> Starting...');

                $.ajax({
                    url: bdpgAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bdpg_start_migration',
                        nonce: bdpgAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Start polling for updates
                            startMigrationPolling();
                            loadMigrationStatus();
                        } else {
                            alert(response.data.message || 'Failed to start migration');
                            $btn.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Start Migration');
                        }
                    },
                    error: function() {
                        alert('Failed to start migration');
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Start Migration');
                    }
                });
            });

            // Reset migration button
            $('#bdpg-reset-migration').on('click', function() {
                if (!confirm('Are you sure you want to reset the migration? This will clear all migration progress.')) {
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true);

                $.ajax({
                    url: bdpgAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bdpg_reset_migration',
                        nonce: bdpgAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            stopMigrationPolling();
                            loadMigrationStatus();
                            alert(response.data.message || 'Migration reset successfully');
                        } else {
                            alert(response.data.message || 'Failed to reset migration');
                        }
                        $btn.prop('disabled', false);
                    },
                    error: function() {
                        alert('Failed to reset migration');
                        $btn.prop('disabled', false);
                    }
                });
            });

            /**
             * Load migration status via AJAX
             */
            function loadMigrationStatus() {
                $.ajax({
                    url: bdpgAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'bdpg_get_migration_status',
                        nonce: bdpgAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            updateMigrationStatus(response.data);
                        }
                    }
                });
            }

            /**
             * Update migration status display
             */
            function updateMigrationStatus(data) {
                // Hide loading, show content
                $('#bdpg-status-loading').hide();
                $('#bdpg-status-content').show();

                // Update status text
                var statusText = 'Not Started';
                var statusClass = 'bdpg-status-pending';

                switch(data.status) {
                    case 'pending':
                        statusText = 'Pending';
                        statusClass = 'bdpg-status-pending';
                        break;
                    case 'running':
                        statusText = 'Running';
                        statusClass = 'bdpg-status-running';
                        break;
                    case 'completed':
                        statusText = 'Completed';
                        statusClass = 'bdpg-status-completed';
                        break;
                }

                $('#bdpg-status-text')
                    .text(statusText)
                    .removeClass()
                    .addClass('bdpg-status-value ' + statusClass);

                // Update progress
                $('#bdpg-status-count').text(data.processed + ' / ' + data.total);
                $('#bdpg-status-percent').text(data.percentage + '%');
                $('#bdpg-progress-fill').css('width', data.percentage + '%');

                // Update gateway name
                var gatewayNames = {
                    'bkash': 'bKash',
                    'rocket': 'Rocket',
                    'nagad': 'Nagad',
                    'upay': 'Upay',
                    '': '-'
                };
                $('#bdpg-status-gateway').text(gatewayNames[data.current_gateway] || data.current_gateway);

                // Update times
                $('#bdpg-status-start').text(data.start_time);
                $('#bdpg-status-end').text(data.end_time);

                // Update start button state
                var $startBtn = $('#bdpg-start-migration');
                if (data.is_running || data.is_scheduled) {
                    $startBtn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt bdpg-spin"></span> Migration in Progress...');
                    startMigrationPolling();
                } else if (data.status === 'completed') {
                    $startBtn.prop('disabled', false).html('<span class="dashicons dashicons-yes-alt"></span> Migration Complete');
                    stopMigrationPolling();
                } else {
                    $startBtn.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Start Migration');
                    stopMigrationPolling();
                }
            }

            /**
             * Start polling for migration updates
             */
            function startMigrationPolling() {
                if (migrationInterval) {
                    return;
                }
                migrationInterval = setInterval(loadMigrationStatus, 3000); // Poll every 3 seconds
            }

            /**
             * Stop polling for migration updates
             */
            function stopMigrationPolling() {
                if (migrationInterval) {
                    clearInterval(migrationInterval);
                    migrationInterval = null;
                }
            }

            // Clean up interval when leaving page
            $(window).on('beforeunload', function() {
                stopMigrationPolling();
            });
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