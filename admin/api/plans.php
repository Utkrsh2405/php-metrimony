<?php
// Admin Plans API - CRUD operations
session_start();
header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once("../../includes/dbconn.php");

// Verify admin
$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - List all plans
if ($method == 'GET') {
    if (isset($_GET['id'])) {
        // Get single plan
        $plan_id = intval($_GET['id']);
        $query = "SELECT * FROM plans WHERE id = $plan_id";
        $result = mysqli_query($conn, $query);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Decode JSON features
            if ($row['features']) {
                $row['features'] = json_decode($row['features'], true);
            }
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['error' => 'Plan not found']);
        }
    } else {
        // Get all plans
        $query = "SELECT p.*, 
                         COUNT(us.id) as active_subscriptions
                  FROM plans p
                  LEFT JOIN user_subscriptions us ON p.id = us.plan_id AND us.status = 'active'
                  GROUP BY p.id
                  ORDER BY p.display_order ASC, p.price ASC";
        
        $result = mysqli_query($conn, $query);
        $plans = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Decode JSON features
            if ($row['features']) {
                $row['features'] = json_decode($row['features'], true);
            }
            $plans[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $plans]);
    }
}

// POST - Create or Update plan
elseif ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $plan_id = isset($data['id']) ? intval($data['id']) : 0;
    $name = mysqli_real_escape_string($conn, $data['name']);
    $price = floatval($data['price']);
    $duration_days = intval($data['duration_days']);
    $features = mysqli_real_escape_string($conn, json_encode($data['features']));
    $is_active = isset($data['is_active']) ? 1 : 0;
    $max_contacts = intval($data['max_contacts']);
    $max_messages = intval($data['max_messages']);
    $max_interests = intval($data['max_interests']);
    $max_shortlist = intval($data['max_shortlist']);
    $display_order = intval($data['display_order']);
    $description = mysqli_real_escape_string($conn, $data['description'] ?? '');
    
    if ($plan_id > 0) {
        // Update existing plan
        $query = "UPDATE plans SET
                  name = '$name',
                  price = $price,
                  duration_days = $duration_days,
                  features = '$features',
                  is_active = $is_active,
                  max_contacts = $max_contacts,
                  max_messages = $max_messages,
                  max_interests = $max_interests,
                  max_shortlist = $max_shortlist,
                  display_order = $display_order,
                  description = '$description',
                  updated_at = NOW()
                  WHERE id = $plan_id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Plan updated successfully', 'id' => $plan_id]);
        } else {
            echo json_encode(['error' => 'Failed to update plan: ' . mysqli_error($conn)]);
        }
    } else {
        // Create new plan
        $query = "INSERT INTO plans 
                  (name, price, duration_days, features, is_active, max_contacts, max_messages, 
                   max_interests, max_shortlist, display_order, description, created_at, updated_at)
                  VALUES 
                  ('$name', $price, $duration_days, '$features', $is_active, $max_contacts, 
                   $max_messages, $max_interests, $max_shortlist, $display_order, '$description', 
                   NOW(), NOW())";
        
        if (mysqli_query($conn, $query)) {
            $new_id = mysqli_insert_id($conn);
            echo json_encode(['success' => true, 'message' => 'Plan created successfully', 'id' => $new_id]);
        } else {
            echo json_encode(['error' => 'Failed to create plan: ' . mysqli_error($conn)]);
        }
    }
}

// PUT - Reorder plans
elseif ($method == 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['reorder']) && is_array($data['order'])) {
        // Update display order for multiple plans
        $success = true;
        foreach ($data['order'] as $order_item) {
            $plan_id = intval($order_item['id']);
            $display_order = intval($order_item['order']);
            
            $query = "UPDATE plans SET display_order = $display_order WHERE id = $plan_id";
            if (!mysqli_query($conn, $query)) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Plans reordered successfully']);
        } else {
            echo json_encode(['error' => 'Failed to reorder plans']);
        }
    } else {
        echo json_encode(['error' => 'Invalid reorder data']);
    }
}

// DELETE - Delete plan
elseif ($method == 'DELETE') {
    $plan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($plan_id == 0) {
        echo json_encode(['error' => 'Invalid plan ID']);
        exit();
    }
    
    // Check if plan has active subscriptions
    $check_query = "SELECT COUNT(*) as count FROM user_subscriptions 
                    WHERE plan_id = $plan_id AND status = 'active'";
    $check_result = mysqli_query($conn, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['count'] > 0) {
        echo json_encode(['error' => 'Cannot delete plan with active subscriptions. Deactivate it instead.']);
        exit();
    }
    
    // Soft delete: just set is_active to 0
    $query = "UPDATE plans SET is_active = 0 WHERE id = $plan_id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Plan deactivated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete plan']);
    }
}

else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
