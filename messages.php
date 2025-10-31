<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("includes/dbconn.php");

$user_id = $_SESSION['id'];
$conversation_with = isset($_GET['user']) ? intval($_GET['user']) : 0;

include("includes/header.php");
?>

<style>
.messages-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 0;
    text-align: center;
    margin-bottom: 0;
}
.messages-container {
    display: flex;
    height: calc(100vh - 200px);
    background: white;
    border: 1px solid #ddd;
}
.messages-sidebar {
    width: 350px;
    border-right: 1px solid #ddd;
    display: flex;
    flex-direction: column;
}
.messages-tabs {
    display: flex;
    border-bottom: 2px solid #f0f0f0;
}
.messages-tab {
    flex: 1;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    background: #f9f9f9;
    border: none;
    transition: all 0.3s;
}
.messages-tab.active {
    background: white;
    border-bottom: 3px solid #667eea;
    color: #667eea;
    font-weight: bold;
}
.messages-list {
    flex: 1;
    overflow-y: auto;
}
.message-item {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background 0.2s;
}
.message-item:hover {
    background: #f9f9f9;
}
.message-item.active {
    background: #e3f2fd;
}
.message-item.unread {
    background: #fff3e0;
    font-weight: bold;
}
.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 10px;
}
.conversation-area {
    flex: 1;
    display: flex;
    flex-direction: column;
}
.conversation-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    background: #f9f9f9;
}
.conversation-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f5f5f5;
}
.message-bubble {
    max-width: 70%;
    margin-bottom: 15px;
    padding: 12px 16px;
    border-radius: 18px;
    position: relative;
}
.message-bubble.sent {
    background: #667eea;
    color: white;
    margin-left: auto;
    text-align: right;
}
.message-bubble.received {
    background: white;
    border: 1px solid #ddd;
}
.message-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 5px;
}
.compose-area {
    padding: 20px;
    border-top: 1px solid #ddd;
    background: white;
}
.compose-form {
    display: flex;
    gap: 10px;
}
.compose-input {
    flex: 1;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 25px;
    resize: none;
}
.send-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}
.send-btn:hover {
    background: #764ba2;
    transform: scale(1.1);
}
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #999;
}
.unread-badge {
    background: #f44336;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    margin-left: 5px;
}
</style>

<div class="messages-header">
    <div class="container">
        <h1><i class="fa fa-envelope"></i> Messages</h1>
        <p style="margin: 10px 0 0 0;">Communicate with your matches</p>
    </div>
</div>

<div class="container-fluid" style="padding: 0;">
    <div class="messages-container">
        <!-- Sidebar -->
        <div class="messages-sidebar">
            <div class="messages-tabs">
                <button class="messages-tab active" onclick="switchTab('inbox')">
                    Inbox <span class="unread-badge" id="unread-count" style="display: none;">0</span>
                </button>
                <button class="messages-tab" onclick="switchTab('sent')">
                    Sent
                </button>
            </div>
            <div class="messages-list" id="messages-list">
                <p style="text-align: center; padding: 20px; color: #999;">Loading...</p>
            </div>
        </div>
        
        <!-- Conversation Area -->
        <div class="conversation-area" id="conversation-area">
            <div class="empty-state">
                <i class="fa fa-comments-o" style="font-size: 64px; margin-bottom: 20px;"></i>
                <h3>Select a conversation</h3>
                <p>Choose a message from the left to start chatting</p>
            </div>
        </div>
    </div>
</div>

<script>
let currentTab = 'inbox';
let currentConversation = <?php echo $conversation_with; ?>;
let unreadCount = 0;

$(document).ready(function() {
    loadMessages('inbox');
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        if (currentConversation > 0) {
            loadConversation(currentConversation, true);
        }
        loadMessages(currentTab, true);
    }, 30000);
    
    // If user parameter is set, open conversation
    if (currentConversation > 0) {
        loadConversation(currentConversation);
    }
});

function switchTab(tab) {
    currentTab = tab;
    $('.messages-tab').removeClass('active');
    $('.messages-tab:contains("' + (tab === 'inbox' ? 'Inbox' : 'Sent') + '")').addClass('active');
    loadMessages(tab);
}

function loadMessages(type, silent = false) {
    $.get('/api/messages.php?action=' + type, function(response) {
        if (response.success) {
            if (type === 'inbox') {
                unreadCount = response.unread;
                if (unreadCount > 0) {
                    $('#unread-count').text(unreadCount).show();
                } else {
                    $('#unread-count').hide();
                }
            }
            
            if (!silent) {
                renderMessagesList(response.data, type);
            }
        }
    });
}

