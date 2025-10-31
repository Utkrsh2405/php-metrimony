<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("../includes/dbconn.php");

$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    header("Location: /index.php");
    exit();
}

$page_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page = null;

if ($page_id > 0) {
    $result = mysqli_query($conn, "SELECT * FROM cms_pages WHERE id = $page_id");
    $page = mysqli_fetch_assoc($result);
}

include("../includes/admin-header.php");
?>

<!-- TinyMCE Editor -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<div class="admin-content">
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-6">
            <h1><?= $page ? 'Edit Page' : 'Create New Page' ?></h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="pages.php" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Pages
            </a>
        </div>
    </div>
    
    <form id="page-form">
        <input type="hidden" name="id" value="<?= $page ? $page['id'] : '' ?>">
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Page Title *</label>
                            <input type="text" name="title" class="form-control" 
                                   value="<?= $page ? htmlspecialchars($page['title']) : '' ?>" 
                                   required onkeyup="generateSlug(this.value)">
                        </div>
                        
                        <div class="form-group">
                            <label>Slug *</label>
                            <input type="text" name="slug" id="slug" class="form-control" 
                                   value="<?= $page ? htmlspecialchars($page['slug']) : '' ?>" 
                                   required pattern="[a-z0-9-]+">
                            <small class="text-muted">URL-friendly name (lowercase, hyphens only)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Page Content</label>
                            <textarea name="content" id="content" class="form-control" rows="20"><?= $page ? htmlspecialchars($page['content']) : '' ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>SEO Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Meta Title</label>
                            <input type="text" name="meta_title" class="form-control" 
                                   value="<?= $page ? htmlspecialchars($page['meta_title']) : '' ?>"
                                   maxlength="60">
                            <small class="text-muted">Recommended: 50-60 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Meta Description</label>
                            <textarea name="meta_description" class="form-control" rows="3" 
                                      maxlength="160"><?= $page ? htmlspecialchars($page['meta_description']) : '' ?></textarea>
                            <small class="text-muted">Recommended: 150-160 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control" 
                                   value="<?= $page ? htmlspecialchars($page['meta_keywords']) : '' ?>">
                            <small class="text-muted">Comma-separated keywords</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Publish</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="draft" <?= $page && $page['status'] == 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= !$page || $page['status'] == 'published' ? 'selected' : '' ?>>Published</option>
                            </select>
                        </div>
                        
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_featured" value="1" 
                                       <?= $page && $page['is_featured'] == 1 ? 'checked' : '' ?>>
                                <strong>Featured Page</strong>
                            </label>
                            <br><small class="text-muted">Show in footer navigation</small>
                        </div>
                        
                        <?php if ($page): ?>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                            <small class="text-muted">
                                <strong>Views:</strong> <?= $page['view_count'] ?><br>
                                <strong>Created:</strong> <?= date('M j, Y', strtotime($page['created_at'])) ?><br>
                                <strong>Updated:</strong> <?= date('M j, Y', strtotime($page['updated_at'])) ?>
                            </small>
                        </div>
                        <?php endif; ?>
                        
                        <div style="margin-top: 20px;">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-save"></i> <?= $page ? 'Update Page' : 'Publish Page' ?>
                            </button>
                            <?php if ($page): ?>
                            <a href="/page.php?slug=<?= $page['slug'] ?>" target="_blank" class="btn btn-info btn-block">
                                <i class="fa fa-eye"></i> Preview Page
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Tips</h3>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <ul style="padding-left: 15px; margin: 0;">
                                <li>Use descriptive titles for better SEO</li>
                                <li>Keep URLs short and readable</li>
                                <li>Add meta description for search results</li>
                                <li>Use headings (H2, H3) to structure content</li>
                                <li>Add images with alt text</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Initialize TinyMCE
tinymce.init({
    selector: '#content',
    height: 500,
    menubar: false,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code fullscreen',
    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
    relative_urls: false,
    remove_script_host: false,
    document_base_url: window.location.origin
});

function generateSlug(title) {
    // Only auto-generate slug for new pages
    <?php if (!$page): ?>
    const slug = title.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('slug').value = slug;
    <?php endif; ?>
}

$('#page-form').on('submit', function(e) {
    e.preventDefault();
    
    // Get content from TinyMCE
    const content = tinymce.get('content').getContent();
    
    const formData = {
        id: $('[name="id"]').val(),
        title: $('[name="title"]').val(),
        slug: $('[name="slug"]').val(),
        content: content,
        meta_title: $('[name="meta_title"]').val(),
        meta_description: $('[name="meta_description"]').val(),
        meta_keywords: $('[name="meta_keywords"]').val(),
        status: $('[name="status"]').val(),
        is_featured: $('[name="is_featured"]').is(':checked') ? 1 : 0
    };
    
    $.ajax({
        url: '/admin/api/pages.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                window.location.href = 'pages.php';
            } else {
                alert('Error: ' + response.error);
            }
        },
        error: function() {
            alert('Failed to save page. Please try again.');
        }
    });
});
</script>

<?php include("../includes/admin-footer.php"); ?>
