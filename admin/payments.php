<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("../includes/dbconn.php");

// Verify admin status
$user_id = intval($_SESSION['id']);
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    header("Location: /index.php");
    exit();
}

// Get filter options
$plans_query = mysqli_query($conn, "SELECT id, name FROM plans ORDER BY price ASC");
$plans = [];
while ($row = mysqli_fetch_assoc($plans_query)) {
    $plans[] = $row;
}

// Get payment statistics
$stats_query = "SELECT 
                COUNT(*) as total_payments,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'refunded' THEN refund_amount ELSE 0 END) as total_refunded,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count
                FROM payments";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

include("../includes/admin-header.php");
?>

<div class="admin-content">
    <h1>Payment Management</h1>
    <p class="text-muted">View and manage all payment transactions</p>
    
    <!-- Statistics Cards -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-3">
            <div class="stat-card blue">
                <h3>Total Revenue</h3>
                <div class="number">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <p class="text-muted"><?php echo $stats['total_payments']; ?> total payments</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card orange">
                <h3>Pending Payments</h3>
                <div class="number"><?php echo $stats['pending_count']; ?></div>
                <p class="text-muted">₹<?php echo number_format($stats['pending_amount'], 2); ?> pending</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card purple">
                <h3>Refunded</h3>
                <div class="number">₹<?php echo number_format($stats['total_refunded'], 2); ?></div>
                <p class="text-muted">Total refunded amount</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card green">
                <h3>Success Rate</h3>
                <div class="number">
                    <?php 
                    $completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM payments WHERE status = 'completed'"))['cnt'];
                    $total = $stats['total_payments'] > 0 ? $stats['total_payments'] : 1;
                    echo round(($completed / $total) * 100, 1); 
                    ?>%
                </div>
                <p class="text-muted">Successful payments</p>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <select id="status-filter" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="plan-filter" class="form-control">
                        <option value="0">All Plans</option>
                        <?php foreach ($plans as $plan): ?>
                            <option value="<?php echo $plan['id']; ?>"><?php echo htmlspecialchars($plan['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="method-filter" class="form-control">
                        <option value="">All Methods</option>
                        <option value="stripe">Stripe</option>
                        <option value="paypal">PayPal</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" id="date-from" class="form-control" placeholder="From Date">
                </div>
                <div class="col-md-2">
                    <input type="date" id="date-to" class="form-control" placeholder="To Date">
                </div>
                <div class="col-md-2">
                    <button id="filter-btn" class="btn btn-primary btn-block">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                </div>
            </div>
            <div class="row" style="margin-top: 10px;">
                <div class="col-md-12">
                    <button id="reset-btn" class="btn btn-default">
                        <i class="fa fa-refresh"></i> Reset Filters
                    </button>
                    <button id="export-csv" class="btn btn-success">
                        <i class="fa fa-download"></i> Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payments Table -->
    <div class="card">
        <div class="card-body">
            <div id="loading" style="text-align: center; padding: 20px; display: none;">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p>Loading payments...</p>
            </div>
            
            <div id="payments-container">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Member</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="payments-tbody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div id="pagination" class="text-center" style="margin-top: 20px;">
                <!-- Populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Payment Details</h4>
            </div>
            <div class="modal-body" id="modal-payment-details">
                <!-- Populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Process Refund</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="refund-payment-id">
                <div class="form-group">
                    <label>Refund Amount (₹)</label>
                    <input type="number" id="refund-amount" class="form-control" step="0.01" required>
                    <small class="text-muted">Maximum: <span id="max-refund"></span></small>
                </div>
                <div class="form-group">
                    <label>Reason/Note</label>
                    <textarea id="refund-note" class="form-control" rows="3" placeholder="Enter reason for refund"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-refund-btn">
                    <i class="fa fa-money"></i> Process Refund
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Reject Payment</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="reject-payment-id">
                <div class="form-group">
                    <label>Rejection Reason</label>
                    <textarea id="reject-note" class="form-control" rows="3" placeholder="Enter reason for rejection" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirm-reject-btn">
                    <i class="fa fa-ban"></i> Reject Payment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentFilters = {};

$(document).ready(function() {
    loadPayments();
    
    $('#filter-btn').click(function() {
        currentPage = 1;
        loadPayments();
    });
    
    $('#reset-btn').click(function() {
        $('#status-filter').val('');
        $('#plan-filter').val('0');
        $('#method-filter').val('');
        $('#date-from').val('');
        $('#date-to').val('');
        currentPage = 1;
        loadPayments();
    });
    
    $('#export-csv').click(function() {
        exportCSV();
    });
    
    $('#confirm-refund-btn').click(function() {
        processRefund();
    });
    
    $('#confirm-reject-btn').click(function() {
        rejectPayment();
    });
});

function loadPayments() {
    $('#loading').show();
    $('#payments-container').hide();
    
    const status = $('#status-filter').val();
    const planId = $('#plan-filter').val();
    const method = $('#method-filter').val();
    const dateFrom = $('#date-from').val();
    const dateTo = $('#date-to').val();
    
    currentFilters = { status, plan_id: planId, payment_method: method, date_from: dateFrom, date_to: dateTo, page: currentPage };
    
    $.ajax({
        url: '/admin/api/payments.php',
        method: 'GET',
        data: currentFilters,
        success: function(response) {
            if (response.success) {
                renderPayments(response.data);
                renderPagination(response.pagination);
            } else {
                alert('Error loading payments: ' + (response.error || 'Unknown error'));
            }
            $('#loading').hide();
            $('#payments-container').show();
        },
        error: function() {
            alert('Failed to load payments');
            $('#loading').hide();
            $('#payments-container').show();
        }
    });
}

function renderPayments(payments) {
    const tbody = $('#payments-tbody');
    tbody.empty();
    
    if (payments.length === 0) {
        tbody.append('<tr><td colspan="9" class="text-center">No payments found</td></tr>');
        return;
    }
    
    payments.forEach(function(payment) {
        const name = (payment.firstname || '') + ' ' + (payment.lastname || '') || payment.username;
        const date = new Date(payment.created_at).toLocaleDateString();
        const amount = '₹' + parseFloat(payment.amount).toFixed(2);
        
        let statusBadge = '';
        switch(payment.status) {
            case 'completed':
                statusBadge = '<span class="label label-success">Completed</span>';
                break;
            case 'pending':
                statusBadge = '<span class="label label-warning">Pending</span>';
                break;
            case 'failed':
                statusBadge = '<span class="label label-danger">Failed</span>';
                break;
            case 'refunded':
                statusBadge = '<span class="label label-info">Refunded</span>';
                break;
        }
        
        let actions = `<button class="btn btn-xs btn-info" onclick="viewPayment(${payment.id})" title="View Details">
                        <i class="fa fa-eye"></i>
                      </button>`;
        
        if (payment.status === 'pending') {
            actions += ` <button class="btn btn-xs btn-success" onclick="approvePayment(${payment.id})" title="Approve">
                          <i class="fa fa-check"></i>
                        </button>
                        <button class="btn btn-xs btn-warning" onclick="openRejectModal(${payment.id})" title="Reject">
                          <i class="fa fa-times"></i>
                        </button>`;
        }
        
        if (payment.status === 'completed') {
            actions += ` <button class="btn btn-xs btn-danger" onclick="openRefundModal(${payment.id}, ${payment.amount})" title="Refund">
                          <i class="fa fa-money"></i>
                        </button>`;
        }
        
        const row = `
            <tr>
                <td>${payment.id}</td>
                <td>${date}</td>
                <td>${name}</td>
                <td>${payment.plan_name}</td>
                <td>${amount}</td>
                <td>${payment.payment_method || 'N/A'}</td>
                <td><small>${payment.transaction_id || 'N/A'}</small></td>
                <td>${statusBadge}</td>
                <td>
                    <div class="btn-group btn-group-xs">
                        ${actions}
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function renderPagination(pagination) {
    const container = $('#pagination');
    container.empty();
    
    if (pagination.pages <= 1) {
        return;
    }
    
    let html = '<ul class="pagination">';
    
    if (pagination.page > 1) {
        html += `<li><a href="#" onclick="changePage(${pagination.page - 1}); return false;">&laquo;</a></li>`;
    } else {
        html += '<li class="disabled"><span>&laquo;</span></li>';
    }
    
    for (let i = 1; i <= pagination.pages; i++) {
        if (i === pagination.page) {
            html += `<li class="active"><span>${i}</span></li>`;
        } else if (i === 1 || i === pagination.pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
            html += `<li><a href="#" onclick="changePage(${i}); return false;">${i}</a></li>`;
        } else if (i === pagination.page - 3 || i === pagination.page + 3) {
            html += '<li class="disabled"><span>...</span></li>';
        }
    }
    
    if (pagination.page < pagination.pages) {
        html += `<li><a href="#" onclick="changePage(${pagination.page + 1}); return false;">&raquo;</a></li>`;
    } else {
        html += '<li class="disabled"><span>&raquo;</span></li>';
    }
    
    html += '</ul>';
    html += `<p class="text-muted">Showing page ${pagination.page} of ${pagination.pages} (${pagination.total} total payments)</p>`;
    
    container.html(html);
}

function changePage(page) {
    currentPage = page;
    loadPayments();
}

function viewPayment(paymentId) {
    // In a real implementation, fetch payment details from API
    alert('View payment details for ID: ' + paymentId);
}

function approvePayment(paymentId) {
    if (!confirm('Are you sure you want to approve this payment? This will activate the subscription.')) {
        return;
    }
    
    $.ajax({
        url: '/admin/api/payments.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'approve', payment_id: paymentId }),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                loadPayments();
            } else {
                alert('Error: ' + (response.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Failed to approve payment');
        }
    });
}

function openRejectModal(paymentId) {
    $('#reject-payment-id').val(paymentId);
    $('#reject-note').val('');
    $('#rejectModal').modal('show');
}

function rejectPayment() {
    const paymentId = $('#reject-payment-id').val();
    const note = $('#reject-note').val();
    
    if (!note) {
        alert('Please enter a rejection reason');
        return;
    }
    
    $.ajax({
        url: '/admin/api/payments.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'reject', payment_id: paymentId, note: note }),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#rejectModal').modal('hide');
                loadPayments();
            } else {
                alert('Error: ' + (response.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Failed to reject payment');
        }
    });
}

function openRefundModal(paymentId, amount) {
    $('#refund-payment-id').val(paymentId);
    $('#refund-amount').val(amount);
    $('#max-refund').text('₹' + amount);
    $('#refund-note').val('');
    $('#refundModal').modal('show');
}

function processRefund() {
    const paymentId = $('#refund-payment-id').val();
    const amount = parseFloat($('#refund-amount').val());
    const maxAmount = parseFloat($('#max-refund').text().replace('₹', ''));
    const note = $('#refund-note').val();
    
    if (!amount || amount <= 0) {
        alert('Please enter a valid refund amount');
        return;
    }
    
    if (amount > maxAmount) {
        alert('Refund amount cannot exceed payment amount');
        return;
    }
    
    if (!confirm(`Are you sure you want to refund ₹${amount}? This action cannot be undone.`)) {
        return;
    }
    
    $.ajax({
        url: '/admin/api/payments.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'refund', payment_id: paymentId, refund_amount: amount, note: note }),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#refundModal').modal('hide');
                loadPayments();
            } else {
                alert('Error: ' + (response.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Failed to process refund');
        }
    });
}

function exportCSV() {
    const params = new URLSearchParams(currentFilters);
    params.set('export', 'csv');
    window.location.href = '/admin/api/export-payments.php?' + params.toString();
}
</script>

<?php include("../includes/admin-footer.php"); ?>
