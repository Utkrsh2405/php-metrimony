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

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'languages';

include("../includes/admin-header.php");
?>

<div class="admin-content">
    <h1>Multi-language Management</h1>
    <p class="text-muted">Manage languages and translations for internationalization</p>
    
    <!-- Tabs -->
    <ul class="nav nav-tabs" style="margin-bottom: 20px;">
        <li class="<?php echo $active_tab == 'languages' ? 'active' : ''; ?>">
            <a href="?tab=languages">Languages</a>
        </li>
        <li class="<?php echo $active_tab == 'translations' ? 'active' : ''; ?>">
            <a href="?tab=translations">Translations</a>
        </li>
        <li class="<?php echo $active_tab == 'import-export' ? 'active' : ''; ?>">
            <a href="?tab=import-export">Import/Export</a>
        </li>
    </ul>
    
    <!-- Languages Tab -->
    <div id="languages-tab" style="display: <?php echo $active_tab == 'languages' ? 'block' : 'none'; ?>;">
        <button id="add-language-btn" class="btn btn-primary" style="margin-bottom: 15px;">
            <i class="fa fa-plus"></i> Add Language
        </button>
        
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Native Name</th>
                            <th>Flag</th>
                            <th>Direction</th>
                            <th>Translations</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="languages-tbody">
                        <tr><td colspan="8" class="text-center">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Translations Tab -->
    <div id="translations-tab" style="display: <?php echo $active_tab == 'translations' ? 'block' : 'none'; ?>;">
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-3">
                <select id="language-selector" class="form-control">
                    <option value="en">English</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="category-filter" class="form-control">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" id="translation-search" class="form-control" placeholder="Search translations...">
            </div>
            <div class="col-md-2">
                <button id="filter-translations-btn" class="btn btn-primary">
                    <i class="fa fa-filter"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <button id="add-translation-btn" class="btn btn-success">
                    <i class="fa fa-plus"></i> Add
                </button>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div id="translations-loading" class="text-center" style="padding: 20px; display: none;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                </div>
                <table class="table table-hover" id="translations-table">
                    <thead>
                        <tr>
                            <th width="20%">Key</th>
                            <th width="50%">Translation</th>
                            <th width="15%">Category</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="translations-tbody">
                        <tr><td colspan="4" class="text-center">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Import/Export Tab -->
    <div id="import-export-tab" style="display: <?php echo $active_tab == 'import-export' ? 'block' : 'none'; ?>;">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa fa-download"></i> Export Translations</h3>
                    </div>
                    <div class="card-body">
                        <p>Export translations to JSON format for backup or sharing.</p>
                        <div class="form-group">
                            <label>Select Language</label>
                            <select id="export-language" class="form-control">
                                <!-- Populated by JS -->
                            </select>
                        </div>
                        <button id="export-btn" class="btn btn-success">
                            <i class="fa fa-download"></i> Download JSON
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa fa-upload"></i> Import Translations</h3>
                    </div>
                    <div class="card-body">
                        <p>Import translations from JSON file. Existing translations will be updated.</p>
                        <div class="form-group">
                            <label>Select Language</label>
                            <select id="import-language" class="form-control">
                                <!-- Populated by JS -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label>JSON File</label>
                            <input type="file" id="import-file" class="form-control" accept=".json">
                        </div>
                        <button id="import-btn" class="btn btn-primary">
                            <i class="fa fa-upload"></i> Import Translations
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row" style="margin-top: 20px;">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa fa-copy"></i> Copy Translations</h3>
                    </div>
                    <div class="card-body">
                        <p>Copy all translations from one language to another as a starting point.</p>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>From Language</label>
                                    <select id="copy-from-language" class="form-control">
                                        <!-- Populated by JS -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 text-center" style="padding-top: 30px;">
                                <i class="fa fa-arrow-right fa-2x"></i>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>To Language</label>
                                    <select id="copy-to-language" class="form-control">
                                        <!-- Populated by JS -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button id="copy-translations-btn" class="btn btn-warning">
                            <i class="fa fa-copy"></i> Copy Translations
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Language Edit Modal -->
<div class="modal fade" id="languageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Language</h4>
            </div>
            <form id="language-form">
                <div class="modal-body">
                    <input type="hidden" id="language-id">
                    
                    <div class="form-group">
                        <label>Language Code (e.g., en, hi, es)</label>
                        <input type="text" id="language-code" class="form-control" required maxlength="10">
                    </div>
                    
                    <div class="form-group">
                        <label>Language Name</label>
                        <input type="text" id="language-name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Native Name</label>
                        <input type="text" id="language-native-name" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Flag Icon (emoji or code)</label>
                        <input type="text" id="language-flag" class="form-control" placeholder="🇬🇧">
                    </div>
                    
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="language-rtl"> Right-to-Left (RTL) Language
                        </label>
                    </div>
                    
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="language-active"> Active
                        </label>
                    </div>
                    
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="language-default"> Default Language
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Language
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Translation Edit Modal -->
<div class="modal fade" id="translationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Translation</h4>
            </div>
            <form id="translation-form">
                <div class="modal-body">
                    <input type="hidden" id="translation-id">
                    <input type="hidden" id="translation-language-code">
                    
                    <div class="form-group">
                        <label>Translation Key</label>
                        <input type="text" id="translation-key" class="form-control" required>
                        <small class="text-muted">Use lowercase with underscores (e.g., welcome_message)</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Translation Value</label>
                        <textarea id="translation-value" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" id="translation-category" class="form-control" value="general">
                        <small class="text-muted">e.g., general, auth, profile, dashboard</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Translation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let languages = [];
