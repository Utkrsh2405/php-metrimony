<?php
session_start();
require_once("../../includes/dbconn.php");
require_once("../../includes/security.php");

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || $_SESSION['userlevel'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - Retrieve categories
if ($method == 'GET') {
    if (isset($_GET['id'])) {
        // Get specific category
        $id = intval($_GET['id']);
        $sql = "SELECT * FROM homepage_search_categories WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Category not found']);
        }
    } else if (isset($_GET['type'])) {
        // Get categories by type
        $type = Security::sanitizeInput($_GET['type']);
        $sql = "SELECT * FROM homepage_search_categories WHERE category_type = ? ORDER BY category_order ASC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $type);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $categories]);
    } else {
        // Get all categories
        $sql = "SELECT * FROM homepage_search_categories ORDER BY category_type, category_order ASC";
        $result = mysqli_query($conn, $sql);
        
        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $categories]);
    }
}

// POST - Create or Update category
else if ($method == 'POST') {
    $id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : null;
    $category_type = Security::sanitizeInput($_POST['category_type'] ?? '');
    $category_name = Security::sanitizeInput($_POST['category_name'] ?? '');
    $category_value = Security::sanitizeInput($_POST['category_value'] ?? '');
    $category_order = intval($_POST['category_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($category_type) || empty($category_name) || empty($category_value)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    if ($id) {
        // Update existing category
        $update_sql = "UPDATE homepage_search_categories SET 
                       category_type = ?, 
                       category_name = ?, 
                       category_value = ?, 
                       category_order = ?, 
                       is_active = ?,
                       updated_at = NOW()
                       WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "sssiii", $category_type, $category_name, $category_value, $category_order, $is_active, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update category']);
        }
    } else {
        // Insert new category
        $insert_sql = "INSERT INTO homepage_search_categories (category_type, category_name, category_value, category_order, is_active) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "sssii", $category_type, $category_name, $category_value, $category_order, $is_active);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Category created successfully', 'id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create category']);
        }
    }
}

// DELETE - Delete category
else if ($method == 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = intval($_DELETE['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
        exit();
    }
    
    $sql = "DELETE FROM homepage_search_categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