function renderMessagesList(messages, type) {
    const container = $('#messages-list');
    container.empty();
    
    if (messages.length === 0) {
        container.html('<p style="text-align: center; padding: 20px; color: #999;">No messages</p>');
        return;
    }
    
    messages.forEach(msg => {
        const isUnread = type === 'inbox' && msg.is_read == 0;
        const userName = type === 'inbox' ? msg.sender_name : msg.receiver_name;
        const verified = (type === 'inbox' ? msg.sender_verified : msg.receiver_verified) == 1 
            ? '<i class="fa fa-check-circle" style="color: #4caf50; font-size: 12px;"></i> ' : '';
        const userId = type === 'inbox' ? msg.from_user_id : msg.to_user_id;
        const initials = userName ? userName.charAt(0).toUpperCase() : '?';
        const time = moment(msg.created_at).fromNow();
        const preview = msg.message.length > 50 ? msg.message.substring(0, 50) + '...' : msg.message;
        
        container.append(`
            <div class="message-item ${isUnread ? 'unread' : ''} ${currentConversation == userId ? 'active' : ''}" 
                onclick="loadConversation(${userId})">
                <div style="display: flex; align-items: center;">
                    <div class="message-avatar">${initials}</div>
                    <div style="flex: 1;">
                        <div style="font-weight: ${isUnread ? 'bold' : 'normal'};">
                            ${verified}${userName}
                        </div>
                        <div style="font-size: 13px; color: #666; margin-top: 3px;">
                            ${preview}
                        </div>
                        <div style="font-size: 11px; color: #999; margin-top: 3px;">
                            ${time}
                        </div>
                    </div>
                </div>
            </div>
        `);
    });
}

function loadConversation(userId, silent = false) {
    currentConversation = userId;
    $('.message-item').removeClass('active');
    $('.message-item').each(function() {
        if ($(this).attr('onclick').includes(userId)) {
            $(this).addClass('active');
        }
    });
    
    $.get('/api/messages.php?action=conversation&user_id=' + userId, function(response) {
        if (response.success) {
            if (!silent) {
                renderConversation(response.data, response.user_info);
            } else {
                // Just append new messages
                updateConversation(response.data);
            }
        }
    });
}

function renderConversation(messages, userInfo) {
    const initials = userInfo.name ? userInfo.name.charAt(0).toUpperCase() : '?';
    const verified = userInfo.verified == 1 
        ? '<i class="fa fa-check-circle" style="color: #4caf50;"></i> ' : '';
    
    const conversationHtml = `
        <div class="conversation-header">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center;">
                    <div class="message-avatar" style="width: 50px; height: 50px; font-size: 20px;">${initials}</div>
                    <div style="margin-left: 10px;">
                        <h4 style="margin: 0;">${verified}${userInfo.name}</h4>
                        <p style="margin: 5px 0 0 0; color: #666; font-size: 13px;">
                            ${userInfo.age || 'N/A'} years | ${userInfo.location || 'N/A'}
                        </p>
                    </div>
                </div>
                <div>
                    <a href="/profile.php?id=${userInfo.id}" class="btn btn-sm btn-info">
                        <i class="fa fa-user"></i> View Profile
                    </a>
                </div>
            </div>
        </div>
        <div class="conversation-messages" id="conversation-messages">
        </div>
        <div class="compose-area">
            <form class="compose-form" onsubmit="sendMessage(event, ${userInfo.id})">
                <textarea class="compose-input" id="message-input" placeholder="Type your message..." rows="1" required></textarea>
                <button type="submit" class="send-btn">
                    <i class="fa fa-send"></i>
                </button>
            </form>
        </div>
    `;
    
    $('#conversation-area').html(conversationHtml);
    updateConversation(messages);
}

function updateConversation(messages) {
    const container = $('#conversation-messages');
    const userId = <?php echo $_SESSION['id']; ?>;
    
    container.empty();
    
    if (messages.length === 0) {
        container.html('<p style="text-align: center; color: #999;">No messages yet. Start the conversation!</p>');
        return;
    }
    
    messages.forEach(msg => {
        const isSent = msg.from_user_id == userId;
        const time = moment(msg.created_at).format('MMM D, h:mm A');
        
        container.append(`
            <div class="message-bubble ${isSent ? 'sent' : 'received'}">
                ${msg.subject ? '<div style="font-weight: bold; margin-bottom: 5px;">' + msg.subject + '</div>' : ''}
                <div>${msg.message}</div>
                <div class="message-time">${time}</div>
            </div>
        `);
    });
    
    // Scroll to bottom
    container.scrollTop(container[0].scrollHeight);
}

function sendMessage(event, toUserId) {
    event.preventDefault();
    
    const message = $('#message-input').val().trim();
    if (!message) return;
    
    $.ajax({
        url: '/api/messages.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            to_user_id: toUserId,
            message: message
        }),
        success: function(response) {
            if (response.success) {
                $('#message-input').val('');
                loadConversation(toUserId, false);
                loadMessages('sent', true);
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}

// Auto-expand textarea
$(document).on('input', '.compose-input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<?php include("includes/footer.php"); ?>
