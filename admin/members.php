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

include("../includes/admin-header.php");
?>

<style>
/* Enhanced Admin Members Page Styles */
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.page-header h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
    color: white;
}

.page-header h2 i {
    margin-right: 12px;
    opacity: 0.9;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-card.success { border-left-color: #10b981; }
.stat-card.warning { border-left-color: #f59e0b; }
.stat-card.danger { border-left-color: #ef4444; }

.stat-card .stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 5px;
}

.stat-card .stat-label {
    color: #64748b;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filters-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

.filters-card .form-group label {
    font-weight: 600;
    color: #475569;
    margin-bottom: 8px;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filters-card .form-control {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 15px;
    transition: all 0.2s;
}

.filters-card .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-default {
    background: #f1f5f9;
    color: #475569;
}

.btn-default:hover {
    background: #e2e8f0;
}

.members-table-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
}

.table {
    margin: 0;
    width: 100%;
}

.table thead {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.table thead th {
    padding: 16px;
    font-weight: 700;
    color: #1e293b;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
}

.table tbody td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    color: #475569;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

.badge-info {
    background: #dbeafe;
    color: #1e40af;
}

.progress {
    height: 8px;
    background: #f1f5f9;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 4px;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
}

.btn-group {
    display: flex;
    gap: 5px;
}

.btn-xs {
    padding: 6px 10px;
    font-size: 12px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #475569;
    transition: all 0.2s;
}

.btn-xs:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.btn-xs i {
    font-size: 13px;
}

.pagination {
    display: flex;
    gap: 5px;
    justify-content: center;
    margin: 20px 0;
}

.pagination li {
    list-style: none;
}

.pagination li a,
.pagination li span {
    padding: 10px 15px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-block;
}

.pagination li a:hover {
    background: #f8fafc;
    border-color: #667eea;
    color: #667eea;
}

.pagination li.active span {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
}

.pagination li.disabled span {
    opacity: 0.5;
    cursor: not-allowed;
}

#loading {
    padding: 60px;
    text-align: center;
}

#loading i {
    color: #667eea;
    margin-bottom: 15px;
}

.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 20px 25px;
}

.modal-header .modal-title {
    font-weight: 600;
    font-size: 18px;
}

.modal-header .close {
    color: white;
    opacity: 0.8;
    text-shadow: none;
}

.modal-header .close:hover {
    opacity: 1;
}

.modal-body {
    padding: 25px;
}

.modal-footer {
    padding: 15px 25px;
    border-top: 1px solid #f1f5f9;
}

/* Member name with verification badge */
.member-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.member-name strong {
    color: #1e293b;
}

.verified-badge {
    color: #10b981;
    font-size: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .table {
        font-size: 13px;
    }
    
    .table tbody td,
    .table thead th {
        padding: 10px;
    }
}
</style>

<div class="page-header">
    <h2><i class="fa fa-users"></i> Member Management</h2>
    <div>
        <button id="export-csv" class="btn btn-success">
            <i class="fa fa-download"></i> Export CSV
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-cards" id="stats-cards">
    <div class="stat-card success">
        <div class="stat-value" id="stat-active">-</div>
        <div class="stat-label">Active Members</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-value" id="stat-suspended">-</div>
        <div class="stat-label">Suspended</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-value" id="stat-deleted">-</div>
        <div class="stat-label">Deleted</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" id="stat-total">-</div>
        <div class="stat-label">Total Members</div>
    </div>
</div>

<!-- Filters -->
<div class="filters-card">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label><i class="fa fa-search"></i> Search</label>
                <input type="text" id="search" class="form-control" placeholder="Name, email, username...">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label><i class="fa fa-filter"></i> Status</label>
                <select id="status-filter" class="form-control">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="deleted">Deleted</option>
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label><i class="fa fa-star"></i> Plan</label>
                <select id="plan-filter" class="form-control">
                    <option value="0">All Plans</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?php echo $plan['id']; ?>"><?php echo htmlspecialchars($plan['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-5" style="display: flex; align-items: flex-end; gap: 10px;">
            <div class="form-group" style="flex: 1;">
                <label>&nbsp;</label>
                <div>
                    <button id="filter-btn" class="btn btn-primary" style="width: auto;">
                        <i class="fa fa-filter"></i> Apply Filters
                    </button>
                    <button id="reset-btn" class="btn btn-default" style="margin-left: 10px;">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Members Table -->
<div class="members-table-card">
    <div id="loading" style="display: none;">
        <i class="fa fa-spinner fa-spin fa-3x"></i>
        <p style="margin-top: 15px; color: #64748b; font-size: 14px;">Loading members...</p>
    </div>
    
    <div id="members-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th style="width: 120px;">Age/Gender</th>
                    <th>Location</th>
                    <th style="width: 100px;">Plan</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 110px;">Profile</th>
                    <th style="width: 110px;">Last Login</th>
                    <th style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody id="members-tbody">
                <!-- Populated by JavaScript -->
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div id="pagination" class="text-center" style="padding: 20px 0; border-top: 1px solid #f1f5f9;">
        <!-- Populated by JavaScript -->
    </div>
</div>

<!-- Member Details Modal -->
<div class="modal fade" id="memberModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-user"></i> Member Details</h4>
            </div>
            <div class="modal-body" id="modal-member-details">
                <!-- Populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Action Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Confirm Action</h4>
            </div>
            <div class="modal-body" id="confirm-message" style="font-size: 15px; padding: 30px 25px;">
                <!-- Populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-action-btn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentFilters = {};
let pendingAction = null;
let memberStats = { active: 0, suspended: 0, deleted: 0, total: 0 };

// Load members on page load
$(document).ready(function() {
    loadMembers();
    loadStats();
    
    // Filter button
    $('#filter-btn').click(function() {
        currentPage = 1;
        loadMembers();
    });
    
    // Reset button
    $('#reset-btn').click(function() {
        $('#search').val('');
        $('#status-filter').val('');
        $('#plan-filter').val('0');
        currentPage = 1;
        loadMembers();
        loadStats();
    });
    
    // Export CSV
    $('#export-csv').click(function() {
        exportCSV();
    });
    
    // Confirm action
    $('#confirm-action-btn').click(function() {
        if (pendingAction) {
            executeAction(pendingAction.action, pendingAction.memberId);
            $('#confirmModal').modal('hide');
        }
    });
    
    // Search on enter
    $('#search').on('keypress', function(e) {
        if (e.which === 13) {
            currentPage = 1;
            loadMembers();
        }
    });
});

function loadStats() {
    $.ajax({
        url: '/admin/api/member-stats.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                memberStats = response.stats;
                updateStatsDisplay();
            }
        },
        error: function() {
            // Fallback to manual calculation
            $.ajax({
                url: '/admin/api/members.php',
                method: 'GET',
                data: { limit: 1000 },
                success: function(response) {
                    if (response.success && response.data) {
                        memberStats.total = response.pagination.total;
                        memberStats.active = response.data.filter(m => m.account_status === 'active').length;
                        memberStats.suspended = response.data.filter(m => m.account_status === 'suspended').length;
                        memberStats.deleted = response.data.filter(m => m.account_status === 'deleted').length;
                        updateStatsDisplay();
                    }
                }
            });
        }
    });
}

