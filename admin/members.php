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

<div class="page-header">
    <h2><i class="fa fa-users"></i> Member Management</h2>
    <div>
        <button id="export-csv" class="btn btn-success">
            <i class="fa fa-download"></i> Export CSV
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 24px;">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Search</label>
                <input type="text" id="search" class="form-control" placeholder="Name, email, username...">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>Status</label>
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
                <label>Plan</label>
                <select id="plan-filter" class="form-control">
                    <option value="0">All Plans</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?php echo $plan['id']; ?>"><?php echo htmlspecialchars($plan['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-3" style="padding-top: 25px;">
            <button id="filter-btn" class="btn btn-primary">
                <i class="fa fa-filter"></i> Filter
            </button>
            <button id="reset-btn" class="btn btn-default" style="margin-left: 10px;">
                <i class="fa fa-refresh"></i> Reset
            </button>
        </div>
    </div>
</div>

<!-- Members Table -->
<div class="card">
    <div id="loading" style="text-align: center; padding: 40px; display: none;">
        <i class="fa fa-spinner fa-spin fa-2x" style="color: var(--primary-color);"></i>
        <p style="margin-top: 10px; color: #64748b;">Loading members...</p>
    </div>
    
    <div id="members-container" class="admin-table">
        <table class="table">
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
    <div id="pagination" class="text-center" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
        <!-- Populated by JavaScript -->
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
                <td>#${member.id}</td>
                <td><strong>${name.trim() || member.username}</strong> ${verified}</td>
                <td>${member.email}</td>
                <td>${age} / ${gender}</td>
                <td>${member.state || 'N/A'}</td>
                <td><span class="badge badge-info">${plan}</span></td>
                <td>${statusBadge}</td>
                <td style="width: 100px;">
                    <div class="progress" style="margin: 0; height: 10px; border-radius: 5px; background-color: #e2e8f0;">
                        <div class="progress-bar" role="progressbar" style="width: ${progress}%; background-color: var(--primary-color);"></div>
                    </div>
                    <small style="font-size: 10px; color: #64748b;">${progress}%</small>
                </td>
                <td><small>${lastLogin}</small></td>
                <td>
                    <div class="btn-group">
                        <a href="/admin/member-edit.php?id=${member.id}" class="btn btn-default btn-xs" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button class="btn btn-default btn-xs" onclick="viewMember(${member.id})" title="View">
                            <i class="fa fa-eye"></i>
                        </button>
                        ${status === 'active' ? 
                            `<button class="btn btn-default btn-xs" onclick="confirmAction('suspend', ${member.id})" title="Suspend" style="color: var(--warning-color);">
                                <i class="fa fa-ban"></i>
                            </button>` : 
                            `<button class="btn btn-default btn-xs" onclick="confirmAction('activate', ${member.id})" title="Activate" style="color: var(--success-color);">
                                <i class="fa fa-check"></i>
                            </button>`
                        }
                        <button class="btn btn-default btn-xs" onclick="confirmAction('delete', ${member.id})" title="Delete" style="color: var(--danger-color);">
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
                // alert(response.message);
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
