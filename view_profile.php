<?php include_once("includes/basic_includes.php");?>
<?php include_once("functions.php"); ?>
<?php require_once("includes/dbconn.php");?>
<?php
if(isloggedin()){
 //do nothing stay here
} else{
   header("location:login.php");
}
 
// Sanitize and validate ID parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid profile ID");
}

// Check if this is the logged-in user's own profile
$is_own_profile = (isset($_SESSION['id']) && $_SESSION['id'] == $id);

$is_exclusive_profile_var = false;
$user_is_subscribed = false;

if (isset($_SESSION['id'])) {
    $user_is_subscribed = isSubscribedUser($_SESSION['id']);
}

//safty purpose copy the get id
$profileid=$id;

//getting profile details from db
$sql="SELECT * FROM customer WHERE cust_id = $id";
$result = mysqlexec($sql);
if($result){
$row=mysqli_fetch_assoc($result);

  $fname=$row['firstname'];
  $lname=$row['lastname'];
  $sex=$row['sex'];

  // Ensure user cannot view same gender unless it's their own profile
  if(isset($_SESSION['id']) && !$is_own_profile) {
      $viewer_id = $_SESSION['id'];
        $viewer_gender = get_user_gender($viewer_id);
        if($viewer_gender) {
            $viewer_gender = strtolower(trim($viewer_gender));
          if($viewer_gender == $profile_gender) {
              echo "<script>alert('You can only view profiles of the opposite gender.'); window.location.href='userhome.php';</script>";
              exit;
          }
      }
  }

  $is_exclusive_profile_var = (isset($row['is_exclusive']) && $row['is_exclusive'] == 1);
  $email=$row['email'];
	$dob=$row['dateofbirth'];
	$religion=$row['religion'];
	$caste = $row['caste'];
	$subcaste=$row['subcaste'];
	$country = $row['country'];
	$state=$row['state'];
	$district=$row['district'];
	$age=$row['age'];
	$maritalstatus=$row['maritalstatus'];
	$profileby=$row['profilecreatedby'];
	$education=$row['education'];
	$edudescr=$row['education_sub'];
	$bodytype=$row['body_type'];
	$physicalstatus=$row['physical_status'];
	$drink=$row['drink'];
	$smoke=$row['smoke'];
	$mothertounge=$row['mothertounge'];
	$bloodgroup=$row['blood_group'];
	$weight=$row['weight'];
	$height=$row['height'];
	$colour=$row['colour'];
	$diet=$row['diet'];
	$occupation=$row['occupation'];
	$occupationdescr=$row['occupation_descr'];
	$fatheroccupation=$row['fathers_occupation'];
	$motheroccupation=$row['mothers_occupation'];
	$income=$row['annual_income'];
	$bros=$row['no_bro'];
	$sis=$row['no_sis'];
	$aboutme=$row['aboutme'];
	
	// Mobile number (contact info)
	$mobile=$row['mobile'] ?? '';
	$phone_code=$row['phone_code'] ?? '91';

	$pic1="";
	$pic2="";
	$pic3="";
	$pic4="";
//getting image filenames from db
$sql2="SELECT * FROM photos WHERE cust_id = $profileid";
$result2 = mysqlexec($sql2);
if($result2){
	$row2=mysqli_fetch_array($result2);
	$pic1=$row2['pic1'] ?? 'default-avatar.jpg';
	$pic2=$row2['pic2'] ?? '';
	$pic3=$row2['pic3'] ?? '';
	$pic4=$row2['pic4'] ?? '';
}
}else{
	echo "<script>alert(\"Invalid Profile ID\")</script>";
}

//getting partner preference
$sql = "SELECT * FROM partnerprefs WHERE custId = $id";
$result = mysqlexec($sql);
$partner_row = mysqli_fetch_assoc($result);

