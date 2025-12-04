<?php
session_start();
require_once("includes/dbconn.php");

// Get page by slug
$slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, $_GET['slug']) : '';

if (empty($slug)) {
    header("Location: /index.php");
    exit();
}

// Fetch page
$query = "SELECT * FROM cms_pages WHERE slug = '$slug' AND status = 'published'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header("Location: /index.php");
    exit();
}

$page = mysqli_fetch_assoc($result);

// Increment view count
mysqli_query($conn, "UPDATE cms_pages SET view_count = view_count + 1 WHERE id = " . $page['id']);

// Set meta tags
$meta_title = !empty($page['meta_title']) ? $page['meta_title'] : $page['title'];
$meta_description = $page['meta_description'];
$meta_keywords = $page['meta_keywords'];
?>

<!DOCTYPE HTML>
<html>
<head>
<title><?php echo htmlspecialchars($meta_title); ?> - MakeMyLove</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php if (!empty($meta_description)): ?>
<meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
<?php endif; ?>
<?php if (!empty($meta_keywords)): ?>
<meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
<?php endif; ?>

<link href="css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<link href="css/font-awesome.css" rel="stylesheet"> 
<link href="css/style.css" rel='stylesheet' type='text/css' />
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>

<style>
.page-header-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
    text-align: center;
}
.page-header-section h1 {
    margin: 0;
    font-size: 42px;
    font-weight: bold;
}
.page-content {
    padding: 60px 0;
    background: #fff;
}
.page-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 20px 0;
}
.page-content h2 {
    margin-top: 40px;
    margin-bottom: 20px;
    color: #333;
}
.page-content h3 {
    margin-top: 30px;
    margin-bottom: 15px;
    color: #555;
}
.page-content p {
    line-height: 1.8;
    margin-bottom: 20px;
    color: #666;
}
.page-content ul, .page-content ol {
    margin-bottom: 20px;
    padding-left: 30px;
}
.page-content li {
    margin-bottom: 10px;
    line-height: 1.6;
}
.breadcrumb-section {
    background: #f8f9fa;
    padding: 15px 0;
}
</style>
</head>
<body>

<!-- Breadcrumb -->
<div class="breadcrumb-section">
    <div class="container">
        <ol class="breadcrumb">
            <li><a href="/index.php">Home</a></li>
            <li class="active"><?php echo htmlspecialchars($page['title']); ?></li>
        </ol>
    </div>
</div>

<!-- Page Header -->
<div class="page-header-section">
    <div class="container">
        <h1><?php echo htmlspecialchars($page['title']); ?></h1>
    </div>
</div>

<!-- Page Content -->
<div class="page-content">
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <?php echo $page['content']; ?>
                
                <div style="margin-top: 60px; padding-top: 30px; border-top: 2px solid #eee; text-align: center; color: #999;">
                    <p>
                        <small>
                            Last updated: <?php echo date('F d, Y', strtotime($page['updated_at'])); ?> | 
                            Views: <?php echo number_format($page['view_count']); ?>
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer Links -->
<div style="background: #2c3e50; color: white; padding: 40px 0; text-align: center;">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php
                // Get featured pages for footer
                $featured = mysqli_query($conn, "SELECT title, slug FROM cms_pages WHERE is_featured = 1 AND status = 'published' ORDER BY title");
                if (mysqli_num_rows($featured) > 0):
                ?>
                <h4 style="margin-bottom: 20px;">Quick Links</h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php while ($link = mysqli_fetch_assoc($featured)): ?>
                    <li style="display: inline-block; margin: 0 15px;">
                        <a href="/page.php?slug=<?php echo $link['slug']; ?>" style="color: #ecf0f1; text-decoration: none;">
                            <?php echo htmlspecialchars($link['title']); ?>
                        </a>
                    </li>
                    <?php endwhile; ?>
                </ul>
                <?php endif; ?>
                
                <p style="margin-top: 30px; color: #95a5a6;">
                    © <?php echo date('Y'); ?> MakeMyLove. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
