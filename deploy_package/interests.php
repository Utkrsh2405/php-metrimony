<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("includes/dbconn.php");

$user_id = $_SESSION['id'];

include("includes/header.php");
?>

<style>
.interests-header {
    background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
    color: white;
    padding: 40px 0;
    text-align: center;
    margin-bottom: 30px;
}
.interests-header h1 {
    margin: 0;
    font-size: 36px;
}
.interest-tabs {
    border-bottom: 2px solid #e91e63;
    margin-bottom: 30px;
}
.interest-tabs .tab {
    display: inline-block;
    padding: 15px 30px;
    cursor: pointer;
    background: #f5f5f5;
    margin-right: 5px;
    border-radius: 5px 5px 0 0;
    transition: all 0.3s;
}
.interest-tabs .tab.active {
    background: #e91e63;
    color: white;
}
.interest-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}
.interest-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.interest-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e91e63, #c2185b);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 32px;
    font-weight: bold;
    margin-right: 20px;
}
.interest-info {
    flex: 1;
}
.interest-actions {
    display: flex;
    gap: 10px;
    flex-direction: column;
}
.status-badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}
.status-pending {
    background: #ffc107;
    color: #000;
}
.status-accepted {
    background: #4caf50;
    color: white;
}
.status-declined {
    background: #f44336;
    color: white;
}
.status-cancelled {
    background: #9e9e9e;
    color: white;
}
.interest-message {
    background: #f5f5f5;
    padding: 10px;
    border-left: 3px solid #e91e63;
    margin-top: 10px;
    font-style: italic;
}
</style>

<div class="interests-header">
    <div class="container">
        <h1><i class="fa fa-heart"></i> My Interests</h1>
        <p style="margin: 10px 0 0 0; font-size: 18px;">Manage your sent and received interests</p>
    </div>
</div>

<div class="container">
    <div class="interest-tabs">
        <div class="tab active" onclick="switchTab('received')">
            <i class="fa fa-inbox"></i> Received (<span id="received-count">0</span>)
        </div>
        <div class="tab" onclick="switchTab('sent')">
            <i class="fa fa-send"></i> Sent (<span id="sent-count">0</span>)
        </div>
    </div>
    
    <div id="received-tab" class="tab-content">
        <h3>Interests You Received</h3>
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <select id="received-filter" class="form-control" style="max-width: 200px;" onchange="loadInterests('received')">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="declined">Declined</option>
                </select>
            </div>
        </div>
        <div id="received-list">
            <p class="text-center">Loading...</p>
        </div>
    </div>
    
    <div id="sent-tab" class="tab-content" style="display: none;">
        <h3>Interests You Sent</h3>
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <select id="sent-filter" class="form-control" style="max-width: 200px;" onchange="loadInterests('sent')">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="declined">Declined</option>
                </select>
            </div>
        </div>
        <div id="sent-list">
            <p class="text-center">Loading...</p>
        </div>
    </div>
</div>

<script>
let currentTab = 'received';

$(document).ready(function() {
    loadInterests('received');
    loadInterests('sent');
});

function switchTab(tab) {
    currentTab = tab;
    $('.interest-tabs .tab').removeClass('active');
    $('.interest-tabs .tab').eq(tab === 'received' ? 0 : 1).addClass('active');
    
    if (tab === 'received') {
        $('#received-tab').show();
        $('#sent-tab').hide();
    } else {
        $('#received-tab').hide();
        $('#sent-tab').show();
    }
}

function loadInterests(type) {
    const filter = $('#' + type + '-filter').val();
    const listId = '#' + type + '-list';
    
    $.get('/api/interest-response.php?type=' + type + '&status=' + filter, function(response) {
        if (response.success) {
            renderInterests(response.data, listId, type);
            $('#' + type + '-count').text(response.count);
        }
    });
}

function renderInterests(interests, listId, type) {
    const container = $(listId);
    container.empty();
    
    if (interests.length === 0) {
        container.html('<p class="text-center text-muted">No interests found</p>');
        return;
    }
    
    interests.forEach(interest => {
        const initials = interest.name ? interest.name.charAt(0).toUpperCase() : '?';
        const statusClass = 'status-' + interest.status;
        const verified = interest.verified == 1 ? '<i class="fa fa-check-circle" style="color: #4caf50;"></i> ' : '';
        const time = moment(interest.created_at).fromNow();
        
        let actions = '';
        if (type === 'received' && interest.status === 'pending') {
            actions = `
                <button class="btn btn-success btn-sm" onclick="respondToInterest(${interest.id}, 'accept')">
                    <i class="fa fa-check"></i> Accept
                </button>
                <button class="btn btn-danger btn-sm" onclick="respondToInterest(${interest.id}, 'decline')">
                    <i class="fa fa-times"></i> Decline
                </button>
            `;
        } else if (type === 'sent' && interest.status === 'accepted') {
            actions = `
                <a href="/messages.php?user=${interest.user_id}" class="btn btn-primary btn-sm">
                    <i class="fa fa-envelope"></i> Message
                </a>
            `;
        }
        
        const messageBox = interest.message ? `<div class="interest-message">"${interest.message}"</div>` : '';
        
        container.append(`
            <div class="interest-card">
                <div class="interest-avatar">${initials}</div>
                <div class="interest-info">
                    <h4 style="margin: 0 0 5px 0;">
                        ${verified}${interest.name}
                        <span class="status-badge ${statusClass}">${interest.status.toUpperCase()}</span>
                    </h4>
                    <p style="margin: 5px 0; color: #666;">
                        <i class="fa fa-user"></i> ${interest.age || 'N/A'} years | 
                        <i class="fa fa-map-marker"></i> ${interest.location || 'N/A'} | 
                        <i class="fa fa-briefcase"></i> ${interest.occupation || 'N/A'}
                    </p>
                    <p style="margin: 5px 0; color: #999; font-size: 12px;">
                        <i class="fa fa-clock-o"></i> ${time}
                    </p>
                    ${messageBox}
                </div>
                <div class="interest-actions">
                    <a href="/profile.php?id=${interest.user_id}" class="btn btn-info btn-sm">
                        <i class="fa fa-eye"></i> View Profile
                    </a>
                    ${actions}
                </div>
            </div>
        `);
    });
}

function respondToInterest(interestId, action) {
    if (!confirm('Are you sure you want to ' + action + ' this interest?')) return;
    
    $.ajax({
        url: '/api/interest-response.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            interest_id: interestId,
            action: action
        }),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                loadInterests('received');
                loadInterests('sent');
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<?php include("includes/footer.php"); ?>
