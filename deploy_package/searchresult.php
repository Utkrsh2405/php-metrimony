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
// Allow profiles where account_status is active, NULL, or empty (for newly created profiles)
$where_conditions = ["(u.account_status = 'active' OR u.account_status IS NULL OR u.account_status = '')"];
$where_conditions[] = "c.cust_id > 0"; // Ensure valid profile
$where_conditions[] = "(c.firstname IS NOT NULL AND c.firstname != '')"; // Must have a name

// Gender filter - handle Groom/Bride mapping
if (!empty($_GET['gender'])) {
    $gender_input = trim($_GET['gender']);
    // Map Groom to Male and Bride to Female
    if (strtolower($gender_input) == 'groom') {
        $gender = 'Male';
    } elseif (strtolower($gender_input) == 'bride') {
        $gender = 'Female';
    } else {
        $gender = mysqli_real_escape_string($conn, $gender_input);
    }
    $where_conditions[] = "LOWER(TRIM(c.sex)) = LOWER('$gender')";
}

// Age filter - precise range with validation
if (!empty($_GET['age_min'])) {
    $age_min = intval($_GET['age_min']);
    if ($age_min >= 18 && $age_min <= 80) {
        $where_conditions[] = "(CAST(c.age AS UNSIGNED) >= $age_min OR (c.dateofbirth IS NOT NULL AND c.dateofbirth != '0000-00-00' AND TIMESTAMPDIFF(YEAR, c.dateofbirth, CURDATE()) >= $age_min))";
    }
}
if (!empty($_GET['age_max'])) {
    $age_max = intval($_GET['age_max']);
    if ($age_max >= 18 && $age_max <= 80) {
        $where_conditions[] = "(CAST(c.age AS UNSIGNED) <= $age_max OR (c.dateofbirth IS NOT NULL AND c.dateofbirth != '0000-00-00' AND TIMESTAMPDIFF(YEAR, c.dateofbirth, CURDATE()) <= $age_max))";
    }
}

// Religion filter - exact match, case-insensitive
if (!empty($_GET['religion'])) {
    $religion = mysqli_real_escape_string($conn, trim($_GET['religion']));
    $where_conditions[] = "LOWER(TRIM(c.religion)) = LOWER('$religion')";
}

// Caste filter - exact match or contains for sub-castes
if (!empty($_GET['caste'])) {
    $caste = mysqli_real_escape_string($conn, trim($_GET['caste']));
    $where_conditions[] = "(LOWER(TRIM(c.caste)) = LOWER('$caste') OR LOWER(c.caste) LIKE LOWER('%$caste%'))";
}

// Sub-caste filter
if (!empty($_GET['subcaste'])) {
    $subcaste = mysqli_real_escape_string($conn, trim($_GET['subcaste']));
    $where_conditions[] = "LOWER(c.subcaste) LIKE LOWER('%$subcaste%')";
}

// Mother tongue filter - exact match, case-insensitive
if (!empty($_GET['mother_tongue'])) {
    $mother_tongue = mysqli_real_escape_string($conn, trim($_GET['mother_tongue']));
    $where_conditions[] = "LOWER(TRIM(c.mothertounge)) = LOWER('$mother_tongue')";
}

// Country filter
if (!empty($_GET['country'])) {
    $country = mysqli_real_escape_string($conn, trim($_GET['country']));
    $where_conditions[] = "LOWER(TRIM(c.country)) = LOWER('$country')";
}

// State filter - exact match
if (!empty($_GET['state'])) {
    $state = mysqli_real_escape_string($conn, trim($_GET['state']));
    $where_conditions[] = "LOWER(TRIM(c.state)) = LOWER('$state')";
}

// District/City filter
if (!empty($_GET['district'])) {
    $district = mysqli_real_escape_string($conn, trim($_GET['district']));
    $where_conditions[] = "LOWER(c.district) LIKE LOWER('%$district%')";
}

// Marital status filter - exact match
if (!empty($_GET['marital_status'])) {
    $marital_status = mysqli_real_escape_string($conn, trim($_GET['marital_status']));
    $where_conditions[] = "LOWER(TRIM(c.maritalstatus)) = LOWER('$marital_status')";
}

// Education filter - flexible matching
if (!empty($_GET['education'])) {
    $education = mysqli_real_escape_string($conn, trim($_GET['education']));
    // Match exact or contains for education levels
    $where_conditions[] = "(LOWER(c.education) = LOWER('$education') OR LOWER(c.education) LIKE LOWER('%$education%') OR LOWER(c.education_sub) LIKE LOWER('%$education%'))";
}

// Occupation filter - flexible matching
if (!empty($_GET['occupation'])) {
    $occupation = mysqli_real_escape_string($conn, trim($_GET['occupation']));
    $where_conditions[] = "(LOWER(c.occupation) LIKE LOWER('%$occupation%') OR LOWER(c.occupation_descr) LIKE LOWER('%$occupation%'))";
}