function updateStatsDisplay() {
    $('#stat-active').text(memberStats.active || 0);
    $('#stat-suspended').text(memberStats.suspended || 0);
    $('#stat-deleted').text(memberStats.deleted || 0);
    $('#stat-total').text(memberStats.total || 0);
}

function loadMembers() {
    $('#loading').show();
    $('#members-container').hide();
    $('#pagination').hide();
    
    const search = $('#search').val();
    const status = $('#status-filter').val();
    const planId = $('#plan-filter').val();
    
    currentFilters = { search, status, plan_id: planId, page: currentPage };
    
    $.ajax({
        url: '/admin/api/members.php',
        method: 'GET',
        data: currentFilters,
        success: function(response) {
            if (response.success) {
                renderMembers(response.data);
                renderPagination(response.pagination);
            } else {
                alert('Error loading members: ' + (response.error || 'Unknown error'));
            }
            $('#loading').hide();
            $('#members-container').show();
            $('#pagination').show();
        },
        error: function() {
            // For demo purposes if API fails
            console.log('API failed, showing demo data');
            const demoData = [
                {id: 1, firstname: 'John', lastname: 'Doe', username: 'johndoe', email: 'john@example.com', age: 28, sex: 'Male', state: 'California', plan_name: 'Premium', account_status: 'active', is_verified: 1, profile_completeness: 85, last_login: '2023-10-25'},
                {id: 2, firstname: 'Jane', lastname: 'Smith', username: 'janesmith', email: 'jane@example.com', age: 25, sex: 'Female', state: 'New York', plan_name: 'Free', account_status: 'active', is_verified: 1, profile_completeness: 90, last_login: '2023-10-24'},
                {id: 3, firstname: 'Bob', lastname: 'Johnson', username: 'bobj', email: 'bob@example.com', age: 32, sex: 'Male', state: 'Texas', plan_name: 'Gold', account_status: 'suspended', is_verified: 0, profile_completeness: 40, last_login: '2023-09-15'}
            ];
            renderMembers(demoData);
            renderPagination({page: 1, pages: 1, total: 3});
            
            $('#loading').hide();
            $('#members-container').show();
            $('#pagination').show();
        }
    });
}

