<?php
// Admin Translations API - Manage languages and translations
session_start();
header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once("../../includes/dbconn.php");

// Verify admin
$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'languages';

// GET - List languages or translations
if ($method == 'GET') {
    if ($endpoint == 'languages') {
        $query = "SELECT l.*, 
                         COUNT(t.id) as translation_count
                  FROM languages l
                  LEFT JOIN translations t ON l.code = t.language_code
                  GROUP BY l.id
                  ORDER BY l.is_default DESC, l.name ASC";
        $result = mysqli_query($conn, $query);
        $languages = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $languages[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $languages]);
        
    } elseif ($endpoint == 'translations') {
        $language_code = isset($_GET['language']) ? mysqli_real_escape_string($conn, $_GET['language']) : 'en';
        $category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        
        $where = ["language_code = '$language_code'"];
        if ($category) $where[] = "category = '$category'";
        if ($search) $where[] = "(translation_key LIKE '%$search%' OR translation_value LIKE '%$search%')";
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT * FROM translations WHERE $where_clause ORDER BY category, translation_key";
        $result = mysqli_query($conn, $query);
        $translations = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $translations[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $translations]);
        
    } elseif ($endpoint == 'categories') {
        $query = "SELECT DISTINCT category FROM translations ORDER BY category";
        $result = mysqli_query($conn, $query);
        $categories = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row['category'];
        }
        
        echo json_encode(['success' => true, 'data' => $categories]);
        
    } elseif ($endpoint == 'export') {
        $language_code = isset($_GET['language']) ? mysqli_real_escape_string($conn, $_GET['language']) : 'en';
        
        $query = "SELECT translation_key, translation_value FROM translations WHERE language_code = '$language_code'";
        $result = mysqli_query($conn, $query);
        $translations = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $translations[$row['translation_key']] = $row['translation_value'];
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="translations_' . $language_code . '.json"');
        echo json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
}

