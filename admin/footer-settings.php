<?php
session_start();
include_once("../includes/dbconn.php");

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

include_once("../includes/admin-header.php");
?>

            <h1 class="page-header">Footer Settings</h1>

            <div class="row">
                <div class="col-md-12">
                    <!-- General Settings Form -->
                    <div class="panel panel-default">
                        <div class="panel-heading">General Settings</div>
                        <div class="panel-body">
                            <form id="footerSettingsForm" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_settings">
                                
                                <div class="form-group">
                                    <label>Background Image</label>
                                    <input type="file" name="background_image" class="form-control">
                                    <div id="currentBgImage" style="margin-top: 10px;"></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Address</label>
                                            <textarea name="address" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Phone</label>
                                            <input type="text" name="phone" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Copyright Text</label>
                                    <input type="text" name="copyright_text" class="form-control">
                                </div>

                                <h4>Social Links</h4>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Facebook</label>
                                            <input type="text" name="facebook_link" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Twitter</label>
                                            <input type="text" name="twitter_link" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Instagram</label>
                                            <input type="text" name="instagram_link" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>YouTube</label>
                                            <input type="text" name="youtube_link" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                    </div>

                    <!-- Footer Links Management -->
                    <div class="panel panel-default">
                        <div class="panel-heading">Footer Links</div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Column 1 (Help & Support)</h4>
                                    <ul id="links-col-quick_links" class="list-group"></ul>
                                    <form class="add-link-form" data-col="quick_links">
                                        <div class="input-group">
                                            <input type="text" name="link_label" class="form-control" placeholder="Link Label" required>
                                            <input type="text" name="link_url" class="form-control" placeholder="URL" required>
                                            <span class="input-group-btn">
                                                <button class="btn btn-success" type="submit">+</button>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <h4>Column 2 (Quick Links)</h4>
                                    <ul id="links-col-links" class="list-group"></ul>
                                    <form class="add-link-form" data-col="links">
                                        <div class="input-group">
                                            <input type="text" name="link_label" class="form-control" placeholder="Link Label" required>
                                            <input type="text" name="link_url" class="form-control" placeholder="URL" required>
                                            <span class="input-group-btn">
                                                <button class="btn btn-success" type="submit">+</button>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

<script>
$(document).ready(function() {
    loadSettings();

    function loadSettings() {
        $.get('api/footer-settings.php', function(data) {
            // Populate settings
            if (data.settings) {
                $('textarea[name="address"]').val(data.settings.address);
                $('input[name="phone"]').val(data.settings.phone);
                $('input[name="email"]').val(data.settings.email);
                $('input[name="copyright_text"]').val(data.settings.copyright_text);
                $('input[name="facebook_link"]').val(data.settings.facebook_link);
                $('input[name="twitter_link"]').val(data.settings.twitter_link);
                $('input[name="instagram_link"]').val(data.settings.instagram_link);
                $('input[name="youtube_link"]').val(data.settings.youtube_link);

                if (data.settings.background_image) {
                    $('#currentBgImage').html('<img src="../uploads/homepage/' + data.settings.background_image + '" style="max-width: 200px;">');
                }
            }

            // Populate links
            $('#links-col-quick_links, #links-col-links').empty();
            if (data.links) {
                data.links.forEach(function(link) {
                    var li = '<li class="list-group-item">' + 
                             link.link_label + ' (' + link.link_url + ')' +
                             '<button class="btn btn-danger btn-xs pull-right delete-link" data-id="' + link.id + '">&times;</button>' +
                             '</li>';
                    $('#links-col-' + link.column_name).append(li);
                });
            }
        });
    }

    $('#footerSettingsForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'api/footer-settings.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                alert('Settings saved successfully');
                loadSettings();
            },
            error: function() {
                alert('Error saving settings');
            }
        });
    });

    $('.add-link-form').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var col = form.data('col');
        var label = form.find('input[name="link_label"]').val();
        var url = form.find('input[name="link_url"]').val();

        $.post('api/footer-settings.php', {
            action: 'add_link',
            column_name: col,
            link_label: label,
            link_url: url
        }, function(response) {
            loadSettings();
            form[0].reset();
        });
    });

    $(document).on('click', '.delete-link', function() {
        if (confirm('Are you sure?')) {
            var id = $(this).data('id');
            $.post('api/footer-settings.php', {
                action: 'delete_link',
                id: id
            }, function(response) {
                loadSettings();
            });
        }
    });
});
</script>

<?php include_once("../includes/admin-footer.php"); ?>