let categories = [];
let currentLanguage = 'en';

$(document).ready(function() {
    loadLanguages();
    loadCategories();
    loadTranslations();
    
    $('#add-language-btn').click(() => openLanguageModal());
    $('#language-form').submit(function(e) {
        e.preventDefault();
        saveLanguage();
    });
    
    $('#add-translation-btn').click(() => openTranslationModal());
    $('#translation-form').submit(function(e) {
        e.preventDefault();
        saveTranslation();
    });
    
    $('#language-selector').change(function() {
        currentLanguage = $(this).val();
        loadTranslations();
    });
    
    $('#filter-translations-btn').click(() => loadTranslations());
    
    $('#export-btn').click(() => exportTranslations());
    $('#import-btn').click(() => importTranslations());
    $('#copy-translations-btn').click(() => copyTranslations());
});

function loadLanguages() {
    $.get('/admin/api/translations.php?endpoint=languages', function(response) {
        if (response.success) {
            languages = response.data;
            renderLanguages(languages);
            populateLanguageSelectors(languages);
        }
    });
}

function renderLanguages(langs) {
    const tbody = $('#languages-tbody');
    tbody.empty();
    
    if (langs.length === 0) {
        tbody.append('<tr><td colspan="8" class="text-center">No languages found</td></tr>');
        return;
    }
    
    langs.forEach(lang => {
        const direction = lang.is_rtl == 1 ? '<span class="label label-warning">RTL</span>' : '<span class="label label-info">LTR</span>';
        const statusBadges = [];
        
        if (lang.is_default == 1) statusBadges.push('<span class="label label-primary">Default</span>');
        if (lang.is_active == 1) statusBadges.push('<span class="label label-success">Active</span>');
        else statusBadges.push('<span class="label label-default">Inactive</span>');
        
        tbody.append(`
            <tr>
                <td><code>${lang.code}</code></td>
                <td>${lang.name}</td>
                <td>${lang.native_name || '-'}</td>
                <td style="font-size: 24px;">${lang.flag_icon || '🏳️'}</td>
                <td>${direction}</td>
                <td><span class="badge">${lang.translation_count || 0}</span></td>
                <td>${statusBadges.join(' ')}</td>
                <td>
                    <button class="btn btn-xs btn-primary" onclick="editLanguage(${lang.id})"><i class="fa fa-edit"></i></button>
                    ${lang.is_default != 1 ? `<button class="btn btn-xs btn-danger" onclick="deleteLanguage(${lang.id})"><i class="fa fa-trash"></i></button>` : ''}
                </td>
            </tr>
        `);
    });
}

function populateLanguageSelectors(langs) {
    const selectors = ['#language-selector', '#export-language', '#import-language', '#copy-from-language', '#copy-to-language'];
    
    selectors.forEach(selector => {
        const $select = $(selector);
        $select.empty();
        langs.forEach(lang => {
            $select.append(`<option value="${lang.code}">${lang.name} (${lang.code})</option>`);
        });
    });
}

function openLanguageModal(lang = null) {
    if (lang) {
        $('#language-id').val(lang.id);
        $('#language-code').val(lang.code).prop('readonly', true);
        $('#language-name').val(lang.name);
        $('#language-native-name').val(lang.native_name);
        $('#language-flag').val(lang.flag_icon);
        $('#language-rtl').prop('checked', lang.is_rtl == 1);
        $('#language-active').prop('checked', lang.is_active == 1);
        $('#language-default').prop('checked', lang.is_default == 1);
    } else {
        $('#language-form')[0].reset();
        $('#language-id').val('');
        $('#language-code').prop('readonly', false);
        $('#language-active').prop('checked', true);
    }
    $('#languageModal').modal('show');
}

function editLanguage(id) {
    const lang = languages.find(l => l.id == id);
    if (lang) openLanguageModal(lang);
}

