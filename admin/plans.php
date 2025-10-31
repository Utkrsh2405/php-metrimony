<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("../includes/dbconn.php");

// Verify admin status
$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    header("Location: /index.php");
    exit();
}

include("../includes/admin-header.php");
?>

<div class="admin-content">
    <h1>Subscription Plans Management</h1>
    <p class="text-muted">Create and manage subscription plans for members</p>
    
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-12">
            <button id="add-plan-btn" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add New Plan
            </button>
            <button id="reorder-btn" class="btn btn-info">
                <i class="fa fa-sort"></i> Reorder Plans
            </button>
        </div>
    </div>
    
    <!-- Plans Grid -->
    <div id="loading" style="text-align: center; padding: 40px; display: none;">
        <i class="fa fa-spinner fa-spin fa-3x"></i>
        <p>Loading plans...</p>
    </div>
    
    <div id="plans-container" class="row">
        <!-- Populated by JavaScript -->
    </div>
    
    <!-- Reorder Mode -->
    <div id="reorder-container" style="display: none;">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> Drag and drop plans to reorder them. Click "Save Order" when done.
        </div>
        <div id="sortable-plans" class="row">
            <!-- Populated by JavaScript -->
        </div>
        <button id="save-order-btn" class="btn btn-success">
            <i class="fa fa-save"></i> Save Order
        </button>
        <button id="cancel-reorder-btn" class="btn btn-default">
            <i class="fa fa-times"></i> Cancel
        </button>
    </div>
</div>

<!-- Plan Edit Modal -->
<div class="modal fade" id="planModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modal-title">Add New Plan</h4>
            </div>
            <form id="plan-form">
                <div class="modal-body">
                    <input type="hidden" id="plan-id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Plan Name *</label>
                                <input type="text" class="form-control" id="plan-name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Price (₹) *</label>
                                <input type="number" class="form-control" id="plan-price" name="price" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Duration (Days) *</label>
                                <input type="number" class="form-control" id="plan-duration" name="duration_days" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" id="plan-description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Max Contacts</label>
                                <input type="number" class="form-control" id="plan-max-contacts" name="max_contacts" value="0">
                                <small class="text-muted">0 = Unlimited</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Max Messages</label>
                                <input type="number" class="form-control" id="plan-max-messages" name="max_messages" value="0">
                                <small class="text-muted">0 = Unlimited</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Max Interests</label>
                                <input type="number" class="form-control" id="plan-max-interests" name="max_interests" value="0">
                                <small class="text-muted">0 = Unlimited</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Max Shortlist</label>
                                <input type="number" class="form-control" id="plan-max-shortlist" name="max_shortlist" value="0">
                                <small class="text-muted">0 = Unlimited</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Features</label>
                        <div id="features-list">
                            <!-- Dynamic feature inputs -->
                        </div>
                        <button type="button" class="btn btn-sm btn-default" id="add-feature-btn">
                            <i class="fa fa-plus"></i> Add Feature
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Display Order</label>
                                <input type="number" class="form-control" id="plan-display-order" name="display_order" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="checkbox" style="margin-top: 30px;">
                                <label>
                                    <input type="checkbox" id="plan-is-active" name="is_active" checked>
                                    <strong>Active (Visible to members)</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Confirm Delete</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to deactivate this plan?</p>
                <p class="text-muted">Plans with active subscriptions cannot be deleted, only deactivated.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script>
let plans = [];
let featureCount = 0;
let pendingDeleteId = null;

$(document).ready(function() {
    loadPlans();
    
    $('#add-plan-btn').click(function() {
        openPlanModal();
    });
    
    $('#reorder-btn').click(function() {
        enableReorderMode();
    });
    
    $('#cancel-reorder-btn').click(function() {
        disableReorderMode();
    });
    
    $('#save-order-btn').click(function() {
        saveOrder();
    });
    
    $('#add-feature-btn').click(function() {
        addFeatureInput();
    });
    
    $('#plan-form').submit(function(e) {
        e.preventDefault();
        savePlan();
    });
    
    $('#confirm-delete-btn').click(function() {
        deletePlan(pendingDeleteId);
        $('#confirmModal').modal('hide');
    });
});

