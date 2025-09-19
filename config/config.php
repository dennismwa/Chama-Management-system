<?php
/**
 * Chama Management Platform - Main Configuration
 * 
 * Central configuration file for the entire application
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

// Security check
if (!defined('CHAMA_ACCESS')) {
    define('CHAMA_ACCESS', true);
}

// Error reporting and environment setup
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development'); // Change to 'production' for live
}

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Set default timezone
date_default_timezone_set('Africa/Nairobi');

// ================================================
// APPLICATION CONSTANTS
// ================================================

// Application settings
define('APP_NAME', 'Chama Management Platform');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'https://chama.zuri.co.ke/'); // Change to your domain
define('APP_DESCRIPTION', 'Complete Chama Management Solution');

// File paths
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');

// URL paths
define('BASE_URL', rtrim(APP_URL, '/'));
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOAD_URL', BASE_URL . '/uploads');

// ================================================
// SECURITY SETTINGS
// ================================================

// Session settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SESSION_SAVE_PATH', ''); // Leave empty for default
define('CSRF_TOKEN_NAME', '_token');

// Password settings
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_SYMBOLS', false);

// Login attempt settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Encryption settings
define('ENCRYPTION_KEY', 'your-secret-encryption-key-here-change-this'); // Change this!
define('ENCRYPTION_CIPHER', 'AES-256-CBC');

// ================================================
// DATABASE SETTINGS
// ================================================
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// ================================================
// FILE UPLOAD SETTINGS
// ================================================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Upload directories
define('MEMBER_PHOTOS_PATH', UPLOAD_PATH . '/members');
define('DOCUMENTS_PATH', UPLOAD_PATH . '/documents');
define('RECEIPTS_PATH', UPLOAD_PATH . '/receipts');
define('REPORTS_PATH', UPLOAD_PATH . '/reports');

// ================================================
// EMAIL SETTINGS
// ================================================
define('MAIL_DRIVER', 'smtp'); // smtp, sendmail, mail
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'info@zurihub.co.ke');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_ENCRYPTION', 'tls'); // tls, ssl
define('MAIL_FROM_ADDRESS', 'noreply@chamagroup.com');
define('MAIL_FROM_NAME', 'Chama Management Platform');

// ================================================
// SMS SETTINGS
// ================================================
define('SMS_DRIVER', 'africastalking'); // africastalking, twilio
define('SMS_USERNAME', 'your-africastalking-username');
define('SMS_API_KEY', 'your-africastalking-api-key');
define('SMS_SENDER_ID', 'CHAMA');

// ================================================
// PAYMENT GATEWAY SETTINGS
// ================================================
// M-Pesa Settings
define('MPESA_ENVIRONMENT', 'sandbox'); // sandbox, live
define('MPESA_CONSUMER_KEY', 'your-consumer-key');
define('MPESA_CONSUMER_SECRET', 'your-consumer-secret');
define('MPESA_SHORTCODE', 'your-shortcode');
define('MPESA_PASSKEY', 'your-passkey');
define('MPESA_CALLBACK_URL', BASE_URL . '/api/mpesa/callback.php');
define('MPESA_TIMEOUT_URL', BASE_URL . '/api/mpesa/timeout.php');

// Stripe Settings
define('STRIPE_PUBLISHABLE_KEY', 'your-stripe-publishable-key');
define('STRIPE_SECRET_KEY', 'your-stripe-secret-key');
define('STRIPE_WEBHOOK_SECRET', 'your-stripe-webhook-secret');

// ================================================
// LOGGING SETTINGS
// ================================================
define('LOG_LEVEL', 'info'); // debug, info, warning, error, critical
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('LOG_MAX_FILES', 5);

// ================================================
// CACHE SETTINGS
// ================================================
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour
define('CACHE_PATH', ROOT_PATH . '/cache');

// ================================================
// PAGINATION SETTINGS
// ================================================
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// ================================================
// FINANCIAL SETTINGS
// ================================================
define('DEFAULT_CURRENCY', 'KES');
define('CURRENCY_SYMBOL', 'KSh');
define('DECIMAL_PLACES', 2);
define('INTEREST_CALCULATION_METHOD', 'reducing_balance'); // fixed, reducing_balance
define('DEFAULT_LOAN_INTEREST_RATE', 2.5);
define('DEFAULT_LATE_PAYMENT_PENALTY', 5.0);
define('MINIMUM_SAVINGS_AMOUNT', 100.00);

// ================================================
// SYSTEM FEATURES
// ================================================
define('ENABLE_MULTI_CURRENCY', false);
define('ENABLE_SMS_NOTIFICATIONS', true);
define('ENABLE_EMAIL_NOTIFICATIONS', true);
define('ENABLE_AUDIT_TRAIL', true);
define('ENABLE_TWO_FACTOR_AUTH', false);
define('ENABLE_API_ACCESS', false);
define('ENABLE_BACKUP_AUTOMATION', true);

// ================================================
// API SETTINGS
// ================================================
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per minute
define('API_TOKEN_EXPIRY', 86400); // 24 hours

// ================================================
// THEME SETTINGS
// ================================================
define('DEFAULT_THEME', 'light');
define('AVAILABLE_THEMES', ['light', 'dark']);
define('THEME_COOKIE_NAME', 'chama_theme');
define('THEME_COOKIE_LIFETIME', 30 * 24 * 3600); // 30 days

// ================================================
// HELPER FUNCTIONS
// ================================================

/**
 * Get configuration value
 */
