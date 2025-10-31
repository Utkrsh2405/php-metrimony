<?php
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once("includes/dbconn.php");
require_once("includes/profile-completion.php");

$user_id = $_SESSION['id'];
$profile_completion = getProfileCompletion($conn, $user_id);

$action = isset($_GET['action']) ? $_GET['action'] : 'widget';

if ($action === 'widget') {
    // Return widget HTML
    $percentage = $profile_completion->getCompletionPercentage();
    $message = $profile_completion->getStatusMessage();
    $suggestions = $profile_completion->getTopSuggestions(5);
    ?>
    <div class="profile-completion-widget">
        <div class="completion-header">
            <h4><i class="fa fa-user-circle"></i> Profile Completion</h4>
            <div class="completion-percentage"><?php echo $percentage; ?>%</div>
        </div>
        <div class="completion-bar">
            <div class="completion-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
        </div>
        <p class="completion-message"><?php echo $message; ?></p>
        
        <?php if (count($suggestions) > 0): ?>
        <div class="completion-suggestions">
            <strong>Complete these fields:</strong>
            <ul>
                <?php foreach ($suggestions as $suggestion): ?>
                <li>
                    <i class="fa fa-check-circle-o"></i> 
                    <?php echo htmlspecialchars($suggestion['label']); ?>
                    <span class="suggestion-category">(<?php echo $suggestion['category']; ?>)</span>
                </li>
                <?php endforeach; ?>
            </ul>
            <a href="/edit-profile.php" class="btn btn-primary btn-sm btn-block">
                <i class="fa fa-edit"></i> Complete Profile
            </a>
        </div>
        <?php endif; ?>
    </div>
    
    <style>
    .profile-completion-widget {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .completion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .completion-header h4 {
        margin: 0;
        font-size: 18px;
    }
    .completion-percentage {
        font-size: 32px;
        font-weight: bold;
        color: #667eea;
    }
    .completion-bar {
        height: 12px;
        background: #f0f0f0;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 10px;
    }
    .completion-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transition: width 0.5s ease;
    }
    .completion-message {
        color: #666;
        font-size: 14px;
        margin: 10px 0;
    }
    .completion-suggestions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
    }
    .completion-suggestions ul {
        margin: 10px 0;
        padding-left: 0;
        list-style: none;
    }
    .completion-suggestions li {
        padding: 5px 0;
        font-size: 14px;
    }
    .suggestion-category {
        font-size: 11px;
        color: #999;
    }
    </style>
    <?php
    exit();
}

if ($action === 'json') {
    // Return JSON data
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'percentage' => $profile_completion->getCompletionPercentage(),
        'message' => $profile_completion->getStatusMessage(),
        'missing_fields' => $profile_completion->getMissingFields(),
        'top_suggestions' => $profile_completion->getTopSuggestions(5),
        'category_completion' => $profile_completion->getCategoryCompletion()
    ]);
    exit();
}
?>
