<?php
session_start();
header('Content-Type: application/json');

require_once("../includes/dbconn.php");

$religion = isset($_GET['religion']) ? mysqli_real_escape_string($conn, $_GET['religion']) : '';

if (empty($religion)) {
    echo json_encode(['success' => false, 'error' => 'Religion required']);
    exit();
}

$query = "SELECT DISTINCT caste_name, category, region FROM castes 
          WHERE religion = '$religion' AND status = 1 
          ORDER BY caste_name ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    exit();
}

$castes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $label = $row['caste_name'];
    if ($row['category']) {
        $label .= ' (' . $row['category'] . ')';
    }
    $castes[] = [
        'caste_name' => $row['caste_name'],
        'label' => $label,
        'category' => $row['category'],
        'region' => $row['region']
    ];
}

echo json_encode([
    'success' => true,
    'data' => $castes,
    'count' => count($castes)
]);
?>
