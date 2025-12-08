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

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'templates';

include("../includes/admin-header.php");
?>

<div class="admin-content">
    <h1>SMS Management</h1>
    <p class="text-muted">Manage SMS templates, configuration, and view logs</p>
    
    <!-- Tabs -->
    <ul class="nav nav-tabs" style="margin-bottom: 20px;">
        <li class="<?php echo $active_tab == 'templates' ? 'active' : ''; ?>">
            <a href="?tab=templates">Templates</a>
        </li>
        <li class="<?php echo $active_tab == 'config' ? 'active' : ''; ?>">
            <a href="?tab=config">Configuration</a>
        </li>
        <li class="<?php echo $active_tab == 'logs' ? 'active' : ''; ?>">
            <a href="?tab=logs">SMS Logs</a>
        </li>
        <li class="<?php echo $active_tab == 'test' ? 'active' : ''; ?>">
            <a href="?tab=test">Test Send</a>
        </li>
    </ul>
    
    <!-- Templates Tab -->
    <div id="templates-tab" style="display: <?php echo $active_tab == 'templates' ? 'block' : 'none'; ?>;">
        <button id="add-template-btn" class="btn btn-primary" style="margin-bottom: 15px;">
            <i class="fa fa-plus"></i> Add New Template
        </button>
        
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Event Trigger</th>
                            <th>Content Preview</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="templates-tbody">
                        <tr><td colspan="5" class="text-center">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Configuration Tab -->
    <div id="config-tab" style="display: <?php echo $active_tab == 'config' ? 'block' : 'none'; ?>;">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> Configure your SMS gateway credentials. Only one gateway can be active at a time.
        </div>
        
        <div id="config-container">
            <p class="text-center">Loading...</p>
        </div>
    </div>
    
    <!-- Logs Tab -->
    <div id="logs-tab" style="display: <?php echo $active_tab == 'logs' ? 'block' : 'none'; ?>;">
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-3">
                <select id="log-status-filter" class="form-control">
                    <option value="">All Status</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="log-event-filter" class="form-control">
                    <option value="">All Events</option>
                    <option value="registration">Registration</option>
                    <option value="plan_expiry">Plan Expiry</option>
                    <option value="interest_received">Interest Received</option>
                    <option value="message_received">Message Received</option>
                    <option value="test">Test</option>
                </select>
            </div>
            <div class="col-md-2">
                <button id="filter-logs-btn" class="btn btn-primary">
                    <i class="fa fa-filter"></i> Filter
                </button>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Member</th>
                            <th>Phone</th>
                            <th>Message</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th>Gateway</th>
                        </tr>
                    </thead>
                    <tbody id="logs-tbody">
                        <tr><td colspan="7" class="text-center">Loading...</td></tr>
                    </tbody>
                </table>
                <div id="logs-pagination" class="text-center"></div>
            </div>
        </div>
    </div>
    
    <!-- Test Send Tab -->
    <div id="test-tab" style="display: <?php echo $active_tab == 'test' ? 'block' : 'none'; ?>;">
        <div class="card">
            <div class="card-header">
                <h3>Send Test SMS</h3>
            </div>
            <div class="card-body">
                <form id="test-sms-form">
                    <div class="form-group">
                        <label>Phone Number (with country code)</label>
                        <input type="text" id="test-phone" class="form-control" placeholder="+919876543210" required>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea id="test-message" class="form-control" rows="4" required>This is a test SMS from MakeMyLove matrimony platform.</textarea>
                        <small class="text-muted"><span id="char-count">0</span> characters</small>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-paper-plane"></i> Send Test SMS
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Template Edit Modal -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Template</h4>
            </div>
            <form id="template-form">
                <div class="modal-body">
                    <input type="hidden" id="template-id">
                    
                    <div class="form-group">
                        <label>Template Name</label>
                        <input type="text" id="template-name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Event Trigger</label>
                        <select id="template-event" class="form-control" required>
                            <option value="registration">Registration</option>
                            <option value="plan_expiry">Plan Expiry</option>
                            <option value="interest_received">Interest Received</option>
                            <option value="interest_accepted">Interest Accepted</option>
                            <option value="message_received">Message Received</option>
                            <option value="payment_success">Payment Success</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Subject (Optional)</label>
                        <input type="text" id="template-subject" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Message Content</label>
                        <textarea id="template-content" class="form-control" rows="5" required></textarea>
                        <small class="text-muted">Use {{variable}} for placeholders. Example: {{name}}, {{plan_name}}</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Available Variables (comma separated)</label>
                        <input type="text" id="template-variables" class="form-control" placeholder="name, email, plan_name">
                    </div>
                    
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="template-active"> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentLogsPage = 1;

$(document).ready(function() {
    loadTemplates();
    loadConfig();
    loadLogs();
    
    $('#add-template-btn').click(() => openTemplateModal());
    $('#template-form').submit(function(e) {
        e.preventDefault();
        saveTemplate();
    });
    
    $('#test-sms-form').submit(function(e) {
        e.preventDefault();
        sendTestSMS();
    });
    
    $('#test-message').on('input', function() {
        $('#char-count').text($(this).val().length);
    });
    
    $('#filter-logs-btn').click(() => loadLogs());
});

