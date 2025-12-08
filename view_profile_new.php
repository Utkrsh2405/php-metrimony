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
<title><?php echo $fname . " " . $lname; ?>'s Profile | Make My Love</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link href="css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<link href="css/style.css" rel='stylesheet' type='text/css' />
<link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
<link href="css/font-awesome.css" rel="stylesheet"> 

<style>
/* Modern Profile Page Styles */
:root {
    --primary-color: #e91e63;
    --secondary-color: #667eea;
    --success-color: #10b981;
    --text-dark: #1e293b;
    --text-light: #64748b;
    --border-color: #e2e8f0;
    --bg-light: #f8fafc;
}

.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #e91e63 100%);
    background-size: 200% 200%;
    animation: gradientShift 15s ease infinite;
    padding: 40px 0;
    margin-bottom: 30px;
    color: white;
    position: relative;
    overflow: hidden;
}

.profile-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at top right, rgba(255,255,255,0.1), transparent);
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.profile-header-content {
    position: relative;
    z-index: 1;
}

.profile-id-badge {
    background: rgba(255,255,255,0.2);
    padding: 8px 20px;
    border-radius: 25px;
    display: inline-block;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
    backdrop-filter: blur(10px);
}

.profile-name {
    font-size: 36px;
    font-weight: 700;
    margin: 0 0 10px 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-meta {
    font-size: 16px;
    opacity: 0.95;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.profile-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
}

.action-buttons {
    margin-top: 20px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-modern {
    padding: 12px 28px;
    border-radius: 25px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.btn-edit {
    background: white;
    color: var(--primary-color);
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-photos {
    background: rgba(255,255,255,0.2);
    color: white;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.3);
}

.btn-photos:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
}

.btn-interest {
    background: var(--success-color);
    color: white;
}

.btn-interest:hover {
    background: #059669;
    transform: translateY(-2px);
}

.profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

.profile-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    padding: 30px;
    margin-bottom: 25px;
    border: 1px solid var(--border-color);
}

.photo-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.photo-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 3/4;
    background: var(--bg-light);
}

.photo-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.photo-item:hover img {
    transform: scale(1.05);
}

.photo-main {
    grid-column: span 2;
    grid-row: span 2;
}

.section-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 3px solid var(--primary-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: var(--primary-color);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.info-item {
    display: flex;
    padding: 15px;
    border-radius: 10px;
    background: var(--bg-light);
    transition: all 0.3s;
}

.info-item:hover {
    background: #f1f5f9;
    transform: translateX(5px);
}

.info-label {
    font-weight: 600;
    color: var(--text-light);
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    min-width: 140px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-label i {
    color: var(--primary-color);
    font-size: 16px;
}

.info-value {
    color: var(--text-dark);
    font-weight: 500;
    font-size: 15px;
}

.about-text {
    background: var(--bg-light);
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid var(--primary-color);
    line-height: 1.8;
    color: var(--text-dark);
    font-size: 15px;
}

.nav-tabs-modern {
    border-bottom: 2px solid var(--border-color);
    margin-bottom: 25px;
}

.nav-tabs-modern li a {
    border: none;
    color: var(--text-light);
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 8px 8px 0 0;
    margin-right: 5px;
}

.nav-tabs-modern li.active a,
.nav-tabs-modern li a:hover {
    background: var(--primary-color);
    color: white;
    border: none;
}

.sidebar-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    padding: 25px;
    margin-bottom: 25px;
    border: 1px solid var(--border-color);
}

.sidebar-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--primary-color);
}

.recent-profile-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 12px;
    transition: all 0.3s;
    border: 1px solid var(--border-color);
}

.recent-profile-item:hover {
    background: var(--bg-light);
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.recent-profile-img {
    width: 70px;
    height: 70px;
    border-radius: 10px;
    overflow: hidden;
}

.recent-profile-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.recent-profile-info h5 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-dark);
    margin: 0 0 5px 0;
}

.recent-profile-info p {
    font-size: 13px;
    color: var(--text-light);
    margin: 0 0 5px 0;
}

.recent-profile-info .view-link {
    color: var(--primary-color);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
}

