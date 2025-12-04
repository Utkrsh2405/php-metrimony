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
.shortlist-header {
    background: linear-gradient(135deg, #ff5722 0%, #e64a19 100%);
    color: white;
    padding: 40px 0;
    text-align: center;
    margin-bottom: 30px;
}
.shortlist-header h1 {
    margin: 0;
    font-size: 36px;
}
.shortlist-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    transition: all 0.3s;
    position: relative;
}
.shortlist-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.shortlist-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff5722, #e64a19);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 32px;
    font-weight: bold;
    margin-right: 20px;
}
.shortlist-info {
    flex: 1;
}
.shortlist-actions {
    display: flex;
    gap: 10px;
    flex-direction: column;
}
.remove-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #f44336;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    transition: all 0.3s;
}
.remove-btn:hover {
    background: #d32f2f;
    transform: scale(1.1);
}
.notes-section {
    margin-top: 10px;
    padding: 10px;
    background: #f5f5f5;
    border-left: 3px solid #ff5722;
}
.notes-input {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px;
    resize: vertical;
    min-height: 60px;
}
.save-notes-btn {
    margin-top: 5px;
    background: #ff5722;
    color: white;
    border: none;
    padding: 5px 15px;
    border-radius: 4px;
    cursor: pointer;
}
.save-notes-btn:hover {
    background: #e64a19;
}
</style>

<div class="shortlist-header">
    <div class="container">
        <h1><i class="fa fa-star"></i> My Shortlist</h1>
        <p style="margin: 10px 0 0 0; font-size: 18px;">Your favorite profiles (<span id="shortlist-count">0</span>)</p>
    </div>
</div>

<div class="container">
    <div id="shortlist-list">
        <p class="text-center">Loading...</p>
    </div>
</div>

<script>
$(document).ready(function() {
    loadShortlist();
});

function loadShortlist() {
    $.get('/api/shortlist.php?action=list', function(response) {
        if (response.success) {
            renderShortlist(response.data);
            $('#shortlist-count').text(response.count);
        }
    });
}

function renderShortlist(profiles) {
    const container = $('#shortlist-list');
    container.empty();
    
    if (profiles.length === 0) {
        container.html(`
            <div class="text-center" style="padding: 40px;">
                <i class="fa fa-star-o" style="font-size: 64px; color: #ccc;"></i>
                <h3 style="margin-top: 20px; color: #666;">Your shortlist is empty</h3>
                <p style="color: #999;">Browse profiles and add your favorites here</p>
                <a href="/advanced-search.php" class="btn btn-primary" style="margin-top: 10px;">
                    <i class="fa fa-search"></i> Search Profiles
                </a>
            </div>
        `);
        return;
    }
    
    profiles.forEach(profile => {
        const initials = profile.name ? profile.name.charAt(0).toUpperCase() : '?';
        const verified = profile.verified == 1 ? '<i class="fa fa-check-circle" style="color: #4caf50;"></i> ' : '';
        const time = moment(profile.created_at).fromNow();
        
        container.append(`
            <div class="shortlist-card" id="shortlist-${profile.profile_id}">
                <button class="remove-btn" onclick="removeFromShortlist(${profile.profile_id})" title="Remove from shortlist">
                    <i class="fa fa-times"></i>
                </button>
                <div class="shortlist-avatar">${initials}</div>
                <div class="shortlist-info">
                    <h4 style="margin: 0 0 5px 0;">
                        ${verified}${profile.name}
                    </h4>
                    <p style="margin: 5px 0; color: #666;">
                        <i class="fa fa-user"></i> ${profile.age || 'N/A'} years | 
                        <i class="fa fa-map-marker"></i> ${profile.location || 'N/A'} | 
                        <i class="fa fa-briefcase"></i> ${profile.occupation || 'N/A'}
                    </p>
                    <p style="margin: 5px 0; color: #999; font-size: 12px;">
                        <i class="fa fa-clock-o"></i> Added ${time}
                    </p>
                    <div class="notes-section">
                        <label style="font-weight: bold; font-size: 12px; color: #666;">PRIVATE NOTES:</label>
                        <textarea class="notes-input" id="notes-${profile.profile_id}" placeholder="Add your private notes about this profile...">${profile.notes || ''}</textarea>
                        <button class="save-notes-btn" onclick="saveNotes(${profile.profile_id})">
                            <i class="fa fa-save"></i> Save Notes
                        </button>
                    </div>
                </div>
                <div class="shortlist-actions">
                    <a href="/profile.php?id=${profile.profile_id}" class="btn btn-info btn-sm">
                        <i class="fa fa-eye"></i> View Profile
                    </a>
                    <button class="btn btn-primary btn-sm" onclick="sendInterest(${profile.profile_id})">
                        <i class="fa fa-heart"></i> Send Interest
                    </button>
                    <a href="/messages.php?user=${profile.profile_id}" class="btn btn-success btn-sm">
                        <i class="fa fa-envelope"></i> Message
                    </a>
                </div>
            </div>
        `);
    });
}

function removeFromShortlist(profileId) {
    if (!confirm('Remove this profile from your shortlist?')) return;
    
    $.ajax({
        url: '/api/shortlist.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            profile_id: profileId
        }),
        success: function(response) {
            if (response.success) {
                $('#shortlist-' + profileId).fadeOut(300, function() {
                    $(this).remove();
                    const count = $('#shortlist-list .shortlist-card').length;
                    $('#shortlist-count').text(count);
                    if (count === 0) {
                        loadShortlist();
                    }
                });
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}

function saveNotes(profileId) {
    const notes = $('#notes-' + profileId).val();
    
    $.ajax({
        url: '/api/shortlist.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            profile_id: profileId,
            notes: notes,
            update_notes: true
        }),
        success: function(response) {
            if (response.success) {
                alert('Notes saved successfully!');
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}

function sendInterest(profileId) {
    $.ajax({
        url: '/api/interest.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            to_user_id: profileId
        }),
        success: function(response) {
            if (response.success) {
                alert('Interest sent successfully!');
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<?php include("includes/footer.php"); ?>
