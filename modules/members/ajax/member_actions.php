<?php
/**
 * Chama Management Platform - Member AJAX Actions
 * 
 * Handle AJAX requests for member operations
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

define('CHAMA_ACCESS', true);
require_once '../../../config/config.php';

// Ensure user is logged in and has permission
requireLogin();
requirePermission('manage_members');

// Set JSON response headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$currentUser = currentUser();
$chamaGroupId = currentChamaGroup();
$response = ['success' => false, 'message' => 'Invalid request'];

try {
    // Get request data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate CSRF token
    $token = $data['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!session()->validateCsrfToken($token)) {
        throw new Exception('Invalid CSRF token');
    }
    
    $action = $data['action'] ?? '';
    $db = Database::getInstance();
    
    switch ($action) {
        case 'delete_member':
            $memberId = (int)($data['member_id'] ?? 0);
            
            if (!$memberId) {
                throw new Exception('Invalid member ID');
            }
            
            // Verify member belongs to current chama group
            $member = $db->fetchOne(
                "SELECT id, full_name FROM members WHERE id = ? AND chama_group_id = ?",
                [$memberId, $chamaGroupId]
            );
            
            if (!$member) {
                throw new Exception('Member not found');
            }
            
            // Check if member has active loans
            $activeLoans = $db->fetchValue(
                "SELECT COUNT(*) FROM loans WHERE member_id = ? AND status = 'Active'",
                [$memberId]
            );
            
            if ($activeLoans > 0) {
                throw new Exception('Cannot delete member with active loans. Please settle all loans first.');
            }
            
            $db->beginTransaction();
            
            // Delete member photo if exists
            $photoPath = $db->fetchValue("SELECT photo FROM members WHERE id = ?", [$memberId]);
            if ($photoPath && file_exists(getUploadPath('member_photos') . '/' . $photoPath)) {
                unlink(getUploadPath('member_photos') . '/' . $photoPath);
            }
            
            // Delete related data
            $db->execute("DELETE FROM member_savings WHERE member_id = ?", [$memberId]);
            $db->execute("DELETE FROM target_contributions WHERE member_id = ?", [$memberId]);
            $db->execute("DELETE FROM loan_guarantors WHERE guarantor_member_id = ?", [$memberId]);
            
            // Update transactions to remove member reference (keep for audit)
            $db->execute(
                "UPDATE transactions SET member_id = NULL WHERE member_id = ?", 
                [$memberId]
            );
            
            // Delete the member
            $db->execute("DELETE FROM members WHERE id = ?", [$memberId]);
            
            $db->commit();
            
            $response = [
                'success' => true,
                'message' => "Member '{$member['full_name']}' has been deleted successfully"
            ];
            break;
            
        case 'get_member_stats':
            $memberId = (int)($data['member_id'] ?? 0);
            
            if (!$memberId) {
                throw new Exception('Invalid member ID');
            }
            
            // Get updated member statistics
            $stats = $db->fetchOne(
                "SELECT 
                    ms.balance as savings_balance,
                    COUNT(DISTINCT t.id) as total_transactions,
                    COALESCE(SUM(CASE WHEN l.status = 'Active' THEN l.balance ELSE 0 END), 0) as active_loan_balance,
                    COALESCE(SUM(tc.amount), 0) as target_contributions
                 FROM members m
                 LEFT JOIN member_savings ms ON m.id = ms.member_id
                 LEFT JOIN transactions t ON m.id = t.member_id AND t.status = 'Completed'
                 LEFT JOIN loans l ON m.id = l.member_id
                 LEFT JOIN target_contributions tc ON m.id = tc.member_id
                 WHERE m.id = ? AND m.chama_group_id = ?
                 GROUP BY m.id",
                [$memberId, $chamaGroupId]
            );
            
            if (!$stats) {
                throw new Exception('Member not found');
            }
            
            $response = [
                'success' => true,
                'stats' => $stats
            ];
            break;
            
        case 'update_member_status':
            $memberId = (int)($data['member_id'] ?? 0);
            $newStatus = $data['status'] ?? '';
            
            if (!$memberId || !in_array($newStatus, ['Active', 'Inactive', 'Suspended'])) {
                throw new Exception('Invalid parameters');
            }
            
            // Verify member belongs to current chama group
            $member = $db->fetchOne(
                "SELECT id, full_name, status FROM members WHERE id = ? AND chama_group_id = ?",
                [$memberId, $chamaGroupId]
            );
            
            if (!$member) {
                throw new Exception('Member not found');
            }
            
            if ($member['status'] === $newStatus) {
                throw new Exception('Member is already ' . strtolower($newStatus));
            }
            
            // Update member status
            $db->execute(
                "UPDATE members SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$newStatus, $memberId]
            );
            
            $response = [
                'success' => true,
                'message' => "Member status updated to {$newStatus}"
            ];
            break;
            
        case 'get_member_transactions':
            $memberId = (int)($data['member_id'] ?? 0);
            $limit = min(50, max(1, (int)($data['limit'] ?? 10)));
            $offset = max(0, (int)($data['offset'] ?? 0));
            
            if (!$memberId) {
                throw new Exception('Invalid member ID');
            }
            
            // Verify member belongs to current chama group
            $memberExists = $db->fetchValue(
                "SELECT COUNT(*) FROM members WHERE id = ? AND chama_group_id = ?",
                [$memberId, $chamaGroupId]
            );
            
            if (!$memberExists) {
                throw new Exception('Member not found');
            }
            
            // Get transactions
            $transactions = $db->fetchAll(
                "SELECT 
                    t.id, t.transaction_number, t.transaction_type, t.amount, 
                    t.description, t.transaction_date, t.status, t.payment_method,
                    u.full_name as processed_by_name
                 FROM transactions t
                 LEFT JOIN users u ON t.processed_by = u.id
                 WHERE t.member_id = ? AND t.status = 'Completed'
                 ORDER BY t.created_at DESC
                 LIMIT ? OFFSET ?",
                [$memberId, $limit, $offset]
            );
            
            // Format transactions
            $formattedTransactions = array_map(function($transaction) {
                return [
                    'id' => $transaction['id'],
                    'number' => $transaction['transaction_number'],
                    'type' => $transaction['transaction_type'],
                    'amount' => (float)$transaction['amount'],
                    'description' => $transaction['description'],
                    'date' => $transaction['transaction_date'],
                    'status' => $transaction['status'],
                    'payment_method' => $transaction['payment_method'],
                    'processed_by' => $transaction['processed_by_name']
                ];
            }, $transactions);
            
            $response = [
                'success' => true,
                'transactions' => $formattedTransactions,
                'has_more' => count($transactions) === $limit
            ];
            break;
            
        case 'search_members':
            $query = trim($data['query'] ?? '');
            $limit = min(20, max(1, (int)($data['limit'] ?? 10)));
            
            if (strlen($query) < 2) {
                throw new Exception('Search query too short');
            }
            
            // Search members
            $members = $db->fetchAll(
                "SELECT 
                    m.id, m.member_number, m.full_name, m.phone, m.email, 
                    m.status, m.photo, ms.balance as savings_balance
                 FROM members m
                 LEFT JOIN member_savings ms ON m.id = ms.member_id
                 WHERE m.chama_group_id = ? 
                 AND (m.full_name LIKE ? OR m.member_number LIKE ? OR m.phone LIKE ? OR m.email LIKE ?)
                 ORDER BY 
                    CASE WHEN m.full_name LIKE ? THEN 1 ELSE 2 END,
                    m.full_name ASC
                 LIMIT ?",
                [
                    $chamaGroupId,
                    "%{$query}%", "%{$query}%", "%{$query}%", "%{$query}%",
                    "{$query}%", // Exact match priority
                    $limit
                ]
            );
            
            // Format members
            $formattedMembers = array_map(function($member) {
                return [
                    'id' => $member['id'],
                    'member_number' => $member['member_number'],
                    'full_name' => $member['full_name'],
                    'phone' => $member['phone'],
                    'email' => $member['email'],
                    'status' => $member['status'],
                    'savings_balance' => (float)($member['savings_balance'] ?? 0),
                    'avatar_url' => $member['photo'] 
                        ? getUploadUrl('members') . '/' . $member['photo']
                        : null
                ];
            }, $members);
            
            $response = [
                'success' => true,
                'members' => $formattedMembers
            ];
            break;
            
        case 'bulk_update_status':
            $memberIds = $data['member_ids'] ?? [];
            $newStatus = $data['status'] ?? '';
            
            if (empty($memberIds) || !is_array($memberIds) || !in_array($newStatus, ['Active', 'Inactive', 'Suspended'])) {
                throw new Exception('Invalid parameters');
            }
            
            // Sanitize member IDs
            $memberIds = array_filter(array_map('intval', $memberIds));
            
            if (empty($memberIds)) {
                throw new Exception('No valid member IDs provided');
            }
            
            // Verify all members belong to current chama group
            $placeholders = str_repeat('?,', count($memberIds) - 1) . '?';
            $validMembers = $db->fetchValue(
                "SELECT COUNT(*) FROM members WHERE id IN ({$placeholders}) AND chama_group_id = ?",
                array_merge($memberIds, [$chamaGroupId])
            );
            
            if ($validMembers !== count($memberIds)) {
                throw new Exception('Some members not found or do not belong to this chama');
            }
            
            // Update member statuses
            $affected = $db->execute(
                "UPDATE members SET status = ?, updated_at = CURRENT_TIMESTAMP 
                 WHERE id IN ({$placeholders}) AND chama_group_id = ?",
                array_merge([$newStatus], $memberIds, [$chamaGroupId])
            )->rowCount();
            
            $response = [
                'success' => true,
                'message' => "Updated status for {$affected} member(s) to {$newStatus}"
            ];
            break;
            
        case 'export_members':
            $format = $data['format'] ?? 'csv';
            $status = $data['status'] ?? '';
            
            // Get members for export
            $conditions = ['m.chama_group_id = ?'];
            $params = [$chamaGroupId];
            
            if ($status) {
                $conditions[] = 'm.status = ?';
                $params[] = $status;
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            
            $members = $db->fetchAll(
                "SELECT 
                    m.member_number, m.full_name, m.phone, m.email, m.id_number,
                    m.date_of_birth, m.gender, m.occupation, m.address,
                    m.membership_date, m.membership_fee_paid, m.status,
                    ms.balance as savings_balance
                 FROM members m 
                 LEFT JOIN member_savings ms ON m.id = ms.member_id
                 {$whereClause}
                 ORDER BY m.member_number ASC",
                $params
            );
            
            if ($format === 'csv') {
                // Generate CSV content
                $csvContent = "Member Number,Full Name,Phone,Email,ID Number,Date of Birth,Gender,Occupation,Address,Membership Date,Membership Fee,Savings Balance,Status\n";
                
                foreach ($members as $member) {
                    $csvContent .= sprintf(
                        "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                        $member['member_number'],
                        '"' . str_replace('"', '""', $member['full_name']) . '"',
                        $member['phone'],
                        $member['email'] ?? '',
                        $member['id_number'] ?? '',
                        $member['date_of_birth'] ?? '',
                        $member['gender'] ?? '',
                        '"' . str_replace('"', '""', $member['occupation'] ?? '') . '"',
                        '"' . str_replace('"', '""', $member['address'] ?? '') . '"',
                        $member['membership_date'],
                        number_format($member['membership_fee_paid'] ?? 0, 2),
                        number_format($member['savings_balance'] ?? 0, 2),
                        $member['status']
                    );
                }
                
                // Set headers for file download
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="members_export_' . date('Y-m-d') . '.csv"');
                header('Content-Length: ' . strlen($csvContent));
                
                echo $csvContent;
                exit;
            }
            
            $response = [
                'success' => true,
                'data' => $members,
                'count' => count($members)
            ];
            break;
            
        case 'validate_member_data':
            $memberData = $data['member_data'] ?? [];
            $memberId = (int)($data['member_id'] ?? 0); // For updates
            
            $errors = [];
            
            // Validate phone number
            if (!empty($memberData['phone'])) {
                $phone = formatPhone($memberData['phone']);
                $phoneQuery = "SELECT id FROM members WHERE phone = ? AND chama_group_id = ?";
                $phoneParams = [$phone, $chamaGroupId];
                
                if ($memberId) {