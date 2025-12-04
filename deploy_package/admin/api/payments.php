<?php
// Admin Payments API - View and manage payments
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

// GET - List payments with filters
if ($method == 'GET') {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    $offset = ($page - 1) * $limit;
    
    $status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
    $plan_id = isset($_GET['plan_id']) ? intval($_GET['plan_id']) : 0;
    $user_id_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $payment_method = isset($_GET['payment_method']) ? mysqli_real_escape_string($conn, $_GET['payment_method']) : '';
    $date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';
    
    // Build WHERE clause
    $where = ["1=1"];
    
    if ($status) {
        $where[] = "p.status = '$status'";
    }
    
    if ($plan_id > 0) {
        $where[] = "p.plan_id = $plan_id";
    }
    
    if ($user_id_filter > 0) {
        $where[] = "p.user_id = $user_id_filter";
    }
    
    if ($payment_method) {
        $where[] = "p.payment_method = '$payment_method'";
    }
    
    if ($date_from) {
        $where[] = "DATE(p.created_at) >= '$date_from'";
    }
    
    if ($date_to) {
        $where[] = "DATE(p.created_at) <= '$date_to'";
    }
    
    $where_clause = implode(' AND ', $where);
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM payments p WHERE $where_clause";
    $count_result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $total = $count_row['total'];
    
    // Get payments
    $query = "SELECT p.*, 
                     pl.name as plan_name,
                     u.username, u.email,
                     c.firstname, c.lastname
              FROM payments p
              LEFT JOIN plans pl ON p.plan_id = pl.id
              LEFT JOIN users u ON p.user_id = u.id
              LEFT JOIN customer c ON u.id = c.cust_id
              WHERE $where_clause
              ORDER BY p.created_at DESC
              LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($conn, $query);
    $payments = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $payments,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

// POST - Update payment status or process refund
elseif ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $payment_id = isset($data['payment_id']) ? intval($data['payment_id']) : 0;
    
    if ($payment_id == 0) {
        echo json_encode(['error' => 'Invalid payment ID']);
        exit();
    }
    
    // Get payment details
    $payment_query = "SELECT * FROM payments WHERE id = $payment_id";
    $payment_result = mysqli_query($conn, $payment_query);
    $payment = mysqli_fetch_assoc($payment_result);
    
    if (!$payment) {
        echo json_encode(['error' => 'Payment not found']);
        exit();
    }
    
    switch ($action) {
        case 'approve':
            if ($payment['status'] !== 'pending') {
                echo json_encode(['error' => 'Only pending payments can be approved']);
                exit();
            }
            
            // Update payment status
            $update_payment = "UPDATE payments SET 
                              status = 'completed',
                              updated_at = NOW()
                              WHERE id = $payment_id";
            
            if (mysqli_query($conn, $update_payment)) {
                // Create or extend subscription
                $user_id = $payment['user_id'];
                $plan_id = $payment['plan_id'];
                
                // Get plan details
                $plan_query = "SELECT duration_days FROM plans WHERE id = $plan_id";
                $plan_result = mysqli_query($conn, $plan_query);
                $plan = mysqli_fetch_assoc($plan_result);
                
                if ($plan) {
                    // Check if user has active subscription
                    $sub_query = "SELECT * FROM user_subscriptions 
                                 WHERE user_id = $user_id AND status = 'active'";
                    $sub_result = mysqli_query($conn, $sub_query);
                    
                    if (mysqli_num_rows($sub_result) > 0) {
                        // Extend existing subscription
                        $sub = mysqli_fetch_assoc($sub_result);
                        $current_end = $sub['end_date'];
                        
                        $update_sub = "UPDATE user_subscriptions SET
                                      end_date = DATE_ADD('$current_end', INTERVAL {$plan['duration_days']} DAY),
                                      updated_at = NOW()
                                      WHERE id = {$sub['id']}";
                        mysqli_query($conn, $update_sub);
                    } else {
                        // Create new subscription
                        $start_date = date('Y-m-d');
                        $end_date = date('Y-m-d', strtotime("+{$plan['duration_days']} days"));
                        
                        $insert_sub = "INSERT INTO user_subscriptions 
                                      (user_id, plan_id, start_date, end_date, status, created_at, updated_at)
                                      VALUES 
                                      ($user_id, $plan_id, '$start_date', '$end_date', 'active', NOW(), NOW())";
                        mysqli_query($conn, $insert_sub);
                    }
                }
                
                echo json_encode(['success' => true, 'message' => 'Payment approved and subscription activated']);
            } else {
                echo json_encode(['error' => 'Failed to approve payment']);
            }
            break;
            
        case 'reject':
            if ($payment['status'] !== 'pending') {
                echo json_encode(['error' => 'Only pending payments can be rejected']);
                exit();
            }
            
            $rejection_note = mysqli_real_escape_string($conn, $data['note'] ?? 'Rejected by admin');
            
            $update_payment = "UPDATE payments SET 
                              status = 'failed',
                              notes = '$rejection_note',
                              updated_at = NOW()
                              WHERE id = $payment_id";
            
            if (mysqli_query($conn, $update_payment)) {
                echo json_encode(['success' => true, 'message' => 'Payment rejected']);
            } else {
                echo json_encode(['error' => 'Failed to reject payment']);
            }
            break;
            
        case 'refund':
            if ($payment['status'] !== 'completed') {
                echo json_encode(['error' => 'Only completed payments can be refunded']);
                exit();
            }
            
            $refund_amount = isset($data['refund_amount']) ? floatval($data['refund_amount']) : $payment['amount'];
            $refund_note = mysqli_real_escape_string($conn, $data['note'] ?? 'Refunded by admin');
            
            if ($refund_amount > $payment['amount']) {
                echo json_encode(['error' => 'Refund amount cannot exceed payment amount']);
                exit();
            }
            
            // Update payment status
            $update_payment = "UPDATE payments SET 
                              status = 'refunded',
                              refund_amount = $refund_amount,
                              notes = '$refund_note',
                              updated_at = NOW()
                              WHERE id = $payment_id";
            
            if (mysqli_query($conn, $update_payment)) {
                // Deactivate subscription if full refund
                if ($refund_amount == $payment['amount']) {
                    $deactivate_sub = "UPDATE user_subscriptions SET
                                      status = 'cancelled',
                                      updated_at = NOW()
                                      WHERE user_id = {$payment['user_id']} 
                                      AND plan_id = {$payment['plan_id']}
                                      AND status = 'active'";
                    mysqli_query($conn, $deactivate_sub);
                }
                
                echo json_encode(['success' => true, 'message' => 'Payment refunded successfully']);
            } else {
                echo json_encode(['error' => 'Failed to process refund']);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
}

else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
