<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("../includes/dbconn.php");

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
    margin: 10px 0;
    color: #667eea;
}
.filter-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.messages-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}
.read-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
}
.badge-read {
    background: #d4edda;
    color: #155724;
}
.badge-unread {
    background: #fff3cd;
    color: #856404;
}
.message-preview {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>

<div class="container-fluid">
    <div class="page-header">
        <h1><i class="fa fa-envelope"></i> Message Management</h1>
        <p style="margin: 10px 0 0 0;">View and moderate all member messages</p>
    </div>
    
    <!-- Statistics -->
    <div class="row stats-row">
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-envelope" style="font-size: 48px; color: #667eea;"></i>
                <div class="stat-value" id="total-count">0</div>
                <div>Total Messages</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-envelope-open" style="font-size: 48px; color: #4caf50;"></i>
                <div class="stat-value" style="color: #4caf50;" id="read-count">0</div>
                <div>Read Messages</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-envelope-o" style="font-size: 48px; color: #ffc107;"></i>
                <div class="stat-value" style="color: #ffc107;" id="unread-count">0</div>
                <div>Unread Messages</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fa fa-calendar" style="font-size: 48px; color: #2196f3;"></i>
                <div class="stat-value" style="color: #2196f3;" id="today-count">0</div>
                <div>Today's Messages</div>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-section">
        <div class="row">
            <div class="col-md-3">
                <label>From User ID</label>
                <input type="number" id="filter-from" class="form-control" placeholder="User ID">
            </div>
            <div class="col-md-3">
                <label>To User ID</label>
                <input type="number" id="filter-to" class="form-control" placeholder="User ID">
            </div>
            <div class="col-md-3">
                <label>Status</label>
                <select id="filter-read" class="form-control">
                    <option value="">All</option>
                    <option value="1">Read</option>
                    <option value="0">Unread</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <button class="btn btn-primary btn-block" onclick="loadMessages()">
                    <i class="fa fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>
    
    <!-- Table -->
    <div class="messages-table">
        <table class="table table-striped table-hover">
            <thead style="background: #f5f5f5;">
                <tr>
                    <th>ID</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="messages-tbody">
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

<!-- Message View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Message Details</h4>
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
    loadStats();
    loadMessages();
});

function loadStats() {
    $.get('/admin/api/message-logs.php?action=stats', function(response) {
        if (response.success) {
            $('#total-count').text(response.stats.total);
            $('#read-count').text(response.stats.read);
            $('#unread-count').text(response.stats.unread);
            $('#today-count').text(response.stats.today);
        }
    });
}

function loadMessages(append = false) {
    if (!append) {
        currentPage = 0;
    }
    
    const filters = {
        from_user: $('#filter-from').val(),
        to_user: $('#filter-to').val(),
        is_read: $('#filter-read').val(),
        page: currentPage,
        per_page: perPage
    };
    
    $.get('/admin/api/message-logs.php', filters, function(response) {
        if (response.success) {
            renderMessages(response.data, append);
            
            if (response.has_more) {
                $('#load-more-btn').show();
            } else {
                $('#load-more-btn').hide();
            }
        }
    });
}

function renderMessages(messages, append) {
    const tbody = $('#messages-tbody');
    if (!append) {
        tbody.empty();
    }
    
    if (messages.length === 0 && !append) {
        tbody.html('<tr><td colspan="8" class="text-center text-muted">No messages found</td></tr>');
        return;
    }
    
    messages.forEach(msg => {
        const readBadge = msg.is_read == 1 
            ? '<span class="read-badge badge-read">Read</span>'
            : '<span class="read-badge badge-unread">Unread</span>';
        const created = moment(msg.created_at).format('YYYY-MM-DD HH:mm');
        const subject = msg.subject || '-';
        const preview = msg.message.length > 50 ? msg.message.substring(0, 50) + '...' : msg.message;
        
        tbody.append(`
            <tr>
                <td>${msg.id}</td>
                <td>
                    <a href="/admin/members.php?id=${msg.from_user_id}" target="_blank">
                        ${msg.from_name} (#${msg.from_user_id})
                    </a>
                </td>
                <td>
                    <a href="/admin/members.php?id=${msg.to_user_id}" target="_blank">
                        ${msg.to_name} (#${msg.to_user_id})
                    </a>
                </td>
                <td>${subject}</td>
                <td class="message-preview" title="${msg.message}">${preview}</td>
                <td>${readBadge}</td>
                <td>${created}</td>
                <td>
                    <button class="btn btn-info btn-xs" onclick="viewMessage(${msg.id})">
                        <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-danger btn-xs" onclick="deleteMessage(${msg.id})">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function loadMore() {
    currentPage++;
    loadMessages(true);
}

function viewMessage(messageId) {
    $.get('/admin/api/message-logs.php?action=view&id=' + messageId, function(response) {
        if (response.success) {
            const msg = response.data;
            const readStatus = msg.is_read == 1 ? '<span class="label label-success">Read</span>' : '<span class="label label-warning">Unread</span>';
            
            $('#modal-body').html(`
                <table class="table table-bordered">
                    <tr>
                        <th width="150">From:</th>
                        <td>${msg.from_name} (#${msg.from_user_id})</td>
                    </tr>
                    <tr>
                        <th>To:</th>
                        <td>${msg.to_name} (#${msg.to_user_id})</td>
                    </tr>
                    <tr>
                        <th>Subject:</th>
                        <td>${msg.subject || '-'}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>${readStatus}</td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>${moment(msg.created_at).format('MMMM D, YYYY h:mm A')}</td>
                    </tr>
                    <tr>
                        <th>Message:</th>
                        <td style="white-space: pre-wrap;">${msg.message}</td>
                    </tr>
                </table>
            `);
            $('#viewModal').modal('show');
        }
    });
}

function deleteMessage(messageId) {
    if (!confirm('Are you sure you want to delete this message? This action cannot be undone.')) return;
    
    $.ajax({
        url: '/admin/api/message-logs.php',
        method: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({ message_id: messageId }),
        success: function(response) {
            if (response.success) {
                alert('Message deleted successfully');
                loadStats();
                loadMessages();
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<?php include("includes/footer.php"); ?>
