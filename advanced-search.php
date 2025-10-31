<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("includes/dbconn.php");

$user_id = $_SESSION['id'];

// Get user's gender to search opposite gender by default
$user_query = mysqli_query($conn, "SELECT gender FROM customer WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($user_query);
$default_gender = ($user_data['gender'] == 'Male') ? 'Female' : 'Male';

// Get saved searches
$saved_searches = mysqli_query($conn, "SELECT * FROM saved_searches WHERE user_id = $user_id ORDER BY is_default DESC, search_name");

include("includes/header.php");
?>

<style>
.search-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
    margin: 30px 0;
}
.search-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 0;
    text-align: center;
    margin-bottom: 30px;
}
.search-header h1 {
    margin: 0;
    font-size: 36px;
}
.filter-section {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 20px;
}
.filter-section h4 {
    margin: 0 0 15px 0;
    color: #333;
    font-weight: 600;
}
.saved-search-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s;
}
.saved-search-card:hover {
    border-color: #667eea;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
}
.saved-search-card.default {
    border-color: #28a745;
    background: #f0fff4;
}
.result-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}
.result-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.result-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 36px;
    font-weight: bold;
    margin-right: 20px;
}
.result-info {
    flex: 1;
}
.badge-verified {
    background: #28a745;
    color: white;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    margin-left: 5px;
}
.badge-premium {
    background: #ffc107;
    color: #000;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    margin-left: 5px;
}
</style>

<div class="search-header">
    <div class="container">
        <h1><i class="fa fa-search"></i> Advanced Search</h1>
        <p style="margin: 10px 0 0 0; font-size: 18px;">Find your perfect match with detailed filters</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-md-3">
            <div class="search-container">
                <h3 style="margin-top: 0;">Saved Searches</h3>
                <div id="saved-searches-list">
                    <?php if (mysqli_num_rows($saved_searches) > 0): ?>
                        <?php while ($search = mysqli_fetch_assoc($saved_searches)): ?>
                            <div class="saved-search-card <?= $search['is_default'] ? 'default' : '' ?>" 
                                 onclick="loadSavedSearch(<?= $search['id'] ?>)"
                                 data-filters='<?= htmlspecialchars($search['search_filters']) ?>'>
                                <strong><?= htmlspecialchars($search['search_name']) ?></strong>
                                <?php if ($search['is_default']): ?>
                                    <span class="label label-success pull-right">Default</span>
                                <?php endif; ?>
                                <button class="btn btn-xs btn-danger pull-right" style="margin-right: 5px;" 
                                        onclick="event.stopPropagation(); deleteSavedSearch(<?= $search['id'] ?>)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">No saved searches yet</p>
                    <?php endif; ?>
                </div>
                <button class="btn btn-sm btn-success btn-block" onclick="saveCurrentSearch()" style="margin-top: 10px;">
                    <i class="fa fa-save"></i> Save Current Search
                </button>
            </div>
        </div>
        
        <!-- Main Search Area -->
        <div class="col-md-9">
            <div class="search-container">
                <form id="search-form">
                    <!-- Basic Filters -->
                    <div class="filter-section">
                        <h4><i class="fa fa-user"></i> Basic Information</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select name="gender" class="form-control">
                                        <option value="">Any</option>
                                        <option value="Male" <?= $default_gender == 'Male' ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= $default_gender == 'Female' ? 'selected' : '' ?>>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Age From</label>
                                    <input type="number" name="age_min" class="form-control" min="18" max="100" placeholder="18">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Age To</label>
                                    <input type="number" name="age_max" class="form-control" min="18" max="100" placeholder="60">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Religion</label>
                                    <select name="religion" class="form-control">
                                        <option value="">Any</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Muslim">Muslim</option>
                                        <option value="Christian">Christian</option>
                                        <option value="Sikh">Sikh</option>
                                        <option value="Jain">Jain</option>
                                        <option value="Buddhist">Buddhist</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Physical Attributes -->
                    <div class="filter-section">
                        <h4><i class="fa fa-male"></i> Physical Attributes</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Height From (cm)</label>
                                    <input type="number" name="height_min" class="form-control" min="100" max="250" placeholder="150">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Height To (cm)</label>
                                    <input type="number" name="height_max" class="form-control" min="100" max="250" placeholder="200">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Education & Career -->
                    <div class="filter-section">
                        <h4><i class="fa fa-graduation-cap"></i> Education & Career</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Occupation</label>
                                    <input type="text" name="occupation" class="form-control" placeholder="e.g. Engineer, Doctor">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Annual Income (Min)</label>
                                    <select name="income_min" class="form-control">
                                        <option value="">Any</option>
                                        <option value="0">0 - 2 Lakhs</option>
                                        <option value="200000">2 - 5 Lakhs</option>
                                        <option value="500000">5 - 10 Lakhs</option>
                                        <option value="1000000">10+ Lakhs</option>
                                        <option value="2000000">20+ Lakhs</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Location/City</label>
                                    <input type="text" name="location" class="form-control" placeholder="City or State">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Filters -->
                    <div class="filter-section">
                        <h4><i class="fa fa-filter"></i> Additional Filters</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="verified_only" value="1">
                                        <strong>Show verified profiles only</strong>
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="with_photo" value="1">
                                        <strong>Show profiles with photo only</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search Buttons -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg" style="padding: 12px 40px;">
                            <i class="fa fa-search"></i> Search Profiles
                        </button>
                        <button type="button" class="btn btn-default btn-lg" onclick="resetFilters()" style="padding: 12px 40px;">
                            <i class="fa fa-refresh"></i> Reset Filters
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Search Results -->
            <div id="search-results" style="margin-top: 30px;">
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

