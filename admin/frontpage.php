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
    <h1>Homepage Configuration</h1>
    <p class="text-muted">Configure sections displayed on the homepage</p>
    
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-12">
            <button id="save-all" class="btn btn-primary">
                <i class="fa fa-save"></i> Save All Changes
            </button>
            <button id="reorder-mode" class="btn btn-default">
                <i class="fa fa-sort"></i> Reorder Sections
            </button>
            <a href="/" target="_blank" class="btn btn-info">
                <i class="fa fa-eye"></i> Preview Homepage
            </a>
        </div>
    </div>
    
    <div id="sections-container">
        <div class="text-center" style="padding: 50px;">
            <i class="fa fa-spinner fa-spin fa-3x"></i>
            <p>Loading sections...</p>
        </div>
    </div>
</div>

<style>
.section-card {
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
}
.section-header {
    padding: 15px;
    background: #f5f5f5;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
}
.section-header h3 {
    margin: 0;
    display: inline-block;
}
.section-body {
    padding: 20px;
    display: none;
}
.section-body.active {
    display: block;
}
.drag-handle {
    cursor: move;
    color: #999;
    margin-right: 10px;
}
.reorder-mode .section-card {
    cursor: move;
}
.reorder-mode .section-body {
    display: none !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
let sections = [];
let reorderMode = false;

$(document).ready(function() {
    loadSections();
    
    $('#save-all').on('click', saveAllSections);
    $('#reorder-mode').on('click', toggleReorderMode);
});

function loadSections() {
    $.get('/admin/api/frontpage.php', function(response) {
        if (response.success) {
            sections = response.data;
            renderSections();
        }
    });
}

function renderSections() {
    const container = $('#sections-container');
    container.empty();
    
    sections.forEach((section, index) => {
        const active = section.is_active == 1;
        const content = section.content;
        
        let contentFields = '';
        
        switch(section.section_key) {
            case 'hero_banner':
                contentFields = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Main Heading</label>
                                <input type="text" class="form-control" data-key="heading" value="${content.heading || ''}">
                            </div>
                            <div class="form-group">
                                <label>Sub Heading</label>
                                <input type="text" class="form-control" data-key="subheading" value="${content.subheading || ''}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Background Image URL</label>
                                <input type="text" class="form-control" data-key="background_image" value="${content.background_image || ''}">
                            </div>
                            <div class="form-group">
                                <label>CTA Button Text</label>
                                <input type="text" class="form-control" data-key="cta_text" value="${content.cta_text || ''}">
                            </div>
                            <div class="form-group">
                                <label>CTA Button Link</label>
                                <input type="text" class="form-control" data-key="cta_link" value="${content.cta_link || ''}">
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'statistics':
                const stats = content.stats || [
                    {label: 'Active Members', value: '10,000+', icon: 'users'},
                    {label: 'Success Stories', value: '500+', icon: 'heart'},
                    {label: 'Daily Matches', value: '200+', icon: 'random'},
                    {label: 'Countries', value: '50+', icon: 'globe'}
                ];
                contentFields = '<div class="row">';
                stats.forEach((stat, i) => {
                    contentFields += `
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Stat ${i+1} Label</label>
                                <input type="text" class="form-control" data-key="stats[${i}].label" value="${stat.label}">
                            </div>
                            <div class="form-group">
                                <label>Value</label>
                                <input type="text" class="form-control" data-key="stats[${i}].value" value="${stat.value}">
                            </div>
                            <div class="form-group">
                                <label>Icon (Font Awesome)</label>
                                <input type="text" class="form-control" data-key="stats[${i}].icon" value="${stat.icon}">
                            </div>
                        </div>
                    `;
                });
                contentFields += '</div>';
                break;
                
            case 'featured_profiles':
                contentFields = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Number of Profiles to Show</label>
                                <input type="number" class="form-control" data-key="limit" value="${content.limit || 6}" min="3" max="12">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Filter By</label>
                                <select class="form-control" data-key="filter">
                                    <option value="newest" ${content.filter == 'newest' ? 'selected' : ''}>Newest Members</option>
                                    <option value="verified" ${content.filter == 'verified' ? 'selected' : ''}>Verified Only</option>
                                    <option value="random" ${content.filter == 'random' ? 'selected' : ''}>Random</option>
                                </select>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'success_stories':
                contentFields = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Number of Stories to Show</label>
                                <input type="number" class="form-control" data-key="limit" value="${content.limit || 3}" min="2" max="6">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Link to Full Stories Page</label>
                                <input type="text" class="form-control" data-key="view_all_link" value="${content.view_all_link || '/page.php?slug=success-stories'}">
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'testimonials':
                contentFields = `
                    <div class="form-group">
                        <label>Testimonials (one per line, format: Name | Message)</label>
                        <textarea class="form-control" rows="8" data-key="items" placeholder="John Doe | Great service!\nJane Smith | Found my partner here!">${(content.items || []).join('\n')}</textarea>
                        <small class="text-muted">Each line should be: Name | Testimonial text</small>
                    </div>
                `;
                break;
        }
        
        container.append(`
            <div class="section-card" data-index="${index}">
                <div class="section-header" onclick="toggleSection(${index})">
                    <i class="fa fa-bars drag-handle"></i>
                    <h3>${section.title}</h3>
                    <div class="pull-right">
                        <label class="switch" style="margin: 0;">
                            <input type="checkbox" class="section-active" data-index="${index}" ${active ? 'checked' : ''} onclick="event.stopPropagation()">
                            <span class="slider"></span>
                        </label>
                        <span style="margin-left: 10px; color: ${active ? '#5cb85c' : '#999'};">
                            ${active ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                </div>
                <div class="section-body" data-index="${index}">
                    ${contentFields}
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                        <small class="text-muted">Display Order: ${section.display_order}</small>
                    </div>
                </div>
            </div>
        `);
    });
}

function toggleSection(index) {
    $(`.section-body[data-index="${index}"]`).toggleClass('active');
}

function saveAllSections() {
    const updates = [];
    
    $('.section-card').each(function(cardIndex) {
        const index = $(this).data('index');
        const section = sections[index];
        const content = {};
        
        // Collect all input values
        $(this).find('[data-key]').each(function() {
            const key = $(this).data('key');
            let value = $(this).val();
            
            // Handle nested keys like stats[0].label
            if (key.includes('[')) {
                const match = key.match(/(\w+)\[(\d+)\]\.(\w+)/);
                if (match) {
                    const [, arrayName, idx, prop] = match;
                    if (!content[arrayName]) content[arrayName] = [];
                    if (!content[arrayName][idx]) content[arrayName][idx] = {};
                    content[arrayName][idx][prop] = value;
                }
            } else if (key === 'items') {
                // Handle testimonials
                content[key] = value.split('\n').filter(line => line.trim());
            } else {
                content[key] = value;
            }
        });
        
        updates.push({
            id: section.id,
            is_active: $(this).find('.section-active').is(':checked') ? 1 : 0,
            content: content
        });
    });
    
    // Save all updates
    let saved = 0;
    updates.forEach(update => {
        $.ajax({
            url: '/admin/api/frontpage.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(update),
            success: function(response) {
                saved++;
                if (saved === updates.length) {
                    alert('All sections updated successfully!');
                    loadSections();
                }
            }
        });
    });
}

function toggleReorderMode() {
    reorderMode = !reorderMode;
    const container = $('#sections-container');
    
    if (reorderMode) {
        container.addClass('reorder-mode');
        $('#reorder-mode').html('<i class="fa fa-check"></i> Save Order').addClass('btn-success').removeClass('btn-default');
        
        // Enable drag and drop
        new Sortable(container[0], {
            animation: 150,
            handle: '.section-header',
            onEnd: function() {
                updateDisplayOrder();
            }
        });
    } else {
        container.removeClass('reorder-mode');
        $('#reorder-mode').html('<i class="fa fa-sort"></i> Reorder Sections').removeClass('btn-success').addClass('btn-default');
        saveOrder();
    }
}

function updateDisplayOrder() {
    $('.section-card').each(function(newIndex) {
        const oldIndex = $(this).data('index');
        sections[oldIndex].display_order = newIndex + 1;
    });
}

function saveOrder() {
    const order = [];
    $('.section-card').each(function() {
        const index = $(this).data('index');
        order.push(sections[index].id);
    });
    
    $.ajax({
        url: '/admin/api/frontpage.php',
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify({ order: order }),
        success: function(response) {
            if (response.success) {
                alert('Section order saved!');
                loadSections();
            }
        }
    });
}
</script>

<!-- Toggle Switch CSS -->
<style>
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #5cb85c;
}
input:checked + .slider:before {
    transform: translateX(26px);
}
</style>

<?php include("../includes/admin-footer.php"); ?>