// Annual income filter - range
if (!empty($_GET['income_min'])) {
    $income_min = mysqli_real_escape_string($conn, trim($_GET['income_min']));
    $where_conditions[] = "CAST(REPLACE(REPLACE(c.annual_income, ',', ''), ' ', '') AS UNSIGNED) >= '$income_min'";
}
if (!empty($_GET['income_max'])) {
    $income_max = mysqli_real_escape_string($conn, trim($_GET['income_max']));
    $where_conditions[] = "CAST(REPLACE(REPLACE(c.annual_income, ',', ''), ' ', '') AS UNSIGNED) <= '$income_max'";
}

// Height filter - range (in cm)
if (!empty($_GET['height_min'])) {
    $height_min = intval($_GET['height_min']);
    $where_conditions[] = "CAST(c.height AS UNSIGNED) >= $height_min";
}
if (!empty($_GET['height_max'])) {
    $height_max = intval($_GET['height_max']);
    $where_conditions[] = "CAST(c.height AS UNSIGNED) <= $height_max";
}

// Body type filter
if (!empty($_GET['body_type'])) {
    $body_type = mysqli_real_escape_string($conn, trim($_GET['body_type']));
    $where_conditions[] = "LOWER(TRIM(c.body_type)) = LOWER('$body_type')";
}

// Complexion/Color filter
if (!empty($_GET['complexion'])) {
    $complexion = mysqli_real_escape_string($conn, trim($_GET['complexion']));
    $where_conditions[] = "LOWER(TRIM(c.colour)) = LOWER('$complexion')";
}

// Diet filter
if (!empty($_GET['diet'])) {
    $diet = mysqli_real_escape_string($conn, trim($_GET['diet']));
    $where_conditions[] = "LOWER(TRIM(c.diet)) = LOWER('$diet')";
}

// Smoking filter
if (!empty($_GET['smoke'])) {
    $smoke = mysqli_real_escape_string($conn, trim($_GET['smoke']));
    $where_conditions[] = "LOWER(TRIM(c.smoke)) = LOWER('$smoke')";
}

// Drinking filter
if (!empty($_GET['drink'])) {
    $drink = mysqli_real_escape_string($conn, trim($_GET['drink']));
    $where_conditions[] = "LOWER(TRIM(c.drink)) = LOWER('$drink')";
}

// Physical status filter
if (!empty($_GET['physical_status'])) {
    $physical_status = mysqli_real_escape_string($conn, trim($_GET['physical_status']));
    $where_conditions[] = "LOWER(TRIM(c.physical_status)) = LOWER('$physical_status')";
}

// Profile with photo filter
if (!empty($_GET['with_photo']) && $_GET['with_photo'] == '1') {
    $where_conditions[] = "EXISTS (SELECT 1 FROM photos p WHERE p.cust_id = c.cust_id AND (p.pic1 IS NOT NULL AND p.pic1 != ''))";
}

// Profile ID search (exact match)
if (!empty($_GET['profile_id'])) {
    $profile_id = mysqli_real_escape_string($conn, trim($_GET['profile_id']));
    // Remove SP prefix if present
    $profile_id = preg_replace('/^SP/i', '', $profile_id);
    $profile_id = intval($profile_id);
    if ($profile_id > 0) {
        $where_conditions[] = "c.cust_id = $profile_id";
    }
}

// Keyword search - search across multiple fields
if (!empty($_GET['keyword'])) {
    $keyword = mysqli_real_escape_string($conn, trim($_GET['keyword']));
    $where_conditions[] = "(
        LOWER(c.firstname) LIKE LOWER('%$keyword%') OR 
        LOWER(c.lastname) LIKE LOWER('%$keyword%') OR 
        LOWER(c.aboutme) LIKE LOWER('%$keyword%') OR 
        LOWER(c.occupation) LIKE LOWER('%$keyword%') OR 
        LOWER(c.education) LIKE LOWER('%$keyword%') OR
        LOWER(c.district) LIKE LOWER('%$keyword%') OR
        LOWER(c.state) LIKE LOWER('%$keyword%')
    )";
}

