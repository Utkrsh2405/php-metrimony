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
$user_query = mysqli_query($conn, "SELECT sex FROM customer WHERE cust_id = $user_id");
$user_data = mysqli_fetch_assoc($user_query);
$default_gender = (strcasecmp($user_data['sex'], 'Male') == 0) ? 'Female' : 'Male';

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
                                <input type="checkbox" name="with_photo" value="1"> Photo
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
    
    // Build query string and redirect to searchresult.php
    const formData = $(this).serialize();
    window.location.href = 'searchresult.php?mode=quick&' + formData;
});
</script>

<?php include_once("footer.php");?>
</body>
</html>