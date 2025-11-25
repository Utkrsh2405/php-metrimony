<?php
session_start();

// Auth check is handled in admin-header.php

require_once("../includes/dbconn.php");
require_once("../includes/activity-logger.php");

$logger = getActivityLogger($conn);
$stats = $logger->getStats();

include("../includes/admin-header.php");
?>

<div class="admin-content-inner">
    <div class="page-header-custom">
        <h1><i class="fa fa-history"></i> Admin Activity Logs</h1>
        <p class="text-muted">Comprehensive audit trail of all admin actions</p>
    </div>
    
    <!-- Statistics -->
    <div class="row stats-row" style="margin-bottom: 30px;">
        <div class="col-md-3">
            <div class="stat-card blue">
                <i class="fa fa-list" style="font-size: 32px; float: right; opacity: 0.3;"></i>
                <h3>Total Actions</h3>
                <div class="number"><?php echo number_format($stats['total']); ?></div>
                <p class="text-muted">All time</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card green">
                <i class="fa fa-calendar-o" style="font-size: 32px; float: right; opacity: 0.3;"></i>
                <h3>Today</h3>
                <div class="number"><?php echo number_format($stats['today']); ?></div>
                <p class="text-muted">Actions today</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card orange">
                <i class="fa fa-calendar-check-o" style="font-size: 32px; float: right; opacity: 0.3;"></i>
                <h3>This Week</h3>
                <div class="number"><?php echo number_format($stats['this_week']); ?></div>
                <p class="text-muted">Actions this week</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card purple">
                <i class="fa fa-calendar" style="font-size: 32px; float: right; opacity: 0.3;"></i>
                <h3>This Month</h3>
                <div class="number"><?php echo number_format($stats['this_month']); ?></div>
                <p class="text-muted">Actions this month</p>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="card">
        <div class="card-body">
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
    </div>
    
    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover admin-table">
                    <thead>
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
        
        // Map action to badge color
        let badgeClass = 'label-default';
        if (log.action === 'create') badgeClass = 'label-success';
        if (log.action === 'update') badgeClass = 'label-info';
        if (log.action === 'delete') badgeClass = 'label-danger';
        if (log.action === 'approve') badgeClass = 'label-primary';
        if (log.action === 'reject') badgeClass = 'label-warning';
        
        tbody.append(`
            <tr>
                <td>${log.id}</td>
                <td>
                    <strong>${log.admin_name || 'Unknown'}</strong><br>
                    <small class="text-muted">#${log.admin_id}</small>
                </td>
                <td>
                    <span class="label ${badgeClass}">${log.action}</span>
                </td>
                <td>
                    ${log.entity_type}
                    ${log.entity_id ? '<br><small class="text-muted">#' + log.entity_id + '</small>' : ''}
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
                        <td><span class="label label-info">${log.action}</span></td>
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

<?php include("../includes/admin-footer.php"); ?>
