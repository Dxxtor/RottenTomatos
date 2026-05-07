<?php
/**
 * config.php - Database and site configuration for Fresh Potatos movie site.
 * Include this file at the top of every PHP page that needs database access.
 * Change the values below to match your XAMPP/MySQL setup.
 */

// __DIR__ is the folder where this file lives. We store it so other scripts can use it.
if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', __DIR__);
}

// =============================================================================
// DATABASE CREDENTIALS
// =============================================================================
// Support both Railway hosting (environment variables) and XAMPP local
// Railway uses MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT
// XAMPP uses localhost, root, empty password
// =============================================================================
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'fresh_potatos');
define('DB_PORT', getenv('MYSQLPORT') ?: 3306);

// =============================================================================
// SITE SETTINGS
// =============================================================================
define('SITE_NAME', 'Fresh Potatos');      // Shown in navbar and page titles
define('BASE_URL', '/');                   // Base URL path for all links
define('SITE_TAGLINE', 'Totally Real Reviews');  // Shown in footer
define('POSTERS_PATH', '');  // Subfolder for poster images (empty = same folder as PHP files)
define('MOVIES_PER_PAGE', 24);  // Pagination: movies per page on index and genre

// API Keys
define('TMDB_API_KEY', '5603312e62194ded8413c11dcae0ba83');

// =============================================================================
// SECURITY FUNCTIONS
// =============================================================================

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Get CSRF input field
function getCSRFInput() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '" />';
}

// Sanitize input
function sanitizeInput($input, $type = 'string') {
    $input = trim($input);
    $input = stripslashes($input);
    
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT);
        default:
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

// Validate username
function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username);
}

// Validate password
function validatePassword($password) {
    return strlen($password) >= 8;
}

// Check login attempts
function checkLoginAttempts($username) {
    $key = 'login_attempts_' . $username;
    $attempts = $_SESSION[$key] ?? 0;
    return $attempts < 5;
}

// Reset login attempts
function resetLoginAttempts($username) {
    $key = 'login_attempts_' . $username;
    $_SESSION[$key] = 0;
}

// =============================================================================
// getPosterSrc($poster) - Returns image src for movie poster or placeholder
// =============================================================================
function getPosterSrc($poster) {
    if (empty($poster) || strpos($poster, 'placeholder.jpg') !== false) {
        return 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="300" viewBox="0 0 200 300"><rect fill="#333" width="200" height="300"/><text fill="#666" x="100" y="150" text-anchor="middle" font-size="14">No poster</text></svg>');
    }
    return $poster;
}

// =============================================================================
// getDbConnection() - Create and return a MySQL connection using PDO
// =============================================================================
function getDbConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

// =============================================================================
// Initialize Session
// =============================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