function loadPlans() {
    $('#loading').show();
    $('#plans-container').hide();
    
    $.ajax({
        url: '/admin/api/plans.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                plans = response.data;
                renderPlans();
            } else {
                alert('Error loading plans: ' + (response.error || 'Unknown error'));
            }
            $('#loading').hide();
            $('#plans-container').show();
        },
        error: function() {
            alert('Failed to load plans');
            $('#loading').hide();
        }
    });
}

function renderPlans() {
    const container = $('#plans-container');
    container.empty();
    
    if (plans.length === 0) {
        container.append('<div class="col-md-12"><p class="text-center text-muted">No plans found. Click "Add New Plan" to create one.</p></div>');
        return;
    }
    
    plans.forEach(function(plan) {
        const activeClass = plan.is_active == 1 ? 'success' : 'default';
        const activeText = plan.is_active == 1 ? 'Active' : 'Inactive';
        
        let featuresHtml = '';
        if (plan.features && plan.features.length > 0) {
            plan.features.forEach(function(feature) {
                featuresHtml += `<li><i class="fa fa-check text-success"></i> ${feature}</li>`;
            });
        }
        
        const card = `
            <div class="col-md-4">
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <h3 style="margin: 0;">${plan.name}</h3>
                        <div style="font-size: 28px; font-weight: bold; color: #2c3e50; margin: 10px 0;">
                            ₹${parseFloat(plan.price).toFixed(2)}
                            <small style="font-size: 14px; color: #7f8c8d;">/${plan.duration_days} days</small>
                        </div>
                        <span class="label label-${activeClass}">${activeText}</span>
                        ${plan.active_subscriptions > 0 ? `<span class="label label-info">${plan.active_subscriptions} active</span>` : ''}
                    </div>
                    <div class="card-body">
                        ${plan.description ? `<p class="text-muted">${plan.description}</p>` : ''}
                        
                        <h4>Quotas:</h4>
                        <ul class="list-unstyled">
                            <li><i class="fa fa-users"></i> Contacts: ${plan.max_contacts == 0 ? 'Unlimited' : plan.max_contacts}</li>
                            <li><i class="fa fa-envelope"></i> Messages: ${plan.max_messages == 0 ? 'Unlimited' : plan.max_messages}</li>
                            <li><i class="fa fa-heart"></i> Interests: ${plan.max_interests == 0 ? 'Unlimited' : plan.max_interests}</li>
                            <li><i class="fa fa-star"></i> Shortlist: ${plan.max_shortlist == 0 ? 'Unlimited' : plan.max_shortlist}</li>
                        </ul>
                        
                        ${featuresHtml ? `<h4>Features:</h4><ul>${featuresHtml}</ul>` : ''}
                        
                        <div class="btn-group btn-group-justified" role="group">
                            <div class="btn-group" role="group">
                                <button class="btn btn-primary" onclick="editPlan(${plan.id})">
                                    <i class="fa fa-edit"></i> Edit
                                </button>
                            </div>
                            <div class="btn-group" role="group">
                                <button class="btn btn-danger" onclick="confirmDelete(${plan.id})">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.append(card);
    });
}

function openPlanModal(plan = null) {
    featureCount = 0;
    $('#features-list').empty();
    
    if (plan) {
        $('#modal-title').text('Edit Plan');
        $('#plan-id').val(plan.id);
        $('#plan-name').val(plan.name);
        $('#plan-price').val(plan.price);
        $('#plan-duration').val(plan.duration_days);
        $('#plan-description').val(plan.description);
        $('#plan-max-contacts').val(plan.max_contacts);
        $('#plan-max-messages').val(plan.max_messages);
        $('#plan-max-interests').val(plan.max_interests);
        $('#plan-max-shortlist').val(plan.max_shortlist);
        $('#plan-display-order').val(plan.display_order);
        $('#plan-is-active').prop('checked', plan.is_active == 1);
        
        if (plan.features && plan.features.length > 0) {
            plan.features.forEach(function(feature) {
                addFeatureInput(feature);
            });
        }
    } else {
        $('#modal-title').text('Add New Plan');
        $('#plan-form')[0].reset();
        $('#plan-id').val('');
        $('#plan-is-active').prop('checked', true);
    }
    
    // Add at least one feature input if none exist
    if (featureCount === 0) {
        addFeatureInput();
    }
    
    $('#planModal').modal('show');
}

function addFeatureInput(value = '') {
    featureCount++;
    const html = `
        <div class="input-group" style="margin-bottom: 10px;" id="feature-${featureCount}">
            <input type="text" class="form-control feature-input" placeholder="Enter feature" value="${value}">
            <span class="input-group-btn">
                <button class="btn btn-danger" type="button" onclick="removeFeature(${featureCount})">
                    <i class="fa fa-times"></i>
                </button>
            </span>
        </div>
    `;
    $('#features-list').append(html);
}

function removeFeature(id) {
    $(`#feature-${id}`).remove();
}

function savePlan() {
    const formData = {
        id: $('#plan-id').val(),
        name: $('#plan-name').val(),
        price: $('#plan-price').val(),
        duration_days: $('#plan-duration').val(),
        description: $('#plan-description').val(),
        max_contacts: $('#plan-max-contacts').val(),
        max_messages: $('#plan-max-messages').val(),
        max_interests: $('#plan-max-interests').val(),
        max_shortlist: $('#plan-max-shortlist').val(),
        display_order: $('#plan-display-order').val(),
        is_active: $('#plan-is-active').is(':checked'),
        features: []
    };
    
    // Collect features
    $('.feature-input').each(function() {
        const value = $(this).val().trim();
        if (value) {
            formData.features.push(value);
        }
    });
    
    $.ajax({
        url: '/admin/api/plans.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#planModal').modal('hide');
                loadPlans();
            } else {
                alert('Error: ' + (response.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Failed to save plan');
        }
    });
}

function editPlan(planId) {
    const plan = plans.find(p => p.id == planId);
    if (plan) {
        openPlanModal(plan);
    }
}

function confirmDelete(planId) {
    pendingDeleteId = planId;
    $('#confirmModal').modal('show');
}

function deletePlan(planId) {
    $.ajax({
        url: '/admin/api/plans.php?id=' + planId,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                loadPlans();
            } else {
                alert('Error: ' + (response.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Failed to delete plan');
        }
    });
}

function enableReorderMode() {
    $('#plans-container').hide();
    $('#add-plan-btn, #reorder-btn').hide();
    $('#reorder-container').show();
    
    const sortable = $('#sortable-plans');
    sortable.empty();
    
    plans.forEach(function(plan) {
        const item = `
            <div class="col-md-4" data-plan-id="${plan.id}" style="cursor: move;">
                <div class="card" style="margin-bottom: 20px; border: 2px dashed #ccc;">
                    <div class="card-body text-center">
                        <i class="fa fa-bars fa-2x" style="color: #ccc;"></i>
                        <h3>${plan.name}</h3>
                        <p class="text-muted">₹${plan.price} / ${plan.duration_days} days</p>
                    </div>
                </div>
            </div>
        `;
        sortable.append(item);
    });
    
    sortable.sortable({
        placeholder: "ui-state-highlight",
        forcePlaceholderSize: true
    });
}

function disableReorderMode() {
    $('#sortable-plans').sortable('destroy');
    $('#reorder-container').hide();
    $('#plans-container').show();
    $('#add-plan-btn, #reorder-btn').show();
}

function saveOrder() {
    const order = [];
    let index = 0;
    
    $('#sortable-plans .col-md-4').each(function() {
        const planId = $(this).data('plan-id');
        order.push({ id: planId, order: index });
        index++;
    });
    
    $.ajax({
        url: '/admin/api/plans.php',
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({ reorder: true, order: order }),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                disableReorderMode();
                loadPlans();
            } else {
                alert('Error: ' + (response.error || 'Unknown error'));
            }
        },
        error: function() {
            alert('Failed to save order');
        }
    });
}
</script>

<?php include("../includes/admin-footer.php"); ?>
