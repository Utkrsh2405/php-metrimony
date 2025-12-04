<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("includes/dbconn.php");
require_once("includes/quota-manager.php");

$user_id = $_SESSION['id'];
$quota_manager = getQuotaManager($conn, $user_id);
$quotas = $quota_manager->getAllQuotas();
$plan = $quota_manager->getPlan();

include("includes/header.php");
?>

<style>
.quota-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 40px 0;
    text-align: center;
    margin-bottom: 30px;
}
.plan-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.plan-name {
    font-size: 32px;
    font-weight: bold;
    color: #f5576c;
    margin-bottom: 10px;
}
.plan-expiry {
    font-size: 16px;
    color: #666;
}
.quota-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.quota-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s;
}
.quota-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}
.quota-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    color: #333;
    text-transform: uppercase;
}
.quota-stats {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    font-size: 14px;
}
.quota-progress {
    height: 10px;
    background: #f0f0f0;
    border-radius: 5px;
    overflow: hidden;
    margin-bottom: 10px;
}
.quota-progress-bar {
    height: 100%;
    transition: width 0.5s ease;
    border-radius: 5px;
}
.progress-low { background: #4caf50; }
.progress-medium { background: #ffc107; }
.progress-high { background: #f44336; }
.quota-numbers {
    display: flex;
    justify-content: space-around;
    margin-top: 15px;
}
.quota-number {
    text-align: center;
}
.quota-number-value {
    font-size: 24px;
    font-weight: bold;
}
.quota-number-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}
.upgrade-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    margin-top: 30px;
}
.unlimited-badge {
    background: #4caf50;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}
</style>

<div class="quota-header">
    <div class="container">
        <h1><i class="fa fa-dashboard"></i> My Plan & Quotas</h1>
        <p style="margin: 10px 0 0 0; font-size: 18px;">Track your subscription usage and limits</p>
    </div>
</div>

<div class="container">
    <!-- Plan Info -->
    <div class="plan-card">
        <div class="row">
            <div class="col-md-8">
                <div class="plan-name">
                    <i class="fa fa-trophy"></i> <?php echo htmlspecialchars($quota_manager->getPlanName()); ?>
                </div>
                <?php if ($plan): ?>
                    <div class="plan-expiry">
                        <i class="fa fa-calendar"></i> 
                        Valid from <?php echo date('M d, Y', strtotime($plan['start_date'])); ?> 
                        to <?php echo date('M d, Y', strtotime($plan['end_date'])); ?>
                        <br>
                        <strong><?php echo $quota_manager->getDaysRemaining(); ?> days remaining</strong>
                    </div>
                <?php else: ?>
                    <div class="plan-expiry">
                        <i class="fa fa-exclamation-triangle"></i> No active subscription
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-right">
                <a href="/plans.php" class="btn btn-primary btn-lg">
                    <i class="fa fa-rocket"></i> Upgrade Plan
                </a>
            </div>
        </div>
    </div>
    
    <!-- Quota Cards -->
    <div class="quota-grid">
        <!-- Interests -->
        <div class="quota-card">
            <div class="quota-title">
                <i class="fa fa-heart"></i> Express Interests
            </div>
            <?php if ($quotas['interests']['is_unlimited']): ?>
                <span class="unlimited-badge">UNLIMITED</span>
            <?php else: ?>
                <div class="quota-stats">
                    <span>Used: <?php echo $quotas['interests']['used']; ?></span>
                    <span>Limit: <?php echo $quotas['interests']['limit']; ?></span>
                </div>
                <div class="quota-progress">
                    <div class="quota-progress-bar <?php 
                        echo $quotas['interests']['percentage'] < 50 ? 'progress-low' : 
                             ($quotas['interests']['percentage'] < 80 ? 'progress-medium' : 'progress-high');
                    ?>" style="width: <?php echo $quotas['interests']['percentage']; ?>%"></div>
                </div>
                <div class="quota-numbers">
                    <div class="quota-number">
                        <div class="quota-number-value" style="color: #4caf50;">
                            <?php echo $quotas['interests']['remaining']; ?>
                        </div>
                        <div class="quota-number-label">Remaining</div>
                    </div>
                </div>
            <?php endif; ?>
            <div style="margin-top: 15px;">
                <a href="/advanced-search.php" class="btn btn-sm btn-info btn-block">
                    <i class="fa fa-search"></i> Find Matches
                </a>
            </div>
        </div>
        
        <!-- Messages -->
        <div class="quota-card">
            <div class="quota-title">
                <i class="fa fa-envelope"></i> Send Messages
            </div>
            <?php if ($quotas['messages']['is_unlimited']): ?>
                <span class="unlimited-badge">UNLIMITED</span>
            <?php else: ?>
                <div class="quota-stats">
                    <span>Used: <?php echo $quotas['messages']['used']; ?></span>
                    <span>Limit: <?php echo $quotas['messages']['limit']; ?></span>
                </div>
                <div class="quota-progress">
                    <div class="quota-progress-bar <?php 
                        echo $quotas['messages']['percentage'] < 50 ? 'progress-low' : 
                             ($quotas['messages']['percentage'] < 80 ? 'progress-medium' : 'progress-high');
                    ?>" style="width: <?php echo $quotas['messages']['percentage']; ?>%"></div>
                </div>
                <div class="quota-numbers">
                    <div class="quota-number">
                        <div class="quota-number-value" style="color: #4caf50;">
                            <?php echo $quotas['messages']['remaining']; ?>
                        </div>
                        <div class="quota-number-label">Remaining</div>
                    </div>
                </div>
            <?php endif; ?>
            <div style="margin-top: 15px;">
                <a href="/messages.php" class="btn btn-sm btn-info btn-block">
                    <i class="fa fa-inbox"></i> View Inbox
                </a>
            </div>
        </div>
        
        <!-- Contacts View -->
        <div class="quota-card">
            <div class="quota-title">
                <i class="fa fa-eye"></i> View Contacts
            </div>
            <?php if ($quotas['contacts']['is_unlimited']): ?>
                <span class="unlimited-badge">UNLIMITED</span>
            <?php else: ?>
                <div class="quota-stats">
                    <span>Used: <?php echo $quotas['contacts']['used']; ?></span>
                    <span>Limit: <?php echo $quotas['contacts']['limit']; ?></span>
                </div>
                <div class="quota-progress">
                    <div class="quota-progress-bar <?php 
                        echo $quotas['contacts']['percentage'] < 50 ? 'progress-low' : 
                             ($quotas['contacts']['percentage'] < 80 ? 'progress-medium' : 'progress-high');
                    ?>" style="width: <?php echo $quotas['contacts']['percentage']; ?>%"></div>
                </div>
                <div class="quota-numbers">
                    <div class="quota-number">
                        <div class="quota-number-value" style="color: #4caf50;">
                            <?php echo $quotas['contacts']['remaining']; ?>
                        </div>
                        <div class="quota-number-label">Remaining</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Shortlist -->
        <div class="quota-card">
            <div class="quota-title">
                <i class="fa fa-star"></i> Shortlist Profiles
            </div>
            <?php if ($quotas['shortlist']['is_unlimited']): ?>
                <span class="unlimited-badge">UNLIMITED</span>
            <?php else: ?>
                <div class="quota-stats">
                    <span>Used: <?php echo $quotas['shortlist']['used']; ?></span>
                    <span>Limit: <?php echo $quotas['shortlist']['limit']; ?></span>
                </div>
                <div class="quota-progress">
                    <div class="quota-progress-bar <?php 
                        echo $quotas['shortlist']['percentage'] < 50 ? 'progress-low' : 
                             ($quotas['shortlist']['percentage'] < 80 ? 'progress-medium' : 'progress-high');
                    ?>" style="width: <?php echo $quotas['shortlist']['percentage']; ?>%"></div>
                </div>
                <div class="quota-numbers">
                    <div class="quota-number">
                        <div class="quota-number-value" style="color: #4caf50;">
                            <?php echo $quotas['shortlist']['remaining']; ?>
                        </div>
                        <div class="quota-number-label">Remaining</div>
                    </div>
                </div>
            <?php endif; ?>
            <div style="margin-top: 15px;">
                <a href="/shortlist.php" class="btn btn-sm btn-info btn-block">
                    <i class="fa fa-list"></i> View Shortlist
                </a>
            </div>
        </div>
        
        <!-- Chat Feature -->
        <div class="quota-card">
            <div class="quota-title">
                <i class="fa fa-comments"></i> Real-time Chat
            </div>
            <?php if ($quotas['chat']['enabled']): ?>
                <span class="unlimited-badge">ENABLED</span>
                <div style="margin-top: 15px;">
                    <p style="color: #666; font-size: 14px;">
                        You can use live chat to connect with your matches instantly!
                    </p>
                </div>
            <?php else: ?>
                <div style="padding: 20px 0;">
                    <i class="fa fa-lock" style="font-size: 48px; color: #ccc;"></i>
                    <p style="margin-top: 10px; color: #666;">
                        Upgrade your plan to unlock real-time chat
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Upgrade Banner -->
    <?php if (!$plan || $quota_manager->getDaysRemaining() < 7): ?>
    <div class="upgrade-banner">
        <h3 style="margin: 0 0 10px 0;">
            <i class="fa fa-gift"></i> 
            <?php if (!$plan): ?>
                Get Started with a Premium Plan
            <?php else: ?>
                Your plan is expiring soon!
            <?php endif; ?>
        </h3>
        <p style="margin: 0 0 20px 0; font-size: 16px;">
            <?php if (!$plan): ?>
                Unlock unlimited access to premium features and find your perfect match
            <?php else: ?>
                Renew now to continue enjoying premium features without interruption
            <?php endif; ?>
        </p>
        <a href="/plans.php" class="btn btn-warning btn-lg">
            <i class="fa fa-arrow-right"></i> View Plans & Pricing
        </a>
    </div>
    <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>