function saveLanguage() {
    const data = {
        id: $('#language-id').val(),
        code: $('#language-code').val(),
        name: $('#language-name').val(),
        native_name: $('#language-native-name').val(),
        flag_icon: $('#language-flag').val(),
        is_rtl: $('#language-rtl').is(':checked'),
        is_active: $('#language-active').is(':checked'),
        is_default: $('#language-default').is(':checked')
    };
    
    $.ajax({
        url: '/admin/api/translations.php?endpoint=language',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#languageModal').modal('hide');
                loadLanguages();
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}

function deleteLanguage(id) {
    if (!confirm('Are you sure you want to delete this language? All translations will be deleted.')) return;
    
    $.ajax({
        url: `/admin/api/translations.php?endpoint=language&id=${id}`,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                loadLanguages();
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}

function loadCategories() {
    $.get('/admin/api/translations.php?endpoint=categories', function(response) {
        if (response.success) {
            categories = response.data;
            const $select = $('#category-filter');
            $select.find('option:not(:first)').remove();
            categories.forEach(cat => {
                $select.append(`<option value="${cat}">${cat}</option>`);
            });
        }
    });
}

function loadTranslations() {
    $('#translations-loading').show();
    $('#translations-table').hide();
    
    const language = $('#language-selector').val();
    const category = $('#category-filter').val();
    const search = $('#translation-search').val();
    
    $.get(`/admin/api/translations.php?endpoint=translations&language=${language}&category=${category}&search=${search}`, function(response) {
        if (response.success) {
            renderTranslations(response.data);
        }
        $('#translations-loading').hide();
        $('#translations-table').show();
    });
}

function renderTranslations(trans) {
    const tbody = $('#translations-tbody');
    tbody.empty();
    
    if (trans.length === 0) {
        tbody.append('<tr><td colspan="4" class="text-center">No translations found</td></tr>');
        return;
    }
    
    trans.forEach(t => {
        tbody.append(`
            <tr>
                <td><code>${t.translation_key}</code></td>
                <td>${t.translation_value}</td>
                <td><span class="label label-info">${t.category}</span></td>
                <td>
                    <button class="btn btn-xs btn-primary" onclick='editTranslation(${JSON.stringify(t)})'><i class="fa fa-edit"></i></button>
                    <button class="btn btn-xs btn-danger" onclick="deleteTranslation(${t.id})"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `);
    });
}

function openTranslationModal(trans = null) {
    if (trans) {
        $('#translation-id').val(trans.id);
        $('#translation-language-code').val(trans.language_code);
        $('#translation-key').val(trans.translation_key);
        $('#translation-value').val(trans.translation_value);
        $('#translation-category').val(trans.category);
    } else {
        $('#translation-form')[0].reset();
        $('#translation-id').val('');
        $('#translation-language-code').val(currentLanguage);
        $('#translation-category').val('general');
    }
    $('#translationModal').modal('show');
}

function editTranslation(trans) {
    openTranslationModal(trans);
}

function saveTranslation() {
    const data = {
        id: $('#translation-id').val(),
        language_code: $('#translation-language-code').val(),
        translation_key: $('#translation-key').val(),
        translation_value: $('#translation-value').val(),
        category: $('#translation-category').val()
    };
    
    $.ajax({
        url: '/admin/api/translations.php?endpoint=translation',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#translationModal').modal('hide');
                loadTranslations();
                loadCategories();
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}

function deleteTranslation(id) {
    if (!confirm('Are you sure you want to delete this translation?')) return;
    
    $.ajax({
        url: `/admin/api/translations.php?endpoint=translation&id=${id}`,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                loadTranslations();
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}

function exportTranslations() {
    const language = $('#export-language').val();
    window.location.href = `/admin/api/translations.php?endpoint=export&language=${language}`;
}

function importTranslations() {
    const file = $('#import-file')[0].files[0];
    const language = $('#import-language').val();
    
    if (!file) {
        alert('Please select a JSON file');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const translations = JSON.parse(e.target.result);
            
            $.ajax({
                url: '/admin/api/translations.php?endpoint=import',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ language_code: language, translations: translations }),
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        loadTranslations();
                    } else {
                        alert('Error: ' + response.error);
                    }
                }
            });
        } catch (error) {
            alert('Invalid JSON file');
        }
    };
    reader.readAsText(file);
}

function copyTranslations() {
    const fromLang = $('#copy-from-language').val();
    const toLang = $('#copy-to-language').val();
    
    if (fromLang === toLang) {
        alert('Please select different languages');
        return;
    }
    
    if (!confirm(`Copy all translations from ${fromLang} to ${toLang}? Existing translations will be overwritten.`)) return;
    
    $.ajax({
        url: '/admin/api/translations.php?endpoint=copy-translations',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ from_language: fromLang, to_language: toLang }),
        success: function(response) {
            if (response.success) {
                alert(response.message);
                loadTranslations();
            } else {
                alert('Error: ' + response.error);
            }
        }
    });
}
</script>

<?php include("../includes/admin-footer.php"); ?>
