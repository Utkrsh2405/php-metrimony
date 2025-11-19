<?php
session_start();
header('Content-Type: application/json');

require_once("../includes/dbconn.php");

$state_code = isset($_GET['state']) ? mysqli_real_escape_string($conn, $_GET['state']) : '';

if (empty($state_code)) {
    echo json_encode(['success' => false, 'error' => 'State code required']);
    exit();
}

$query = "SELECT DISTINCT city_name FROM cities 
          WHERE state_code = '$state_code' AND status = 1 
          ORDER BY is_major DESC, city_name ASC 
          LIMIT 200";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    exit();
}

$cities = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cities[] = [
        'city_name' => $row['city_name']
    ];
}

echo json_encode([
    'success' => true,
    'data' => $cities,
    'count' => count($cities)
]);
?>
