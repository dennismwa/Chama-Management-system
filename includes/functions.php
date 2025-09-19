<?php
/**
 * Chama Management Platform - Core Functions
 * 
 * Essential utility functions for the application
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('CHAMA_ACCESS')) {
    die('Direct access denied');
}

// ================================================
// AUTHENTICATION FUNCTIONS
// ================================================

/**
 * Authenticate user login
 */
function authenticateUser($username, $password) {
    try {
        $db = Database::getInstance();
        
        // Get user by username or email
        $user = $db->fetchOne(
            "SELECT u.*, cg.name as chama_name, cg.status as chama_status 
             FROM users u 
             JOIN chama_groups cg ON u.chama_group_id = cg.id 
             WHERE (u.username = ? OR u.email = ?) AND u.status = 'Active'",
            [$username, $username]
        );
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Check if chama group is active
        if ($user['chama_status'] !== 'Active') {
            return ['success' => false, 'message' => 'Your chama group is currently inactive'];
        }
        
        // Check if account is locked
        if ($user['account_locked_until'] && strtotime($user['account_locked_until']) > time()) {
            $unlockTime = date('H:i', strtotime($user['account_locked_until']));
            return ['success' => false, 'message' => "Account locked until $unlockTime"];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            // Increment login attempts
            incrementLoginAttempts($user['id']);
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Reset login attempts on successful login
        resetLoginAttempts($user['id']);
        
        // Update last login
        updateLastLogin($user['id']);
        
        return ['success' => true, 'user' => $user];
        
    } catch (Exception $e) {
        logError("Authentication error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Authentication failed'];
    }
}

/**
 * Increment login attempts
 */
function incrementLoginAttempts($userId) {
    try {
        $db = Database::getInstance();
        
        $attempts = $db->fetchValue(
            "SELECT login_attempts FROM users WHERE id = ?",
            [$userId]
        );
        
        $newAttempts = $attempts + 1;
        $lockUntil = null;
        
        if ($newAttempts >= MAX_LOGIN_ATTEMPTS) {
            $lockUntil = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_TIME);
        }
        
        $db->execute(
            "UPDATE users SET login_attempts = ?, account_locked_until = ? WHERE id = ?",
            [$newAttempts, $lockUntil, $userId]
        );
        
    } catch (Exception $e) {
        logError("Error incrementing login attempts: " . $e->getMessage());
    }
}

/**
 * Reset login attempts
 */
function resetLoginAttempts($userId) {
    try {
        $db = Database::getInstance();
        $db->execute(
            "UPDATE users SET login_attempts = 0, account_locked_until = NULL WHERE id = ?",
            [$userId]
        );
    } catch (Exception $e) {
        logError("Error resetting login attempts: " . $e->getMessage());
    }
}

/**
 * Update last login timestamp
 */
function updateLastLogin($userId) {
    try {
        $db = Database::getInstance();
        $db->execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$userId]
        );
    } catch (Exception $e) {
        logError("Error updating last login: " . $e->getMessage());
    }
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Generate secure random password
 */
