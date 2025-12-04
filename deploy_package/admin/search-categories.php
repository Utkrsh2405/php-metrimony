<?php
session_start();
require_once("../includes/dbconn.php");
require_once("../includes/security.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || $_SESSION['userlevel'] != 1) {
    header("Location: login.php");
    exit();
}

$page_title = "Search Categories";
include_once("../includes/admin-header.php");
?>

<div class="container-fluid">
    <h1><i class="fa fa-search"></i> Search Categories Manager</h1>
    <p class="text-muted">Manage categories shown in "Search Profiles By" section</p>
    <hr>

    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#location" aria-controls="location" role="tab" data-toggle="tab"><i class="fa fa-map-marker"></i> Location</a></li>
        <li role="presentation"><a href="#religion" aria-controls="religion" role="tab" data-toggle="tab"><i class="fa fa-book"></i> Religion</a></li>
        <li role="presentation"><a href="#community" aria-controls="community" role="tab" data-toggle="tab"><i class="fa fa-users"></i> Community</a></li>
    </ul>

    <div class="tab-content" style="margin-top: 20px;">
        <!-- Location Tab -->
        <div role="tabpanel" class="tab-pane active" id="location">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Location Categories</h3>
                </div>
                <div class="panel-body">
                    <button class="btn btn-success btn-sm" onclick="showAddModal('location')"><i class="fa fa-plus"></i> Add Location</button>
                    <hr>
                    <table class="table table-striped" id="locationTable">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Name</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Religion Tab -->
        <div role="tabpanel" class="tab-pane" id="religion">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Religion Categories</h3>
                </div>
                <div class="panel-body">
                    <button class="btn btn-success btn-sm" onclick="showAddModal('religion')"><i class="fa fa-plus"></i> Add Religion</button>
                    <hr>
                    <table class="table table-striped" id="religionTable">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Name</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Community Tab -->
        <div role="tabpanel" class="tab-pane" id="community">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Community Categories</h3>
                </div>
                <div class="panel-body">
                    <button class="btn btn-success btn-sm" onclick="showAddModal('community')"><i class="fa fa-plus"></i> Add Community</button>
                    <hr>
                    <table class="table table-striped" id="communityTable">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Name</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add Category</h4>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="category_id">
                    <input type="hidden" name="category_type" id="category_type">
                    <div class="form-group">
                        <label>Category Name *</label>
                        <input type="text" class="form-control" name="category_name" id="category_name" required>
                    </div>
                    <div class="form-group">
                        <label>Category Value *</label>
                        <input type="text" class="form-control" name="category_value" id="category_value" required>
                        <p class="help-block">Used for search filtering</p>
                    </div>
                    <div class="form-group">
                        <label>Display Order</label>
                        <input type="number" class="form-control" name="category_order" id="category_order" value="0">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" id="is_active" value="1" checked> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadCategories('location');
    loadCategories('religion');
    loadCategories('community');

    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        saveCategory();
    });
});

function loadCategories(type) {
    $.get('/admin/api/search-categories.php?type=' + type, function(response) {
        if (response.success && response.data) {
            var tbody = $('#' + type + 'Table tbody');
            tbody.empty();
            response.data.forEach(function(cat) {
                var row = '<tr>' +
                    '<td>' + cat.category_order + '</td>' +
                    '<td>' + cat.category_name + '</td>' +
                    '<td>' + cat.category_value + '</td>' +
                    '<td>' + (cat.is_active == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>') + '</td>' +
                    '<td>' +
                        '<button class="btn btn-xs btn-primary" onclick="editCategory(' + cat.id + ')"><i class="fa fa-edit"></i></button> ' +
                        '<button class="btn btn-xs btn-danger" onclick="deleteCategory(' + cat.id + ', \'' + type + '\')"><i class="fa fa-trash"></i></button>' +
                    '</td>' +
                '</tr>';
                tbody.append(row);
            });
        }
    }, 'json');
}

function showAddModal(type) {
    $('#categoryModal .modal-title').text('Add ' + type.charAt(0).toUpperCase() + type.slice(1));
    $('#categoryForm')[0].reset();
    $('#category_id').val('');
    $('#category_type').val(type);
    $('#is_active').prop('checked', true);
    $('#categoryModal').modal('show');
}

function editCategory(id) {
    $.get('/admin/api/search-categories.php?id=' + id, function(response) {
        if (response.success && response.data) {
            var cat = response.data;
            $('#categoryModal .modal-title').text('Edit Category');
            $('#category_id').val(cat.id);
            $('#category_type').val(cat.category_type);
            $('#category_name').val(cat.category_name);
            $('#category_value').val(cat.category_value);
            $('#category_order').val(cat.category_order);
            $('#is_active').prop('checked', cat.is_active == 1);
            $('#categoryModal').modal('show');
        }
    }, 'json');
}

function saveCategory() {
    var formData = $('#categoryForm').serialize();
    $.ajax({
        url: '/admin/api/search-categories.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('✓ Category saved successfully!');
                $('#categoryModal').modal('hide');
                var type = $('#category_type').val();
                loadCategories(type);
            } else {
                alert('Error: ' + (response.message || 'Failed to save category'));
            }
        },
        error: function() {
            alert('Error: Failed to connect to server');
        }
    });
}

function deleteCategory(id, type) {
    if (confirm('Are you sure you want to delete this category?')) {
        $.ajax({
            url: '/admin/api/search-categories.php',
            method: 'DELETE',
            data: {id: id},
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('✓ Category deleted successfully!');
                    loadCategories(type);
                } else {
                    alert('Error: ' + (response.message || 'Failed to delete category'));
                }
            }
        });
    }
}
</script>

<?php include_once("../includes/admin-footer.php"); ?>
