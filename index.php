<?php 
include_once("includes/basic_includes.php");
include_once("includes/dbconn.php");
include_once("functions.php"); 
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Shaadi Partner - Find Your Perfect Match | Home</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<link href="css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<!-- Custom Theme files -->
<link href="css/style.css" rel='stylesheet' type='text/css' />
<link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
<!--font-Awesome-->
<link href="css/font-awesome.css" rel="stylesheet"> 
<!--font-Awesome-->
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
<style>
/* Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Ubuntu', sans-serif;
    overflow-x: hidden;
}

/* Hero/Banner Section */
.hero-section {
    position: relative;
    height: 600px;
    background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/wed.jpg') no-repeat center;
    background-size: cover;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-align: center;
}

.hero-content h1 {
    font-family: 'Great Vibes', cursive;
    font-size: 72px;
    color: #fff;
    margin-bottom: 10px;
    text-shadow: 2px 2px 8px rgba(0,0,0,0.8);
}

.hero-subtitle {
    font-size: 18px;
    margin-bottom: 30px;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.hero-cta {
    display: inline-block;
    background: #e91e63;
    color: #fff;
    padding: 15px 40px;
    border-radius: 30px;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(233,30,99,0.4);
}

.hero-cta:hover {
    background: #c2185b;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(233,30,99,0.6);
    color: #fff;
    text-decoration: none;
}

/* About Section */
.about-section {
    padding: 80px 0;
    background: #fff;
}

.section-title {
    font-family: 'Dancing Script', cursive;
    font-size: 48px;
    color: #e91e63;
    text-align: center;
    margin-bottom: 50px;
}

.about-content {
    display: flex;
    align-items: center;
    gap: 50px;
}

.about-image {
    flex: 1;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.about-image img {
    width: 100%;
    height: auto;
    display: block;
}

.about-text {
    flex: 1;
}

.about-text h3 {
    font-family: 'Dancing Script', cursive;
    font-size: 36px;
    color: #e91e63;
    margin-bottom: 20px;
}

.about-text p {
    color: #666;
    font-size: 16px;
    line-height: 1.8;
    margin-bottom: 15px;
}

/* Featured Profiles Section */
.featured-section {
    padding: 80px 0;
    background: #f9f9f9;
}

.featured-section .section-title {
    margin-bottom: 60px;
}

.profile-slider {
    display: flex;
    gap: 30px;
    overflow-x: auto;
    padding: 20px 0;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

.profile-slider::-webkit-scrollbar {
    height: 8px;
}

.profile-slider::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 10px;
}

.profile-slider::-webkit-scrollbar-thumb {
    background: #e91e63;
    border-radius: 10px;
}

.profile-card {
    min-width: 280px;
    flex-shrink: 0;
    background: #fff;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    text-align: center;
}

.profile-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.profile-card img {
    width: 100%;
    height: 320px;
    object-fit: cover;
}

.profile-info {
    padding: 20px;
}

.profile-name {
    font-size: 20px;
    font-weight: 700;
    color: #333;
    margin-bottom: 8px;
}

.profile-details {
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
}

/* Bride & Groom Section */
.bride-groom-section {
    padding: 80px 0;
    background: #fff;
    position: relative;
}

.bride-groom-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 200px;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23f9f9f9" d="M0,96L80,112C160,128,320,160,480,165.3C640,171,800,149,960,138.7C1120,128,1280,128,1360,128L1440,128L1440,0L1360,0C1280,0,1120,0,960,0C800,0,640,0,480,0C320,0,160,0,80,0L0,0Z"></path></svg>') no-repeat top;
    background-size: cover;
}

.bg-decoration {
    position: relative;
    padding: 60px 0;
}

.bg-leaves {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23d4edda" fill-opacity="0.3" d="M0,160L48,144C96,128,192,96,288,101.3C384,107,480,149,576,165.3C672,181,768,171,864,149.3C960,128,1056,96,1152,96C1248,96,1344,128,1392,144L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat center;
    background-size: cover;
    opacity: 0.5;
    z-index: 0;
}

.bg-decoration .section-title {
    position: relative;
    z-index: 1;
}

.bg-decoration .profile-slider {
    position: relative;
    z-index: 1;
}

/* Search Section */
.search-section {
    padding: 80px 0;
    background: #fff;
}

.search-section .section-title {
    margin-bottom: 60px;
}

.search-category {
    margin-bottom: 50px;
}

.search-category h3 {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-category h3 i {
    width: 40px;
    height: 40px;
    background: #e91e63;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.search-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.search-tag {
    display: inline-block;
    padding: 10px 20px;
    background: #f5f5f5;
    color: #e91e63;
    border-radius: 25px;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
}

.search-tag:hover {
    background: #e91e63;
    color: #fff;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(233,30,99,0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 48px;
    }
    
    .about-content {
        flex-direction: column;
    }
    
    .section-title {
        font-size: 36px;
    }
    
    .profile-card {
        min-width: 250px;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeInUp {
    animation: fadeInUp 0.8s ease;
}
</style>
</head>
<body>
<!-- Navigation -->
<?php include_once("includes/navigation.php");?>

<?php
// Fetch homepage sections
$sections = [];
$sections_sql = "SELECT * FROM homepage_sections WHERE is_active = 1 ORDER BY section_order ASC";
$sections_result = mysqli_query($conn, $sections_sql);
if($sections_result) {
    while($section = mysqli_fetch_assoc($sections_result)) {
        $sections[$section['section_key']] = $section;
    }
}

$hero = $sections['hero'] ?? null;
$about = $sections['about'] ?? null;
$bride_groom = $sections['bride_groom'] ?? null;
$search_by = $sections['search_by'] ?? null;
?>

<!-- Hero Section -->
<!-- Hero Section -->
<section class="hero-section" style="<?php 
    $hero_bg = 'images/wed.jpg';
    if($hero && !empty($hero['section_image']) && file_exists($hero['section_image'])) {
        $hero_bg = $hero['section_image'];
    }
    echo "background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{$hero_bg}') no-repeat center; background-size: cover;";
?>">
    <div class="hero-content animate-fadeInUp">
        <h1><?php echo $hero ? htmlspecialchars($hero['section_title']) : 'Shaadi Partner'; ?></h1>
        <p class="hero-subtitle"><?php echo $hero ? htmlspecialchars($hero['section_subtitle']) : 'LOVE IS LOOKING FOR YOU'; ?></p>
        <?php if($hero && !empty($hero['section_content'])): ?>
        <p style="color: #fff; font-size: 16px; max-width: 600px; margin: 20px auto;"><?php echo htmlspecialchars($hero['section_content']); ?></p>
        <?php endif; ?>
        <a href="register.php" class="hero-cta">Create Profile</a>
    </div>
</section>

<!-- About Section -->
<?php if($about && $about['is_active'] == 1): ?>
<section class="about-section">
    <div class="container">
        <h2 class="section-title"><?php echo htmlspecialchars($about['section_title']); ?></h2>
        <div class="about-content">
            <div class="about-image">
                <?php 
                $about_img = 'images/wed.jpg';
                if(!empty($about['section_image']) && file_exists($about['section_image'])) {
                    $about_img = $about['section_image'];
                }
                ?>
                <img src="<?php echo $about_img; ?>" alt="About Us">
            </div>
            <div class="about-text">
                <h3><?php echo htmlspecialchars($about['section_subtitle']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($about['section_content'])); ?></p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Profiles Section -->
<section class="featured-section">
    <div class="container">
        <h2 class="section-title">Featured Profile</h2>
        <div class="profile-slider">
            <?php
            // Get featured profiles
            $sql = "SELECT * FROM customer ORDER BY cust_id DESC LIMIT 12";
            $result = mysqlexec($sql);
            if($result && mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $name = $row['firstname'] . " " . $row['lastname'];
                    $profileid = $row['cust_id'];
                    $age = $row['age'];
                    $place = $row['state'];
                    
                    // Get profile pic
                    $pic1 = '';
                    $sql2 = "SELECT pic1 FROM photos WHERE cust_id = $profileid";
                    $result2 = mysqlexec($sql2);
                    if($result2 && mysqli_num_rows($result2) > 0) {
                        $row2 = mysqli_fetch_array($result2);
                        $pic1 = $row2['pic1'] ?? '';
                    }
                    $profileImage = $pic1 ? "profile/{$profileid}/{$pic1}" : "images/avatar.jpg";
            ?>
            <div class="profile-card">
                <a href="view_profile.php?id=<?php echo $profileid; ?>" style="text-decoration: none;">
                    <img src="<?php echo $profileImage; ?>" alt="<?php echo htmlspecialchars($name); ?>" onerror="this.onerror=null; this.src='images/avatar.jpg'">
                    <div class="profile-info">
                        <div class="profile-name"><?php echo htmlspecialchars($name); ?></div>
                        <div class="profile-details"><?php echo $age; ?> Years</div>
                        <div class="profile-details"><?php echo htmlspecialchars($place); ?></div>
                    </div>
                </a>
            </div>
            <?php
                }
            } else {
                echo '<p style="text-align:center; padding: 40px; color: #666;">No profiles available yet. <a href="register.php">Be the first to register!</a></p>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Bride & Groom Section -->
<?php if($bride_groom && $bride_groom['is_active'] == 1): ?>
<section class="bride-groom-section">
    <div class="bg-decoration">
        <div class="bg-leaves"></div>
        <div class="container">
            <h2 class="section-title"><?php echo htmlspecialchars($bride_groom['section_title']); ?></h2>
            <div class="profile-slider">
                <?php
                // Get user's gender to show opposite gender profiles
                $bg_gender_filter = "";
                if(isset($_SESSION['id'])) {
                    $logged_user_id = $_SESSION['id'];
                    $bg_gender_sql = "SELECT sex FROM customer WHERE cust_id = $logged_user_id";
                    $bg_gender_result = mysqlexec($bg_gender_sql);
                    if($bg_gender_result && mysqli_num_rows($bg_gender_result) > 0) {
                        $bg_gender_row = mysqli_fetch_assoc($bg_gender_result);
                        $bg_user_gender = $bg_gender_row['sex'];
                        // Show opposite gender
                        $bg_opposite_gender = ($bg_user_gender == 'Male') ? 'Female' : 'Male';
                        $bg_gender_filter = "WHERE sex = '$bg_opposite_gender'";
                    }
                }
                $featured_sql = "SELECT * FROM customer $bg_gender_filter ORDER BY cust_id DESC LIMIT 12";
                $featured_result = mysqlexec($featured_sql);
                if($featured_result && mysqli_num_rows($featured_result) > 0) {
                    while($profile = mysqli_fetch_assoc($featured_result)) {
                        $name = $profile['firstname'] . " " . $profile['lastname'];
                        $profileid = $profile['cust_id'];
                        $age = $profile['age'];
                        $place = $profile['state'];
                        
                        // Get profile pic
                        $pic1 = '';
                        $pic_sql = "SELECT pic1 FROM photos WHERE cust_id = $profileid";
                        $pic_result = mysqlexec($pic_sql);
                        if($pic_result && mysqli_num_rows($pic_result) > 0) {
                            $pic_row = mysqli_fetch_array($pic_result);
                            $pic1 = $pic_row['pic1'] ?? '';
                        }
                        $profileImage = $pic1 ? "profile/{$profileid}/{$pic1}" : "images/avatar.jpg";
                ?>
                <div class="profile-card">
                    <a href="view_profile.php?id=<?php echo $profileid; ?>" style="text-decoration: none;">
                        <img src="<?php echo $profileImage; ?>" alt="<?php echo htmlspecialchars($name); ?>" onerror="this.onerror=null; this.src='images/avatar.jpg'">
                        <div class="profile-info">
                            <div class="profile-name"><?php echo htmlspecialchars($name); ?></div>
                            <div class="profile-details"><?php echo $age; ?> Years</div>
                            <div class="profile-details"><?php echo htmlspecialchars($place); ?></div>
                        </div>
                    </a>
                </div>
                <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Search Profiles By Section -->
<?php if($search_by && $search_by['is_active'] == 1): ?>
<section class="search-section">
    <div class="container">
        <h2 class="section-title"><?php echo htmlspecialchars($search_by['section_title']); ?></h2>
        
        <?php
        // Fetch search categories
        $categories_sql = "SELECT * FROM homepage_search_categories WHERE is_active = 1 ORDER BY category_type, category_order ASC";
        $categories_result = mysqli_query($conn, $categories_sql);
        $categories_by_type = ['location' => [], 'religion' => [], 'community' => []];
        if($categories_result) {
            while($cat = mysqli_fetch_assoc($categories_result)) {
                $categories_by_type[$cat['category_type']][] = $cat;
            }
        }
        ?>
        
        <!-- Location -->
        <?php if(!empty($categories_by_type['location'])): ?>
        <div class="search-category">
            <h3>
                <i class="fa fa-map-marker"></i>
                <span>Location</span>
            </h3>
            <div class="search-tags">
                <?php foreach($categories_by_type['location'] as $cat): ?>
                <a href="advanced-search.php?state=<?php echo urlencode($cat['category_value']); ?>" class="search-tag">
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Religion -->
        <?php if(!empty($categories_by_type['religion'])): ?>
        <div class="search-category">
            <h3>
                <i class="fa fa-book"></i>
                <span>Religion</span>
            </h3>
            <div class="search-tags">
                <?php foreach($categories_by_type['religion'] as $cat): ?>
                <a href="advanced-search.php?religion=<?php echo urlencode($cat['category_value']); ?>" class="search-tag">
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Community -->
        <?php if(!empty($categories_by_type['community'])): ?>
        <div class="search-category">
            <h3>
                <i class="fa fa-users"></i>
                <span>Community</span>
            </h3>
            <div class="search-tags">
                <?php foreach($categories_by_type['community'] as $cat): ?>
                <a href="advanced-search.php?caste=<?php echo urlencode($cat['category_value']); ?>" class="search-tag">
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Footer -->
<?php include("footer.php");?>

</body>
</html>
