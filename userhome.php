<?php 
require_once("includes/basic_includes.php");
require_once("includes/dbconn.php");
require_once("functions.php"); 

if(!isloggedin()){
   header("location:login.php");
   exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid user ID");
}

if ($id != $_SESSION['id']) {
    header("location:userhome.php?id=" . $_SESSION['id']);
    exit();
}

$profileid = $id;

// Get user info
$sql = "SELECT c.*, u.account_status, u.profile_completeness, u.last_login FROM customer c JOIN users u ON c.cust_id = u.id WHERE c.cust_id = $id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if(!$row) {
    // If not in customer table yet (just registered but incomplete)
    $sql2 = "SELECT * FROM users WHERE id = $id";
    $result2 = mysqli_query($conn, $sql2);
    $row = mysqli_fetch_assoc($result2);
    $fname = $row['username'];
    $completeness = $row['profile_completeness'] ?? 10;
    $created_on = "N/A";
    $last_login = $row['last_login'] ? date('d M Y', strtotime($row['last_login'])) : date('d M Y');
    $gender = $row['gender'] ?? 'male';
} else {
    $fname = $row['firstname'] . ' ' . $row['lastname'];
    if(trim($fname) == '') $fname = $row['username'] ?? 'User';
    $completeness = $row['profile_completeness'] ?? 50;
    $created_on = $row['profilecreationdate'] ? date('d M Y', strtotime($row['profilecreationdate'])) : "N/A";
    $last_login = $row['last_login'] ? date('d M Y', strtotime($row['last_login'])) : date('d M Y');
    $gender = $row['sex'] ?? 'male';
}

$is_subscribed_val = (isset($row['is_subscribed']) && $row['is_subscribed'] == 1);
$account_type = $is_subscribed_val ? "UNIQUE" : "FREE";

// Messages Stats
$unread_msg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as c FROM messages WHERE to_user_id=$id AND is_read=0"))['c'] ?? 0;
$total_msg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as c FROM messages WHERE to_user_id=$id"))['c'] ?? 0;

$sent_msg = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as c FROM messages WHERE from_user_id=$id"))['c'] ?? 0;

// Interests Stats
$received_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as c FROM interests WHERE to_user_id=$id AND status='pending'"))['c'] ?? 0;
$received_accepted = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as c FROM interests WHERE to_user_id=$id AND status='accepted'"))['c'] ?? 0;
$received_declined = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as c FROM interests WHERE to_user_id=$id AND status='declined'"))['c'] ?? 0;

$sent_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as c FROM interests WHERE from_user_id=$id AND status='pending'"))['c'] ?? 0;
$sent_accepted = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as c FROM interests WHERE from_user_id=$id AND status='accepted'"))['c'] ?? 0;
$sent_declined = mysqli_fetch_assoc(mysqli_query($conn, "SELECT count(*) as c FROM interests WHERE from_user_id=$id AND status='declined'"))['c'] ?? 0;

// Photos
$pic1 = 'default-avatar.jpg';
if($sql = mysqli_query($conn, "SELECT pic1 FROM photos WHERE cust_id = $id")) {
    if($r = mysqli_fetch_assoc($sql)) {
        $pic1 = $r['pic1'] ?: 'default-avatar.jpg';
    }
}
$profileImage = ($pic1 != 'default-avatar.jpg') ? "profile/{$profileid}/{$pic1}" : "images/avatar.jpg";
?>
<!DOCTYPE HTML>
<html>
<head>
<title>User Home | Make My Love</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<link href="css/style.css" rel='stylesheet' type='text/css' />
<link href="css/font-awesome.css" rel="stylesheet"> 
<style>
/* Exact replication styling */
body {
    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    color: #333;
    background-color: #fcfcfc;
}
.profile-banner {
    background: url('images/wed.jpg') no-repeat center center;
    background-size: cover;
    padding: 20px 0 50px 0;
    color: #fff;
    position: relative;
    border-bottom: 5px solid #cc0000;
}
.profile-banner::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.4); 
}
.banner-content {
    position: relative;
    z-index: 2;
}
.avatar-container {
    width: 160px;
    height: 160px;
    background: #fff;
    border-radius: 50%;
    margin: 0 auto;
    position: relative;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
}
.avatar-container img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
}
.avatar-request {
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255,255,255,0.9);
    color: #000;
    font-weight: bold;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 13px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.badges-bar {
    position: relative;
    z-index: 3;
    margin-top: -15px;
    text-align: center;
}
.badge-red {
    background: #cc0000;
    color: #fff;
    font-size: 11px;
    padding: 4px 8px;
    margin: 0 2px;
    font-weight: bold;
    display: inline-block;
    border-radius: 2px;
}
.badge-red i {
    margin-right: 3px;
}
.gray-header {
    background: #eee;
    color: #555;
    font-size: 18px;
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-bottom: none;
}
.nav-tabs-custom {
    border-bottom: 1px solid #ddd;
    margin: 0; padding: 0;
    list-style: none;
    display: flex;
}
.nav-tabs-custom li {
    flex: 1;
    text-align: center;
    border: 1px solid #ddd;
    border-top: none;
    border-left: none;
    background: #fff;
}
.nav-tabs-custom li:last-child {
    border-right: none;
}
.nav-tabs-custom li a {
    display: block;
    padding: 10px 0;
    color: #cc0000;
    text-decoration: none;
    font-size: 13px;
}
.nav-tabs-custom li.active a {
    color: #333;
}
.stats-table {
    width: 100%;
    font-size: 12px;
}
.stats-table th {
    color: #cc0000;
    font-size: 13px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}
