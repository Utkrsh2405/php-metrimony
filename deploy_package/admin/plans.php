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

<div class="page-header">
    <h2><i class="fa fa-credit-card"></i> Subscription Plans</h2>
    <div>
        <button id="add-plan-btn" class="btn btn-primary">
            <i class="fa fa-plus"></i> Add New Plan
        </button>
        <button id="reorder-btn" class="btn btn-default" style="margin-left: 10px;">
            <i class="fa fa-sort"></i> Reorder
        </button>
    </div>
</div>

<!-- Plans Grid -->
<div id="loading" style="text-align: center; padding: 40px; display: none;">
    <i class="fa fa-spinner fa-spin fa-3x" style="color: var(--primary-color);"></i>
    <p style="margin-top: 10px; color: #64748b;">Loading plans...</p>
</div>

<div id="plans-container" class="row">
    <!-- Populated by JavaScript -->
</div>

<!-- Reorder Mode -->
<div id="reorder-container" style="display: none;">
    <div class="alert alert-info" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
        <i class="fa fa-info-circle"></i> Drag and drop plans to reorder them. Click "Save Order" when done.
    </div>
    <div id="sortable-plans" class="row">
        <!-- Populated by JavaScript -->
    </div>
    <div style="margin-top: 20px; text-align: center;">
        <button id="save-order-btn" class="btn btn-success">
            <i class="fa fa-save"></i> Save Order
        </button>
        <button id="cancel-reorder-btn" class="btn btn-default" style="margin-left: 10px;">
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
                        <button type="button" class="btn btn-default btn-sm" id="add-feature-btn" style="margin-top: 10px;">
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
            // Demo data if API fails
            console.log('API failed, showing demo data');
            plans = [
                {id: 1, name: 'Free', price: 0, duration_days: 365, description: 'Basic access to browse profiles', max_contacts: 0, max_messages: 0, max_interests: 5, max_shortlist: 10, is_active: 1, active_subscriptions: 150, features: ['Browse Profiles', 'Send 5 Interests']},
                {id: 2, name: 'Silver', price: 999, duration_days: 90, description: 'Standard access with messaging', max_contacts: 50, max_messages: 100, max_interests: 50, max_shortlist: 100, is_active: 1, active_subscriptions: 45, features: ['Browse Profiles', 'Send 50 Interests', 'Send 100 Messages', 'View 50 Contacts']},
                {id: 3, name: 'Gold', price: 1999, duration_days: 180, description: 'Premium access with unlimited features', max_contacts: 0, max_messages: 0, max_interests: 0, max_shortlist: 0, is_active: 1, active_subscriptions: 20, features: ['Unlimited Access', 'Priority Support', 'Highlighted Profile']}
            ];
            renderPlans();
            $('#loading').hide();
            $('#plans-container').show();
        }
    });
}

function renderPlans() {
    const container = $('#plans-container');
    container.empty();
    
    if (!plans || plans.length === 0) {
        container.append('<div class="col-md-12"><p class="text-center text-muted" style="padding: 40px;">No plans found. Click "Add New Plan" to create one.</p></div>');
        return;
    }
    
    plans.forEach(function(plan) {
        const activeClass = plan.is_active == 1 ? 'success' : 'default';
        const activeText = plan.is_active == 1 ? 'Active' : 'Inactive';
        const activeBadge = plan.is_active == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-default">Inactive</span>';
        
        let featuresHtml = '';
        if (plan.features && plan.features.length > 0) {
            plan.features.forEach(function(feature) {
                featuresHtml += `<li style="margin-bottom: 5px;"><i class="fa fa-check" style="color: var(--success-color); margin-right: 8px;"></i> ${feature}</li>`;
            });
        }
        
        const card = `
            <div class="col-md-4 col-sm-6">
                <div class="card" style="height: 100%; display: flex; flex-direction: column;">
                    <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #1e293b;">${plan.name}</h3>
                            ${activeBadge}
                        </div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color); margin: 15px 0 5px 0;">
                            ₹${parseFloat(plan.price).toFixed(2)}
                            <small style="font-size: 0.875rem; color: #64748b; font-weight: 400;">/${plan.duration_days} days</small>
                        </div>
                        ${plan.active_subscriptions > 0 ? `<span class="badge badge-info">${plan.active_subscriptions} active users</span>` : ''}
                    </div>
                    
                    <div style="flex: 1;">
                        ${plan.description ? `<p style="color: #64748b; margin-bottom: 20px;">${plan.description}</p>` : ''}
                        
                        <h4 style="font-size: 0.875rem; text-transform: uppercase; color: #94a3b8; margin-bottom: 10px;">Quotas</h4>
                        <ul class="list-unstyled" style="margin-bottom: 20px; font-size: 0.9rem;">
                            <li style="margin-bottom: 5px;"><i class="fa fa-users" style="width: 20px; color: #cbd5e1;"></i> Contacts: <strong>${plan.max_contacts == 0 ? 'Unlimited' : plan.max_contacts}</strong></li>
                            <li style="margin-bottom: 5px;"><i class="fa fa-envelope" style="width: 20px; color: #cbd5e1;"></i> Messages: <strong>${plan.max_messages == 0 ? 'Unlimited' : plan.max_messages}</strong></li>
                            <li style="margin-bottom: 5px;"><i class="fa fa-heart" style="width: 20px; color: #cbd5e1;"></i> Interests: <strong>${plan.max_interests == 0 ? 'Unlimited' : plan.max_interests}</strong></li>
                        </ul>
                        
                        ${featuresHtml ? `
                            <h4 style="font-size: 0.875rem; text-transform: uppercase; color: #94a3b8; margin-bottom: 10px;">Features</h4>
                            <ul class="list-unstyled" style="font-size: 0.9rem; margin-bottom: 20px;">${featuresHtml}</ul>
                        ` : ''}
                    </div>
                    
                    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #f1f5f9; display: flex; gap: 10px;">
                        <button class="btn btn-primary" style="flex: 1;" onclick="editPlan(${plan.id})">
                            <i class="fa fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-default" style="color: var(--danger-color);" onclick="confirmDelete(${plan.id})">
                            <i class="fa fa-trash"></i>
                        </button>
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
                <button class="btn btn-default" type="button" onclick="removeFeature(${featureCount})" style="color: var(--danger-color);">
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
                // alert(response.message);
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
                // alert(response.message);
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
            <div class="col-md-4" data-plan-id="${plan.id}" style="cursor: move; margin-bottom: 20px;">
                <div class="card" style="border: 2px dashed #cbd5e1; background: #f8fafc; padding: 20px;">
                    <div class="text-center">
                        <i class="fa fa-bars fa-2x" style="color: #94a3b8; margin-bottom: 10px;"></i>
                        <h3 style="margin: 0; font-size: 1.1rem; color: #334155;">${plan.name}</h3>
                        <p class="text-muted" style="margin: 5px 0 0 0;">₹${plan.price}</p>
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
                // alert(response.message);
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
