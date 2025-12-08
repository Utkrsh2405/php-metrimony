<?php
session_start();
require_once("../../includes/dbconn.php");
require_once("../../includes/security.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || !isset($_SESSION['userlevel']) || intval($_SESSION['userlevel']) != 1) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    // Get statistics
    $stats = [
        'active' => 0,
        'suspended' => 0,
        'deleted' => 0,
        'total' => 0
    ];
    
    // Count active members
    $activeQuery = "SELECT COUNT(*) as count FROM users WHERE userlevel = 0 AND account_status = 'active'";
    $activeResult = mysqli_query($conn, $activeQuery);
    if ($activeResult) {
        $row = mysqli_fetch_assoc($activeResult);
        $stats['active'] = intval($row['count']);
    }
    
    // Count suspended members
    $suspendedQuery = "SELECT COUNT(*) as count FROM users WHERE userlevel = 0 AND account_status = 'suspended'";
    $suspendedResult = mysqli_query($conn, $suspendedQuery);
    if ($suspendedResult) {
        $row = mysqli_fetch_assoc($suspendedResult);
        $stats['suspended'] = intval($row['count']);
    }
    
    // Count deleted members
    $deletedQuery = "SELECT COUNT(*) as count FROM users WHERE userlevel = 0 AND account_status = 'deleted'";
    $deletedResult = mysqli_query($conn, $deletedQuery);
    if ($deletedResult) {
        $row = mysqli_fetch_assoc($deletedResult);
        $stats['deleted'] = intval($row['count']);
    }
    
    // Count total members (all non-admin users)
    $totalQuery = "SELECT COUNT(*) as count FROM users WHERE userlevel = 0";
    $totalResult = mysqli_query($conn, $totalQuery);
    if ($totalResult) {
        $row = mysqli_fetch_assoc($totalResult);
        $stats['total'] = intval($row['count']);
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
