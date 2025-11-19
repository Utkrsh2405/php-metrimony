<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("includes/dbconn.php");
require_once("includes/basic_includes.php");
require_once("functions.php");

$user_id = $_SESSION['id'];

// Get user's sex to search opposite sex by default
$user_query = mysqli_query($conn, "SELECT sex FROM customer WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($user_query);
$default_gender = ($user_data['sex'] == 'male') ? 'Female' : 'Male';

// Get religions from database
$religions_query = mysqli_query($conn, "SELECT DISTINCT religion FROM castes WHERE status = 1 AND religion IS NOT NULL AND religion != '' ORDER BY religion");
$religions = [];
while ($rel = mysqli_fetch_assoc($religions_query)) {
    $religions[] = $rel['religion'];
}

// Get mother tongues from customer table
$languages_query = mysqli_query($conn, "SELECT DISTINCT mothertounge FROM customer WHERE mothertounge IS NOT NULL AND mothertounge != '' ORDER BY mothertounge");
$languages = [];
while ($lang = mysqli_fetch_assoc($languages_query)) {
    $languages[] = $lang['mothertounge'];
}
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Quick Search | Shaadi Partner</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<link href="css/style.css" rel='stylesheet' type='text/css' />
<link href="css/font-awesome.css" rel="stylesheet">
<style>
.search-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
    text-align: center;
    margin-bottom: 40px;
}
.search-header h1 {
    margin: 0;
    font-size: 42px;
    font-weight: 700;
}
.search-tabs {
    display: flex;
    margin-bottom: 0;
    border-bottom: none;
}
.search-tab {
    flex: 1;
    background: #8B4C4F;
    color: white;
    padding: 15px 20px;
    text-align: center;
    text-decoration: none;
    border-right: 1px solid rgba(255,255,255,0.2);
    font-weight: 600;
    transition: all 0.3s;
}
.search-tab:hover {
    background: #7A3E41;
    color: white;
    text-decoration: none;
}
.search-tab.active {
    background: #6B3234;
    color: white;
}
.search-form {
    background: white;
    padding: 40px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 40px;
}
.form-group label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}
.btn-search {
    background: #8B4C4F;
    color: white;
    padding: 12px 40px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 4px;
    margin-top: 20px;
}
.btn-search:hover {
    background: #7A3E41;
    color: white;
}
.age-range {
    display: flex;
    align-items: center;
    gap: 10px;
}
.age-range .form-control {
    flex: 1;
}
</style>
</head>
<body>
<?php include_once("includes/navigation.php");?>

<div class="search-header">
    <div class="container">
        <h1>Search</h1>
    </div>
</div>

<div class="container">
    <div class="search-tabs">
        <a href="quick-search.php" class="search-tab active">Quick Search</a>
        <a href="advance-search.php" class="search-tab">Advance Search</a>
        <a href="keyword-search.php" class="search-tab">Keyword Search</a>
        <a href="search-id.php" class="search-tab">Search by Profile Id</a>
    </div>
    
    <div class="search-form">
        <form id="quick-search-form">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Looking for</label>
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="gender" value="Male" <?= $default_gender == 'Male' ? 'checked' : '' ?>> Groom
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="gender" value="Female" <?= $default_gender == 'Female' ? 'checked' : '' ?>> Bride
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Age between</label>
                        <div class="age-range">
                            <select name="age_min" class="form-control">
                                <?php for($i=18; $i<=60; $i++): ?>
                                <option value="<?= $i ?>" <?= $i==20 ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <span style="font-weight: 600;">to</span>
                            <select name="age_max" class="form-control">
                                <?php for($i=18; $i<=60; $i++): ?>
                                <option value="<?= $i ?>" <?= $i==32 ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Religion</label>
                        <select name="religion" class="form-control">
                            <option value="">Any Religion</option>
                            <?php foreach($religions as $religion): ?>
                            <option value="<?= htmlspecialchars($religion) ?>"><?= htmlspecialchars($religion) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Mother Tongue</label>
                        <select name="mother_tongue" class="form-control">
                            <option value="">Any Mother Tongue</option>
                            <?php foreach($languages as $lang): ?>
                            <option value="<?= htmlspecialchars($lang) ?>"><?= htmlspecialchars($lang) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Profile with</label>
                        <div>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="with_photo" value="1" checked> Photo
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="with_horoscope" value="1"> Horoscope
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-search">Search</button>
            </div>
        </form>
    </div>
    
    <div id="search-results"></div>
