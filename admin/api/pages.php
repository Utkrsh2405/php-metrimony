<?php
// Admin CMS Pages API
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once("../../includes/dbconn.php");

$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - List pages or get single page
if ($method == 'GET') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $query = "SELECT * FROM cms_pages WHERE id = $id";
        $result = mysqli_query($conn, $query);
        
        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['error' => 'Page not found']);
        }
    } else {
        $status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        
        $where = ["1=1"];
        if ($status) $where[] = "status = '$status'";
        if ($search) $where[] = "(title LIKE '%$search%' OR content LIKE '%$search%')";
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT p.*, u.username as author 
                  FROM cms_pages p
                  LEFT JOIN users u ON p.created_by = u.id
                  WHERE $where_clause
                  ORDER BY p.updated_at DESC";
        $result = mysqli_query($conn, $query);
        $pages = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $pages[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $pages]);
    }
}

// POST - Create/Update page
elseif ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = isset($data['id']) ? intval($data['id']) : 0;
    $title = mysqli_real_escape_string($conn, $data['title']);
    $slug = mysqli_real_escape_string($conn, $data['slug']);
    $content = mysqli_real_escape_string($conn, $data['content']);
    $meta_title = mysqli_real_escape_string($conn, $data['meta_title'] ?? '');
    $meta_description = mysqli_real_escape_string($conn, $data['meta_description'] ?? '');
    $meta_keywords = mysqli_real_escape_string($conn, $data['meta_keywords'] ?? '');
    $status = mysqli_real_escape_string($conn, $data['status']);
    $is_featured = isset($data['is_featured']) ? 1 : 0;
    
    if ($id > 0) {
        // Update
        $published_at = ($status == 'published') ? ', published_at = NOW()' : '';
        
        $query = "UPDATE cms_pages SET
                  title = '$title',
                  slug = '$slug',
                  content = '$content',
                  meta_title = '$meta_title',
                  meta_description = '$meta_description',
                  meta_keywords = '$meta_keywords',
                  status = '$status',
                  is_featured = $is_featured,
                  updated_at = NOW()
                  $published_at
                  WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Page updated successfully']);
        } else {
            echo json_encode(['error' => 'Failed to update page: ' . mysqli_error($conn)]);
        }
    } else {
        // Create
        $published_at = ($status == 'published') ? 'NOW()' : 'NULL';
        
        $query = "INSERT INTO cms_pages 
                  (title, slug, content, meta_title, meta_description, meta_keywords, status, is_featured, created_by, published_at, created_at, updated_at)
                  VALUES 
                  ('$title', '$slug', '$content', '$meta_title', '$meta_description', '$meta_keywords', '$status', $is_featured, $user_id, $published_at, NOW(), NOW())";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Page created successfully', 'id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['error' => 'Failed to create page: ' . mysqli_error($conn)]);
        }
    }
}

// DELETE - Delete page
elseif ($method == 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    $query = "DELETE FROM cms_pages WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Page deleted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete page']);
    }
}

else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
