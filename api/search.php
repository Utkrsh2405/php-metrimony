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
    
    // Build search query
    $query = "SELECT c.id, c.firstname, c.lastname, c.gender, c.age, c.height, 
              c.religion, c.marital_status, c.education, c.occupation, c.location,
              c.verified, u.plan_id
              FROM customer c
              LEFT JOIN users u ON c.id = u.id
              WHERE c.id != $user_id AND u.userlevel = 0";
    
    $params = [];
    
    // Apply filters
    if (!empty($input['gender'])) {
        $gender = mysqli_real_escape_string($conn, $input['gender']);
        $query .= " AND c.gender = '$gender'";
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
        $query .= " AND c.marital_status = '$marital_status'";
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
        $query .= " AND (c.location LIKE '%$location%' OR c.city LIKE '%$location%' OR c.state LIKE '%$location%')";
    }
    
    if (!empty($input['verified_only']) && $input['verified_only'] == 1) {
        $query .= " AND c.verified = 1";
    }
    
    if (!empty($input['with_photo']) && $input['with_photo'] == 1) {
        $query .= " AND c.profile_pic IS NOT NULL AND c.profile_pic != ''";
    }
    
    $query .= " ORDER BY c.verified DESC, u.plan_id DESC, c.created_at DESC LIMIT 50";
    
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
