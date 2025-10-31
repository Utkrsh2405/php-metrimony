<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("../includes/dbconn.php");

// Verify admin status
$user_id = $_SESSION['id'];
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

<div class="admin-content">
    <h1>Member Management</h1>
    
    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" id="search" class="form-control" placeholder="Search by name, email, username...">
                </div>
                <div class="col-md-2">
                    <select id="status-filter" class="form-control">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="deleted">Deleted</option>
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
                    <button id="filter-btn" class="btn btn-primary">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <button id="reset-btn" class="btn btn-default">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
                <div class="col-md-3 text-right">
                    <button id="export-csv" class="btn btn-success">
                        <i class="fa fa-download"></i> Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Members Table -->
    <div class="card">
        <div class="card-body">
            <div id="loading" style="text-align: center; padding: 20px; display: none;">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p>Loading members...</p>
            </div>
            
            <div id="members-container">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Age/Gender</th>
                            <th>State</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Profile</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="members-tbody">
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

<!-- Member Details Modal -->
<div class="modal fade" id="memberModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Member Details</h4>
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
                <h4 class="modal-title">Confirm Action</h4>
            </div>
            <div class="modal-body" id="confirm-message">
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

// Load members on page load
$(document).ready(function() {
    loadMembers();
    
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
});

function loadMembers() {
    $('#loading').show();
    $('#members-container').hide();
    
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
        },
        error: function() {
            alert('Failed to load members');
            $('#loading').hide();
            $('#members-container').show();
        }
    });
}

function renderMembers(members) {
    const tbody = $('#members-tbody');
    tbody.empty();
    
    if (members.length === 0) {
        tbody.append('<tr><td colspan="10" class="text-center">No members found</td></tr>');
        return;
    }
    
    members.forEach(function(member) {
        const name = (member.firstname || '') + ' ' + (member.lastname || '');
        const age = member.age || 'N/A';
        const gender = member.sex || 'N/A';
        const plan = member.plan_name || 'Free';
        const status = member.account_status || 'active';
        const verified = member.is_verified == 1 ? '<i class="fa fa-check-circle text-success"></i>' : '';
        const progress = member.profile_completeness || 0;
        const lastLogin = member.last_login ? new Date(member.last_login).toLocaleDateString() : 'Never';
        
        let statusBadge = '';
        if (status === 'active') {
            statusBadge = '<span class="label label-success">Active</span>';
        } else if (status === 'suspended') {
            statusBadge = '<span class="label label-warning">Suspended</span>';
        } else if (status === 'deleted') {
            statusBadge = '<span class="label label-danger">Deleted</span>';
        }
        
        const row = `
            <tr>
                <td>${member.id}</td>
                <td>${name.trim() || member.username} ${verified}</td>
                <td>${member.email}</td>
                <td>${age}/${gender}</td>
                <td>${member.state || 'N/A'}</td>
                <td>${plan}</td>
                <td>${statusBadge}</td>
                <td>
                    <div class="progress" style="margin: 0;">
                        <div class="progress-bar" role="progressbar" style="width: ${progress}%">${progress}%</div>
                    </div>
                </td>
                <td>${lastLogin}</td>
                <td>
                    <div class="btn-group btn-group-xs">
                        <a href="/admin/member-edit.php?id=${member.id}" class="btn btn-primary" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button class="btn btn-info" onclick="viewMember(${member.id})" title="View">
                            <i class="fa fa-eye"></i>
                        </button>
                        ${status === 'active' ? 
                            `<button class="btn btn-warning" onclick="confirmAction('suspend', ${member.id})" title="Suspend">
                                <i class="fa fa-ban"></i>
                            </button>` : 
                            `<button class="btn btn-success" onclick="confirmAction('activate', ${member.id})" title="Activate">
                                <i class="fa fa-check"></i>
                            </button>`
                        }
                        ${member.is_verified != 1 ? 
                            `<button class="btn btn-info" onclick="confirmAction('verify', ${member.id})" title="Verify">
                                <i class="fa fa-certificate"></i>
                            </button>` : ''
                        }
                        <button class="btn btn-danger" onclick="confirmAction('delete', ${member.id})" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
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
    html += `<p class="text-muted">Showing page ${pagination.page} of ${pagination.pages} (${pagination.total} total members)</p>`;
    
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
    
    switch(action) {
        case 'suspend':
            message = 'Are you sure you want to suspend this member?';
            break;
        case 'activate':
            message = 'Are you sure you want to activate this member?';
            break;
        case 'verify':
            message = 'Are you sure you want to verify this member\'s profile?';
            break;
        case 'delete':
            message = 'Are you sure you want to delete this member? This action cannot be undone.';
            break;
    }
    
    $('#confirm-message').text(message);
    pendingAction = { action, memberId };
    $('#confirmModal').modal('show');
}

function executeAction(action, memberId) {
    $.ajax({
        url: '/admin/api/members.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action, member_id: memberId }),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                loadMembers(); // Reload table
            } else {
                alert('Error: ' + (response.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Failed to execute action');
        }
    });
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