</div>

<script>
$('#quick-search-form').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {};
    $('#quick-search-form').serializeArray().forEach(item => {
        if (item.value) {
            formData[item.name] = item.value;
        }
    });
    
    $('#search-results').html('<div class="text-center" style="padding: 60px;"><i class="fa fa-spinner fa-spin fa-3x text-muted"></i><p style="margin-top: 20px; font-size: 18px;">Searching profiles...</p></div>');
    
    $.ajax({
        url: '/api/search.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                displayResults(response.data, response.count);
            } else {
                $('#search-results').html('<div class="alert alert-warning">No profiles found. Try adjusting your search criteria.</div>');
            }
        },
        error: function() {
            $('#search-results').html('<div class="alert alert-danger">Search failed. Please try again.</div>');
        }
    });
});

function displayResults(results, count) {
    const container = $('#search-results');
    container.empty();
    
    if (count === 0) {
        container.html('<div class="alert alert-info text-center"><h4>No profiles found</h4><p>Try adjusting your search filters</p></div>');
        return;
    }
    
    container.append('<div class="search-form"><h3><i class="fa fa-users"></i> Found ' + count + ' Profile' + (count > 1 ? 's' : '') + '</h3><hr></div>');
    
    results.forEach(profile => {
        const initials = profile.name ? profile.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2) : '?';
        
        container.append(`
            <div class="search-form" style="padding: 20px; margin-bottom: 20px;">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; margin: 0 auto;">
                            ${initials}
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h4 style="margin: 0 0 10px 0;">${profile.name || 'Profile #' + profile.id}</h4>
                        <p style="margin: 5px 0;"><i class="fa fa-birthday-cake"></i> ${profile.age || 'N/A'} years | <i class="fa fa-venus-mars"></i> ${profile.gender || 'N/A'}</p>
                        <p style="margin: 5px 0;"><i class="fa fa-book"></i> ${profile.religion || 'N/A'} | <i class="fa fa-map-marker"></i> ${profile.location || 'N/A'}</p>
                        <p style="margin: 5px 0;"><i class="fa fa-graduation-cap"></i> ${profile.education || 'N/A'} | <i class="fa fa-briefcase"></i> ${profile.occupation || 'N/A'}</p>
                    </div>
                    <div class="col-md-3 text-right">
                        <a href="/view_profile.php?id=${profile.id}" class="btn btn-primary btn-sm" style="margin-bottom: 5px; display: block;"><i class="fa fa-eye"></i> View Profile</a>
                        <button onclick="sendInterest(${profile.id})" class="btn btn-success btn-sm" style="margin-bottom: 5px; display: block; width: 100%;"><i class="fa fa-heart"></i> Send Interest</button>
                        <button onclick="addToShortlist(${profile.id})" class="btn btn-info btn-sm" style="display: block; width: 100%;"><i class="fa fa-star"></i> Shortlist</button>
                    </div>
                </div>
            </div>
        `);
    });
}

function sendInterest(userId) {
    if (!confirm('Send interest to this profile?')) return;
    
    $.ajax({
        url: '/api/interest.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ receiver_id: userId, message: '' }),
        success: function(res) {
            alert(res.success ? (res.message || 'Interest sent!') : ('Failed: ' + (res.error || 'Unknown error')));
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
            alert(res.success ? (res.message || 'Updated!') : ('Failed: ' + (res.error || 'Unknown error')));
        },
        error: function() {
            alert('Failed to update shortlist. Please try again.');
        }
    });
}

// Trigger search on page load
$(document).ready(function() {
    $('#quick-search-form').submit();
});
</script>

<?php include_once("footer.php");?>
</body>
</html>
