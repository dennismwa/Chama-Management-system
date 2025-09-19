<?php
/**
 * Chama Management Platform - Session Management
 * 
 * Secure session handling with timeout and security features
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('CHAMA_ACCESS')) {
    die('Direct access denied');
}

class SessionManager {
    private static $instance = null;
    private $isStarted = false;
    
    private function __construct() {
        $this->configureSession();
        $this->startSession();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Configure session settings
     */
    private function configureSession() {
        // Session security settings
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Session name
        session_name('CHAMA_SESSION');
        
        // Session lifetime
        ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
        ini_set('session.cookie_lifetime', SESSION_TIMEOUT);
        
        // Session save path (optional - for custom session storage)
        if (defined('SESSION_SAVE_PATH') && !empty(SESSION_SAVE_PATH)) {
            session_save_path(SESSION_SAVE_PATH);
        }
    }
    
    /**
     * Start session
     */
    private function startSession() {
        if (!$this->isStarted && session_status() === PHP_SESSION_NONE) {
            if (session_start()) {
                $this->isStarted = true;
                $this->validateSession();
                $this->regenerateIdPeriodically();
            } else {
                throw new Exception('Failed to start session');
            }
        }
    }
    
    /**
     * Validate session security
     */
    private function validateSession() {
        // Check for session hijacking
        if (!$this->validateUserAgent() || !$this->validateIpAddress()) {
            $this->destroy();
            return false;
        }
        
        // Check session timeout
        if ($this->isExpired()) {
            $this->destroy();
            return false;
        }
        
        // Update last activity
        $this->updateLastActivity();
        
        return true;
    }
    
    /**
     * Validate user agent consistency
     */
    private function validateUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $userAgent;
            return true;
        }
        
        return $_SESSION['user_agent'] === $userAgent;
    }
    
    /**
     * Validate IP address consistency (optional - can be problematic with dynamic IPs)
     */
    private function validateIpAddress() {
        $ipAddress = $this->getClientIp();
        
        if (!isset($_SESSION['ip_address'])) {
            $_SESSION['ip_address'] = $ipAddress;
            return true;
        }
        
        // For now, we'll be lenient with IP validation due to mobile networks
        return true; // Change to: $_SESSION['ip_address'] === $ipAddress; for strict validation
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Check if session is expired
     */
    private function isExpired() {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }
        
        return (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT;
    }
    
    /**
     * Update last activity timestamp
     */
    private function updateLastActivity() {
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Regenerate session ID periodically
     */
    private function regenerateIdPeriodically() {
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif ((time() - $_SESSION['created']) > 1800) { // 30 minutes
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
    
    /**
     * Set session value
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session key
     */
    public function remove($key) {
        unset($_SESSION[$key]);
    }
    
    /**
     * Get all session data
     */
    public function all() {
        return $_SESSION;
    }
    
    /**
     * Clear all session data
     */
    public function clear() {
        $_SESSION = [];
    }
    
    /**
     * Destroy session
     */
    public function destroy() {
        if ($this->isStarted) {
            $_SESSION = [];
            
            // Delete session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            session_destroy();
            $this->isStarted = false;
        }
    }
    
    /**
     * Flash message functionality
     */
    public function flash($key, $message = null) {
        if ($message === null) {
            $flash = $this->get('_flash', []);
            $value = $flash[$key] ?? null;
            unset($flash[$key]);
            $this->set('_flash', $flash);
            return $value;
        }
        
        $flash = $this->get('_flash', []);
        $flash[$key] = $message;
        $this->set('_flash', $flash);
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->has('user_id') && $this->has('user_logged_in') && $this->get('user_logged_in') === true;
    }
    
    /**
     * Login user
     */
    public function login($user) {
        session_regenerate_id(true);
        
        $this->set('user_id', $user['id']);
        $this->set('user_username', $user['username']);
        $this->set('user_full_name', $user['full_name']);
        $this->set('user_email', $user['email']);
        $this->set('user_role', $user['role']);
        $this->set('user_permissions', json_decode($user['permissions'] ?? '[]', true));
        $this->set('chama_group_id', $user['chama_group_id']);
        $this->set('user_logged_in', true);
        $this->set('login_time', time());
        
        // Log login activity
        $this->logActivity('login', 'User logged in successfully');
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            $this->logActivity('logout', 'User logged out');
        }
        
        $this->destroy();
    }
    
    /**
     * Get current user ID
     */
    public function getUserId() {
        return $this->get('user_id');
    }
    
    /**
     * Get current user data
     */
    public function getUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $this->get('user_id'),
            'username' => $this->get('user_username'),
            'full_name' => $this->get('user_full_name'),
            'email' => $this->get('user_email'),
            'role' => $this->get('user_role'),
            'permissions' => $this->get('user_permissions', []),
            'chama_group_id' => $this->get('chama_group_id'),
            'login_time' => $this->get('login_time')
        ];
    }
    
    /**
     * Get current chama group ID
     */
    public function getChamaGroupId() {
        return $this->get('chama_group_id');
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $permissions = $this->get('user_permissions', []);
        
        // Super admin has all permissions
        if (in_array('all', $permissions)) {
            return true;
        }
        
        return in_array($permission, $permissions);
    }
    
    /**
     * Generate and store CSRF token
     */
    public function generateCsrfToken() {
        if (!$this->has('csrf_token')) {
            $this->set('csrf_token', bin2hex(random_bytes(32)));
        }
        return $this->get('csrf_token');
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCsrfToken($token) {
        return hash_equals($this->get('csrf_token', ''), $token);
    }
    
    /**
     * Log user activity
     */
    private function logActivity($action, $description) {
        try {
            if (class_exists('Database') && $this->has('user_id')) {
                $db = Database::getInstance();
                $db->execute(
                    "INSERT INTO user_activity_logs (user_id, chama_group_id, activity_type, description, ip_address, user_agent, session_id, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $this->get('user_id'),
                        $this->get('chama_group_id'),
                        $action,
                        $description,
                        $this->getClientIp(),
                        $_SERVER['HTTP_USER_AGENT'] ?? '',
                        session_id()
                    ]
                );
            }
        } catch (Exception $e) {
            error_log("Failed to log user activity: " . $e->getMessage());
        }
    }
    
    /**
     * Get session info for debugging
     */
    public function getSessionInfo() {
        return [
            'session_id' => session_id(),
            'is_started' => $this->isStarted,
            'is_logged_in' => $this->isLoggedIn(),
            'last_activity' => $this->get('last_activity'),
            'created' => $this->get('created'),
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'session_data_count' => count($_SESSION)
        ];
    }
}

// Global session helper functions
function session() {
    return SessionManager::getInstance();
}

function isLoggedIn() {
    return session()->isLoggedIn();
}

function currentUser() {
    return session()->getUser();
}

function hasPermission($permission) {
    return session()->hasPermission($permission);
}

function csrfToken() {
    return session()->generateCsrfToken();
}

function csrfField() {
    $token = csrfToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
}

// Initialize session
SessionManager::getInstance();
?>
```