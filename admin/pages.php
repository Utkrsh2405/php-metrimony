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

include("../includes/admin-header.php");
?>

<div class="admin-content">
    <h1>CMS Pages Management</h1>
    <p class="text-muted">Create and manage content pages</p>
    
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-6">
            <a href="page-edit.php" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add New Page
            </a>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6">
                    <select id="status-filter" class="form-control">
                        <option value="">All Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" id="search" class="form-control" placeholder="Search pages...">
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Author</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="pages-tbody">
                    <tr><td colspan="7" class="text-center">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadPages();
    
    $('#status-filter, #search').on('change keyup', function() {
        loadPages();
    });
});

function loadPages() {
    const status = $('#status-filter').val();
    const search = $('#search').val();
    
    $.get(`/admin/api/pages.php?status=${status}&search=${search}`, function(response) {
        if (response.success) {
            renderPages(response.data);
        }
    });
}

function renderPages(pages) {
    const tbody = $('#pages-tbody');
    tbody.empty();
    
    if (pages.length === 0) {
        tbody.append('<tr><td colspan="7" class="text-center">No pages found</td></tr>');
        return;
    }
    
    pages.forEach(page => {
        const status = page.status == 'published' 
            ? '<span class="label label-success">Published</span>' 
            : '<span class="label label-default">Draft</span>';
        
        const featured = page.is_featured == 1 ? '<span class="label label-warning">Featured</span> ' : '';
        const updated = new Date(page.updated_at).toLocaleDateString();
        
        tbody.append(`
            <tr>
                <td>${featured}${page.title}</td>
                <td><code>${page.slug}</code></td>
                <td>${status}</td>
                <td><span class="badge">${page.view_count || 0}</span></td>
                <td>${page.author || 'System'}</td>
                <td>${updated}</td>
                <td>
                    <a href="page-edit.php?id=${page.id}" class="btn btn-xs btn-primary">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                    <a href="/page.php?slug=${page.slug}" target="_blank" class="btn btn-xs btn-info">
                        <i class="fa fa-eye"></i> View
                    </a>
                    <button class="btn btn-xs btn-danger" onclick="deletePage(${page.id})">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function deletePage(id) {
    if (!confirm('Are you sure you want to delete this page?')) return;
    
    $.ajax({
        url: `/admin/api/pages.php?id=${id}`,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                loadPages();
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}
</script>

<?php include("../includes/admin-footer.php"); ?>
