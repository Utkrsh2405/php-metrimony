<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("../includes/dbconn.php");

// Check admin role
$user_id = intval($_SESSION['id']);
$role_check = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id LIMIT 1");
if (!$role_check || mysqli_num_rows($role_check) === 0) {
    header("Location: /login.php");
    exit();
}
$user = mysqli_fetch_assoc($role_check);
if ($user['userlevel'] != 1) {
    header("Location: /index.php");
    exit();
}

include("../includes/admin-header.php");
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
    transition: all 0.3s;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}
.stat-value {
    font-size: 36px;
    font-weight: bold;
    margin: 10px 0;
}
.stat-pending { color: #ffc107; }
.stat-accepted { color: #4caf50; }
.stat-declined { color: #f44336; }
.stat-total { color: #2196f3; }
.filter-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.interest-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}
.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}
.status-pending { background: #fff3cd; color: #856404; }
.status-accepted { background: #d4edda; color: #155724; }
.status-declined { background: #f8d7da; color: #721c24; }
.action-btn {
    padding: 5px 10px;
    font-size: 12px;
    margin: 0 2px;
}
</style>

<div class="container-fluid">
    <div class="page-header">
        <h1><i class="fa fa-heart"></i> Interest Management</h1>
        <p style="margin: 10px 0 0 0;">View and manage all member interests</p>
    </div>
    
    <!-- Statistics -->
    <div class="row stats-row">
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-list stat-total" style="font-size: 48px;"></i>
                <div class="stat-value stat-total" id="total-count">0</div>
                <div>Total Interests</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-clock-o stat-pending" style="font-size: 48px;"></i>
                <div class="stat-value stat-pending" id="pending-count">0</div>
                <div>Pending</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-check-circle stat-accepted" style="font-size: 48px;"></i>
                <div class="stat-value stat-accepted" id="accepted-count">0</div>
                <div>Accepted</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-times-circle stat-declined" style="font-size: 48px;"></i>
                <div class="stat-value stat-declined" id="declined-count">0</div>
                <div>Declined</div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-section">
        <div class="row">
            <div class="col-md-3">
                <label>Status</label>
                <select id="filter-status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="declined">Declined</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>From User ID</label>
                <input type="number" id="filter-from" class="form-control" placeholder="User ID">
            </div>
            <div class="col-md-3">
                <label>To User ID</label>
                <input type="number" id="filter-to" class="form-control" placeholder="User ID">
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <button class="btn btn-primary btn-block" onclick="loadInterests()">
                    <i class="fa fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>
    
    <!-- Table -->
    <div class="interest-table">
        <table class="table table-striped table-hover" id="interests-table">
            <thead style="background: #f5f5f5;">
                <tr>
                    <th>ID</th>
                    <th>From User</th>
                    <th>To User</th>
                    <th>Status</th>
                    <th>Message</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="interests-tbody">
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

<script>
let currentPage = 0;
const perPage = 50;

$(document).ready(function() {
    loadStats();
    loadInterests();
});

function loadStats() {
    $.get('/admin/api/interest-logs.php?action=stats', function(response) {
        if (response.success) {
            $('#total-count').text(response.stats.total);
            $('#pending-count').text(response.stats.pending);
            $('#accepted-count').text(response.stats.accepted);
            $('#declined-count').text(response.stats.declined);
        }
    });
}

function loadInterests(append = false) {
    if (!append) {
        currentPage = 0;
    }
    
    const filters = {
        status: $('#filter-status').val(),
        from_user: $('#filter-from').val(),
        to_user: $('#filter-to').val(),
        page: currentPage,
        per_page: perPage
    };
    
    $.get('/admin/api/interest-logs.php', filters, function(response) {
        if (response.success) {
            renderInterests(response.data, append);
            
            if (response.has_more) {
                $('#load-more-btn').show();
            } else {
                $('#load-more-btn').hide();
            }
        }
    });
}

function renderInterests(interests, append) {
    const tbody = $('#interests-tbody');
    if (!append) {
        tbody.empty();
    }
    
    if (interests.length === 0 && !append) {
        tbody.html('<tr><td colspan="8" class="text-center text-muted">No interests found</td></tr>');
        return;
    }
    
    interests.forEach(interest => {
        const statusClass = 'status-' + interest.status;
        const created = moment(interest.created_at).format('YYYY-MM-DD HH:mm');
        const updated = interest.updated_at ? moment(interest.updated_at).format('YYYY-MM-DD HH:mm') : '-';
        const message = interest.message ? interest.message.substring(0, 50) + (interest.message.length > 50 ? '...' : '') : '-';
        
        tbody.append(`
            <tr>
                <td>${interest.id}</td>
                <td>
                    <a href="/admin/members.php?id=${interest.from_user_id}" target="_blank">
                        ${interest.from_name} (#${interest.from_user_id})
                    </a>
                </td>
                <td>
                    <a href="/admin/members.php?id=${interest.to_user_id}" target="_blank">
                        ${interest.to_name} (#${interest.to_user_id})
                    </a>
                </td>
                <td>
                    <span class="status-badge ${statusClass}">${interest.status}</span>
                </td>
                <td title="${interest.message || ''}">${message}</td>
                <td>${created}</td>
                <td>${updated}</td>
                <td>
                    <button class="btn btn-info btn-xs action-btn" onclick="viewDetails(${interest.id})">
                        <i class="fa fa-eye"></i>
                    </button>
                    ${interest.status === 'pending' ? `
                        <button class="btn btn-danger btn-xs action-btn" onclick="deleteInterest(${interest.id})">
                            <i class="fa fa-trash"></i>
                        </button>
                    ` : ''}
                </td>
            </tr>
        `);
    });
}

function loadMore() {
    currentPage++;
    loadInterests(true);
}

function viewDetails(interestId) {
    alert('View interest details #' + interestId + '\n(Full details modal can be implemented here)');
}

function deleteInterest(interestId) {
    if (!confirm('Are you sure you want to delete this interest? This action cannot be undone.')) return;
    
    $.ajax({
        url: '/admin/api/interest-logs.php',
        method: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({ interest_id: interestId }),
        success: function(response) {
            if (response.success) {
                alert('Interest deleted successfully');
                loadStats();
                loadInterests();
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<?php include("../includes/admin-footer.php"); ?>