$('#search-form').on('submit', function(e) {
    e.preventDefault();
    performSearch();
});

function performSearch() {
    const formData = {};
    $('#search-form').serializeArray().forEach(item => {
        if (item.value) {
            formData[item.name] = item.value;
        }
    });
    
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
        }
    });
}

function displayResults(results, count) {
    const container = $('#search-results');
    container.empty();
    
    if (count === 0) {
        container.html(`
            <div class="search-container text-center">
                <i class="fa fa-search fa-3x" style="color: #ccc; margin-bottom: 20px;"></i>
                <h3>No profiles found</h3>
                <p class="text-muted">Try adjusting your search filters</p>
            </div>
        `);
        return;
    }
    
    container.append(`
        <div class="search-container">
            <h3>${count} Profile${count > 1 ? 's' : ''} Found</h3>
            <hr>
        </div>
    `);
    
    results.forEach(profile => {
        const age = profile.age || 'N/A';
        const height = profile.height ? profile.height + ' cm' : 'N/A';
        const verified = profile.verified == 1 ? '<span class="badge-verified"><i class="fa fa-check-circle"></i> Verified</span>' : '';
        const premium = profile.plan_id > 1 ? '<span class="badge-premium"><i class="fa fa-star"></i> Premium</span>' : '';
        const initials = profile.name ? profile.name.charAt(0).toUpperCase() : '?';
        
        container.append(`
            <div class="result-card">
                <div class="result-avatar">${initials}</div>
                <div class="result-info">
                    <h4 style="margin: 0 0 10px 0;">
                        ${profile.name || 'User #' + profile.id}
                        ${verified}
                        ${premium}
                    </h4>
                    <p style="margin: 5px 0; color: #666;">
                        <i class="fa fa-user"></i> ${age} years, ${profile.gender || 'N/A'} | 
                        <i class="fa fa-arrows-v"></i> ${height} | 
                        <i class="fa fa-map-marker"></i> ${profile.location || 'N/A'}
                    </p>
                    <p style="margin: 5px 0; color: #666;">
                        <i class="fa fa-graduation-cap"></i> ${profile.education || 'N/A'} | 
                        <i class="fa fa-briefcase"></i> ${profile.occupation || 'N/A'}
                    </p>
                    <p style="margin: 5px 0; color: #666;">
                        <i class="fa fa-book"></i> ${profile.religion || 'N/A'} | 
                        <i class="fa fa-heart"></i> ${profile.marital_status || 'N/A'}
                    </p>
                </div>
                <div>
                    <a href="/profile.php?id=${profile.id}" class="btn btn-primary">
                        <i class="fa fa-eye"></i> View Profile
                    </a>
                    <button class="btn btn-success" onclick="sendInterest(${profile.id})">
                        <i class="fa fa-heart"></i> Interest
                    </button>
                    <button class="btn btn-info" onclick="addToShortlist(${profile.id})">
                        <i class="fa fa-star"></i> Shortlist
                    </button>
                </div>
            </div>
        `);
    });
}

function resetFilters() {
    $('#search-form')[0].reset();
    performSearch();
}

function loadSavedSearch(id) {
    const card = $(`[onclick="loadSavedSearch(${id})"]`);
    const filters = JSON.parse(card.attr('data-filters'));
    
    // Populate form with saved filters
    Object.keys(filters).forEach(key => {
        const input = $(`[name="${key}"]`);
        if (input.attr('type') === 'checkbox') {
            input.prop('checked', filters[key] == 1);
        } else {
            input.val(filters[key]);
        }
    });
    
    performSearch();
}

function saveCurrentSearch() {
    const name = prompt('Enter a name for this search:');
    if (!name) return;
    
    const formData = {};
    $('#search-form').serializeArray().forEach(item => {
        if (item.value) {
            formData[item.name] = item.value;
        }
    });
    
    $.ajax({
        url: '/api/saved-searches.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            name: name,
            filters: formData
        }),
        success: function(response) {
            if (response.success) {
                alert('Search saved successfully!');
                location.reload();
            } else {
                alert('Failed to save search: ' + response.error);
            }
        }
    });
}

function deleteSavedSearch(id) {
    if (!confirm('Delete this saved search?')) return;
    
    $.ajax({
        url: `/api/saved-searches.php?id=${id}`,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Failed to delete: ' + response.error);
            }
        }
    });
}

function sendInterest(userId) {
    if (!confirm('Send interest to this profile?')) return;
    const btn = event ? event.currentTarget : null;
    $.ajax({
        url: '/api/interest.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ receiver_id: userId, message: '' }),
        success: function(res) {
            if (res.success) {
                alert(res.message || 'Interest sent');
                // Optionally update UI
            } else {
                alert('Failed: ' + (res.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Failed to contact server.');
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
                alert(res.message || (res.action === 'added' ? 'Added to shortlist' : 'Removed from shortlist'));
            } else {
                alert('Failed: ' + (res.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Failed to contact server.');
        }
    });
}
</script>

<?php include("includes/footer.php"); ?>