$agemin=$partner_row['agemin'] ?? '';
$agemax=$partner_row['agemax'] ?? '';
$p_maritalstatus=$partner_row['maritalstatus'] ?? '';
$p_complexion=$partner_row['complexion'] ?? '';
$p_height=$partner_row['height'] ?? '';
$p_diet=$partner_row['diet'] ?? '';
$p_religion=$partner_row['religion'] ?? '';
$p_caste=$partner_row['caste'] ?? '';
$p_mothertounge=$partner_row['mothertounge'] ?? '';
$p_education=$partner_row['education'] ?? '';
$p_occupation=$partner_row['occupation'] ?? '';
$p_country=$partner_row['country'] ?? '';
$p_descr=$partner_row['descr'] ?? '';

?>

<!DOCTYPE HTML>
<html>
<head>
<!-- SEO Meta Tags -->
<meta name="robots" content="index, follow">
<meta name="description" content="Mangal Vivah is your trusted Shaadi Partner. Find your perfect match with our premium matrimonial services.">
<meta name="keywords" content="Mangal Vivah, Shaadi Partner, Matrimony, Marriage, Matchmaking, Find Match, Indian Matrimony, Wedding">
<meta name="author" content="Mangal Vivah">
<title><?php echo htmlspecialchars($fname . " " . $lname); ?>'s Profile | Make My Love</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<link href="css/font-awesome.css" rel="stylesheet"> 
<style>
/* Exact replication styling */
body {
    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    color: #333;
    background-color: #fcfcfc;
}
.main-header {
    background: #fff;
    padding: 10px 0;
    border-bottom: 2px solid #cc0000;
}
.profile-banner {
    background: url('images/wed.jpg') no-repeat center center;
    background-size: cover;
    padding: 30px 0;
    color: #fff;
    position: relative;
    border-bottom: 5px solid #cc0000;
}
.profile-banner::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5); /* darkened overlay */
}
.banner-content {
    position: relative;
    z-index: 2;
}
/* Avatar */
.avatar-container {
    width: 200px;
    height: 200px;
    background: #fff;
    border-radius: 50%;
    margin: 0 auto;
    position: relative;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    overflow: visible;
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
    bottom: 5px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255,255,255,0.8);
    color: #cc0000;
    font-weight: bold;
    padding: 2px 10px;
    border-radius: 10px;
    font-size: 14px;
}
/* Info */
.profile-banner h2 {
    margin-top: 0;
    font-size: 24px;
    font-weight: normal;
    text-transform: uppercase;
}
.profile-banner h2 strong {
    font-weight: bold;
}
.details-string {
    font-size: 13px;
    margin-bottom: 15px;
    line-height: 1.5;
}
.btn-red {
    background: #cc0000;
    color: #fff;
    border: 1px solid #b30000;
    border-radius: 2px;
    padding: 5px 12px;
    font-size: 12px;
    margin-right: 5px;
    margin-bottom: 5px;
    transition: 0.2s;
}
.btn-red:hover, .btn-red:focus {
    background: #a30000;
    color: #fff;
    text-decoration: none;
}
.action-buttons .fa { margin-right: 5px; }

/* Contact Popup area */
.contact-popup {
    background: #fff;
    border: 2px solid #cc0000;
    padding: 15px;
    color: #000;
    margin-top: 15px;
    font-size: 12px;
    position: relative;
    display: none; /* Can be toggled with JS */
}
.contact-popup .close-btn {
    position: absolute;
    top: 5px; right: 8px;
    font-weight: bold;
    cursor: pointer;
    font-size: 14px;
}
.contact-popup h4 {
    font-size: 13px; margin: 5px 0; font-weight: bold;
}

/* Tabs & Content */
.profile-content-area {
    background: #fff;
    padding: 20px 0;
}
.tab-buttons {
    display: flex;
    margin-bottom: 0px;
}
.tab-buttons .tab-btn {
    background: #a30000;
    color: #fff;
    padding: 10px 15px;
    border: 1px solid #fff;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
}
.tab-buttons .tab-btn.active {
    background: #cc0000;
}
.section-title {
    background: #cc0000;
    color: #fff;
    padding: 8px 15px;
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    margin-top: 0;
}
.sub-title {
    color: #cc0000;
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 10px;
    margin-top: 20px;
}
.info-row {
    margin-bottom: 8px;
    font-size: 12px;
}
.info-label {
    color: #555;
    padding-right: 10px;
}
.info-value {
    color: #000;
    font-weight: bold;
}
.note-text {
    font-size: 11px;
    color: #666;
    margin-top: 20px;
    line-height: 1.4;
}
.note-text strong {
    color: #333;
}
</style>
<link rel="icon" type="image/png" href="images/favicon.png">
</head>
<body>