// POST - Create/Update language or translation
elseif ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($endpoint == 'language') {
        $id = isset($data['id']) ? intval($data['id']) : 0;
        $code = mysqli_real_escape_string($conn, $data['code']);
        $name = mysqli_real_escape_string($conn, $data['name']);
        $native_name = mysqli_real_escape_string($conn, $data['native_name'] ?? '');
        $is_rtl = isset($data['is_rtl']) ? 1 : 0;
        $is_active = isset($data['is_active']) ? 1 : 0;
        $is_default = isset($data['is_default']) ? 1 : 0;
        $flag_icon = mysqli_real_escape_string($conn, $data['flag_icon'] ?? '');
        
        if ($id > 0) {
            // Update
            // If setting as default, unset other defaults
            if ($is_default) {
                mysqli_query($conn, "UPDATE languages SET is_default = 0 WHERE id != $id");
            }
            
            $query = "UPDATE languages SET
                      code = '$code',
                      name = '$name',
                      native_name = '$native_name',
                      is_rtl = $is_rtl,
                      is_active = $is_active,
                      is_default = $is_default,
                      flag_icon = '$flag_icon',
                      updated_at = NOW()
                      WHERE id = $id";
            
            if (mysqli_query($conn, $query)) {
                echo json_encode(['success' => true, 'message' => 'Language updated successfully']);
            } else {
                echo json_encode(['error' => 'Failed to update language']);
            }
        } else {
            // Create
            if ($is_default) {
                mysqli_query($conn, "UPDATE languages SET is_default = 0");
            }
            
            $query = "INSERT INTO languages 
                      (code, name, native_name, is_rtl, is_active, is_default, flag_icon, created_at, updated_at)
                      VALUES 
                      ('$code', '$name', '$native_name', $is_rtl, $is_active, $is_default, '$flag_icon', NOW(), NOW())";
            
            if (mysqli_query($conn, $query)) {
                echo json_encode(['success' => true, 'message' => 'Language created successfully', 'id' => mysqli_insert_id($conn)]);
            } else {
                echo json_encode(['error' => 'Failed to create language']);
            }
        }
        
    } elseif ($endpoint == 'translation') {
        $id = isset($data['id']) ? intval($data['id']) : 0;
        $language_code = mysqli_real_escape_string($conn, $data['language_code']);
        $translation_key = mysqli_real_escape_string($conn, $data['translation_key']);
        $translation_value = mysqli_real_escape_string($conn, $data['translation_value']);
        $category = mysqli_real_escape_string($conn, $data['category'] ?? 'general');
        
        if ($id > 0) {
            // Update
            $query = "UPDATE translations SET
                      translation_key = '$translation_key',
                      translation_value = '$translation_value',
                      category = '$category',
                      updated_at = NOW()
                      WHERE id = $id";
            
            if (mysqli_query($conn, $query)) {
                echo json_encode(['success' => true, 'message' => 'Translation updated successfully']);
            } else {
                echo json_encode(['error' => 'Failed to update translation']);
            }
        } else {
            // Create
            $query = "INSERT INTO translations 
                      (language_code, translation_key, translation_value, category, created_at, updated_at)
                      VALUES 
                      ('$language_code', '$translation_key', '$translation_value', '$category', NOW(), NOW())";
            
            if (mysqli_query($conn, $query)) {
                echo json_encode(['success' => true, 'message' => 'Translation created successfully', 'id' => mysqli_insert_id($conn)]);
            } else {
                echo json_encode(['error' => 'Failed to create translation: ' . mysqli_error($conn)]);
            }
        }
        
    } elseif ($endpoint == 'import') {
        $language_code = mysqli_real_escape_string($conn, $data['language_code']);
        $translations = $data['translations'];
        
        if (!is_array($translations)) {
            echo json_encode(['error' => 'Invalid translations data']);
            exit();
        }
        
        $imported = 0;
        $errors = 0;
        
        foreach ($translations as $key => $value) {
            $key_escaped = mysqli_real_escape_string($conn, $key);
            $value_escaped = mysqli_real_escape_string($conn, $value);
            
            $query = "INSERT INTO translations (language_code, translation_key, translation_value, category, created_at, updated_at)
                      VALUES ('$language_code', '$key_escaped', '$value_escaped', 'general', NOW(), NOW())
                      ON DUPLICATE KEY UPDATE 
                      translation_value = '$value_escaped',
                      updated_at = NOW()";
            
            if (mysqli_query($conn, $query)) {
                $imported++;
            } else {
                $errors++;
            }
        }
        
        echo json_encode(['success' => true, 'message' => "$imported translations imported", 'imported' => $imported, 'errors' => $errors]);
        
    } elseif ($endpoint == 'copy-translations') {
        $from_language = mysqli_real_escape_string($conn, $data['from_language']);
        $to_language = mysqli_real_escape_string($conn, $data['to_language']);
        
        // Copy all translations from one language to another
        $query = "INSERT INTO translations (language_code, translation_key, translation_value, category, created_at, updated_at)
                  SELECT '$to_language', translation_key, translation_value, category, NOW(), NOW()
                  FROM translations
                  WHERE language_code = '$from_language'
                  ON DUPLICATE KEY UPDATE 
                  translation_value = VALUES(translation_value),
                  updated_at = NOW()";
        
        if (mysqli_query($conn, $query)) {
            $copied = mysqli_affected_rows($conn);
            echo json_encode(['success' => true, 'message' => "$copied translations copied", 'copied' => $copied]);
        } else {
            echo json_encode(['error' => 'Failed to copy translations']);
        }
    }
}

// DELETE - Delete language or translation
elseif ($method == 'DELETE') {
    if ($endpoint == 'language') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Check if it's the default language
        $check_query = "SELECT is_default FROM languages WHERE id = $id";
        $check_result = mysqli_query($conn, $check_query);
        $lang = mysqli_fetch_assoc($check_result);
        
        if ($lang['is_default'] == 1) {
            echo json_encode(['error' => 'Cannot delete the default language']);
            exit();
        }
        
        $query = "DELETE FROM languages WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Language deleted successfully']);
        } else {
            echo json_encode(['error' => 'Failed to delete language']);
        }
        
    } elseif ($endpoint == 'translation') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        $query = "DELETE FROM translations WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Translation deleted successfully']);
        } else {
            echo json_encode(['error' => 'Failed to delete translation']);
        }
    }
}

else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
