<?php
// Fetch footer settings
$footer_settings_sql = "SELECT * FROM footer_settings LIMIT 1";
$footer_settings_result = mysqli_query($conn, $footer_settings_sql);
$footer_settings = mysqli_fetch_assoc($footer_settings_result);

// Fetch footer links
$footer_links_sql = "SELECT * FROM footer_links ORDER BY column_name, display_order";
$footer_links_result = mysqli_query($conn, $footer_links_sql);
$footer_links = [];
while ($row = mysqli_fetch_assoc($footer_links_result)) {
    $footer_links[$row['column_name']][] = $row;
}

// Background image style
$footer_style = "";
if (!empty($footer_settings['background_image'])) {
    $bg_image = "uploads/homepage/" . $footer_settings['background_image'];
    $footer_style = "style='background: url(\"$bg_image\") no-repeat 0px 0px; background-size: cover;'";
}
?>
<div class="footer" <?php echo $footer_style; ?>>
    <div class="container">
        <div class="col-md-4 col_2">
            <h4>Contact Us</h4>
            <p>
                <?php echo nl2br(htmlspecialchars($footer_settings['address'] ?? '')); ?><br>
                Phone: <?php echo htmlspecialchars($footer_settings['phone'] ?? ''); ?><br>
                Email: <a href="mailto:<?php echo htmlspecialchars($footer_settings['email'] ?? ''); ?>"><?php echo htmlspecialchars($footer_settings['email'] ?? ''); ?></a>
            </p>
        </div>
        
        <div class="col-md-2 col_2">
            <h4>Help & Support</h4>
            <ul class="footer_links">
                <?php if (isset($footer_links['quick_links'])): ?>
                    <?php foreach ($footer_links['quick_links'] as $link): ?>
                        <li><a href="<?php echo htmlspecialchars($link['link_url']); ?>"><?php echo htmlspecialchars($link['link_label']); ?></a></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="col-md-2 col_2">
            <h4>Quick Links</h4>
            <ul class="footer_links">
                <?php if (isset($footer_links['links'])): ?>
                    <?php foreach ($footer_links['links'] as $link): ?>
                        <li><a href="<?php echo htmlspecialchars($link['link_url']); ?>"><?php echo htmlspecialchars($link['link_label']); ?></a></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="col-md-2 col_2">
            <h4>Social</h4>
            <ul class="footer_social">
                <?php if (!empty($footer_settings['facebook_link']) && $footer_settings['facebook_link'] != '#'): ?>
                    <li><a href="<?php echo htmlspecialchars($footer_settings['facebook_link']); ?>"><i class="fa fa-facebook fa1"> </i></a></li>
                <?php endif; ?>
                <?php if (!empty($footer_settings['twitter_link']) && $footer_settings['twitter_link'] != '#'): ?>
                    <li><a href="<?php echo htmlspecialchars($footer_settings['twitter_link']); ?>"><i class="fa fa-twitter fa1"> </i></a></li>
                <?php endif; ?>
                <?php if (!empty($footer_settings['youtube_link']) && $footer_settings['youtube_link'] != '#'): ?>
                    <li><a href="<?php echo htmlspecialchars($footer_settings['youtube_link']); ?>"><i class="fa fa-youtube fa1"> </i></a></li>
                <?php endif; ?>
                <?php if (!empty($footer_settings['instagram_link']) && $footer_settings['instagram_link'] != '#'): ?>
                    <li><a href="<?php echo htmlspecialchars($footer_settings['instagram_link']); ?>"><i class="fa fa-instagram fa1"> </i></a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="clearfix"> </div>
        <div class="copy">
            <p><?php echo htmlspecialchars($footer_settings['copyright_text'] ?? 'Copyright © 2024 Marital. All Rights Reserved'); ?></p>
        </div>
    </div>
</div>
</body>
<!-- FlexSlider -->
<script defer src="js/jquery.flexslider.js"></script>
<link rel="stylesheet" href="css/flexslider.css" type="text/css" media="screen" />
<script>
// Can also be used with $(document).ready()
$(window).load(function() {
  $('.flexslider').flexslider({
    animation: "slide",
    controlNav: "thumbnails"
  });
});
</script>   
</html>