function config($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

/**
 * Check if feature is enabled
 */
function featureEnabled($feature) {
    $constant = 'ENABLE_' . strtoupper($feature);
    return defined($constant) && constant($constant) === true;
}

/**
 * Get upload path for specific type
 */
function getUploadPath($type = '') {
    switch (strtolower($type)) {
        case 'member_photos':
        case 'members':
            return MEMBER_PHOTOS_PATH;
        case 'documents':
            return DOCUMENTS_PATH;
        case 'receipts':
            return RECEIPTS_PATH;
        case 'reports':
            return REPORTS_PATH;
        default:
            return UPLOAD_PATH;
    }
}

/**
 * Get upload URL for specific type
 */
function getUploadUrl($type = '') {
    switch (strtolower($type)) {
        case 'member_photos':
        case 'members':
            return UPLOAD_URL . '/members';
        case 'documents':
            return UPLOAD_URL . '/documents';
        case 'receipts':
            return UPLOAD_URL . '/receipts';
        case 'reports':
            return UPLOAD_URL . '/reports';
        default:
            return UPLOAD_URL;
    }
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = null) {
    $currency = $currency ?: DEFAULT_CURRENCY;
    $symbol = ($currency === 'KES') ? 'KSh ' : $currency . ' ';
    return $symbol . number_format($amount, DECIMAL_PLACES);
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'Y-m-d H:i:s') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Generate unique reference number
 */
function generateReference($prefix = '', $length = 10) {
    $prefix = $prefix ?: 'REF';
    $timestamp = date('YmdHis');
    $random = strtoupper(substr(uniqid(), -($length - strlen($prefix) - strlen($timestamp))));
    return $prefix . $timestamp . $random;
}

/**
 * Sanitize input
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Kenyan format)
 */
function isValidPhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return preg_match('/^(\+254|0)[7][0-9]{8}$/', $phone);
}

/**
 * Format phone number
 */
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (preg_match('/^0[7][0-9]{8}$/', $phone)) {
        return '+254' . substr($phone, 1);
    }
    return $phone;
}

/**
 * Check if current user has permission
 */
function userCan($permission) {
    return hasPermission($permission);
}

/**
 * Get current chama group ID
 */
function currentChamaGroup() {
    return session()->getChamaGroupId();
}

/**
 * Log error
 */
function logError($message, $file = '', $line = '', $context = []) {
    if (!featureEnabled('LOGGING')) return;
    
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'file' => $file,
        'line' => $line,
        'context' => $context,
        'user_id' => function_exists('session') ? session()->getUserId() : null,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $logFile = LOGS_PATH . '/error_' . date('Y-m-d') . '.log';
    if (!file_exists(LOGS_PATH)) {
        mkdir(LOGS_PATH, 0755, true);
    }
    
    file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Debug function (only works in development)
 */
function debug($data, $exit = false) {
    if (ENVIRONMENT !== 'development') return;
    echo '<pre style="background: #f4f4f4; padding: 10px; border: 1px solid #ddd; margin: 10px 0;">';
    print_r($data);
    echo '</pre>';
    if ($exit) exit;
}

// ================================================
// CREATE REQUIRED DIRECTORIES
// ================================================
$requiredDirs = [
    UPLOAD_PATH,
    MEMBER_PHOTOS_PATH,
    DOCUMENTS_PATH,
    RECEIPTS_PATH,
    REPORTS_PATH,
    LOGS_PATH,
    CACHE_PATH
];

foreach ($requiredDirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        // Create .htaccess for security
        file_put_contents($dir . '/.htaccess', "Options -Indexes\nDeny from all");
    }
}

// ================================================
// AUTOLOAD CLASSES
// ================================================
spl_autoload_register(function ($className) {
    $paths = [
        INCLUDES_PATH . '/' . $className . '.php',
        CONFIG_PATH . '/' . $className . '.php',
        ROOT_PATH . '/classes/' . $className . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
});

// ================================================
// INCLUDE ESSENTIAL FILES
// ================================================
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/session.php';
require_once INCLUDES_PATH . '/functions.php';

// ================================================
// INITIALIZE APPLICATION
// ================================================
// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError("PHP Error: $errstr", $errfile, $errline, [
        'errno' => $errno,
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    if (ENVIRONMENT === 'development') {
        return false; // Let PHP handle the error
    }
    return true; // Suppress error in production
});

// Set exception handler
set_exception_handler(function($exception) {
    logError("Uncaught Exception: " . $exception->getMessage(), $exception->getFile(), $exception->getLine(), [
        'trace' => $exception->getTraceAsString()
    ]);
    
    if (ENVIRONMENT === 'development') {
        echo '<h1>Uncaught Exception</h1>';
        echo '<p><strong>Message:</strong> ' . $exception->getMessage() . '</p>';
        echo '<p><strong>File:</strong> ' . $exception->getFile() . '</p>';
        echo '<p><strong>Line:</strong> ' . $exception->getLine() . '</p>';
        echo '<pre>' . $exception->getTraceAsString() . '</pre>';
    } else {
        echo '<h1>An error occurred</h1><p>Please try again later.</p>';
    }
});

// Check if application is in maintenance mode
if (config('MAINTENANCE_MODE', 0)) {
    if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'])) {
        http_response_code(503);
        if (file_exists(ROOT_PATH . '/maintenance.html')) {
            include ROOT_PATH . '/maintenance.html';
        } else {
            echo '<h1>Maintenance Mode</h1><p>The system is currently under maintenance. Please try again later.</p>';
        }
        exit;
    }
}
?>