function loadTemplates() {
    $.get('/admin/api/sms.php?endpoint=templates', function(response) {
        if (response.success) {
            renderTemplates(response.data);
        }
    });
}

function renderTemplates(templates) {
    const tbody = $('#templates-tbody');
    tbody.empty();
    
    if (templates.length === 0) {
        tbody.append('<tr><td colspan="5" class="text-center">No templates found</td></tr>');
        return;
    }
    
    templates.forEach(t => {
        const preview = t.content.substring(0, 60) + '...';
        const status = t.is_active == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>';
        
        tbody.append(`
            <tr>
                <td>${t.name}</td>
                <td><span class="label label-info">${t.event_trigger}</span></td>
                <td><small>${preview}</small></td>
                <td>${status}</td>
                <td>
                    <button class="btn btn-xs btn-primary" onclick="editTemplate(${t.id})"><i class="fa fa-edit"></i></button>
                    <button class="btn btn-xs btn-danger" onclick="deleteTemplate(${t.id})"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `);
    });
}

function openTemplateModal(template = null) {
    if (template) {
        $('#template-id').val(template.id);
        $('#template-name').val(template.name);
        $('#template-event').val(template.event_trigger);
        $('#template-subject').val(template.subject);
        $('#template-content').val(template.content);
        $('#template-variables').val(template.variables ? template.variables.join(', ') : '');
        $('#template-active').prop('checked', template.is_active == 1);
    } else {
        $('#template-form')[0].reset();
        $('#template-id').val('');
        $('#template-active').prop('checked', true);
    }
    $('#templateModal').modal('show');
}

function editTemplate(id) {
    $.get(`/admin/api/sms.php?endpoint=template&id=${id}`, function(response) {
        if (response.success) {
            openTemplateModal(response.data);
        }
    });
}

function saveTemplate() {
    const variables = $('#template-variables').val().split(',').map(v => v.trim()).filter(v => v);
    
    const data = {
        id: $('#template-id').val(),
        name: $('#template-name').val(),
        event_trigger: $('#template-event').val(),
        subject: $('#template-subject').val(),
        content: $('#template-content').val(),
        variables: variables,
        is_active: $('#template-active').is(':checked')
    };
    
    $.ajax({
        url: '/admin/api/sms.php?endpoint=template',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#templateModal').modal('hide');
                loadTemplates();
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}

function deleteTemplate(id) {
    if (!confirm('Are you sure you want to delete this template?')) return;
    
    $.ajax({
        url: `/admin/api/sms.php?endpoint=template&id=${id}`,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                loadTemplates();
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}

function loadConfig() {
    $.get('/admin/api/sms.php?endpoint=config', function(response) {
        if (response.success) {
            renderConfig(response.data);
        }
    });
}

function renderConfig(configs) {
    const container = $('#config-container');
    container.empty();
    
    configs.forEach(c => {
        const active = c.is_active == 1 ? 'checked' : '';
        container.append(`
            <div class="card" style="margin-bottom: 15px;">
                <div class="card-header">
                    <h3>${c.gateway.toUpperCase()} Configuration</h3>
                </div>
                <div class="card-body">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" ${active} onchange="toggleGateway(${c.id}, this.checked)"> 
                            <strong>Active</strong>
                        </label>
                    </div>
                    <p class="text-muted">Configure ${c.gateway} credentials in database or contact developer.</p>
                </div>
            </div>
        `);
    });
}

function toggleGateway(id, isActive) {
    $.ajax({
        url: '/admin/api/sms.php?endpoint=config',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ id: id, is_active: isActive }),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                loadConfig();
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}

function loadLogs() {
    const status = $('#log-status-filter').val();
    const event = $('#log-event-filter').val();
    
    $.get(`/admin/api/sms.php?endpoint=logs&status=${status}&event_type=${event}&page=${currentLogsPage}`, function(response) {
        if (response.success) {
            renderLogs(response.data);
        }
    });
}

function renderLogs(logs) {
    const tbody = $('#logs-tbody');
    tbody.empty();
    
    if (logs.length === 0) {
        tbody.append('<tr><td colspan="7" class="text-center">No logs found</td></tr>');
        return;
    }
    
    logs.forEach(log => {
        const name = (log.firstname || '') + ' ' + (log.lastname || '') || log.username || 'N/A';
        const date = new Date(log.created_at).toLocaleString();
        const statusClass = log.status == 'sent' ? 'success' : (log.status == 'failed' ? 'danger' : 'warning');
        
        tbody.append(`
            <tr>
                <td><small>${date}</small></td>
                <td>${name}</td>
                <td>${log.phone_number}</td>
                <td><small>${log.message.substring(0, 50)}...</small></td>
                <td><span class="label label-info">${log.event_type}</span></td>
                <td><span class="label label-${statusClass}">${log.status}</span></td>
                <td>${log.gateway || 'N/A'}</td>
            </tr>
        `);
    });
}

function sendTestSMS() {
    const phone = $('#test-phone').val();
    const message = $('#test-message').val();
    
    $.ajax({
        url: '/admin/api/sms.php?endpoint=test',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ phone, message }),
        success: function(response) {
            if (response.success) {
                alert('Test SMS sent successfully!');
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}
</script>

<?php include("../includes/admin-footer.php"); ?>
