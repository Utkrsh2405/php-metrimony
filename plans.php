<?php include_once("includes/basic_includes.php");?>
<?php include_once("functions.php"); ?>
<?php
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
<html>
<head>
<title>Find Your Perfect Partner - Matrimony | Membership Plans
</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<link href="css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<link href="css/style.css" rel='stylesheet' type='text/css' />
<link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
<link href="css/font-awesome.css" rel="stylesheet"> 
<script>
$(document).ready(function(){
    $(".dropdown").hover(            
        function() {
            $('.dropdown-menu', this).stop( true, true ).slideDown("fast");
            $(this).toggleClass('open');        
        },
        function() {
            $('.dropdown-menu', this).stop( true, true ).slideUp("fast");
            $(this).toggleClass('open');       
        }
    );
});
</script>
<style>
.plan-box {
    border: 1px solid #e0e0e0;
    padding: 30px 20px;
    text-align: center;
    border-radius: 5px;
    transition: all 0.3s;
    background: #fff;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}
.plan-box:hover {
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    transform: translateY(-5px);
    border-color: #c32143;
}
.plan-box h3 {
    color: #c32143;
    font-size: 24px;
    margin-bottom: 20px;
    font-weight: 600;
}
.plan-price {
    font-size: 36px;
    font-weight: 700;
    color: #333;
    margin-bottom: 20px;
}
.plan-price span {
    font-size: 16px;
    color: #999;
}
.plan-features {
    list-style: none;
    padding: 0;
    margin: 0 0 30px 0;
}
.plan-features li {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
    color: #666;
}
.plan-features li i {
    color: #c32143;
    margin-right: 10px;
}
.plan-btn {
    background: #c32143;
    color: #fff;
    padding: 10px 30px;
    border-radius: 3px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
    text-transform: uppercase;
    font-weight: 500;
    border: none;
}
.plan-btn:hover {
    background: #a01a35;
    color: #fff;
    text-decoration: none;
}
.popular-badge {
    position: absolute;
    top: 15px;
    right: -35px;
    background: #ff9800;
    color: #fff;
    padding: 5px 40px;
    transform: rotate(45deg);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.featured-plan {
    border-color: #ff9800;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}
</style>
</head>
<body>
<?php include_once("includes/navigation.php");?>
<div class="grid_3">
  <div class="container">
   <div class="breadcrumb1">
     <ul>
        <a href="index.php"><i class="fa fa-home home_1"></i></a>
        <span class="divider">&nbsp;|&nbsp;</span>
        <li class="current-page">Membership Plans</li>
     </ul>
   </div>
   
   <div class="services">
      <div class="col-md-12 text-center">
          <h2>Welcome to Our Membership Plans</h2>
          <p style="margin-bottom: 40px; color: #888;">Choose a plan that works best for you and take a step forward to finding your perfect partner.</p>
      </div>
      
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
                <div class="plan-box <?php echo $is_featured ? 'featured-plan' : ''; ?>">
                    <?php if($is_featured): ?>
                    <div class="popular-badge">POPULAR</div>
                    <?php endif; ?>
                    
                    <h3><i class="fa <?php echo $icon; ?>" style="margin-right: 8px;"></i><?php echo htmlspecialchars($plan['name']); ?></h3>
                    
                    <div class="plan-price">
                        ₹<?php echo number_format($plan['price'], 0); ?>
                        <div style="font-size: 14px; font-weight: normal; color: #999; margin-top: 5px;">For <?php echo $plan['duration_days']; ?> days</div>
                    </div>
                    
                    <p style="font-size: 13px; color: #777; margin-bottom: 20px; min-height: 40px;">
                        <?php echo htmlspecialchars($plan['description']); ?>
                    </p>
                    
                    <ul class="plan-features">
                        <li><i class="fa fa-check"></i> 
                            <?php echo $plan['max_contacts_view'] == 0 ? 'Unlimited' : $plan['max_contacts_view']; ?> Contacts View
                        </li>
                        <li><i class="fa fa-check"></i> 
                            <?php echo $plan['max_messages_send'] == 0 ? 'Unlimited' : $plan['max_messages_send']; ?> Messages
                        </li>
                        <li><i class="fa fa-check"></i> 
                            <?php echo $plan['max_interests_express'] == 0 ? 'Unlimited' : $plan['max_interests_express']; ?> Interests Express
                        </li>
                        <li><i class="fa <?php echo $plan['can_chat'] ? 'fa-check' : 'fa-times'; ?>" 
                               style="color: <?php echo $plan['can_chat'] ? '#c32143' : '#ccc'; ?>"></i> 
                            Live Chat
                        </li>
                    </ul>
                    
                    <?php if(isset($_SESSION['id'])): ?>
                        <a href="contact.php" class="plan-btn">Contact to Buy</a>
                    <?php else: ?>
                        <a href="login.php" class="plan-btn">Login to Buy</a>
                    <?php endif; ?>
                    
                </div>
            </div>
            <?php endforeach; ?>
      </div>
      <div class="clearfix"> </div>
      
      <div style="margin-top: 50px; background: #fdfdfd; padding: 30px; border: 1px solid #eee; border-radius: 5px;">
          <h3 style="color:#c32143; margin-bottom:15px;"><i class="fa fa-info-circle"></i> Payment Information</h3>
          <p style="color: #666; line-height: 1.6;">
              Currently we are supporting manual payments through bank transfer or UPI. Once you have chosen your plan and made the payment, please contact the administrator to activate your subscription. Your account will be upgraded instantly after verification.
          </p>
      </div>
   </div>
  </div>
</div>

<?php include_once("footer.php");?>
