<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("includes/dbconn.php");

$user_id = $_SESSION['id'];

// Get user's gender to search opposite gender by default
$user_query = mysqli_query($conn, "SELECT c.gender FROM customer c WHERE c.id = $user_id");
$user_data = mysqli_fetch_assoc($user_query);
$default_gender = ($user_data['gender'] == 'Male') ? 'Female' : 'Male';

// Get all states for dropdown
$states_query = mysqli_query($conn, "SELECT id, state_name, state_code FROM states WHERE status = 1 ORDER BY state_name");
$states = [];
while ($state = mysqli_fetch_assoc($states_query)) {
    $states[] = $state;
}

// Get religions from castes table
$religions_query = mysqli_query($conn, "SELECT DISTINCT religion FROM castes WHERE status = 1 AND religion IS NOT NULL AND religion != 'All Religions' ORDER BY religion");
$religions = [];
while ($rel = mysqli_fetch_assoc($religions_query)) {
    $religions[] = $rel['religion'];
}

include("includes/header.php");
?>

<style>
.quick-search {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 50px 0;
    margin-bottom: 30px;
}
.search-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    padding: 30px;
    margin-bottom: 30px;
}
.filter-group {
    margin-bottom: 20px;
}
.filter-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
}
.btn-search {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 12px 40px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 25px;
    transition: all 0.3s;
}
.btn-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    color: white;
}
.profile-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 20px;
}
.profile-card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transform: translateY(-3px);
}
.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 32px;
    font-weight: bold;
    flex-shrink: 0;
}
.profile-info {
    flex: 1;
}
.profile-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.badge-verified {
    background: #28a745;
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    margin-left: 8px;
}
.no-results {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}
.no-results i {
    font-size: 64px;
    margin-bottom: 20px;
    color: #ddd;
}
</style>

<div class="quick-search">
    <div class="container">
        <h1 style="margin: 0 0 10px 0;"><i class="fa fa-search"></i> Quick Search</h1>
        <p style="margin: 0; font-size: 18px; opacity: 0.9;">Find your perfect match with simple filters</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="search-card">
                <form id="quick-search-form">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="filter-group">
                                <label>Gender</label>
                                <select name="gender" class="form-control">
                                    <option value="">Any</option>
                                    <option value="Male" <?= $default_gender == 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= $default_gender == 'Female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="filter-group">
                                <label>Age From - To</label>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <input type="number" name="age_min" class="form-control" placeholder="21" min="18" max="100">
                                    </div>
                                    <div class="col-xs-6">
                                        <input type="number" name="age_max" class="form-control" placeholder="35" min="18" max="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="filter-group">
                                <label>Religion</label>
                                <select name="religion" class="form-control">
                                    <option value="">Any</option>
                                    <?php foreach ($religions as $religion): ?>
                                    <option value="<?= htmlspecialchars($religion) ?>"><?= htmlspecialchars($religion) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="filter-group">
                                <label>State</label>
                                <select name="state" class="form-control">
                                    <option value="">Any State</option>
                                    <?php foreach ($states as $state): ?>
                                    <option value="<?= $state['state_code'] ?>"><?= htmlspecialchars($state['state_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="filter-group">
                                <label>Marital Status</label>
                                <select name="marital_status" class="form-control">
                                    <option value="">Any</option>
                                    <option value="Never Married">Never Married</option>
                                    <option value="Divorced">Divorced</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Awaiting Divorce">Awaiting Divorce</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="filter-group">
                                <label>Education</label>
                                <select name="education" class="form-control">
                                    <option value="">Any</option>
                                    <option value="High School">High School</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="Graduate">Graduate</option>
                                    <option value="Post Graduate">Post Graduate</option>
                                    <option value="Doctorate">Doctorate</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="filter-group">
                                <label>Mother Tongue</label>
                                <select name="mother_tongue" class="form-control">
                                    <option value="">Any</option>
                                    <option value="Hindi">Hindi</option>
                                    <option value="English">English</option>
                                    <option value="Tamil">Tamil</option>
                                    <option value="Telugu">Telugu</option>
                                    <option value="Malayalam">Malayalam</option>
                                    <option value="Kannada">Kannada</option>
                                    <option value="Marathi">Marathi</option>
                                    <option value="Bengali">Bengali</option>
                                    <option value="Gujarati">Gujarati</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="filter-group">
                                <label>&nbsp;</label>
                                <div class="checkbox" style="margin-top: 0;">
                                    <label>
                                        <input type="checkbox" name="with_photo" value="1"> With Photo Only
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center" style="margin-top: 20px;">
                        <button type="submit" class="btn btn-search">
                            <i class="fa fa-search"></i> Search Profiles
                        </button>
                        <button type="button" class="btn btn-default" onclick="resetSearch()" style="margin-left: 10px;">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                        <a href="advanced-search.php" class="btn btn-info" style="margin-left: 10px;">
                            <i class="fa fa-search-plus"></i> Advanced Search
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div id="search-results">
                <!-- Results will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Perform initial search with default filters
    performSearch();
});