.recent-profile-info .view-link:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .profile-name {
        font-size: 28px;
    }
    
    .photo-main {
        grid-column: span 1;
        grid-row: span 1;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-modern {
        width: 100%;
        justify-content: center;
    }
}

/* Photo Upload Modal */
.modal-modern .modal-content {
    border-radius: 16px;
    border: none;
}

.modal-modern .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 16px 16px 0 0;
    padding: 20px 25px;
}

.modal-modern .modal-title {
    font-weight: 700;
}

.photo-upload-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-top: 20px;
}

.upload-box {
    border: 2px dashed var(--border-color);
    border-radius: 12px;
    padding: 30px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: var(--bg-light);
}

.upload-box:hover {
    border-color: var(--primary-color);
    background: white;
}

.upload-box i {
    font-size: 36px;
    color: var(--text-light);
    margin-bottom: 10px;
}

.upload-box.has-image {
    padding: 0;
    position: relative;
    aspect-ratio: 3/4;
}

.upload-box.has-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10px;
}

.upload-box .remove-photo {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.upload-box .remove-photo:hover {
    background: #dc2626;
    transform: scale(1.1);
}
</style>

<script>
$(document).ready(function(){
    $(".dropdown").hover(            
        function() {
            $('.dropdown-menu', this).stop( true, true ).slideDown("fast");
            $(this).toggleClass('open');        
        },
        function() {
            $('.dropdown-menu', this).stop( true, true ).slideUp("fast");
            $(this).toggleClass('open');       
        }
    );
});
</script>
</head>
<body>
<?php include_once("includes/navigation.php");?>

<!-- Profile Header -->
<div class="profile-header">
    <div class="container profile-header-content">
        <div class="profile-id-badge">
            <i class="fa fa-id-card"></i> Profile ID: <?php echo $profileid; ?>
        </div>
        <h1 class="profile-name"><?php echo $fname . " " . $lname; ?></h1>
        <div class="profile-meta">
            <span><i class="fa fa-birthday-cake"></i> <?php echo $age; ?> Years</span>
            <span><i class="fa fa-venus-mars"></i> <?php echo $sex; ?></span>
            <span><i class="fa fa-map-marker"></i> <?php echo $state . ", " . $country; ?></span>
            <span><i class="fa fa-briefcase"></i> <?php echo $occupation; ?></span>
        </div>
        
        <div class="action-buttons">
            <?php if ($is_own_profile): ?>
                <button class="btn-modern btn-edit" onclick="window.location.href='edit-profile.php'">
                    <i class="fa fa-edit"></i> Edit Profile
                </button>
                <button class="btn-modern btn-photos" data-toggle="modal" data-target="#photoModal">
                    <i class="fa fa-camera"></i> Manage Photos
                </button>
            <?php else: ?>
                <button class="btn-modern btn-interest" onclick="sendInterest(<?php echo $profileid; ?>)">
                    <i class="fa fa-heart"></i> Express Interest
                </button>
                <button class="btn-modern btn-photos">
                    <i class="fa fa-comments"></i> Send Message
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container profile-container">
    <div class="row">
        <div class="col-md-8">
            <!-- Photo Gallery -->
            <div class="profile-card">
                <div class="section-title">
                    <i class="fa fa-camera"></i> Photos
                </div>
                <div class="photo-gallery">
                    <?php if($pic1): ?>
                        <div class="photo-item photo-main">
                            <img src="profile/<?php echo $profileid;?>/<?php echo $pic1;?>" alt="<?php echo $fname;?>">
                        </div>
                    <?php endif; ?>
                    <?php if($pic2): ?>
                        <div class="photo-item">
                            <img src="profile/<?php echo $profileid;?>/<?php echo $pic2;?>" alt="<?php echo $fname;?>">
                        </div>
                    <?php endif; ?>
                    <?php if($pic3): ?>
                        <div class="photo-item">
                            <img src="profile/<?php echo $profileid;?>/<?php echo $pic3;?>" alt="<?php echo $fname;?>">
                        </div>
                    <?php endif; ?>
                    <?php if($pic4): ?>
                        <div class="photo-item">
                            <img src="profile/<?php echo $profileid;?>/<?php echo $pic4;?>" alt="<?php echo $fname;?>">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- About Me -->
            <div class="profile-card">
                <div class="section-title">
                    <i class="fa fa-user"></i> About Me
                </div>
                <div class="about-text">
                    <?php echo $aboutme ? $aboutme : "No description provided yet."; ?>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="profile-card">
                <div class="section-title">
                    <i class="fa fa-info-circle"></i> Basic Information
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-user"></i> Name</div>
                        <div class="info-value"><?php echo $fname . " " . $lname; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-calendar"></i> Age</div>
                        <div class="info-value"><?php echo $age; ?> Years</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-arrows-v"></i> Height</div>
                        <div class="info-value"><?php echo $height; ?> cm</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-balance-scale"></i> Weight</div>
                        <div class="info-value"><?php echo $weight; ?> kg</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-venus-mars"></i> Marital Status</div>
                        <div class="info-value"><?php echo $maritalstatus; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-language"></i> Mother Tongue</div>
                        <div class="info-value"><?php echo $mothertounge; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-tint"></i> Blood Group</div>
                        <div class="info-value"><?php echo $bloodgroup; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-cutlery"></i> Diet</div>
                        <div class="info-value"><?php echo $diet; ?></div>
                    </div>
                </div>
            </div>

            <!-- Religious Background -->
            <div class="profile-card">
                <div class="section-title">
                    <i class="fa fa-book"></i> Religious Background
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-book"></i> Religion</div>
                        <div class="info-value"><?php echo $religion; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-users"></i> Caste</div>
                        <div class="info-value"><?php echo $caste; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-user"></i> Sub Caste</div>
                        <div class="info-value"><?php echo $subcaste; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-birthday-cake"></i> Date of Birth</div>
                        <div class="info-value"><?php echo $dob; ?></div>
                    </div>
                </div>
            </div>

            <!-- Education & Career -->
            <div class="profile-card">
                <div class="section-title">
                    <i class="fa fa-graduation-cap"></i> Education & Career
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-graduation-cap"></i> Education</div>
                        <div class="info-value"><?php echo $education; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-book"></i> Education Details</div>
                        <div class="info-value"><?php echo $edudescr; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-briefcase"></i> Occupation</div>
                        <div class="info-value"><?php echo $occupation; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-file-text"></i> Occupation Details</div>
                        <div class="info-value"><?php echo $occupationdescr; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-money"></i> Annual Income</div>
                        <div class="info-value"><?php echo $income; ?></div>
                    </div>
                </div>
            </div>

            <!-- Family Details -->
            <div class="profile-card">
                <div class="section-title">
                    <i class="fa fa-users"></i> Family Details
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-male"></i> Father's Occupation</div>
                        <div class="info-value"><?php echo $fatheroccupation; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-female"></i> Mother's Occupation</div>
                        <div class="info-value"><?php echo $motheroccupation; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-male"></i> Brothers</div>
                        <div class="info-value"><?php echo $bros; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-female"></i> Sisters</div>
                        <div class="info-value"><?php echo $sis; ?></div>
                    </div>
                </div>
            </div>

            <!-- Partner Preference -->
            <div class="profile-card">
                <div class="section-title">
                    <i class="fa fa-heart"></i> Partner Preference
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-calendar"></i> Age Range</div>
                        <div class="info-value"><?php echo $agemin . " to " . $agemax; ?> Years</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-arrows-v"></i> Height</div>
                        <div class="info-value"><?php echo $p_height; ?> cm</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-venus-mars"></i> Marital Status</div>
                        <div class="info-value"><?php echo $p_maritalstatus; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-book"></i> Religion</div>
                        <div class="info-value"><?php echo $p_religion; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-users"></i> Caste</div>
                        <div class="info-value"><?php echo $p_caste; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-graduation-cap"></i> Education</div>
                        <div class="info-value"><?php echo $p_education; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fa fa-globe"></i> Country</div>
                        <div class="info-value"><?php echo $p_country; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="sidebar-card">
                <div class="sidebar-title">
                    <i class="fa fa-clock-o"></i> Recent Profiles
                </div>
                <?php
                $sql="SELECT c.*, u.account_status FROM customer c 
                      LEFT JOIN users u ON c.cust_id = u.id 
                      WHERE (u.account_status = 'active' OR u.account_status IS NULL)
                      ORDER BY c.profilecreationdate DESC LIMIT 10";
                $result=mysqlexec($sql);
                while($row=mysqli_fetch_assoc($result)){
                    $profid=$row['cust_id'];
                    //getting photo
                    $sql2="SELECT * FROM photos WHERE cust_id=$profid";
                    $result2=mysqlexec($sql2);
                    $photo=mysqli_fetch_assoc($result2);
                    $pic=$photo['pic1'] ?? 'default-avatar.jpg';
                    
                    echo '<a href="view_profile.php?id='.$profid.'" style="text-decoration: none;">';
                    echo '<div class="recent-profile-item">';
                    echo '<div class="recent-profile-img">';
                    echo '<img src="profile/'.$profid.'/'.$pic.'" alt="'.$row['firstname'].'">';
                    echo '</div>';
                    echo '<div class="recent-profile-info">';
                    echo '<h5>'.$row['firstname'].' '.$row['lastname'].'</h5>';
                    echo '<p>'.$row['age'].' Yrs, '.$row['religion'].'</p>';
                    echo '<span class="view-link">View Profile <i class="fa fa-arrow-right"></i></span>';
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Photo Upload Modal -->
<div class="modal fade modal-modern" id="photoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-camera"></i> Manage Photos</h4>
            </div>
            <div class="modal-body">
                <form id="photoUploadForm" action="photouploader.php" method="POST" enctype="multipart/form-data">
                    <div class="photo-upload-grid">
                        <div class="upload-box" onclick="document.getElementById('photo1').click()">
                            <i class="fa fa-camera"></i>
                            <p>Upload Photo 1<br><small>Main Photo</small></p>
                            <input type="file" id="photo1" name="photo1" accept="image/*" style="display:none;" onchange="previewPhoto(this, 0)">
                        </div>
                        <div class="upload-box" onclick="document.getElementById('photo2').click()">
                            <i class="fa fa-camera"></i>
                            <p>Upload Photo 2</p>
                            <input type="file" id="photo2" name="photo2" accept="image/*" style="display:none;" onchange="previewPhoto(this, 1)">
                        </div>
                        <div class="upload-box" onclick="document.getElementById('photo3').click()">
                            <i class="fa fa-camera"></i>
                            <p>Upload Photo 3</p>
                            <input type="file" id="photo3" name="photo3" accept="image/*" style="display:none;" onchange="previewPhoto(this, 2)">
                        </div>
                        <div class="upload-box" onclick="document.getElementById('photo4').click()">
                            <i class="fa fa-camera"></i>
                            <p>Upload Photo 4</p>
                            <input type="file" id="photo4" name="photo4" accept="image/*" style="display:none;" onchange="previewPhoto(this, 3)">
                        </div>
                    </div>
                    <input type="hidden" name="profile_id" value="<?php echo $profileid; ?>">
                </form>
                <p style="margin-top: 20px; color: #64748b; font-size: 13px;">
                    <i class="fa fa-info-circle"></i> Allowed formats: JPG, PNG, GIF. Maximum size: 5MB per photo.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" form="photoUploadForm" class="btn btn-primary">
                    <i class="fa fa-upload"></i> Upload Photos
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function previewPhoto(input, index) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var uploadBox = input.parentElement;
            uploadBox.classList.add('has-image');
            uploadBox.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
            uploadBox.innerHTML += '<div class="remove-photo" onclick="removePhoto(this, ' + index + ')"><i class="fa fa-times"></i></div>';
            uploadBox.onclick = null;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function removePhoto(btn, index) {
    event.stopPropagation();
    var uploadBox = btn.parentElement;
    var photoInput = document.getElementById('photo' + (index + 1));
    photoInput.value = '';
    
    uploadBox.classList.remove('has-image');
    uploadBox.innerHTML = '<i class="fa fa-camera"></i><p>Upload Photo ' + (index + 1) + (index === 0 ? '<br><small>Main Photo</small>' : '') + '</p>';
    uploadBox.onclick = function() { photoInput.click(); };
}

function sendInterest(profileId) {
    // Add AJAX call to send interest
    alert('Interest sent to profile #' + profileId);
}
</script>

<?php include_once("footer.php");?>
</body>
</html>