function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long";
    }
    
    if (PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (PASSWORD_REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if (PASSWORD_REQUIRE_SYMBOLS && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    return empty($errors) ? true : $errors;
}

// ================================================
// MEMBER MANAGEMENT FUNCTIONS
// ================================================

/**
 * Generate unique member number
 */
function generateMemberNumber($chamaGroupId) {
    try {
        $db = Database::getInstance();
        
        // Get the last member number for this chama group
        $lastNumber = $db->fetchValue(
            "SELECT member_number FROM members 
             WHERE chama_group_id = ? 
             ORDER BY id DESC LIMIT 1",
            [$chamaGroupId]
        );
        
        if ($lastNumber) {
            // Extract numeric part and increment
            $numericPart = (int) filter_var($lastNumber, FILTER_SANITIZE_NUMBER_INT);
            $newNumber = $numericPart + 1;
        } else {
            $newNumber = 1;
        }
        
        return 'MEM' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        
    } catch (Exception $e) {
        logError("Error generating member number: " . $e->getMessage());
        return 'MEM' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}

/**
 * Get member by ID
 */
function getMember($memberId, $chamaGroupId = null) {
    try {
        $db = Database::getInstance();
        
        $sql = "SELECT m.*, ms.balance as savings_balance 
                FROM members m 
                LEFT JOIN member_savings ms ON m.id = ms.member_id 
                WHERE m.id = ?";
        $params = [$memberId];
        
        if ($chamaGroupId) {
            $sql .= " AND m.chama_group_id = ?";
            $params[] = $chamaGroupId;
        }
        
        return $db->fetchOne($sql, $params);
        
    } catch (Exception $e) {
        logError("Error fetching member: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all members for a chama group
 */
function getMembers($chamaGroupId, $status = null, $limit = null, $offset = 0) {
    try {
        $db = Database::getInstance();
        
        $sql = "SELECT m.*, ms.balance as savings_balance,
                       COALESCE(loan_summary.active_loans, 0) as active_loans,
                       COALESCE(loan_summary.total_loan_balance, 0) as total_loan_balance
                FROM members m 
                LEFT JOIN member_savings ms ON m.id = ms.member_id
                LEFT JOIN (
                    SELECT member_id, COUNT(*) as active_loans, SUM(balance) as total_loan_balance
                    FROM loans WHERE status = 'Active'
                    GROUP BY member_id
                ) loan_summary ON m.id = loan_summary.member_id
                WHERE m.chama_group_id = ?";
        
        $params = [$chamaGroupId];
        
        if ($status) {
            $sql .= " AND m.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY m.full_name ASC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        return $db->fetchAll($sql, $params);
        
    } catch (Exception $e) {
        logError("Error fetching members: " . $e->getMessage());
        return [];
    }
}

/**
 * Create member savings account
 */
function createMemberSavingsAccount($memberId) {
    try {
        $db = Database::getInstance();
        
        // Get the default savings account
        $savingsAccount = $db->fetchOne(
            "SELECT id FROM accounts WHERE account_code = ? AND chama_group_id = ?",
            [config('MEMBER_SAVINGS_ACCOUNT', '1001'), currentChamaGroup()]
        );
        
        if ($savingsAccount) {
            $db->execute(
                "INSERT INTO member_savings (member_id, account_id, balance, status) 
                 VALUES (?, ?, 0.00, 'Active')",
                [$memberId, $savingsAccount['id']]
            );
        }
        
    } catch (Exception $e) {
        logError("Error creating member savings account: " . $e->getMessage());
    }
}

// ================================================
// TRANSACTION FUNCTIONS
// ================================================

/**
 * Generate transaction number
 */
function generateTransactionNumber($type = 'TXN') {
    return generateReference($type, 12);
}

/**
 * Record transaction
 */
function recordTransaction($data) {
    try {
        $db = Database::getInstance();
        
        $db->beginTransaction();
        
        // Generate transaction number if not provided
        if (empty($data['transaction_number'])) {
            $data['transaction_number'] = generateTransactionNumber();
        }
        
        // Insert transaction
        $transactionId = $db->execute(
            "INSERT INTO transactions (
                chama_group_id, transaction_number, transaction_type, amount, 
                description, reference_number, payment_method, payment_reference,
                from_account_id, to_account_id, member_id, loan_id, target_id,
                transaction_date, processed_by, status, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['chama_group_id'] ?? currentChamaGroup(),
                $data['transaction_number'],
                $data['transaction_type'],
                $data['amount'],
                $data['description'] ?? '',
                $data['reference_number'] ?? '',
                $data['payment_method'] ?? 'Cash',
                $data['payment_reference'] ?? '',
                $data['from_account_id'] ?? null,
                $data['to_account_id'] ?? null,
                $data['member_id'] ?? null,
                $data['loan_id'] ?? null,
                $data['target_id'] ?? null,
                $data['transaction_date'] ?? date('Y-m-d H:i:s'),
                $data['processed_by'] ?? session()->getUserId(),
                $data['status'] ?? 'Completed',
                $data['notes'] ?? ''
            ]
        );
        
        $transactionId = $db->lastInsertId();
        
        // Update account balances if applicable
        if (!empty($data['member_id']) && in_array($data['transaction_type'], ['Deposit', 'Withdrawal'])) {
            updateMemberSavingsBalance($data['member_id'], $data['amount'], $data['transaction_type']);
        }
        
        $db->commit();
        
        return ['success' => true, 'transaction_id' => $transactionId];
        
    } catch (Exception $e) {
        $db->rollback();
        logError("Error recording transaction: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to record transaction'];
    }
}

/**
 * Update member savings balance
 */
function updateMemberSavingsBalance($memberId, $amount, $transactionType) {
    try {
        $db = Database::getInstance();
        
        if ($transactionType === 'Deposit') {
            $db->execute(
                "UPDATE member_savings 
                 SET balance = balance + ?, 
                     total_deposits = total_deposits + ?,
                     last_transaction_date = NOW() 
                 WHERE member_id = ?",
                [$amount, $amount, $memberId]
            );
        } elseif ($transactionType === 'Withdrawal') {
            $db->execute(
                "UPDATE member_savings 
                 SET balance = balance - ?, 
                     total_withdrawals = total_withdrawals + ?,
                     last_transaction_date = NOW() 
                 WHERE member_id = ?",
                [$amount, $amount, $memberId]
            );
        }
        
    } catch (Exception $e) {
        logError("Error updating member savings balance: " . $e->getMessage());
        throw $e;
    }
}

// ================================================
// LOAN FUNCTIONS
// ================================================

/**
 * Generate loan number
 */
function generateLoanNumber() {
    return generateReference('LOAN', 10);
}

/**
 * Calculate loan monthly payment
 */
function calculateLoanPayment($principal, $interestRate, $tenureMonths, $method = 'reducing_balance') {
    if ($method === 'reducing_balance') {
        $monthlyRate = $interestRate / 100 / 12;
        if ($monthlyRate == 0) return $principal / $tenureMonths;
        
        return $principal * ($monthlyRate * pow(1 + $monthlyRate, $tenureMonths)) / 
               (pow(1 + $monthlyRate, $tenureMonths) - 1);
    } else {
        // Fixed interest
        $totalInterest = $principal * ($interestRate / 100) * ($tenureMonths / 12);
        return ($principal + $totalInterest) / $tenureMonths;
    }
}

/**
 * Generate loan repayment schedule
 */
function generateLoanSchedule($loanId, $principal, $interestRate, $tenureMonths, $startDate) {
    try {
        $db = Database::getInstance();
        
        // Call stored procedure
        $db->execute(
            "CALL CalculateLoanSchedule(?, ?, ?, ?, ?)",
            [$loanId, $principal, $interestRate, $tenureMonths, $startDate]
        );
        
        return true;
        
    } catch (Exception $e) {
        logError("Error generating loan schedule: " . $e->getMessage());
        return false;
    }
}

// ================================================
// PAYMENT INTEGRATION FUNCTIONS
// ================================================

/**
 * Initialize M-Pesa STK Push
 */
function initiateMpesaPayment($phone, $amount, $reference, $description) {
    try {
        // Format phone number
        $phone = formatPhone($phone);
        
        // Generate access token
        $accessToken = getMpesaAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to get M-Pesa access token'];
        }
        
        // Prepare STK Push request
        $timestamp = date('YmdHis');
        $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);
        
        $data = [
            'BusinessShortCode' => MPESA_SHORTCODE,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) $amount,
            'PartyA' => $phone,
            'PartyB' => MPESA_SHORTCODE,
            'PhoneNumber' => $phone,
            'CallBackURL' => MPESA_CALLBACK_URL,
            'AccountReference' => $reference,
            'TransactionDesc' => $description
        ];
        
        $url = (MPESA_ENVIRONMENT === 'live') 
            ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
            : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        
        $response = makeCurlRequest($url, $data, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        
        return $response;
        
    } catch (Exception $e) {
        logError("M-Pesa payment error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Payment initialization failed'];
    }
}

/**
 * Get M-Pesa access token
 */
function getMpesaAccessToken() {
    try {
        $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
        
        $url = (MPESA_ENVIRONMENT === 'live')
            ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
            : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        
        $response = makeCurlRequest($url, null, [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json'
        ]);
        
        return $response['success'] ? $response['data']['access_token'] : null;
        
    } catch (Exception $e) {
        logError("M-Pesa token error: " . $e->getMessage());
        return null;
    }
}

/**
 * Make CURL request
 */
function makeCurlRequest($url, $data = null, $headers = []) {
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    if ($data) {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    
    curl_close($curl);
    
    if ($error) {
        return ['success' => false, 'message' => $error];
    }
    
    $decodedResponse = json_decode($response, true);
    
    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'data' => $decodedResponse,
        'raw_response' => $response
    ];
}

// ================================================
// NOTIFICATION FUNCTIONS
// ================================================

/**
 * Send SMS notification
 */
function sendSMS($phone, $message, $priority = 'Normal') {
    if (!featureEnabled('SMS_NOTIFICATIONS')) {
        return ['success' => false, 'message' => 'SMS notifications disabled'];
    }
    
    try {
        $db = Database::getInstance();
        
        // Queue the SMS
        $db->execute(
            "INSERT INTO notifications (
                chama_group_id, notification_type, message, recipient_phone, priority, status
            ) VALUES (?, 'SMS', ?, ?, ?, 'Pending')",
            [currentChamaGroup(), $message, formatPhone($phone), $priority]
        );
        
        // Process immediately if not in queue mode
        return processSMSQueue();
        
    } catch (Exception $e) {
        logError("SMS sending error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to send SMS'];
    }
}

/**
 * Process SMS queue
 */
function processSMSQueue() {
    try {
        $db = Database::getInstance();
        
        // Get pending SMS notifications
        $notifications = $db->fetchAll(
            "SELECT * FROM notifications 
             WHERE notification_type = 'SMS' AND status = 'Pending' 
             ORDER BY priority DESC, created_at ASC 
             LIMIT 10"
        );
        
        foreach ($notifications as $notification) {
            $result = sendSMSViaProvider($notification['recipient_phone'], $notification['message']);
            
            $status = $result['success'] ? 'Sent' : 'Failed';
            $failedReason = $result['success'] ? null : $result['message'];
            
            $db->execute(
                "UPDATE notifications 
                 SET status = ?, failed_reason = ?, sent_at = ?, attempts = attempts + 1 
                 WHERE id = ?",
                [$status, $failedReason, date('Y-m-d H:i:s'), $notification['id']]
            );
        }
        
        return ['success' => true, 'processed' => count($notifications)];
        
    } catch (Exception $e) {
        logError("SMS queue processing error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to process SMS queue'];
    }
}

/**
 * Send SMS via provider (Africa's Talking)
 */
function sendSMSViaProvider($phone, $message) {
    try {
        if (SMS_DRIVER !== 'africastalking') {
            return ['success' => false, 'message' => 'SMS provider not configured'];
        }
        
        $data = [
            'username' => SMS_USERNAME,
            'to' => $phone,
            'message' => $message,
            'from' => SMS_SENDER_ID
        ];
        
        $response = makeCurlRequest(
            'https://api.africastalking.com/version1/messaging',
            $data,
            [
                'ApiKey: ' . SMS_API_KEY,
                'Content-Type: application/x-www-form-urlencoded'
            ]
        );
        
        return $response;
        
    } catch (Exception $e) {
        logError("SMS provider error: " . $e->getMessage());
        return ['success' => false, 'message' => 'SMS provider error'];
    }
}

// ================================================
// FILE UPLOAD FUNCTIONS
// ================================================

/**
 * Handle file upload
 */
function handleFileUpload($file, $uploadType = 'documents', $allowedTypes = null) {
    try {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'No file uploaded'];
        }
        
        // Check file size
        $maxSize = ($uploadType === 'member_photos') ? MAX_IMAGE_SIZE : MAX_FILE_SIZE;
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File too large'];
        }
        
        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate file type
        $allowedTypes = $allowedTypes ?: (
            ($uploadType === 'member_photos') 
                ? ALLOWED_IMAGE_TYPES 
                : array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOCUMENT_TYPES)
        );
        
        if (!in_array($extension, $allowedTypes)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        // Get upload directory
        $uploadDir = getUploadPath($uploadType);
        $filepath = $uploadDir . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'url' => getUploadUrl($uploadType) . '/' . $filename
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to save file'];
        }
        
    } catch (Exception $e) {
        logError("File upload error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Upload failed'];
    }
}

// ================================================
// UTILITY FUNCTIONS
// ================================================

/**
 * Generate pagination HTML
 */
function generatePagination($currentPage, $totalPages, $baseUrl, $params = []) {
    if ($totalPages <= 1) return '';
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
    $prevPage = max(1, $currentPage - 1);
    $prevUrl = buildUrl($baseUrl, array_merge($params, ['page' => $prevPage]));
    
    $html .= '<li class="page-item ' . $prevDisabled . '">';
    $html .= '<a class="page-link" href="' . $prevUrl . '">Previous</a>';
    $html .= '</li>';
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $url = buildUrl($baseUrl, array_merge($params, ['page' => 1]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $currentPage) ? 'active' : '';
        $url = buildUrl($baseUrl, array_merge($params, ['page' => $i]));
        
        $html .= '<li class="page-item ' . $active . '">';
        $html .= '<a class="page-link" href="' . $url . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $url = buildUrl($baseUrl, array_merge($params, ['page' => $totalPages]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
    $nextPage = min($totalPages, $currentPage + 1);
    $nextUrl = buildUrl($baseUrl, array_merge($params, ['page' => $nextPage]));
    
    $html .= '<li class="page-item ' . $nextDisabled . '">';
    $html .= '<a class="page-link" href="' . $nextUrl . '">Next</a>';
    $html .= '</li>';
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Build URL with parameters
 */
function buildUrl($baseUrl, $params = []) {
    if (empty($params)) return $baseUrl;
    
    $queryString = http_build_query($params);
    $separator = (strpos($baseUrl, '?') !== false) ? '&' : '?';
    
    return $baseUrl . $separator . $queryString;
}

/**
 * Redirect with message
 */
function redirect($url, $message = null, $type = 'info') {
    if ($message) {
        session()->flash($type, $message);
    }
    
    header("Location: $url");
    exit;
}

/**
 * JSON response
 */
function jsonResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/index.php?error=login_required');
    }
}

/**
 * Require permission
 */
function requirePermission($permission) {
    requireLogin();
    
    if (!hasPermission($permission)) {
        redirect('/dashboard.php?error=access_denied');
    }
}

/**
 * Get flash message
 */
function getFlashMessage($key) {
    return session()->flash($key);
}

/**
 * Set flash message
 */
function setFlashMessage($key, $message) {
    session()->flash($key, $message);
}

?>