<?php
session_start();
require_once("includes/dbconn.php");
require_once("functions.php");

// Fetch all active plans
$query = "SELECT * FROM plans WHERE is_active = 1 ORDER BY price ASC";
$result = mysqli_query($conn, $query);
$plans = [];
if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $plans[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Plans - Matrimony</title>
    
    <!-- CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .plans-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 0 60px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .plans-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .plans-hero h1 {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .plans-hero p {
            font-size: 20px;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
            opacity: 0.95;
        }
        
        .plans-section {
            padding: 60px 0;
        }
        
        .plan-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            border: 3px solid transparent;
        }
        
        .plan-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        
        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        
        .plan-card:hover::before {
            transform: scaleX(1);
        }
        
        .plan-card.featured {
            border-color: #667eea;
            transform: scale(1.05);
        }
        
        .plan-card.featured .plan-badge {
            display: block;
        }
        
        .plan-badge {
            position: absolute;
            top: 20px;
            right: -35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 40px;
            font-size: 12px;
            font-weight: 600;
            transform: rotate(45deg);
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            display: none;
        }
        
        .plan-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .plan-name {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .plan-price {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .plan-price .currency {
            font-size: 24px;
            font-weight: 600;
            color: #667eea;
            vertical-align: super;
        }
        
        .plan-price .amount {
            font-size: 52px;
            font-weight: 700;
            color: #667eea;
            line-height: 1;
        }
        
        .plan-price .period {
            font-size: 16px;
            color: #718096;
            display: block;
            margin-top: 5px;
        }
        
        .plan-description {
            text-align: center;
            color: #718096;
            font-size: 15px;
            margin-bottom: 30px;
            min-height: 45px;
        }
        
        .plan-features {
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
        }
        
        .plan-features li {
            padding: 12px 0;
            color: #4a5568;
            font-size: 15px;
            position: relative;
            padding-left: 30px;
        }
        
        .plan-features li::before {
            content: '\f00c';
            font-family: 'FontAwesome';
            position: absolute;
            left: 0;
            color: #48bb78;
            font-size: 16px;
        }
        
        .plan-features li.disabled {
            color: #cbd5e0;
            text-decoration: line-through;
        }
        
        .plan-features li.disabled::before {
            content: '\f00d';
            color: #fc8181;
        }
        
        .plan-button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .plan-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .plan-button.current {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.4);
        }
        
        .features-comparison {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            margin-top: 60px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .features-comparison h2 {
            font-size: 36px;
            font-weight: 700;
            color: #2d3748;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .comparison-table {
            width: 100%;
            margin-top: 30px;
        }
        
        .comparison-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: 600;
        }
        
        .comparison-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .comparison-table tr:hover {
            background: #f7fafc;
        }
        
        .check-icon {
            color: #48bb78;
            font-size: 20px;
        }
        
        .cross-icon {
            color: #fc8181;
            font-size: 20px;
        }
        
        @media (max-width: 768px) {
            .plans-hero h1 {
                font-size: 32px;
            }
            
            .plans-hero p {
                font-size: 16px;
            }
            
            .plan-card.featured {
                transform: scale(1);
            }
            
            .plan-name {
                font-size: 24px;
            }
            
            .plan-price .amount {
                font-size: 42px;
            }
        }
    </style>
</head>
<body>

<?php include("includes/navigation.php"); ?>

<!-- Hero Section -->
<div class="plans-hero">
    <div class="container">
        <h1>Choose Your Perfect Plan</h1>
        <p>Find the plan that helps you discover your perfect match</p>
    </div>
</div>

<!-- Plans Section -->
<div class="plans-section">
    <div class="container">
        <div class="row">
            <?php
            $plan_icons = [
                'Free' => 'fa-gift',
                'Silver' => 'fa-star-o',
                'Gold' => 'fa-star',
                'Platinum' => 'fa-diamond'
            ];
            
            $featured_plan = 'Gold'; // Mark this as featured
            
            foreach($plans as $index => $plan):
                $is_featured = ($plan['name'] == $featured_plan);
                $icon = isset($plan_icons[$plan['name']]) ? $plan_icons[$plan['name']] : 'fa-heart';
            ?>
            <div class="col-md-3 col-sm-6">
                <div class="plan-card <?php echo $is_featured ? 'featured' : ''; ?>">
                    <?php if($is_featured): ?>
                    <div class="plan-badge">POPULAR</div>
                    <?php endif; ?>
                    
                    <div class="plan-icon">
                        <i class="fa <?php echo $icon; ?>"></i>
                    </div>
                    
                    <h3 class="plan-name"><?php echo htmlspecialchars($plan['name']); ?></h3>
                    
                    <div class="plan-price">
                        <span class="currency">₹</span>
                        <span class="amount"><?php echo number_format($plan['price'], 0); ?></span>
                        <span class="period">/<?php echo $plan['duration_days']; ?> days</span>
                    </div>
                    
                    <p class="plan-description">
                        <?php echo htmlspecialchars($plan['description']); ?>
                    </p>
                    
                    <ul class="plan-features">
                        <li <?php echo $plan['max_contacts_view'] == 0 ? '' : ($plan['max_contacts_view'] < 50 ? 'class="limited"' : ''); ?>>
                            <?php echo $plan['max_contacts_view'] == 0 ? 'Unlimited' : $plan['max_contacts_view']; ?> Contact Views
                        </li>
                        <li <?php echo $plan['max_messages_send'] == 0 ? '' : ($plan['max_messages_send'] < 100 ? 'class="limited"' : ''); ?>>
                            <?php echo $plan['max_messages_send'] == 0 ? 'Unlimited' : $plan['max_messages_send']; ?> Messages
                        </li>
                        <li <?php echo $plan['max_interests_express'] == 0 ? '' : ($plan['max_interests_express'] < 25 ? 'class="limited"' : ''); ?>>
                            <?php echo $plan['max_interests_express'] == 0 ? 'Unlimited' : $plan['max_interests_express']; ?> Interest Expressions
                        </li>
                        <li <?php echo $plan['can_chat'] ? '' : 'class="disabled"'; ?>>
                            Live Chat Support
                        </li>
                        <li <?php echo $plan['price'] > 0 ? '' : 'class="disabled"'; ?>>
                            Priority Profile Display
                        </li>
                        <li <?php echo $plan['price'] >= 50 ? '' : 'class="disabled"'; ?>>
                            Verified Badge
                        </li>
                        <li <?php echo $plan['price'] >= 99 ? '' : 'class="disabled"'; ?>>
                            Dedicated Relationship Manager
                        </li>
                    </ul>
                    
                    <?php if(isset($_SESSION['id'])): ?>
                        <button class="plan-button" onclick="subscribeToPlan(<?php echo $plan['id']; ?>, '<?php echo htmlspecialchars($plan['name']); ?>')">
                            Choose <?php echo htmlspecialchars($plan['name']); ?>
                        </button>
                    <?php else: ?>
                        <button class="plan-button" onclick="location.href='login.php'">
                            Login to Subscribe
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Features Comparison -->
        <div class="features-comparison">
            <h2>Compare All Features</h2>
            <div class="table-responsive">
                <table class="comparison-table table">
                    <thead>
                        <tr>
                            <th style="text-align: left;">Features</th>
                            <?php foreach($plans as $plan): ?>
                            <th><?php echo htmlspecialchars($plan['name']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: left;"><strong>Contact Views</strong></td>
                            <?php foreach($plans as $plan): ?>
                            <td><?php echo $plan['max_contacts_view'] == 0 ? '<i class="fa fa-check check-icon"></i> Unlimited' : $plan['max_contacts_view']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><strong>Messages</strong></td>
                            <?php foreach($plans as $plan): ?>
                            <td><?php echo $plan['max_messages_send'] == 0 ? '<i class="fa fa-check check-icon"></i> Unlimited' : $plan['max_messages_send']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><strong>Interest Expressions</strong></td>
                            <?php foreach($plans as $plan): ?>
                            <td><?php echo $plan['max_interests_express'] == 0 ? '<i class="fa fa-check check-icon"></i> Unlimited' : $plan['max_interests_express']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><strong>Live Chat</strong></td>
                            <?php foreach($plans as $plan): ?>
                            <td><?php echo $plan['can_chat'] ? '<i class="fa fa-check check-icon"></i>' : '<i class="fa fa-times cross-icon"></i>'; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><strong>Priority Display</strong></td>
                            <?php foreach($plans as $plan): ?>
                            <td><?php echo $plan['price'] > 0 ? '<i class="fa fa-check check-icon"></i>' : '<i class="fa fa-times cross-icon"></i>'; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><strong>Verified Badge</strong></td>
                            <?php foreach($plans as $plan): ?>
                            <td><?php echo $plan['price'] >= 50 ? '<i class="fa fa-check check-icon"></i>' : '<i class="fa fa-times cross-icon"></i>'; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><strong>Relationship Manager</strong></td>
                            <?php foreach($plans as $plan): ?>
                            <td><?php echo $plan['price'] >= 99 ? '<i class="fa fa-check check-icon"></i>' : '<i class="fa fa-times cross-icon"></i>'; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td style="text-align: left;"><strong>Duration</strong></td>
                            <?php foreach($plans as $plan): ?>
                            <td><?php echo $plan['duration_days']; ?> days</td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

<!-- Scripts -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>

<script>
function subscribeToPlan(planId, planName) {
    if(confirm('Are you sure you want to subscribe to ' + planName + ' plan?')) {
        // Redirect to payment page
        window.location.href = 'payment.php?plan_id=' + planId;
    }
}
</script>

</body>
</html>