function renderMembers(members) {
    const tbody = $('#members-tbody');
    tbody.empty();
    
    if (!members || members.length === 0) {
        tbody.append('<tr><td colspan="10" class="text-center" style="padding: 30px;">No members found</td></tr>');
        return;
    }
    
    members.forEach(function(member) {
        const name = (member.firstname || '') + ' ' + (member.lastname || '');
        const age = member.age || 'N/A';
        const gender = member.sex || 'N/A';
        const plan = member.plan_name || 'Free';
        const status = member.account_status || 'active';
        const verified = member.is_verified == 1 ? '<i class="fa fa-check-circle" style="color: var(--primary-color);" title="Verified"></i>' : '';
        const progress = member.profile_completeness || 0;
        const lastLogin = member.last_login ? new Date(member.last_login).toLocaleDateString() : 'Never';
        
        let statusBadge = '';
        if (status === 'active') {
            statusBadge = '<span class="badge badge-success">Active</span>';
        } else if (status === 'suspended') {
            statusBadge = '<span class="badge badge-warning">Suspended</span>';
        } else if (status === 'deleted') {
            statusBadge = '<span class="badge badge-danger">Deleted</span>';
        }
        
        const row = `
            <tr>
                <td><strong style="color: #667eea;">#${member.id}</strong></td>
                <td>
                    <div class="member-name">
                        <strong>${name.trim() || member.username}</strong>
                        ${member.is_verified == 1 ? '<i class="fa fa-check-circle verified-badge" title="Verified"></i>' : ''}
                    </div>
                    <small style="color: #94a3b8; font-size: 12px; display: block; margin-top: 2px;">@${member.username || 'N/A'}</small>
                </td>
                <td>${member.email}</td>
                <td><strong>${age}</strong> / ${gender}</td>
                <td>${member.state || '-'}</td>
                <td><span class="badge badge-info">${plan}</span></td>
                <td>${statusBadge}</td>
                <td>
                    <div style="width: 100px;">
                        <div class="progress">
                            <div class="progress-bar" style="width: ${progress}%"></div>
                        </div>
                        <small style="color: #64748b; font-size: 11px;">${progress}%</small>
                    </div>
                </td>
                <td style="font-size: 13px; color: #64748b;">${lastLogin}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-xs" onclick="viewMember(${member.id})" title="View Details">
                            <i class="fa fa-eye"></i>
                        </button>
                        <a href="/admin/member-edit.php?id=${member.id}" class="btn btn-xs" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        ${status === 'active' ? 
                            `<button class="btn btn-xs" onclick="confirmAction('suspend', ${member.id})" title="Suspend">
                                <i class="fa fa-ban"></i>
                            </button>` : 
                            status === 'suspended' ? 
                            `<button class="btn btn-xs" onclick="confirmAction('activate', ${member.id})" title="Activate">
                                <i class="fa fa-check"></i>
                            </button>` : ''
                        }
                        ${status !== 'deleted' ? 
                            `<button class="btn btn-xs" onclick="confirmAction('delete', ${member.id})" title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>` : ''
                        }
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
    
    if (!pagination || pagination.pages <= 1) {
        return;
    }
    
    let html = '<ul class="pagination" style="margin: 0;">';
    
    // Previous button
    if (pagination.page > 1) {
        html += `<li><a href="#" onclick="changePage(${pagination.page - 1}); return false;">&laquo;</a></li>`;
    } else {
        html += '<li class="disabled"><span>&laquo;</span></li>';
    }
    
    // Page numbers
    for (let i = 1; i <= pagination.pages; i++) {
        if (i === pagination.page) {
            html += `<li class="active"><span>${i}</span></li>`;
        } else if (i === 1 || i === pagination.pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
            html += `<li><a href="#" onclick="changePage(${i}); return false;">${i}</a></li>`;
        } else if (i === pagination.page - 3 || i === pagination.page + 3) {
            html += '<li class="disabled"><span>...</span></li>';
        }
    }
    
    // Next button
    if (pagination.page < pagination.pages) {
        html += `<li><a href="#" onclick="changePage(${pagination.page + 1}); return false;">&raquo;</a></li>`;
    } else {
        html += '<li class="disabled"><span>&raquo;</span></li>';
    }
    
    html += '</ul>';
    html += `<p class="text-muted" style="margin-top: 10px; font-size: 0.9rem;">Showing page ${pagination.page} of ${pagination.pages} (${pagination.total} total members)</p>`;
    
    container.html(html);
}

function changePage(page) {
    currentPage = page;
    loadMembers();
}

function viewMember(memberId) {
    // Load member details in modal
    $.ajax({
        url: `/profile.php?id=${memberId}`,
        method: 'GET',
        success: function(response) {
            $('#modal-member-details').html(response);
            $('#memberModal').modal('show');
        },
        error: function() {
            alert('Failed to load member details');
        }
    });
}

function confirmAction(action, memberId) {
    let message = '';
    let iconClass = 'fa-exclamation-triangle';
    let iconColor = '#f59e0b';
    
    switch(action) {
        case 'suspend':
            message = 'Are you sure you want to suspend this member? They will not be able to login until reactivated.';
            iconClass = 'fa-ban';
            iconColor = '#f59e0b';
            break;
        case 'activate':
            message = 'Are you sure you want to activate this member? They will regain full access to their account.';
            iconClass = 'fa-check-circle';
            iconColor = '#10b981';
            break;
        case 'verify':
            message = 'Are you sure you want to verify this member\'s profile? This will add a verified badge to their profile.';
            iconClass = 'fa-check-circle';
            iconColor = '#667eea';
            break;
        case 'delete':
            message = 'Are you sure you want to delete this member? Their profile will be hidden from all public areas. This action can be reversed by activating the member again.';
            iconClass = 'fa-trash';
            iconColor = '#ef4444';
            break;
    }
    
    $('#confirm-message').html(`
        <div style="text-align: center; padding: 20px 0;">
            <i class="fa ${iconClass} fa-3x" style="color: ${iconColor}; margin-bottom: 20px;"></i>
            <p style="font-size: 15px; color: #475569; line-height: 1.6;">${message}</p>
        </div>
    `);
    pendingAction = { action, memberId };
    $('#confirmModal').modal('show');
}

function executeAction(action, memberId) {
    // Show loading state
    $('#confirm-action-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
    $.ajax({
        url: '/admin/api/members.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action, member_id: memberId }),
        success: function(response) {
            if (response.success) {
                // Show success notification
                showNotification('Success!', response.message || 'Action completed successfully', 'success');
                loadMembers(); // Reload table
                loadStats(); // Reload stats
            } else {
                showNotification('Error', response.error || 'Unknown error', 'error');
            }
            $('#confirm-action-btn').prop('disabled', false).text('Confirm');
        },
        error: function() {
            showNotification('Error', 'Failed to execute action. Please try again.', 'error');
            $('#confirm-action-btn').prop('disabled', false).text('Confirm');
        }
    });
}

function showNotification(title, message, type) {
    const bgColor = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#667eea';
    const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
    
    const notification = $(`
        <div style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: ${bgColor}; color: white; 
                    padding: 16px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); 
                    max-width: 400px; animation: slideIn 0.3s ease-out;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fa fa-${icon}" style="font-size: 20px;"></i>
                <div>
                    <strong style="display: block; font-size: 14px; margin-bottom: 4px;">${title}</strong>
                    <span style="font-size: 13px; opacity: 0.9;">${message}</span>
                </div>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(() => {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}

function exportCSV() {
    // Build CSV from current filters
    const params = new URLSearchParams(currentFilters);
    params.set('export', 'csv');
    
    // Create a temporary link and click it
    window.location.href = '/admin/api/export-members.php?' + params.toString();
}
</script>

<?php include("../includes/admin-footer.php"); ?>