<!-- Retain default navigation to not break overall site flow, but you can hide it via CSS if preferred -->
<?php include_once("includes/navigation.php"); ?>

<!-- Banner Section -->
<div class="profile-banner">
    <div class="container banner-content">
        <div class="row">
            <div class="col-md-3 text-center">
                <div class="avatar-container">
                    <?php 
                        $profileImage = "images/avatar.jpg";
                        if (!empty($pic1) && $pic1 != 'default-avatar.jpg') {
                            $profileImage = "profile/{$profileid}/{$pic1}";
                        }
                    ?>
                    <img src="<?php echo $profileImage; ?>" alt="Profile Picture" onerror="this.src='images/avatar.jpg'">
                    <span class="avatar-request">Request</span>
                </div>
            </div>
            <div class="col-md-9">
                <h2><strong><?php echo htmlspecialchars(strtoupper($fname . " " . $lname)); ?></strong> (MV <?php echo $profileid; ?>)</h2>
                <div class="details-string">
                    <?php 
                    $b_age = $age ? $age." yrs." : "N/A";
                    $b_sex = $sex ? ucfirst($sex) : "N/A";
                    $b_marital = $maritalstatus ? $maritalstatus : "N/A";
                    $b_caste = $caste ? $caste : "N/A";
                    $b_height = $height ? $height : "N/A";
                    $b_occ = $occupation ? $occupation : "N/A";
                    $b_district = $district ? $district : "N/A";
                    $b_state = $state ? $state : "N/A";
                    echo htmlspecialchars("$b_age / $b_sex / $b_marital / $b_caste / / $b_height / $b_occ / $b_district / $b_state");
                    ?>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-red"><i class="fa fa-heart"></i> Express Interest</button>
                    <button class="btn btn-red"><i class="fa fa-envelope"></i> Send Message</button>
                    <button class="btn btn-red"><i class="fa fa-share"></i> Forward This Profile</button>
                    <button class="btn btn-red"><i class="fa fa-thumbs-up"></i> Like This Member</button>
                    <button class="btn btn-red"><i class="fa fa-ban"></i> Block This Member</button>
                </div>
                <div class="action-buttons" style="margin-top:5px;">
                    <button class="btn btn-red" id="toggleContactBtn">Verified Mobile number</button>
                    <button class="btn btn-red"><i class="fa fa-print"></i> Print Profile</button>
                </div>
                
                <!-- Exclusive / Contact Popup -->
                <div class="contact-popup" id="contactPopupBox">
                    <span class="close-btn" onclick="document.getElementById('contactPopupBox').style.display='none'">×</span>
                    <?php if($is_exclusive_profile_var && !$user_is_subscribed && !$is_own_profile): ?>
                        <h4><i class="fa fa-lock"></i> Exclusive Profile</h4>
                        <p>Contact details are hidden because this is an exclusive profile.</p>
                        <p>Please <a href="plans.php" style="color:#cc0000; font-weight:bold;">Subscribe</a> to view the contact information.</p>
                    <?php else: ?>
                        <h4>Email id : <?php echo htmlspecialchars($email); ?></h4>
                        <h4>Primary mobile number: <?php echo htmlspecialchars($phone_code . '-' . $mobile); ?> <button class="btn btn-red" style="padding: 2px 8px; margin-left: 10px;">View Stamp</button></h4>
                        <p style="margin-top: 15px; margin-bottom: 5px;">Not a valid contact number? Kindly select the reason and get THREE additional contacts credited to your account.</p>
                        <select style="width: 200px; padding: 3px;">
                            <option>--Select Reasons--</option>
                            <option>Number unreachable</option>
                            <option>Number belongs to someone else</option>
                        </select>
                        <br><button class="btn btn-red" style="margin-top: 5px;">Submit</button>
                        <p style="margin-top: 10px; margin-bottom:0;"><button class="btn btn-red" style="padding: 1px 5px;">Click here</button> to send your request to update References to the member.</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="profile-content-area">
    <div class="container">
        
        <div class="tab-buttons">
            <div class="tab-btn active">About Me</div>
            <div class="tab-btn">About my Family</div>
            <div class="tab-btn">Desired Partner Profile</div>
        </div>

        <!-- ABOUT ME SECTION -->
        <div class="section-container" style="border: 1px solid #ddd; padding-bottom: 20px; margin-bottom:20px;">
            <h3 class="section-title">About Me</h3>
            
            <div class="col-md-12">
                <h4 class="sub-title">Primary Information</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="row info-row"><div class="col-xs-4 info-label">Marital Status :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($maritalstatus ?: 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Height :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($height ?: 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Body Type :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($bodytype ?: 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Blood Group :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($bloodgroup ?: 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Physical Status:</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($physicalstatus ?: 'N/A'); ?></div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="row info-row"><div class="col-xs-4 info-label">Age (DOB) :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($age." yrs. (" . ($dob && $dob != '0000-00-00' ? date('d/m/Y', strtotime($dob)) : '') . ")"); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Weight :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($weight ? $weight . " kg" : 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Complexion :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($colour ?: 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Mother Tongue :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($mothertounge ?: 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Eating Habits :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($diet ?: 'N/A'); ?></div></div>
                    </div>
                </div>

                <h4 class="sub-title">Socio-Religious Background</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="row info-row"><div class="col-xs-4 info-label">Religion :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($religion ?: 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Caste :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($caste ?: 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Sub Caste :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($subcaste ?: 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Birth Time :</div><div class="col-xs-8 info-value"></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Birth Place :</div><div class="col-xs-8 info-value"></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Star :</div><div class="col-xs-8 info-value"></div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="row info-row"><div class="col-xs-4 info-label">Gotra :</div><div class="col-xs-8 info-value"></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Raasi :</div><div class="col-xs-8 info-value"></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Manglik :</div><div class="col-xs-8 info-value"></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Horoscope Match:</div><div class="col-xs-8 info-value"></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Horoscope :</div><div class="col-xs-8 info-value"><button class="btn btn-red" style="margin:0;">Sent horoscope request</button></div></div>
                    </div>
                </div>

                <h4 class="sub-title">Educational & Professional Information</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="row info-row"><div class="col-xs-4 info-label">Education :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($education ?: 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Education in Detail:</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($edudescr ?: ''); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Employed in :</div><div class="col-xs-8 info-value"></div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="row info-row"><div class="col-xs-4 info-label">Occupation :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($occupation ?: 'N/A'); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Occupation in detail:</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($occupationdescr ?: ''); ?></div></div>
                        <div class="row info-row"><div class="col-xs-4 info-label">Annual Income :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($income ?: 'N/A'); ?></div></div>
                    </div>
                </div>

                <h4 class="sub-title">About me</h4>
                <div class="row info-row"><div class="col-xs-12"><?php echo htmlspecialchars($aboutme ?: ''); ?></div></div>
                <button class="btn btn-red" style="margin-top: 10px;">Send Request for About Me</button>
            </div>
            <div class="clearfix"></div>
        </div>

        <!-- ABOUT MY FAMILY SECTION -->
        <h3 class="section-title">About my Family</h3>
        <div style="border: 1px solid #ddd; padding: 20px; margin-bottom:20px;">
            <div class="row info-row"><div class="col-xs-3 info-label">Father's Occupation:</div><div class="col-xs-9 info-value"><?php echo htmlspecialchars($fatheroccupation ?: ''); ?></div></div>
            <div class="row info-row"><div class="col-xs-3 info-label">Mother's Occupation:</div><div class="col-xs-9 info-value"><?php echo htmlspecialchars($motheroccupation ?: ''); ?></div></div>
            <div class="row info-row"><div class="col-xs-3 info-label">Brothers:</div><div class="col-xs-9 info-value"><?php echo htmlspecialchars($bros ?: '0'); ?></div></div>
            <div class="row info-row"><div class="col-xs-3 info-label">Sisters:</div><div class="col-xs-9 info-value"><?php echo htmlspecialchars($sis ?: '0'); ?></div></div>
            <br>
            <button class="btn btn-red">Send Request for Family Details</button>
        </div>

        <!-- DESIRED PARTNER PROFILE SECTION -->
        <h3 class="section-title">Desired Partner Profile</h3>
        <div style="border: 1px solid #ddd; padding: 20px;">
            <div class="row">
                <div class="col-md-6">
                    <div class="row info-row"><div class="col-xs-4 info-label">Age :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($agemin . " to " . $agemax); ?></div></div>
                    <div class="row info-row"><div class="col-xs-4 info-label">Marital Status :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($p_maritalstatus ?: 'Doesn\'t matter'); ?></div></div>
                    <div class="row info-row"><div class="col-xs-4 info-label">Complexion :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($p_complexion ?: 'Doesn\'t matter'); ?></div></div>
                    <div class="row info-row"><div class="col-xs-4 info-label">Height :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($p_height ?: 'Doesn\'t matter'); ?></div></div>
                    <div class="row info-row"><div class="col-xs-4 info-label">Diet :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($p_diet ?: 'Doesn\'t matter'); ?></div></div>
                    <div class="row info-row"><div class="col-xs-4 info-label">Religion :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($p_religion ?: 'Doesn\'t matter'); ?></div></div>
                </div>
                <div class="col-md-6">
                    <div class="row info-row"><div class="col-xs-4 info-label">Caste :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($p_caste ?: 'Doesn\'t matter'); ?></div></div>
                    <div class="row info-row"><div class="col-xs-4 info-label">Mother Tongue :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($p_mothertounge ?: 'Doesn\'t matter'); ?></div></div>
                    <div class="row info-row"><div class="col-xs-4 info-label">Education :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($p_education ?: 'Doesn\'t matter'); ?></div></div>
                    <div class="row info-row"><div class="col-xs-4 info-label">Occupation :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($p_occupation ?: 'Doesn\'t matter'); ?></div></div>
                    <div class="row info-row"><div class="col-xs-4 info-label">Country :</div><div class="col-xs-8 info-value"><?php echo htmlspecialchars($p_country ?: 'Doesn\'t matter'); ?></div></div>
                </div>
            </div>
            <div class="row info-row" style="margin-top:15px;"><div class="col-xs-12"><?php echo htmlspecialchars($p_descr ?: ''); ?></div></div>
            <br>
            <button class="btn btn-red">Send Request for Partner Details</button>
        </div>

        <div class="note-text">
            <strong>Note:</strong> Make My Love has issued this biodata for matrimonial purposes, use by anyone else will tantamount to a criminal offence & attract criminal penalty.<br>
            <strong>Important:</strong> Make My Love has taken reasonable care to ensure authenticity of this bio-data. However, in some cases, it is not possible to verify confidences of members. Make My Love can not be held responsible for the bio-data contents, or for any loss or damage incurred as a result of individuals displaying their bio-data in this database. We recommend that Members make necessary enquiries before taking forward with other Members, or otherwise acting on a bio-data in any manner what so ever.
        </div>

    </div>
</div>

<?php include_once("includes/footer.php");?>

<script>
// Simple toggles for the UI based on screenshot
document.getElementById('toggleContactBtn').addEventListener('click', function() {
    var box = document.getElementById('contactPopupBox');
    if (box.style.display === 'none' || box.style.display === '') {
        box.style.display = 'block';
    } else {
        box.style.display = 'none';
    }
});
</script>

</body>
</html>
