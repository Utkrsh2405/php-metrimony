<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once("../../includes/dbconn.php");

$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$type = isset($_GET['type']) ? $_GET['type'] : 'stats';

if ($type === 'stats') {
    // Get overall statistics
    $stats = [];
    
    // Total searches in last 30 days
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM search_history 
                                   WHERE searched_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['total_searches'] = mysqli_fetch_assoc($result)['count'];
    
    // Total saved searches
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM saved_searches");
    $stats['saved_searches'] = mysqli_fetch_assoc($result)['count'];
    
    // Average results per search
    $result = mysqli_query($conn, "SELECT AVG(results_count) as avg FROM search_history 
                                   WHERE searched_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['avg_results'] = (float)mysqli_fetch_assoc($result)['avg'] ?: 0;
    
    // Active searchers in last 7 days
    $result = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as count FROM search_history 
                                   WHERE searched_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['active_searchers'] = mysqli_fetch_assoc($result)['count'];
    
    echo json_encode(['success' => true, 'data' => $stats]);
}

elseif ($type === 'filters') {
    // Get popular filters
    $filters = [];
    
    // Get all search histories
    $result = mysqli_query($conn, "SELECT search_filters FROM search_history 
                                   WHERE searched_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    
    $filter_counts = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $search_filters = json_decode($row['search_filters'], true);
        if ($search_filters) {
            foreach ($search_filters as $key => $value) {
                if (!empty($value)) {
                    $filter_key = $key . '|' . $value;
                    if (!isset($filter_counts[$filter_key])) {
                        $filter_counts[$filter_key] = [
                            'filter_name' => ucwords(str_replace('_', ' ', $key)),
                            'filter_value' => $value,
                            'count' => 0
                        ];
                    }
                    $filter_counts[$filter_key]['count']++;
                }
            }
        }
    }
    
    // Sort by count
    usort($filter_counts, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    // Limit to top 10
    $filters = array_slice($filter_counts, 0, 10);
    
    echo json_encode(['success' => true, 'data' => $filters]);
}

elseif ($type === 'recent') {
    // Get recent searches
    $result = mysqli_query($conn, "SELECT sh.*, u.username 
                                   FROM search_history sh
                                   LEFT JOIN users u ON sh.user_id = u.id
                                   ORDER BY sh.searched_at DESC
                                   LIMIT 20");
    
    $searches = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $searches[] = [
            'user_id' => $row['user_id'],
            'username' => $row['username'] ?: 'Unknown',
            'filters' => json_decode($row['search_filters'], true),
            'results_count' => $row['results_count'],
            'searched_at' => $row['searched_at']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $searches]);
}

elseif ($type === 'chart') {
    // Get search activity for last 7 days
    $dates = [];
    $counts = [];
    
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates[] = date('M d', strtotime($date));
        
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM search_history 
                                       WHERE DATE(searched_at) = '$date'");
        $counts[] = (int)mysqli_fetch_assoc($result)['count'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'labels' => $dates,
            'counts' => $counts
        ]
    ]);
}

else {
    echo json_encode(['success' => false, 'error' => 'Invalid type']);
}
?>