.stats-table td {
    padding: 5px 0;
}
.stats-table td span {
    color: #cc0000;
    font-weight: bold;
}
.recent-view-circle {
    text-align: center;
}
.recent-view-circle img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 5px;
}
.recent-view-circle .vid {
    color: #cc0000;
    font-size: 12px;
    margin-bottom: 5px;
}
</style>
</head>
<body>
<?php include_once("includes/navigation.php"); ?>

<!-- Banner Section -->
<div class="profile-banner">
    <div class="container text-center banner-content">
        <h2 style="font-size:26px; margin-bottom:15px; font-weight:normal;">Hello! <?php echo htmlspecialchars($fname); ?> <span style="font-size:26px;">(MV <?php echo $id; ?>)</span> <span style="font-size:14px; margin-left:10px;">Profile <?php echo $completeness; ?>% complete</span></h2>
        <div class="avatar-container">
            <img src="<?php echo $profileImage; ?>" onerror="this.src='images/avatar.jpg'" alt="">
            <span class="avatar-request">Photo</span>
        </div>
    </div>
</div>

<div class="badges-bar">
    <span class="badge-red"><i class="fa fa-user"></i> Verified Profile</span>
    <span class="badge-red"><i class="fa fa-envelope"></i> Verify my email</span>
    <span class="badge-red"><i class="fa fa-mobile"></i> Verify mobile no.</span>
    <span class="badge-red" style="background:#a30000;">Created on : <?php echo $created_on; ?></span>
    <span class="badge-red" style="background:#a30000;">Last Login : <?php echo $last_login; ?></span>
</div>

<!-- Alert Box -->
<div class="container" style="margin-top: 30px;">
    <div class="alert" style="background-color: #e8f5e9; border: 1px solid #c8e6c9; color: #333; padding: 10px 15px;">
        Your favourite list is empty. &nbsp;&nbsp;&nbsp; Members I Ignored: <b style="color:#cc0000;">0</b> 
        <a href="#" style="color:#cc0000; text-decoration:none;">(View ignored profile(s))</a> 
        <span class="pull-right" style="font-size:12px; margin-top:2px;">For better responses, complete your profile.</span>
    </div>
</div>

<!-- Account Type Box -->
<div class="container">
    <div style="background: #fdf6f6; border: 1px solid #eedcdc; padding:10px 15px; font-size:13px; color: #777;">
        Account type : <span class="label label-default" style="background:#777; font-size:11px; padding:3px 6px;"><?php echo $account_type; ?></span> 
        &nbsp;&nbsp; Membership Expires on : <span class="label label-primary" style="background:#337ab7; font-size:11px; padding:3px 6px;">N/A</span> 
        &nbsp;&nbsp; Days Remaining : <span class="label label-success" style="background:#5cb85c; font-size:11px; padding:3px 6px;">0</span> 
        &nbsp;&nbsp; Contact Issued : <span class="label label-info" style="background:#5bc0de; font-size:11px; padding:3px 6px;">0</span>
    </div>
</div>

<!-- Viewed Profiles -->
<div class="container" style="margin-top:20px;">
    <div style="border: 1px solid #ddd;">
        <div style="background:#cc0000; color:#fff; padding:8px 15px; font-weight:bold; font-size:14px;">Total Contact Viewed : 0</div>
        
        <div style="padding: 20px; display:flex; justify-content:space-around; flex-wrap:wrap;">
            <?php 
            // Dummy profiles logic to mimic UI look. We'll load recent profiles of opposite gender.
            $opp_gender = (strtolower($gender) == 'male') ? 'Female' : 'Male';
            $sql = "SELECT c.*, p.pic1 FROM customer c LEFT JOIN photos p ON c.cust_id = p.cust_id WHERE c.sex = '$opp_gender' ORDER BY c.cust_id DESC LIMIT 4";
            $res = mysqli_query($conn, $sql);
            if(mysqli_num_rows($res) > 0) {
                while($vrow = mysqli_fetch_assoc($res)) {
                    $vimage = ($vrow['pic1'] && $vrow['pic1'] != 'default-avatar.jpg') ? "profile/{$vrow['cust_id']}/{$vrow['pic1']}" : "images/avatar.jpg";
                    echo '
                    <div class="recent-view-circle">
                        <div class="vid">MV'.$vrow['cust_id'].'</div>
                        <a href="view_profile.php?id='.$vrow['cust_id'].'">
                            <img src="'.$vimage.'" onerror="this.src=\'images/avatar.jpg\'">
                        </a><br>
                        <span class="avatar-request" style="position:static; padding:2px 8px; font-size:11px; box-shadow:none; border:1px solid #ddd;">View Profile</span>
                    </div>';
                }
            } else {
                echo "<p style='color:#777; font-size:13px;'>No matching profiles viewed recently.</p>";
            }
            ?>
        </div>

        <div style="background:#cc0000; color:#fff; text-align:center; padding:5px 0;">
            <a href="search.php" style="color:#fff; text-decoration:none; font-size:12px;">...View more</a>
        </div>
    </div>
