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
    // Save new search
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['name'])) {
        echo json_encode(['success' => false, 'error' => 'Search name is required']);
        exit();
    }
    
    $search_name = mysqli_real_escape_string($conn, $input['name']);
    $filters_json = json_encode($input['filters']);
    $filters_json_escaped = mysqli_real_escape_string($conn, $filters_json);
    $is_default = !empty($input['is_default']) ? 1 : 0;
    
    // If setting as default, unset other defaults first
    if ($is_default) {
        mysqli_query($conn, "UPDATE saved_searches SET is_default = 0 WHERE user_id = $user_id");
    }
    
    $query = "INSERT INTO saved_searches (user_id, search_name, search_filters, is_default) 
              VALUES ($user_id, '$search_name', '$filters_json_escaped', $is_default)";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode([
            'success' => true,
            'message' => 'Search saved successfully',
            'id' => mysqli_insert_id($conn)
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
}

elseif ($method === 'GET') {
    // Get saved searches
    $result = mysqli_query($conn, "SELECT * FROM saved_searches 
                                   WHERE user_id = $user_id 
                                   ORDER BY is_default DESC, search_name");
    
    $searches = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $searches[] = [
            'id' => $row['id'],
            'name' => $row['search_name'],
            'filters' => json_decode($row['search_filters'], true),
            'is_default' => $row['is_default'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $searches]);
}

elseif ($method === 'PUT') {
    // Update saved search
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int)$_GET['id'];
    
    // Verify ownership
    $check = mysqli_query($conn, "SELECT id FROM saved_searches WHERE id = $id AND user_id = $user_id");
    if (mysqli_num_rows($check) == 0) {
        echo json_encode(['success' => false, 'error' => 'Search not found']);
        exit();
    }
    
    $updates = [];
    
    if (isset($input['name'])) {
        $name = mysqli_real_escape_string($conn, $input['name']);
        $updates[] = "search_name = '$name'";
    }
    
    if (isset($input['filters'])) {
        $filters_json = json_encode($input['filters']);
        $filters_json_escaped = mysqli_real_escape_string($conn, $filters_json);
        $updates[] = "search_filters = '$filters_json_escaped'";
    }
    
    if (isset($input['is_default'])) {
        $is_default = $input['is_default'] ? 1 : 0;
        if ($is_default) {
            mysqli_query($conn, "UPDATE saved_searches SET is_default = 0 WHERE user_id = $user_id");
        }
        $updates[] = "is_default = $is_default";
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        exit();
    }
    
    $query = "UPDATE saved_searches SET " . implode(', ', $updates) . " WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Search updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
}

elseif ($method === 'DELETE') {
    // Delete saved search
    $id = (int)$_GET['id'];
    
    // Verify ownership
    $check = mysqli_query($conn, "SELECT id FROM saved_searches WHERE id = $id AND user_id = $user_id");
    if (mysqli_num_rows($check) == 0) {
        echo json_encode(['success' => false, 'error' => 'Search not found']);
        exit();
    }
    
    if (mysqli_query($conn, "DELETE FROM saved_searches WHERE id = $id")) {
        echo json_encode(['success' => true, 'message' => 'Search deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    }
}

else {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
}
?>