$('#quick-search-form').on('submit', function(e) {
    e.preventDefault();
    performSearch();
});

function performSearch() {
    const formData = {};
    $('#quick-search-form').serializeArray().forEach(item => {
        if (item.value) {
            formData[item.name] = item.value;
        }
    });
    
    $('#search-results').html('<div class="text-center" style="padding: 40px;"><i class="fa fa-spinner fa-spin fa-3x"></i><p style="margin-top: 15px;">Searching...</p></div>');
    
    $.ajax({
        url: '/api/search.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                displayResults(response.data, response.count);
            } else {
                alert('Search failed: ' + response.error);
            }
        },
        error: function() {
            $('#search-results').html('<div class="alert alert-danger">Failed to perform search. Please try again.</div>');
        }
    });
}

function displayResults(results, count) {
    const container = $('#search-results');
    container.empty();
    
    if (count === 0) {
        container.html(`
            <div class="no-results">
                <i class="fa fa-search"></i>
                <h3>No profiles found</h3>
                <p>Try adjusting your search filters or use <a href="advanced-search.php">Advanced Search</a></p>
            </div>
        `);
        return;
    }
    
    container.append(`
        <div class="search-card">
            <h3 style="margin: 0 0 20px 0;"><i class="fa fa-users"></i> Found ${count} Profile${count > 1 ? 's' : ''}</h3>
        </div>
    `);
    
    results.forEach(profile => {
        const age = profile.age || 'N/A';
        const verified = profile.verified == 1 ? '<span class="badge-verified"><i class="fa fa-check-circle"></i> Verified</span>' : '';
        const initials = profile.name ? profile.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : '?';
        
        container.append(`
            <div class="profile-card">
                <div class="profile-avatar">${initials}</div>
                <div class="profile-info">
                    <h4 style="margin: 0 0 10px 0;">
                        ${profile.name || 'Profile #' + profile.id}
                        ${verified}
                    </h4>
                    <p style="margin: 5px 0; color: #666;">
                        <i class="fa fa-birthday-cake"></i> ${age} years | 
                        <i class="fa fa-venus-mars"></i> ${profile.gender || 'N/A'} | 
                        <i class="fa fa-heart"></i> ${profile.marital_status || 'N/A'}
                    </p>
                    <p style="margin: 5px 0; color: #666;">
                        <i class="fa fa-book"></i> ${profile.religion || 'N/A'} | 
                        <i class="fa fa-map-marker"></i> ${profile.location || 'N/A'}
                    </p>
                    <p style="margin: 5px 0; color: #666;">
                        <i class="fa fa-graduation-cap"></i> ${profile.education || 'N/A'} | 
                        <i class="fa fa-briefcase"></i> ${profile.occupation || 'N/A'}
                    </p>
                </div>
                <div class="profile-actions">
                    <a href="/view_profile.php?id=${profile.id}" class="btn btn-primary btn-sm">
                        <i class="fa fa-eye"></i> View Profile
                    </a>
                    <button class="btn btn-success btn-sm" onclick="sendInterest(${profile.id})">
                        <i class="fa fa-heart"></i> Send Interest
                    </button>
                    <button class="btn btn-info btn-sm" onclick="addToShortlist(${profile.id})">
                        <i class="fa fa-star"></i> Shortlist
                    </button>
                </div>
            </div>
        `);
    });
}

function resetSearch() {
    $('#quick-search-form')[0].reset();
    performSearch();
}

function sendInterest(userId) {
    if (!confirm('Send interest to this profile?')) return;
    
    $.ajax({
        url: '/api/interest.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ receiver_id: userId, message: '' }),
        success: function(res) {
            if (res.success) {
                alert(res.message || 'Interest sent successfully!');
            } else {
                alert('Failed: ' + (res.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Failed to send interest. Please try again.');
        }
    });
}

function addToShortlist(userId) {
    $.ajax({
        url: '/api/shortlist.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ profile_id: userId }),
        success: function(res) {
            if (res.success) {
                alert(res.message || (res.action === 'added' ? 'Added to shortlist!' : 'Removed from shortlist'));
            } else {
                alert('Failed: ' + (res.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Failed to update shortlist. Please try again.');
        }
    });
}
</script>

<?php include("includes/footer.php"); ?>