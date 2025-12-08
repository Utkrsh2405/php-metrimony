<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once("../includes/dbconn.php");

$user_id = $_SESSION['id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Perform search
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Build search query - exclude deleted and suspended users
    $query = "SELECT c.cust_id as id, c.firstname, c.lastname, c.sex as gender, c.age, c.height, 
              c.religion, c.maritalstatus as marital_status, c.education, c.occupation, 
              CONCAT(c.district, ', ', c.state) as location,
              c.mothertounge, c.caste, c.subcaste, c.country,
              u.username, u.profilestat
              FROM customer c
              LEFT JOIN users u ON c.cust_id = u.id
              WHERE c.cust_id != $user_id 
              AND (u.userlevel = 0 OR u.userlevel IS NULL)
              AND (u.account_status = 'active' OR u.account_status IS NULL)";
    
    $params = [];
    
    // Get logged-in user's gender to auto-filter for opposite gender
    $user_gender_query = mysqli_query($conn, "SELECT c.sex FROM customer c WHERE c.cust_id = $user_id");
    $user_gender_data = mysqli_fetch_assoc($user_gender_query);
    $user_gender = $user_gender_data['sex'] ?? null;
    
    // Apply gender filter - if user specifies a gender, use that; otherwise show opposite gender
    if (!empty($input['gender'])) {
        $gender = mysqli_real_escape_string($conn, $input['gender']);
        $query .= " AND c.sex = '$gender'";
    } elseif ($user_gender) {
        // Auto-filter: Brides see grooms, Grooms see brides
        $opposite_gender = ($user_gender == 'Male') ? 'Female' : 'Male';
        $query .= " AND c.sex = '$opposite_gender'";
    }
    
    if (!empty($input['age_min'])) {
        $age_min = (int)$input['age_min'];
        $query .= " AND c.age >= $age_min";
    }
    
    if (!empty($input['age_max'])) {
        $age_max = (int)$input['age_max'];
        $query .= " AND c.age <= $age_max";
    }
    
    if (!empty($input['marital_status'])) {
        $marital_status = mysqli_real_escape_string($conn, $input['marital_status']);
        $query .= " AND c.maritalstatus = '$marital_status'";
    }
    
    if (!empty($input['religion'])) {
        $religion = mysqli_real_escape_string($conn, $input['religion']);
        $query .= " AND c.religion = '$religion'";
    }
    
    if (!empty($input['height_min'])) {
        $height_min = (int)$input['height_min'];
        $query .= " AND c.height >= $height_min";
    }
    
    if (!empty($input['height_max'])) {
        $height_max = (int)$input['height_max'];
        $query .= " AND c.height <= $height_max";
    }
    
    if (!empty($input['education'])) {
        $education = mysqli_real_escape_string($conn, $input['education']);
        $query .= " AND c.education = '$education'";
    }
    
    if (!empty($input['occupation'])) {
        $occupation = mysqli_real_escape_string($conn, $input['occupation']);
        $query .= " AND c.occupation LIKE '%$occupation%'";
    }
    
    if (!empty($input['income_min'])) {
        $income_min = (int)$input['income_min'];
        $query .= " AND c.annual_income >= $income_min";
    }
    
    if (!empty($input['location'])) {
        $location = mysqli_real_escape_string($conn, $input['location']);
        $query .= " AND (c.district LIKE '%$location%' OR c.state LIKE '%$location%')";
    }
    
    if (!empty($input['state'])) {
        $state = mysqli_real_escape_string($conn, $input['state']);
        $query .= " AND c.state = '$state'";
    }
    
    if (!empty($input['city'])) {
        $city = mysqli_real_escape_string($conn, $input['city']);
        $query .= " AND c.district = '$city'";
    }
    
    if (!empty($input['caste'])) {
        $caste = mysqli_real_escape_string($conn, $input['caste']);
        $query .= " AND c.caste = '$caste'";
    }
    
    if (!empty($input['mother_tongue'])) {
        $mother_tongue = mysqli_real_escape_string($conn, $input['mother_tongue']);
        $query .= " AND c.mothertounge = '$mother_tongue'";
    }
    
    if (!empty($input['country'])) {
        $country = mysqli_real_escape_string($conn, $input['country']);
        $query .= " AND c.country = '$country'";
    }
    
    // Keyword search - search across multiple fields
    if (!empty($input['keyword'])) {
        $keyword = mysqli_real_escape_string($conn, $input['keyword']);
        $query .= " AND (
            c.firstname LIKE '%$keyword%' OR 
            c.lastname LIKE '%$keyword%' OR 
            c.religion LIKE '%$keyword%' OR 
            c.caste LIKE '%$keyword%' OR 
            c.occupation LIKE '%$keyword%' OR 
            c.education LIKE '%$keyword%' OR 
            c.district LIKE '%$keyword%' OR 
            c.state LIKE '%$keyword%' OR 
            c.mothertounge LIKE '%$keyword%' OR 
            c.aboutme LIKE '%$keyword%'
        )";
    }
    
    if (!empty($input['verified_only']) && $input['verified_only'] == 1) {
        $query .= " AND c.is_verified = 1";
    }
    
    /*
    if (!empty($input['with_photo']) && $input['with_photo'] == 1) {
        $query .= " AND c.profile_pic IS NOT NULL AND c.profile_pic != ''";
    }
    
    if (!empty($input['with_horoscope']) && $input['with_horoscope'] == 1) {
        $query .= " AND c.horoscope IS NOT NULL AND c.horoscope != ''";
    }
    */
    
    // Handle complexion filter
    if (!empty($input['complexion'])) {
        $complexion = mysqli_real_escape_string($conn, $input['complexion']);
        $query .= " AND c.colour = '$complexion'";
    }
    
    /*
    // Handle manglik filter
    if (!empty($input['manglik'])) {
        $manglik = mysqli_real_escape_string($conn, $input['manglik']);
        $query .= " AND c.manglik = '$manglik'";
    }
    */
    
    // Handle physical status filter
    if (!empty($input['physical_status'])) {
        $physical_status = mysqli_real_escape_string($conn, $input['physical_status']);
        $query .= " AND c.physical_status = '$physical_status'";
    }
    
    // Handle eating habits filter
    if (!empty($input['eating_habits'])) {
        $eating_habits = mysqli_real_escape_string($conn, $input['eating_habits']);
        $query .= " AND c.diet = '$eating_habits'";
    }
    
    /*
    // Handle has_children filter
    if (!empty($input['has_children'])) {
        $has_children = mysqli_real_escape_string($conn, $input['has_children']);
        $query .= " AND c.has_children = '$has_children'";
    }
    */
    
    $query .= " ORDER BY c.is_verified DESC, u.plan_id DESC, c.profilecreationdate DESC LIMIT 50";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        exit();
    }
    
    $profiles = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $profiles[] = [
            'id' => $row['id'],
            'name' => $row['firstname'] . ' ' . $row['lastname'],
            'gender' => $row['gender'],
            'age' => $row['age'],
            'height' => $row['height'],
            'religion' => $row['religion'],
            'marital_status' => $row['marital_status'],
            'education' => $row['education'],
            'occupation' => $row['occupation'],
            'location' => $row['location'],
            'verified' => $row['verified'],
            'plan_id' => $row['plan_id']
        ];
    }
    
    // Save to search history
    $filters_json = json_encode($input);
    $filters_json_escaped = mysqli_real_escape_string($conn, $filters_json);
    $count = count($profiles);
    mysqli_query($conn, "INSERT INTO search_history (user_id, search_filters, results_count) 
                         VALUES ($user_id, '$filters_json_escaped', $count)");
    
    // Update last search date
    mysqli_query($conn, "UPDATE users SET last_search_date = NOW() WHERE id = $user_id");
    
    echo json_encode([
        'success' => true,
        'data' => $profiles,
        'count' => $count
    ]);
}

else {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
}
?>