</div>

<!-- Two Columns -->
<div class="container" style="margin-top:30px; margin-bottom: 50px;">
    <div class="row">
        <!-- Messages List Column -->
        <div class="col-md-6">
            <div class="gray-header">Messages</div>
            <ul class="nav-tabs-custom">
                <li><a href="#tab_interest" data-toggle="tab">Interest</a></li>
                <li class="active"><a href="#tab_messages" data-toggle="tab">Messages</a></li>
                <li><a href="#tab_request" data-toggle="tab">Request</a></li>
            </ul>
            <div style="border: 1px solid #ddd; border-top:none; padding: 20px; min-height: 250px;">
                
                <table class="stats-table">
                    <tr>
                        <th style="width:50%;">Recieved</th>
                        <th style="width:50%;">Send</th>
                    </tr>
                    <tr>
                        <td>Recieved : <span><?php echo $total_msg; ?></span></td>
                        <td>Sent : <span><?php echo $sent_msg; ?></span></td>
                    </tr>
                    <tr>
                        <td>Read : <span><?php echo ($total_msg - $unread_msg); ?></span></td>
                        <td>Read : <span>None</span></td>
                    </tr>
                    <tr>
                        <td>Unread : <span><?php echo $unread_msg; ?></span></td>
                        <td>Unread : <span>None</span></td>
                    </tr>
                    <tr>
                        <td>Pending : <span><?php echo $received_pending; ?></span></td>
                        <td>Pending : <span><?php echo $sent_pending; ?></span></td>
                    </tr>
                    <tr>
                        <td>Accepted : <span><?php echo $received_accepted; ?></span></td>
                        <td>Accepted : <span><?php echo $sent_accepted; ?></span></td>
                    </tr>
                    <tr>
                        <td>Decline : <span><?php echo $received_declined; ?></span></td>
                        <td>Decline : <span><?php echo $sent_declined; ?></span></td>
                    </tr>
                </table>

            </div>
        </div>
        
        <!-- Matching Profiles & Callback -->
        <div class="col-md-6">
            <div class="gray-header">Matching Profiles</div>
            <div style="border: 1px solid #ddd; border-top:none; padding: 20px; margin-bottom:20px;">
                <p style="font-size:12px;"><a href="search.php" style="color:#cc0000; font-weight:bold; text-decoration:none;">Search Now</a> to find and contact matching profiles.</p>
            </div>

            <div style="border: 1px solid #ddd; padding: 20px;">
                <div style="color:#cc0000; font-weight:bold; font-size:13px; margin-bottom:15px;">Enter your contact number to call you back</div>
                <form onsubmit="event.preventDefault(); alert('Callback request submitted successfully.');">
                    <div style="margin-bottom:10px; font-size:12px;">
                        <span style="display:inline-block; width:120px;">Preferred language:</span>
                        <input type="radio" name="lang" value="Hindi" id="l_hin"> <label for="l_hin" style="font-weight:normal; margin-right:10px;">Hindi</label>
                        <input type="radio" name="lang" value="English" id="l_eng" checked> <label for="l_eng" style="font-weight:normal;">English</label>
                    </div>
                    <div style="margin-bottom:10px; font-size:12px; display:flex; align-items:center;">
                        <span style="display:inline-block; width:120px;">Contact Details:</span>
                        <input type="text" class="form-control" style="width:200px; height:26px; font-size:12px; display:inline-block; padding:2px 5px;" placeholder="Phone Number" required>
                        <button type="submit" class="btn badge-red" style="padding: 4px 15px; margin-left: 10px; border:none;">submit</button>
                    </div>
                    <div style="font-size:12px; color:#555;">Mention STD code for landline number.</div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include_once("includes/footer.php");?>

<script>
// Simple tab integration (visual only per html given)
$('.nav-tabs-custom a').click(function (e) {
  e.preventDefault()
  $(this).parent().siblings().removeClass('active');
  $(this).parent().addClass('active');
})
</script>
</body>
</html>