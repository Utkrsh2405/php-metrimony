<?php
/**
 * Internationalization (i18n) Helper Functions
 * Provides translation and language management utilities
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set default language if not set
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = getDefaultLanguage();
}

/**
 * Get default language from database
 */
function getDefaultLanguage() {
    global $conn;
    $query = "SELECT code FROM languages WHERE is_default = 1 AND is_active = 1 LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['code'];
    }
    
    return 'en'; // Fallback to English
}

/**
 * Get all active languages
 */
function getActiveLanguages() {
    global $conn;
    $query = "SELECT * FROM languages WHERE is_active = 1 ORDER BY is_default DESC, name ASC";
    $result = mysqli_query($conn, $query);
    
    $languages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $languages[] = $row;
    }
    
    return $languages;
}

/**
 * Get current language
 */
function getCurrentLanguage() {
    return $_SESSION['language'] ?? 'en';
}

/**
 * Set current language
 */
function setLanguage($language_code) {
    global $conn;
    
    // Validate language exists and is active
    $code = mysqli_real_escape_string($conn, $language_code);
    $query = "SELECT code FROM languages WHERE code = '$code' AND is_active = 1";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['language'] = $language_code;
        return true;
    }
    
    return false;
}

/**
 * Translate a key to current language
 * @param string $key Translation key
 * @param string $default Default text if translation not found
 * @return string Translated text
 */
function __($key, $default = null) {
    global $conn;
    
    $language = getCurrentLanguage();
    $key_escaped = mysqli_real_escape_string($conn, $key);
    
    // Try to get translation from database
    $query = "SELECT translation_value FROM translations 
              WHERE language_code = '$language' AND translation_key = '$key_escaped'";
    $result = mysqli_query($conn, $query);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['translation_value'];
    }
    
    // Fallback to English if current language is not English
    if ($language !== 'en') {
        $query = "SELECT translation_value FROM translations 
                  WHERE language_code = 'en' AND translation_key = '$key_escaped'";
        $result = mysqli_query($conn, $query);
        
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return $row['translation_value'];
        }
    }
    
    // Return default or key itself
    return $default ?? $key;
}

/**
 * Translate with variable substitution
 * @param string $key Translation key
 * @param array $vars Variables to substitute (e.g., ['name' => 'John'])
 * @param string $default Default text
 * @return string Translated text with variables replaced
 */
function __t($key, $vars = [], $default = null) {
    $text = __($key, $default);
    
    foreach ($vars as $var_key => $var_value) {
        $text = str_replace('{' . $var_key . '}', $var_value, $text);
    }
    
    return $text;
}

/**
 * Get all translations for a language
 * @param string $language_code Language code
 * @return array Associative array of translations
 */
function getTranslations($language_code = null) {
    global $conn;
    
    $language = $language_code ?? getCurrentLanguage();
    $language_escaped = mysqli_real_escape_string($conn, $language);
    
    $query = "SELECT translation_key, translation_value, category 
              FROM translations 
              WHERE language_code = '$language_escaped'
              ORDER BY category, translation_key";
    $result = mysqli_query($conn, $query);
    
    $translations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $translations[$row['translation_key']] = $row['translation_value'];
    }
    
    return $translations;
}

/**
 * Get translations grouped by category
 */
function getTranslationsByCategory($language_code = null) {
    global $conn;
    
    $language = $language_code ?? getCurrentLanguage();
    $language_escaped = mysqli_real_escape_string($conn, $language);
    
    $query = "SELECT translation_key, translation_value, category 
              FROM translations 
              WHERE language_code = '$language_escaped'
              ORDER BY category, translation_key";
    $result = mysqli_query($conn, $query);
    
    $translations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $category = $row['category'];
        if (!isset($translations[$category])) {
            $translations[$category] = [];
        }
        $translations[$category][$row['translation_key']] = $row['translation_value'];
    }
    
    return $translations;
}

/**
 * Export translations to JSON
 */
function exportTranslationsJSON($language_code) {
    $translations = getTranslations($language_code);
    return json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/**
 * Import translations from JSON
 */
function importTranslationsJSON($language_code, $json_data) {
    global $conn;
    
    $translations = json_decode($json_data, true);
    if (!$translations) {
        return ['success' => false, 'error' => 'Invalid JSON'];
    }
    
    $language_escaped = mysqli_real_escape_string($conn, $language_code);
    $imported = 0;
    
    foreach ($translations as $key => $value) {
        $key_escaped = mysqli_real_escape_string($conn, $key);
        $value_escaped = mysqli_real_escape_string($conn, $value);
        
        // Insert or update
        $query = "INSERT INTO translations (language_code, translation_key, translation_value, created_at, updated_at)
                  VALUES ('$language_escaped', '$key_escaped', '$value_escaped', NOW(), NOW())
                  ON DUPLICATE KEY UPDATE 
                  translation_value = '$value_escaped',
                  updated_at = NOW()";
        
        if (mysqli_query($conn, $query)) {
            $imported++;
        }
    }
    
    return ['success' => true, 'imported' => $imported];
}

/**
 * Check if language is RTL
 */
function isRTL($language_code = null) {
    global $conn;
    
    $language = $language_code ?? getCurrentLanguage();
    $language_escaped = mysqli_real_escape_string($conn, $language);
    
    $query = "SELECT is_rtl FROM languages WHERE code = '$language_escaped'";
    $result = mysqli_query($conn, $query);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['is_rtl'] == 1;
    }
    
    return false;
}

/**
 * Get language direction (ltr or rtl)
 */
function getLanguageDirection($language_code = null) {
    return isRTL($language_code) ? 'rtl' : 'ltr';
}

/**
 * Get language info
 */
function getLanguageInfo($language_code) {
    global $conn;
    
    $language_escaped = mysqli_real_escape_string($conn, $language_code);
    $query = "SELECT * FROM languages WHERE code = '$language_escaped'";
    $result = mysqli_query($conn, $query);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row;
    }
    
    return null;
}
?>
