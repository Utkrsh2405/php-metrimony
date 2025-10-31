<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("../includes/dbconn.php");
require_once("../includes/activity-logger.php");

// Check admin role
$user_id = intval($_SESSION['id']);
$role_check = mysqli_query($conn, "SELECT role FROM users WHERE id = $user_id LIMIT 1");
if (!$role_check || mysqli_num_rows($role_check) === 0) {
    header("Location: /login.php");
    exit();
}
$user = mysqli_fetch_assoc($role_check);
if ($user['role'] !== 'admin') {
    header("Location: /index.php");
    exit();
}

$logger = getActivityLogger($conn);
$stats = $logger->getStats();

include("includes/header.php");
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 8px;
    margin-bottom: 30px;
}
.stats-row {
    margin-bottom: 30px;
}
.stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.stat-value {
    font-size: 36px;
    font-weight: bold;
    color: #667eea;
    margin: 10px 0;
}
.filter-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.logs-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}
.action-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}
.action-create { background: #d4edda; color: #155724; }
.action-update { background: #cce5ff; color: #004085; }
.action-delete { background: #f8d7da; color: #721c24; }
.action-verify_photo { background: #fff3cd; color: #856404; }
.action-approve { background: #d1ecf1; color: #0c5460; }
.action-reject { background: #f5c6cb; color: #721c24; }
</style>

<div class="container-fluid">
    <div class="page-header">
        <h1><i class="fa fa-history"></i> Admin Activity Logs</h1>
        <p style="margin: 10px 0 0 0;">Comprehensive audit trail of all admin actions</p>
    </div>
    
    <!-- Statistics -->
    <div class="row stats-row">
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-list" style="font-size: 48px; color: #667eea;"></i>
                <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
                <div>Total Actions</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-calendar-o" style="font-size: 48px; color: #4caf50;"></i>
                <div class="stat-value" style="color: #4caf50;"><?php echo number_format($stats['today']); ?></div>
                <div>Today</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-calendar-check-o" style="font-size: 48px; color: #ffc107;"></i>
                <div class="stat-value" style="color: #ffc107;"><?php echo number_format($stats['this_week']); ?></div>
                <div>This Week</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-calendar" style="font-size: 48px; color: #2196f3;"></i>
                <div class="stat-value" style="color: #2196f3;"><?php echo number_format($stats['this_month']); ?></div>
                <div>This Month</div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-section">
        <div class="row">
            <div class="col-md-3">
                <label>Action Type</label>
                <select id="filter-action" class="form-control">
                    <option value="">All Actions</option>
                    <option value="create">Create</option>
                    <option value="update">Update</option>
                    <option value="delete">Delete</option>
                    <option value="verify_photo">Verify Photo</option>
                    <option value="approve">Approve</option>
                    <option value="reject">Reject</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Entity Type</label>
                <select id="filter-entity" class="form-control">
                    <option value="">All Entities</option>
                    <option value="user">User</option>
                    <option value="plan">Plan</option>
                    <option value="payment">Payment</option>
                    <option value="message">Message</option>
                    <option value="interest">Interest</option>
                    <option value="cms_page">CMS Page</option>
                    <option value="sms_template">SMS Template</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Admin ID</label>
                <input type="number" id="filter-admin" class="form-control" placeholder="Admin ID">
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <button class="btn btn-primary btn-block" onclick="loadLogs()">
                    <i class="fa fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>
    
    <!-- Table -->
    <div class="logs-table">
        <table class="table table-striped table-hover">
            <thead style="background: #f5f5f5;">
                <tr>
                    <th>ID</th>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Date/Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="logs-tbody">
                <tr>
                    <td colspan="8" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 20px; text-align: center;">
        <button class="btn btn-default" id="load-more-btn" onclick="loadMore()" style="display: none;">
            <i class="fa fa-chevron-down"></i> Load More
        </button>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Activity Details</h4>
            </div>
            <div class="modal-body" id="modal-body">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 0;
const perPage = 50;

$(document).ready(function() {
    loadLogs();
});

function loadLogs(append = false) {
    if (!append) {
        currentPage = 0;
    }
    
    const filters = {
        action: $('#filter-action').val(),
        entity_type: $('#filter-entity').val(),
        admin_id: $('#filter-admin').val(),
        page: currentPage,
        per_page: perPage
    };
    
    $.get('/admin/api/activity-logs.php', filters, function(response) {
        if (response.success) {
            renderLogs(response.data, append);
            
            if (response.has_more) {
                $('#load-more-btn').show();
            } else {
                $('#load-more-btn').hide();
            }
        }
    });
}

function renderLogs(logs, append) {
    const tbody = $('#logs-tbody');
    if (!append) {
        tbody.empty();
    }
    
    if (logs.length === 0 && !append) {
        tbody.html('<tr><td colspan="8" class="text-center text-muted">No activity logs found</td></tr>');
        return;
    }
    
    logs.forEach(log => {
        const actionClass = 'action-' + log.action.replace('_', '-');
        const created = moment(log.created_at).format('MMM D, YYYY h:mm A');
        
        tbody.append(`
            <tr>
                <td>${log.id}</td>
                <td>
                    ${log.admin_name || 'Unknown'}<br>
                    <small style="color: #999;">#${log.admin_id}</small>
                </td>
                <td>
                    <span class="action-badge ${actionClass}">${log.action}</span>
                </td>
                <td>
                    ${log.entity_type}
                    ${log.entity_id ? '<br><small style="color: #999;">#' + log.entity_id + '</small>' : ''}
                </td>
                <td>${log.description || '-'}</td>
                <td><small>${log.ip_address || '-'}</small></td>
                <td><small>${created}</small></td>
                <td>
                    <button class="btn btn-info btn-xs" onclick="viewDetails(${log.id})">
                        <i class="fa fa-eye"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function loadMore() {
    currentPage++;
    loadLogs(true);
}

function viewDetails(logId) {
    $.get('/admin/api/activity-logs.php?action=view&id=' + logId, function(response) {
        if (response.success) {
            const log = response.data;
            
            let oldData = '';
            let newData = '';
            
            if (log.old_data) {
                try {
                    const old = JSON.parse(log.old_data);
                    oldData = '<pre>' + JSON.stringify(old, null, 2) + '</pre>';
                } catch(e) {
                    oldData = log.old_data;
                }
            }
            
            if (log.new_data) {
                try {
                    const newD = JSON.parse(log.new_data);
                    newData = '<pre>' + JSON.stringify(newD, null, 2) + '</pre>';
                } catch(e) {
                    newData = log.new_data;
                }
            }
            
            $('#modal-body').html(`
                <table class="table table-bordered">
                    <tr>
                        <th width="150">Admin:</th>
                        <td>${log.admin_name || 'Unknown'} (${log.admin_email || 'N/A'})</td>
                    </tr>
                    <tr>
                        <th>Action:</th>
                        <td><span class="action-badge action-${log.action}">${log.action}</span></td>
                    </tr>
                    <tr>
                        <th>Entity:</th>
                        <td>${log.entity_type} ${log.entity_id ? '#' + log.entity_id : ''}</td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td>${log.description || '-'}</td>
                    </tr>
                    <tr>
                        <th>IP Address:</th>
                        <td>${log.ip_address || '-'}</td>
                    </tr>
                    <tr>
                        <th>User Agent:</th>
                        <td style="font-size: 11px;">${log.user_agent || '-'}</td>
                    </tr>
                    <tr>
                        <th>Date/Time:</th>
                        <td>${moment(log.created_at).format('MMMM D, YYYY h:mm:ss A')}</td>
                    </tr>
                    ${oldData ? '<tr><th>Old Data:</th><td>' + oldData + '</td></tr>' : ''}
                    ${newData ? '<tr><th>New Data:</th><td>' + newData + '</td></tr>' : ''}
                </table>
            `);
            $('#detailsModal').modal('show');
        }
    });
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<?php include("includes/footer.php"); ?>