// Exclude own profile if logged in
if (isset($_SESSION['id'])) {
    $current_user = intval($_SESSION['id']);
    $where_conditions[] = "c.cust_id != $current_user";
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

// Determine sort order
$sort_by = $_GET['sort'] ?? 'newest';
$order_clause = "c.profilecreationdate DESC"; // Default: newest first

switch ($sort_by) {
    case 'newest':
        $order_clause = "c.profilecreationdate DESC";
        break;
    case 'oldest':
        $order_clause = "c.profilecreationdate ASC";
        break;
    case 'age_asc':
        $order_clause = "CAST(c.age AS UNSIGNED) ASC";
        break;
    case 'age_desc':
        $order_clause = "CAST(c.age AS UNSIGNED) DESC";
        break;
    case 'relevance':
        // Profiles with photos first, then by date
        $order_clause = "(SELECT COUNT(*) FROM photos p WHERE p.cust_id = c.cust_id AND p.pic1 IS NOT NULL) DESC, c.profilecreationdate DESC";
        break;
}

// Fetch results
$sql = "SELECT c.*, u.account_status, u.username 
        FROM customer c 
        LEFT JOIN users u ON c.cust_id = u.id 
        WHERE $where_clause 
        ORDER BY $order_clause 
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

/* Search Controls */
.search-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
    padding: 15px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.search-controls .total-count {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.search-controls .sort-filter {
    display: flex;
    align-items: center;
    gap: 10px;
}

.search-controls .sort-filter label {
    font-weight: 600;
    color: #555;
    margin: 0;
}

.search-controls .sort-filter select {
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
}

/* Active Filters */
.active-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 20px;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #e9ecef;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    color: #495057;
}

.filter-tag strong {
    color: #8B4C4F;
}

.filter-tag .remove-filter {
    background: #dc3545;
    color: white;
    border: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    font-size: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 5px;
}

.filter-tag .remove-filter:hover {
    background: #c82333;
}

.clear-all-filters {
    background: #6c757d;
    color: white;
    border: none;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
}

.clear-all-filters:hover {
    background: #545b62;
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
    
    <?php if ($total_records > 0): ?>
    <!-- Search Controls: Count and Sort -->
    <div class="search-controls">
        <div class="total-count">
            <i class="fa fa-users"></i> <?php echo number_format($total_records); ?> Record(s) Found
        </div>
        <div class="sort-filter">
            <label for="sort-select">Sort by:</label>
            <select id="sort-select" onchange="changeSortOrder(this.value)">
                <option value="newest" <?php echo ($sort_by == 'newest') ? 'selected' : ''; ?>>Newest First</option>
                <option value="oldest" <?php echo ($sort_by == 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                <option value="age_asc" <?php echo ($sort_by == 'age_asc') ? 'selected' : ''; ?>>Age: Low to High</option>
                <option value="age_desc" <?php echo ($sort_by == 'age_desc') ? 'selected' : ''; ?>>Age: High to Low</option>
                <option value="relevance" <?php echo ($sort_by == 'relevance') ? 'selected' : ''; ?>>Relevance (with Photo)</option>
            </select>
        </div>
    </div>
    
    <!-- Active Filters Display -->
    <?php
    $active_filters = [];
    $filter_labels = [
        'gender' => 'Gender',
        'age_min' => 'Min Age',
        'age_max' => 'Max Age',
        'religion' => 'Religion',
        'caste' => 'Caste',
        'mother_tongue' => 'Mother Tongue',
        'state' => 'State',
        'district' => 'City/District',
        'country' => 'Country',
        'marital_status' => 'Marital Status',
        'education' => 'Education',
        'occupation' => 'Occupation',
        'height_min' => 'Min Height',
        'height_max' => 'Max Height',
        'body_type' => 'Body Type',
        'complexion' => 'Complexion',
        'diet' => 'Diet',
        'smoke' => 'Smoking',
        'drink' => 'Drinking',
        'with_photo' => 'With Photo',
        'keyword' => 'Keyword'
    ];
    
    foreach ($filter_labels as $key => $label) {
        if (!empty($_GET[$key])) {
            $value = $_GET[$key];
            if ($key == 'with_photo' && $value == '1') {
                $value = 'Yes';
            }
            $active_filters[$key] = ['label' => $label, 'value' => $value];
        }
    }
    
    if (!empty($active_filters)): ?>
    <div class="active-filters">
        <span style="font-weight: 600; color: #555; margin-right: 10px;">Active Filters:</span>
        <?php foreach ($active_filters as $key => $filter): ?>
        <span class="filter-tag">
            <strong><?php echo $filter['label']; ?>:</strong> <?php echo htmlspecialchars($filter['value']); ?>
            <button class="remove-filter" onclick="removeFilter('<?php echo $key; ?>')" title="Remove filter">×</button>
        </span>
        <?php endforeach; ?>
        <button class="clear-all-filters" onclick="clearAllFilters()">
            <i class="fa fa-times"></i> Clear All
        </button>
    </div>
    <?php endif; ?>
    
    <!-- Pagination Top -->
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

// Change sort order
function changeSortOrder(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortValue);
    url.searchParams.delete('page'); // Reset to first page when sorting changes
    window.location.href = url.toString();
}

// Remove a single filter
function removeFilter(filterKey) {
    const url = new URL(window.location.href);
    url.searchParams.delete(filterKey);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}

// Clear all filters
function clearAllFilters() {
    const url = new URL(window.location.href);
    const mode = url.searchParams.get('mode');
    const sort = url.searchParams.get('sort');
    
    // Clear all params
    url.search = '';
    
    // Keep mode and sort if they exist
    if (mode) url.searchParams.set('mode', mode);
    if (sort) url.searchParams.set('sort', sort);
    
    window.location.href = url.toString();
}
</script>

<?php include_once("footer.php");?>
</body>
</html>