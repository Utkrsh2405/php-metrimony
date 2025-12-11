<?php
session_start();
require_once("includes/dbconn.php");
require_once("includes/basic_includes.php");
require_once("functions.php");

// Get search parameters
$mode = $_GET['mode'] ?? 'quick';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build search query based on parameters
$where_conditions = ["(u.account_status = 'active' OR u.account_status IS NULL)"];
$params = [];

// Gender filter
if (!empty($_GET['gender'])) {
    $gender = mysqli_real_escape_string($conn, $_GET['gender']);
    $where_conditions[] = "c.sex = '$gender'";
}

// Age filter
if (!empty($_GET['age_min'])) {
    $age_min = intval($_GET['age_min']);
    $where_conditions[] = "CAST(c.age AS UNSIGNED) >= $age_min";
}
if (!empty($_GET['age_max'])) {
    $age_max = intval($_GET['age_max']);
    $where_conditions[] = "CAST(c.age AS UNSIGNED) <= $age_max";
}

// Religion filter
if (!empty($_GET['religion'])) {
    $religion = mysqli_real_escape_string($conn, $_GET['religion']);
    $where_conditions[] = "c.religion = '$religion'";
}

// Caste filter
if (!empty($_GET['caste'])) {
    $caste = mysqli_real_escape_string($conn, $_GET['caste']);
    $where_conditions[] = "c.caste = '$caste'";
}

// Mother tongue filter
if (!empty($_GET['mother_tongue'])) {
    $mother_tongue = mysqli_real_escape_string($conn, $_GET['mother_tongue']);
    $where_conditions[] = "c.mothertounge = '$mother_tongue'";
}

// State filter
if (!empty($_GET['state'])) {
    $state = mysqli_real_escape_string($conn, $_GET['state']);
    $where_conditions[] = "c.state = '$state'";
}

// Marital status filter
if (!empty($_GET['marital_status'])) {
    $marital_status = mysqli_real_escape_string($conn, $_GET['marital_status']);
    $where_conditions[] = "c.maritalstatus = '$marital_status'";
}

// Education filter
if (!empty($_GET['education'])) {
    $education = mysqli_real_escape_string($conn, $_GET['education']);
    $where_conditions[] = "c.education LIKE '%$education%'";
}

// Occupation filter
if (!empty($_GET['occupation'])) {
    $occupation = mysqli_real_escape_string($conn, $_GET['occupation']);
    $where_conditions[] = "c.occupation LIKE '%$occupation%'";
}

// Build the WHERE clause
$where_clause = implode(' AND ', $where_conditions);

// Count total results
$count_sql = "SELECT COUNT(*) as total FROM customer c 
              LEFT JOIN users u ON c.cust_id = u.id 
              WHERE $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch results
$sql = "SELECT c.*, u.account_status, u.username 
        FROM customer c 
        LEFT JOIN users u ON c.cust_id = u.id 
        WHERE $where_clause 
        ORDER BY c.profilecreationdate DESC 
        LIMIT $offset, $per_page";
$result = mysqli_query($conn, $sql);

// Build query string for pagination (preserve search params)
$query_params = $_GET;
unset($query_params['page']);
$query_string = http_build_query($query_params);
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Search Result | <?php echo defined('SITE_NAME') ? SITE_NAME : 'Make My Love'; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<link href="css/style.css" rel='stylesheet' type='text/css' />
<link href="css/font-awesome.css" rel="stylesheet">
<style>
/* Search Result Header */
.search-header {
    background: linear-gradient(135deg, #8B4C4F 0%, #6B3234 100%);
    color: white;
    padding: 60px 0;
    text-align: center;
    position: relative;
}

.search-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('images/couple-bg.jpg') center/cover;
    opacity: 0.2;
}

.search-header h1 {
    position: relative;
    z-index: 1;
    margin: 0;
    font-size: 42px;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

/* Pagination Styles */
.pagination-container {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 5px;
    margin-bottom: 20px;
}

.pagination-container .page-info {
    background: #8B4C4F;
    color: white;
    padding: 8px 15px;
    border-radius: 4px;
    font-weight: 600;
    margin-right: 10px;
}

.pagination-container a, 
.pagination-container span {
    display: inline-block;
    padding: 8px 14px;
    background: #5a6268;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 500;
    transition: all 0.3s;
}

.pagination-container a:hover {
    background: #8B4C4F;
    color: white;
}

.pagination-container span.current {
    background: #dc3545;
    color: white;
}

.pagination-container a.next-btn,
.pagination-container a.last-btn {
    background: #28a745;
}

.pagination-container a.next-btn:hover,
.pagination-container a.last-btn:hover {
    background: #218838;
}

.total-records {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 25px;
}

/* Profile Card Styles */
.profile-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    transition: all 0.3s;
}

.profile-card:hover {
    box-shadow: 0 5px 25px rgba(0,0,0,0.1);
}

.profile-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.profile-name {
    font-size: 20px;
    font-weight: 700;
    color: #8B4C4F;
    margin: 0;
}

