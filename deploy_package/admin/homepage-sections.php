<?php
session_start();
require_once("../includes/dbconn.php");
require_once("../includes/security.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || $_SESSION['userlevel'] != 1) {
    header("Location: login.php");
    exit();
}

$page_title = "Homepage Sections";
include_once("../includes/admin-header.php");
?>

<div class="container-fluid">
    <h1><i class="fa fa-home"></i> Homepage Sections Manager</h1>
    <p class="text-muted">Manage all sections displayed on your homepage including images and content</p>
    <hr>

    <!-- Hero Section -->
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-star"></i> Hero Section (Banner)</h3>
        </div>
        <div class="panel-body">
            <form id="heroForm" class="form-horizontal" enctype="multipart/form-data">
                <input type="hidden" name="section_key" value="hero">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Background Image</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control" name="hero_image" id="hero_image" accept="image/*">
                        <p class="help-block">Upload a new banner background image (recommended: 1920x600px)</p>
                        <div id="hero_image_preview" style="margin-top: 10px;"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Main Title</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="section_title" id="hero_title" placeholder="e.g., Shaadi Partner">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Subtitle</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="section_subtitle" id="hero_subtitle" placeholder="e.g., Love is Looking for You">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Description</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" name="section_content" id="hero_content" rows="3"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <label>
                            <input type="checkbox" name="is_active" id="hero_active" value="1"> Display this section
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-save"></i> Save Hero Section</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- About Section -->
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-info-circle"></i> About / Service Section</h3>
        </div>
        <div class="panel-body">
            <form id="aboutForm" class="form-horizontal" enctype="multipart/form-data">
                <input type="hidden" name="section_key" value="about">
                <div class="form-group">
                    <label class="col-sm-2 control-label">About Image</label>
                    <div class="col-sm-10">
                        <input type="file" class="form-control" name="about_image" id="about_image" accept="image/*">
                        <p class="help-block">Upload an image for the about section (recommended: 800x600px)</p>
                        <div id="about_image_preview" style="margin-top: 10px;"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Main Title</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="section_title" id="about_title" placeholder="e.g., Shaadi Partner">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Subtitle</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="section_subtitle" id="about_subtitle" placeholder="e.g., JOIN US EXCLUSIVE MATCHMAKING SERVICE FOR">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Description</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" name="section_content" id="about_content" rows="5"></textarea>
                        <p class="help-block">Detailed description of your service</p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <label>
                            <input type="checkbox" name="is_active" id="about_active" value="1"> Display this section
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-info btn-lg"><i class="fa fa-save"></i> Save About Section</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bride & Groom Section -->
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-heart"></i> Bride & Groom Section</h3>
        </div>
        <div class="panel-body">
            <form id="brideGroomForm" class="form-horizontal">
                <input type="hidden" name="section_key" value="bride_groom">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Section Title</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="section_title" id="bride_groom_title" placeholder="e.g., Bride & Groom">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Subtitle</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="section_subtitle" id="bride_groom_subtitle" placeholder="e.g., Featured Matches">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <label>
                            <input type="checkbox" name="is_active" id="bride_groom_active" value="1"> Display this section
                        </label>
                        <p class="help-block">This section automatically displays featured profiles from your database</p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-save"></i> Save Bride & Groom Section</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Profiles By Section -->
    <div class="panel panel-warning">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-search"></i> Search Profiles By Section</h3>
        </div>
        <div class="panel-body">
            <form id="searchByForm" class="form-horizontal">
                <input type="hidden" name="section_key" value="search_by">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Section Title</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="section_title" id="search_by_title" placeholder="e.g., Search Profiles By">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <label>
                            <input type="checkbox" name="is_active" id="search_by_active" value="1"> Display this section
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-warning btn-lg"><i class="fa fa-save"></i> Save Search By Section</button>
                        <a href="search-categories.php" class="btn btn-default btn-lg"><i class="fa fa-cog"></i> Manage Search Categories</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load existing data
    loadSections();

    // Handle form submissions with file upload
    $('#heroForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        saveSectionWithImage('hero', formData);
    });

    $('#aboutForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        saveSectionWithImage('about', formData);
    });

    $('#brideGroomForm').on('submit', function(e) {
        e.preventDefault();
        saveSection('bride_groom', $(this).serialize());
    });

    $('#searchByForm').on('submit', function(e) {
        e.preventDefault();
        saveSection('search_by', $(this).serialize());
    });

    // Image preview handlers
    $('#hero_image').on('change', function() {
        previewImage(this, 'hero_image_preview');
    });

    $('#about_image').on('change', function() {
        previewImage(this, 'about_image_preview');
    });
});

function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#' + previewId).html('<img src="' + e.target.result + '" style="max-width: 300px; max-height: 200px; border-radius: 5px; border: 2px solid #ddd;">');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function loadSections() {
    $.get('/admin/api/homepage-sections.php', function(response) {
        if (response.success && response.data) {
            response.data.forEach(function(section) {
                var prefix = section.section_key;
                $('#' + prefix + '_title').val(section.section_title);
                $('#' + prefix + '_subtitle').val(section.section_subtitle);
                $('#' + prefix + '_content').val(section.section_content);
                $('#' + prefix + '_active').prop('checked', section.is_active == 1);
                
                // Show existing image if available
                if(section.section_image) {
                    var imageUrl = '../' + section.section_image;
                    $('#' + prefix + '_image_preview').html('<div><strong>Current Image:</strong><br><img src="' + imageUrl + '" style="max-width: 300px; max-height: 200px; border-radius: 5px; border: 2px solid #ddd; margin-top: 10px;"></div>');
                }
            });
        }
    }, 'json');
}

function saveSectionWithImage(key, formData) {
    // Show loading indicator
    var btn = $('#' + key + 'Form button[type="submit"]');
    var originalText = btn.html();
    btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    
    $.ajax({
        url: '/admin/api/homepage-sections.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            btn.html(originalText).prop('disabled', false);
            if (response.success) {
                alert('✓ Section saved successfully!');
                loadSections(); // Reload to show new image
            } else {
                alert('Error: ' + (response.message || 'Failed to save section'));
            }
        },
        error: function(xhr, status, error) {
            btn.html(originalText).prop('disabled', false);
            console.error('AJAX Error:', status, error);
            console.error('Response:', xhr.responseText);
            alert('Error: Failed to connect to server. Check console for details.');
        }
    });
}

function saveSection(key, data) {
    // Show loading indicator
    var btn = $('#' + key + 'Form button[type="submit"]');
    var originalText = btn.html();
    btn.html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    
    $.ajax({
        url: '/admin/api/homepage-sections.php',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            btn.html(originalText).prop('disabled', false);
            if (response.success) {
                alert('✓ Section saved successfully!');
            } else {
                alert('Error: ' + (response.message || 'Failed to save section'));
            }
        },
        error: function(xhr, status, error) {
            btn.html(originalText).prop('disabled', false);
            console.error('AJAX Error:', status, error);
            console.error('Response:', xhr.responseText);
            alert('Error: Failed to connect to server. Check console for details.');
        }
    });
}
</script>

<?php include_once("../includes/admin-footer.php"); ?>
