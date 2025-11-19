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

// Get user data
$user_query = mysqli_query($conn, "SELECT sex FROM customer WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($user_query);
$default_gender = ($user_data['sex'] == 'male') ? 'Female' : 'Male';

// Get states
$states_query = mysqli_query($conn, "SELECT DISTINCT state_code, state_name FROM states WHERE status = 1 ORDER BY state_name");
$states = [];
while ($state = mysqli_fetch_assoc($states_query)) {
    $states[] = $state;
}

// Get religions
$religions_query = mysqli_query($conn, "SELECT DISTINCT religion FROM castes WHERE status = 1 AND religion IS NOT NULL ORDER BY religion");
$religions = [];
while ($rel = mysqli_fetch_assoc($religions_query)) {
    $religions[] = $rel['religion'];
}

// Get mother tongues
$languages_query = mysqli_query($conn, "SELECT DISTINCT mothertounge FROM customer WHERE mothertounge IS NOT NULL AND mothertounge != '' ORDER BY mothertounge");
$languages = [];
while ($lang = mysqli_fetch_assoc($languages_query)) {
    $languages[] = $lang['mothertounge'];
}

// Get educations
$educations_query = mysqli_query($conn, "SELECT DISTINCT education FROM customer WHERE education IS NOT NULL AND education != '' ORDER BY education");
$educations = [];
while ($edu = mysqli_fetch_assoc($educations_query)) {
    $educations[] = $edu['education'];
}

// Get occupations
$occupations_query = mysqli_query($conn, "SELECT DISTINCT occupation FROM customer WHERE occupation IS NOT NULL AND occupation != '' ORDER BY occupation");
$occupations = [];
while ($occ = mysqli_fetch_assoc($occupations_query)) {
    $occupations[] = $occ['occupation'];
}
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Advance Search | Shaadi Partner</title>
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
.age-range, .height-range, .income-range {
    display: flex;
    align-items: center;
    gap: 10px;
}
.age-range .form-control, .height-range .form-control {
    flex: 1;
}
.multi-select-group {
    position: relative;
}
.select-dropdown {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ccc;
    padding: 10px;
    background: white;
}
.select-item {
    padding: 5px;
    cursor: pointer;
}
.select-item:hover {
    background: #f0f0f0;
}
.btn-add-remove {
    background: #8B4C4F;
    color: white;
    border: none;
    padding: 5px 15px;
    margin-left: 5px;
}
.selected-items {
    margin-top: 10px;
    padding: 10px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    min-height: 40px;
}
.selected-tag {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 5px 10px;
    margin: 3px;
    border-radius: 3px;
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
        <a href="quick-search.php" class="search-tab">Quick Search</a>
        <a href="advance-search.php" class="search-tab active">Advance Search</a>
        <a href="keyword-search.php" class="search-tab">Keyword Search</a>
        <a href="search-id.php" class="search-tab">Search by Profile Id</a>
    </div>
    
    <div class="search-form">
        <form id="advance-search-form">
            <div class="row">
                <div class="col-md-12">
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
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Marital Status</label>
                        <div>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="marital_status[]" value="Never Married" checked> Never Married
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="marital_status[]" value="Widowed"> Widowed
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="marital_status[]" value="Divorced"> Divorced
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="marital_status[]" value="Awaiting Divorce"> Separated
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="marital_status_any" value="1"> Any
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Have Children</label>
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="has_children" value="No"> No
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="has_children" value="Yes"> Yes
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="has_children" value="" checked> Doesn't matter
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
                            <span>to</span>
                            <select name="age_max" class="form-control">
                                <?php for($i=18; $i<=60; $i++): ?>
                                <option value="<?= $i ?>" <?= $i==32 ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Height</label>
                        <div class="height-range">
                            <select name="height_min" class="form-control">
                                <option value="">3ft 5in - 105cm</option>
                                <option value="4.0">4ft 0in - 122cm</option>
                                <option value="4.6">4ft 6in - 137cm</option>
                                <option value="5.0" selected>5ft 0in - 152cm</option>
                                <option value="5.6">5ft 6in - 168cm</option>
                                <option value="6.0">6ft 0in - 183cm</option>
                                <option value="6.6">6ft 6in - 198cm</option>
                            </select>
                            <span>to</span>
                            <select name="height_max" class="form-control">
                                <option value="">6ft 11in - 211cm</option>
                                <option value="6.6">6ft 6in - 198cm</option>
                                <option value="6.0" selected>6ft 0in - 183cm</option>
                                <option value="5.6">5ft 6in - 168cm</option>
                                <option value="5.0">5ft 0in - 152cm</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Complexion</label>
                        <select name="complexion" class="form-control">
                            <option value="">All</option>
                            <option value="Fair">Fair</option>
                            <option value="Wheatish">Wheatish</option>
                            <option value="Dark">Dark</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Manglik</label>
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="manglik" value="No"> No
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="manglik" value="Yes"> Yes
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="manglik" value="Anshik"> Anshik
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="manglik" value="" checked> All
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Physical status</label>
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="physical_status" value="Normal" checked> Normal
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="physical_status" value="Disabled"> Disable
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="physical_status" value=""> Doesn't matter
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Eating Habits</label>
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="eating_habits" value="Vegetarian"> Vegetarian
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eating_habits" value="Non-Vegetarian"> Non-Vegetarian
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eating_habits" value="Eggetarian"> Eggetarian
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eating_habits" value="" checked> All
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Mother tongue</label>
                        <select name="mother_tongue" class="form-control" id="mother-tongue-select">
                            <option value="">Any</option>
                            <?php foreach($languages as $lang): ?>
                            <option value="<?= htmlspecialchars($lang) ?>"><?= htmlspecialchars($lang) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Religion</label>
                        <select name="religion" class="form-control" id="religion-select">
                            <option value="">Any</option>
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
                        <label>Caste / Division</label>
                        <select name="caste" class="form-control" id="caste-select">
                            <option value="">Any</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Sub Caste</label>
                        <input type="text" name="sub_caste" class="form-control" placeholder="Enter sub caste">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Education</label>
                        <select name="education" class="form-control">
                            <option value="">Any</option>
                            <?php foreach($educations as $edu): ?>
                            <option value="<?= htmlspecialchars($edu) ?>"><?= htmlspecialchars($edu) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Occupation</label>
                        <select name="occupation" class="form-control">
                            <option value="">Any</option>
                            <?php foreach($occupations as $occ): ?>
                            <option value="<?= htmlspecialchars($occ) ?>"><?= htmlspecialchars($occ) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Employed in</label>
                        <div>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="employed_in[]" value="Government"> Government
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="employed_in[]" value="Private"> Private
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="employed_in[]" value="Business"> Business
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="employed_in[]" value="Defence"> Defence
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="employed_in[]" value="Not working"> Not working
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="employed_in_any" value="1" checked> Any
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Select Income</label>
                        <div class="income-range">
                            <input type="text" name="income_min" class="form-control" placeholder="Min">
                            <span style="margin: 0 10px;">To</span>
                            <input type="text" name="income_max" class="form-control" placeholder="Max">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Country Living in</label>
                        <select name="country" class="form-control">
                            <option value="">Any</option>
                            <option value="India" selected>India</option>
                            <option value="USA">USA</option>
                            <option value="UK">UK</option>
                            <option value="Canada">Canada</option>
                            <option value="Australia">Australia</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Location</label>
                        <select name="state" class="form-control" id="state-select">
                            <option value="">Any</option>
                            <?php foreach($states as $state): ?>
                            <option value="<?= $state['state_code'] ?>"><?= htmlspecialchars($state['state_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>City</label>
                        <select name="city" class="form-control" id="city-select">
                            <option value="">Any</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Citizenship</label>
                        <select name="citizenship" class="form-control">
                            <option value="">India</option>
                            <option value="USA">USA</option>
                            <option value="UK">UK</option>
                            <option value="Canada">Canada</option>
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
// Load cities when state is selected
$('#state-select').on('change', function() {
    const stateCode = $(this).val();
    $('#city-select').html('<option value="">Loading...</option>');
    
    if (!stateCode) {
        $('#city-select').html('<option value="">Any</option>');
        return;
    }
    
    $.ajax({
        url: '/api/get-cities.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ state_code: stateCode }),
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Any</option>';
                response.cities.forEach(city => {
                    options += '<option value="' + city.city_name + '">' + city.city_name + '</option>';
                });
                $('#city-select').html(options);
            }
        }
    });
});

// Load castes when religion is selected
$('#religion-select').on('change', function() {
    const religion = $(this).val();
    $('#caste-select').html('<option value="">Loading...</option>');
    
    if (!religion) {
        $('#caste-select').html('<option value="">Any</option>');
        return;
    }
    
    $.ajax({
        url: '/api/get-castes.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ religion: religion }),
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Any</option>';
                response.castes.forEach(caste => {
                    options += '<option value="' + caste.caste_name + '">' + caste.caste_name + '</option>';
                });
                $('#caste-select').html(options);
            }
        }
    });
});

$('#advance-search-form').on('submit', function(e) {
    e.preventDefault();
    
    const formData = {};
    const formArray = $(this).serializeArray();
    
    formArray.forEach(item => {
        if (item.value) {
            if (item.name.includes('[]')) {
                const key = item.name.replace('[]', '');
                if (!formData[key]) formData[key] = [];
                formData[key].push(item.value);
            } else {
                formData[item.name] = item.value;
            }
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
        }
    });
}
</script>

<?php include_once("footer.php");?>
</body>
</html>