.profile-name .profile-id {
    font-size: 14px;
    color: #888;
    font-weight: normal;
    margin-left: 5px;
}

.btn-view-profile {
    background: #17a2b8;
    color: white;
    padding: 8px 20px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-view-profile:hover {
    background: #138496;
    color: white;
    text-decoration: none;
}

.profile-card-body {
    display: flex;
    gap: 25px;
}

.profile-photo {
    flex-shrink: 0;
    width: 180px;
    height: 220px;
    border-radius: 8px;
    overflow: hidden;
    background: #f5f5f5;
}

.profile-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-photo-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-size: 48px;
    font-weight: bold;
}

.profile-details {
    flex: 1;
}

.profile-detail-row {
    display: flex;
    margin-bottom: 10px;
    font-size: 15px;
}

.profile-detail-label {
    font-weight: 600;
    color: #555;
    min-width: 150px;
}

.profile-detail-value {
    color: #333;
}

.profile-about {
    margin-top: 10px;
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

.profile-card-footer {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 15px;
}

.btn-interest {
    background: transparent;
    border: none;
    color: #dc3545;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 8px 15px;
    transition: all 0.3s;
}

.btn-interest:hover {
    color: #c82333;
}

.btn-message {
    background: transparent;
    border: none;
    color: #28a745;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 8px 15px;
    transition: all 0.3s;
}

.btn-message:hover {
    color: #218838;
}

/* No Results */
.no-results {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.no-results i {
    font-size: 60px;
    color: #ccc;
    margin-bottom: 20px;
}

.no-results h3 {
    color: #666;
    margin-bottom: 10px;
}

.no-results p {
    color: #888;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-card-body {
        flex-direction: column;
    }
    
    .profile-photo {
        width: 100%;
        height: 250px;
    }
    
    .profile-detail-row {
        flex-direction: column;
    }
    
    .profile-detail-label {
        min-width: auto;
        margin-bottom: 2px;
    }
    
    .pagination-container {
        justify-content: center;
    }
}
</style>
</head>
<body>
<?php include_once("includes/navigation.php");?>

<!-- Search Result Header -->
<div class="search-header">
    <div class="container">
        <h1>Search Result</h1>
    </div>
</div>

<div class="container" style="padding: 40px 15px;">
    
    <!-- Pagination Top -->
    <?php if ($total_records > 0): ?>
    <div class="pagination-container">
        <span class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
        
        <?php
        // Calculate which pages to show
        $start_page = max(1, $page - 3);
        $end_page = min($total_pages, $page + 3);
        
        // First page
        if ($page > 1): ?>
            <a href="?<?php echo $query_string; ?>&page=1">1</a>
        <?php endif;
        
        // Show pages
        for ($i = $start_page; $i <= $end_page; $i++):
            if ($i == $page): ?>
                <span class="current"><?php echo $i; ?></span>
            <?php elseif ($i != 1 && $i != $total_pages): ?>
                <a href="?<?php echo $query_string; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif;
        endfor;
        
        // Show last pages
        if ($end_page < $total_pages - 1): ?>
            <span>...</span>
            <a href="?<?php echo $query_string; ?>&page=<?php echo $total_pages - 1; ?>"><?php echo $total_pages - 1; ?></a>
        <?php endif;
        
        if ($end_page < $total_pages): ?>
            <a href="?<?php echo $query_string; ?>&page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
        <?php endif;
        
        // Next and Last buttons
        if ($page < $total_pages): ?>
            <a href="?<?php echo $query_string; ?>&page=<?php echo $page + 1; ?>" class="next-btn">Next</a>
            <a href="?<?php echo $query_string; ?>&page=<?php echo $total_pages; ?>" class="last-btn">Last</a>
        <?php endif; ?>
    </div>
    
    <div class="total-records">
        <?php echo number_format($total_records); ?> Record(s) Found
    </div>
    <?php endif; ?>
    
    <!-- Search Results -->
    <?php if ($total_records > 0): ?>
        <?php while ($profile = mysqli_fetch_assoc($result)): 
            $profile_id = $profile['cust_id'];
            
            // Get profile photo
            $photo_sql = "SELECT pic1 FROM photos WHERE cust_id = $profile_id";
            $photo_result = mysqli_query($conn, $photo_sql);
            $photo = mysqli_fetch_assoc($photo_result);
            $profile_pic = $photo['pic1'] ?? '';
            
            // Format profile ID
            $formatted_id = 'SP' . str_pad($profile_id, 6, '0', STR_PAD_LEFT);
        ?>
        <div class="profile-card">
            <div class="profile-card-header">
                <h3 class="profile-name">
                    <?php echo htmlspecialchars($profile['firstname'] . ' ' . $profile['lastname']); ?>
                    <span class="profile-id">( <?php echo $formatted_id; ?> )</span>
                </h3>
                <a href="view_profile.php?id=<?php echo $profile_id; ?>" class="btn-view-profile">View Profile</a>
            </div>
            
            <div class="profile-card-body">
                <div class="profile-photo">
                    <?php if ($profile_pic && file_exists("profile/$profile_id/$profile_pic")): ?>
                        <img src="profile/<?php echo $profile_id; ?>/<?php echo $profile_pic; ?>" alt="<?php echo htmlspecialchars($profile['firstname']); ?>">
                    <?php else: ?>
                        <div class="profile-photo-placeholder">
                            <?php echo strtoupper(substr($profile['firstname'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="profile-details">
                    <div class="profile-detail-row">
                        <span class="profile-detail-label">Age :</span>
                        <span class="profile-detail-value"><?php echo $profile['age']; ?> Years</span>
                    </div>
                    
                    <div class="profile-detail-row">
                        <span class="profile-detail-label">Caste/Religion :</span>
                        <span class="profile-detail-value"><?php echo htmlspecialchars($profile['caste'] . ' / ' . $profile['religion']); ?></span>
                    </div>
                    
                    <div class="profile-detail-row">
                        <span class="profile-detail-label">Mother Tongue :</span>
                        <span class="profile-detail-value"><?php echo htmlspecialchars($profile['mothertounge']); ?></span>
                    </div>
                    
                    <div class="profile-detail-row">
                        <span class="profile-detail-label">Education :</span>
                        <span class="profile-detail-value"><?php echo htmlspecialchars($profile['education']); ?></span>
                    </div>
                    
                    <div class="profile-detail-row">
                        <span class="profile-detail-label">Occupation :</span>
                        <span class="profile-detail-value"><?php echo htmlspecialchars($profile['occupation']); ?></span>
                    </div>
                    
                    <div class="profile-detail-row">
                        <span class="profile-detail-label">Location :</span>
                        <span class="profile-detail-value"><?php echo htmlspecialchars($profile['district'] . ' ' . $profile['state']); ?></span>
                    </div>
                    
                    <?php if (!empty($profile['aboutme'])): ?>
                    <div class="profile-about">
                        <?php echo htmlspecialchars(substr($profile['aboutme'], 0, 150)); ?><?php echo strlen($profile['aboutme']) > 150 ? '...' : ''; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="profile-card-footer">
                <?php if (isset($_SESSION['id']) && $_SESSION['id'] != $profile_id): ?>
                <button class="btn-interest" onclick="sendInterest(<?php echo $profile_id; ?>)">
                    <i class="fa fa-heart"></i> Send Interest
                </button>
                <button class="btn-message" onclick="sendMessage(<?php echo $profile_id; ?>)">
                    <i class="fa fa-envelope"></i> Send Message
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
        
        <!-- Pagination Bottom -->
        <div class="pagination-container" style="margin-top: 30px;">
            <span class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            
            <?php
            // First page
            if ($page > 1): ?>
                <a href="?<?php echo $query_string; ?>&page=1">1</a>
            <?php endif;
            
            // Show pages
            for ($i = $start_page; $i <= $end_page; $i++):
                if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php elseif ($i != 1 && $i != $total_pages): ?>
                    <a href="?<?php echo $query_string; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif;
            endfor;
            
            // Show last pages
            if ($end_page < $total_pages - 1): ?>
                <span>...</span>
                <a href="?<?php echo $query_string; ?>&page=<?php echo $total_pages - 1; ?>"><?php echo $total_pages - 1; ?></a>
            <?php endif;
            
            if ($end_page < $total_pages): ?>
                <a href="?<?php echo $query_string; ?>&page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
            <?php endif;
            
            // Next and Last buttons
            if ($page < $total_pages): ?>
                <a href="?<?php echo $query_string; ?>&page=<?php echo $page + 1; ?>" class="next-btn">Next</a>
                <a href="?<?php echo $query_string; ?>&page=<?php echo $total_pages; ?>" class="last-btn">Last</a>
            <?php endif; ?>
        </div>
        
    <?php else: ?>
        <!-- No Results -->
        <div class="no-results">
            <i class="fa fa-search"></i>
            <h3>No Profiles Found</h3>
            <p>Try adjusting your search criteria to find more matches.</p>
            <a href="quick-search.php" class="btn btn-primary" style="margin-top: 20px;">
                <i class="fa fa-search"></i> New Search
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
function sendInterest(profileId) {
    <?php if (!isset($_SESSION['id'])): ?>
    window.location.href = 'login.php';
    return;
    <?php endif; ?>
    
    $.ajax({
        url: 'api/interest.php',
        method: 'POST',
        data: { receiver_id: profileId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Interest sent successfully!');
            } else {
                alert(response.message || 'Failed to send interest');
            }
        },
        error: function() {
            alert('Error sending interest. Please try again.');
        }
    });
}

function sendMessage(profileId) {
    <?php if (!isset($_SESSION['id'])): ?>
    window.location.href = 'login.php';
    return;
    <?php endif; ?>
    
    window.location.href = 'messages.php?to=' + profileId;
}
</script>

<?php include_once("footer.php");?>
</body>
</html>
