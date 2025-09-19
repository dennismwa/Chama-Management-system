<?php
/**
 * Chama Management Platform - Members Management
 * 
 * List and manage all chama members
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

define('CHAMA_ACCESS', true);
require_once '../../config/config.php';

// Ensure user is logged in and has permission
requireLogin();
requirePermission('manage_members');

$pageTitle = 'Members Management';
$currentUser = currentUser();
$chamaGroupId = currentChamaGroup();

// Initialize variables
$members = [];
$totalMembers = 0;
$error = '';
$success = '';

// Pagination and filters
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(50, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$sortBy = $_GET['sort'] ?? 'full_name';
$sortOrder = ($_GET['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

// Valid sort columns
$validSortColumns = ['full_name', 'member_number', 'membership_date', 'phone', 'status'];
if (!in_array($sortBy, $validSortColumns)) {
    $sortBy = 'full_name';
}

try {
    $db = Database::getInstance();
    
    // Build query conditions
    $conditions = ['m.chama_group_id = ?'];
    $params = [$chamaGroupId];
    
    if ($search) {
        $conditions[] = '(m.full_name LIKE ? OR m.member_number LIKE ? OR m.phone LIKE ? OR m.email LIKE ?)';
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($status) {
        $conditions[] = 'm.status = ?';
        $params[] = $status;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $conditions);
    
    // Get total count
    $totalMembers = $db->fetchValue(
        "SELECT COUNT(*) FROM members m $whereClause",
        $params
    );
    
    // Get members with pagination
    $members = $db->fetchAll(
        "SELECT 
            m.*,
            ms.balance as savings_balance,
            COALESCE(loan_summary.active_loans, 0) as active_loans,
            COALESCE(loan_summary.total_loan_balance, 0) as total_loan_balance,
            COALESCE(target_summary.target_contributions, 0) as target_contributions
         FROM members m 
         LEFT JOIN member_savings ms ON m.id = ms.member_id
         LEFT JOIN (
             SELECT 
                 member_id,
                 COUNT(*) as active_loans,
                 SUM(balance) as total_loan_balance
             FROM loans 
             WHERE status = 'Active'
             GROUP BY member_id
         ) loan_summary ON m.id = loan_summary.member_id
         LEFT JOIN (
             SELECT 
                 member_id,
                 SUM(amount) as target_contributions
             FROM target_contributions
             GROUP BY member_id
         ) target_summary ON m.id = target_summary.member_id
         $whereClause
         ORDER BY m.$sortBy $sortOrder
         LIMIT ? OFFSET ?",
        array_merge($params, [$limit, $offset])
    );
    
    // Get summary statistics
    $stats = $db->fetchOne(
        "SELECT 
            COUNT(*) as total_members,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_members,
            SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) as inactive_members,
            SUM(CASE WHEN status = 'Suspended' THEN 1 ELSE 0 END) as suspended_members,
            COALESCE(SUM(ms.balance), 0) as total_savings
         FROM members m 
         LEFT JOIN member_savings ms ON m.id = ms.member_id
         WHERE m.chama_group_id = ?",
        [$chamaGroupId]
    );
    
} catch (Exception $e) {
    $error = "Failed to load members: " . $e->getMessage();
    logError($error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!session()->validateCsrfToken($_POST['_token'] ?? '')) {
        $error = 'Invalid request token';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'bulk_update_status':
                $memberIds = $_POST['member_ids'] ?? [];
                $newStatus = $_POST['new_status'] ?? '';
                
                if (!empty($memberIds) && in_array($newStatus, ['Active', 'Inactive', 'Suspended'])) {
                    try {
                        $placeholders = str_repeat('?,', count($memberIds) - 1) . '?';
                        $params = array_merge($memberIds, [$newStatus]);
                        
                        $affected = $db->execute(
                            "UPDATE members SET status = ? WHERE id IN ($placeholders) AND chama_group_id = ?",
                            array_merge([$newStatus], $memberIds, [$chamaGroupId])
                        )->rowCount();
                        
                        $success = "Updated status for $affected member(s)";
                        
                        // Refresh data
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
                        exit;
                        
                    } catch (Exception $e) {
                        $error = "Failed to update member status: " . $e->getMessage();
                    }
                }
                break;
                
            case 'bulk_delete':
                $memberIds = $_POST['member_ids'] ?? [];
                
                if (!empty($memberIds)) {
                    try {
                        $db->beginTransaction();
                        
                        $placeholders = str_repeat('?,', count($memberIds) - 1) . '?';
                        
                        // Delete related data first
                        $db->execute(
                            "DELETE ms FROM member_savings ms 
                             JOIN members m ON ms.member_id = m.id 
                             WHERE m.id IN ($placeholders) AND m.chama_group_id = ?",
                            array_merge($memberIds, [$chamaGroupId])
                        );
                        
                        // Delete members
                        $affected = $db->execute(
                            "DELETE FROM members WHERE id IN ($placeholders) AND chama_group_id = ?",
                            array_merge($memberIds, [$chamaGroupId])
                        )->rowCount();
                        
                        $db->commit();
                        
                        $success = "Deleted $affected member(s)";
                        
                        // Refresh data
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
                        exit;
                        
                    } catch (Exception $e) {
                        $db->rollback();
                        $error = "Failed to delete members: " . $e->getMessage();
                    }
                }
                break;
        }
    }
}

$totalPages = ceil($totalMembers / $limit);

include_once '../../includes/header.php';
?>

<style>
    .members-container {
        padding: 0;
    }
    
    .page-header {
        background: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
    }
    
    [data-theme="dark"] .page-header {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    
    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }
    
    [data-theme="dark"] .page-title {
        color: var(--gray-100);
    }
    
    .header-actions {
        display: flex;
        gap: 1rem;
    }
    
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
    }
    
    .stat-item {
        text-align: center;
        padding: 1rem;
        background: var(--gray-50);
        border-radius: 10px;
        border: 1px solid var(--gray-200);
    }
    
    [data-theme="dark"] .stat-item {
        background: var(--gray-700);
        border-color: var(--gray-600);
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-600);
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 500;
    }
    
    [data-theme="dark"] .stat-label {
        color: var(--gray-400);
    }
    
    .filters-section {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
    }
    
    [data-theme="dark"] .filters-section {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .filters-form {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto auto;
        gap: 1rem;
        align-items: end;
    }
    
    @media (max-width: 768px) {
        .filters-form {
            grid-template-columns: 1fr;
        }
        
        .header-content {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }
        
        .header-actions {
            justify-content: stretch;
        }
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }
    
    [data-theme="dark"] .form-label {
        color: var(--gray-300);
    }
    
    .form-input,
    .form-select {
        padding: 0.75rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 0.875rem;
        background: white;
        color: var(--gray-900);
        transition: all 0.3s ease;
    }
    
    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary-500);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    [data-theme="dark"] .form-input,
    [data-theme="dark"] .form-select {
        background: var(--gray-700);
        border-color: var(--gray-600);
        color: var(--gray-100);
    }
    
    .members-table-container {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
        overflow: hidden;
    }
    
    [data-theme="dark"] .members-table-container {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .table-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    [data-theme="dark"] .table-header {
        border-color: var(--gray-700);
    }
    
    .table-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
    }
    
    [data-theme="dark"] .table-title {
        color: var(--gray-100);
    }
    
    .table-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }
    
    .bulk-actions {
        display: none;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.5rem;
        background: var(--primary-50);
        border-bottom: 1px solid var(--primary-200);
    }
    
    .bulk-actions.active {
        display: flex;
    }
    
    [data-theme="dark"] .bulk-actions {
        background: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.3);
    }
    
    .selected-count {
        font-weight: 600;
        color: var(--primary-700);
    }
    
    [data-theme="dark"] .selected-count {
        color: var(--primary-300);
    }
    
    .members-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .members-table th,
    .members-table td {
        padding: 1rem 1.5rem;
        text-align: left;
        border-bottom: 1px solid var(--gray-200);
    }
    
    [data-theme="dark"] .members-table th,
    [data-theme="dark"] .members-table td {
        border-color: var(--gray-700);
    }
    
    .members-table th {
        background: var(--gray-50);
        font-weight: 600;
        color: var(--gray-700);
        font-size: 0.875rem;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    [data-theme="dark"] .members-table th {
        background: var(--gray-900);
        color: var(--gray-300);
    }
    
    .members-table tbody tr:hover {
        background: var(--gray-50);
    }
    
    [data-theme="dark"] .members-table tbody tr:hover {
        background: var(--gray-700);
    }
    
    .sortable {
        cursor: pointer;
        user-select: none;
        position: relative;
        padding-right: 2rem;
    }
    
    .sortable::after {
        content: '\f0dc';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: 0.5rem;
        opacity: 0.5;
        font-size: 0.8rem;
    }
    
    .sortable.active::after {
        opacity: 1;
    }
    
    .sortable.asc::after {
        content: '\f0de';
    }
    
    .sortable.desc::after {
        content: '\f0dd';
    }
    
    .member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-500);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1rem;
        margin-right: 0.75rem;
    }
    
    .member-info {
        display: flex;
        align-items: center;
    }
    
    .member-details {
        min-width: 0;
    }
    
    .member-name {
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }
    
    [data-theme="dark"] .member-name {
        color: var(--gray-100);
    }
    
    .member-number {
        font-size: 0.8rem;
        color: var(--gray-500);
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .status-badge.active {
        background: var(--success-100);
        color: var(--success-700);
    }
    
    .status-badge.inactive {
        background: var(--gray-100);
        color: var(--gray-700);
    }
    
    .status-badge.suspended {
        background: var(--error-100);
        color: var(--error-700);
    }
    
    [data-theme="dark"] .status-badge.active {
        background: rgba(34, 197, 94, 0.2);
        color: var(--success-400);
    }
    
    [data-theme="dark"] .status-badge.inactive {
        background: rgba(107, 114, 128, 0.2);
        color: var(--gray-400);
    }
    
    [data-theme="dark"] .status-badge.suspended {
        background: rgba(239, 68, 68, 0.2);
        color: var(--error-400);
    }
    
    .action-menu {
        position: relative;
    }
    
    .action-btn {
        background: none;
        border: none;
        color: var(--gray-600);
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .action-btn:hover {
        background: var(--gray-100);
        color: var(--gray-900);
    }
    
    [data-theme="dark"] .action-btn {
        color: var(--gray-400);
    }
    
    [data-theme="dark"] .action-btn:hover {
        background: var(--gray-700);
        color: var(--gray-200);
    }
    
    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        box-shadow: var(--shadow-lg);
        min-width: 150px;
        z-index: 50;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    }
    
    .dropdown-menu.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    [data-theme="dark"] .dropdown-menu {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .dropdown-item {
        display: block;
        padding: 0.75rem 1rem;
        color: var(--gray-700);
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }
    
    .dropdown-item:hover {
        background: var(--gray-100);
        color: var(--gray-900);
    }
    
    [data-theme="dark"] .dropdown-item {
        color: var(--gray-300);
    }
    
    [data-theme="dark"] .dropdown-item:hover {
        background: var(--gray-700);
        color: var(--gray-100);
    }
    
    .dropdown-item.danger:hover {
        background: var(--error-50);
        color: var(--error-700);
    }
    
    [data-theme="dark"] .dropdown-item.danger:hover {
        background: rgba(239, 68, 68, 0.1);
        color: var(--error-400);
    }
    
    .pagination-container {
        padding: 1.5rem;
        border-top: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    [data-theme="dark"] .pagination-container {
        border-color: var(--gray-700);
    }
    
    .pagination-info {
        font-size: 0.875rem;
        color: var(--gray-600);
    }
    
    [data-theme="dark"] .pagination-info {
        color: var(--gray-400);
    }
    
    .pagination {
        display: flex;
        gap: 0.5rem;
    }
    
    .page-btn {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--gray-300);
        background: white;
        color: var(--gray-700);
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }
    
    .page-btn:hover {
        background: var(--gray-100);
        border-color: var(--gray-400);
    }
    
    .page-btn.active {
        background: var(--primary-500);
        color: white;
        border-color: var(--primary-500);
    }
    
    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    [data-theme="dark"] .page-btn {
        background: var(--gray-700);
        border-color: var(--gray-600);
        color: var(--gray-300);
    }
    
    [data-theme="dark"] .page-btn:hover {
        background: var(--gray-600);
        border-color: var(--gray-500);
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--gray-500);
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--gray-700);
    }
    
    [data-theme="dark"] .empty-state h3 {
        color: var(--gray-300);
    }
    
    .checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
</style>

<div class="members-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1 class="page-title">Members Management</h1>
            <div class="header-actions">
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Add Member
                </a>
                <button class="btn btn-secondary" onclick="exportMembers()">
                    <i class="fas fa-download mr-2"></i>
                    Export
                </button>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['total_members'] ?? 0); ?></div>
                <div class="stat-label">Total Members</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['active_members'] ?? 0); ?></div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['inactive_members'] ?? 0); ?></div>
                <div class="stat-label">Inactive</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo number_format($stats['suspended_members'] ?? 0); ?></div>
                <div class="stat-label">Suspended</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo formatCurrency($stats['total_savings'] ?? 0); ?></div>
                <div class="stat-label">Total Savings</div>
            </div>
        </div>
    </div>
    
    <!-- Filters Section -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="form-group">
                <label class="form-label">Search Members</label>
                <input type="text" name="search" class="form-input" placeholder="Search by name, phone, email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="Active" <?php echo $status === 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo $status === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="Suspended" <?php echo $status === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Sort By</label>
                <select name="sort" class="form-select">
                    <option value="full_name" <?php echo $sortBy === 'full_name' ? 'selected' : ''; ?>>Name</option>
                    <option value="member_number" <?php echo $sortBy === 'member_number' ? 'selected' : ''; ?>>Member Number</option>
                    <option value="membership_date" <?php echo $sortBy === 'membership_date' ? 'selected' : ''; ?>>Join Date</option>
                    <option value="phone" <?php echo $sortBy === 'phone' ? 'selected' : ''; ?>>Phone</option>
                    <option value="status" <?php echo $sortBy === 'status' ? 'selected' : ''; ?>>Status</option>
                </select>
                <input type="hidden" name="order" value="<?php echo $sortOrder; ?>">
            </div>
            
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-2"></i>
                    Filter
                </button>
            </div>
            
            <div>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-times mr-2"></i>
                    Clear
                </a>
            </div>
        </form>
    </div>
    
    <!-- Members Table -->
    <div class="members-table-container">
        <div class="table-header">
            <h3 class="table-title">
                <?php echo number_format($totalMembers); ?> Member<?php echo $totalMembers != 1 ? 's' : ''; ?> Found
            </h3>
            <div class="table-actions">
                <select id="limitSelect" class="form-select" onchange="changeLimit(this.value)">
                    <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10 per page</option>
                    <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20 per page</option>
                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 per page</option>
                </select>
            </div>
        </div>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions" id="bulkActions">
            <div class="selected-count">
                <span id="selectedCount">0</span> selected
            </div>
            <form method="POST" id="bulkForm" style="display: flex; gap: 1rem; align-items: center;">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="">
                <input type="hidden" name="member_ids" value="">
                
                <select name="new_status" class="form-select" style="min-width: 120px;">
                    <option value="">Change Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Suspended">Suspended</option>
                </select>
                
                <button type="button" class="btn btn-secondary btn-sm" onclick="bulkUpdateStatus()">
                    Update Status
                </button>
                
                <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()">
                    <i class="fas fa-trash mr-1"></i>
                    Delete
                </button>
                
                <button type="button" class="btn btn-secondary btn-sm" onclick="clearSelection()">
                    Clear
                </button>
            </form>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error m-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success m-4">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($members)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No members found</h3>
                <p>
                    <?php if ($search || $status): ?>
                        Try adjusting your search criteria or <a href="?">clear filters</a>
                    <?php else: ?>
                        Get started by <a href="add.php">adding your first member</a>
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="members-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAll" class="checkbox">
                            </th>
                            <th class="sortable <?php echo $sortBy === 'full_name' ? 'active ' . $sortOrder : ''; ?>" onclick="sortTable('full_name')">
                                Member
                            </th>
                            <th class="sortable <?php echo $sortBy === 'phone' ? 'active ' . $sortOrder : ''; ?>" onclick="sortTable('phone')">
                                Contact
                            </th>
                            <th class="sortable <?php echo $sortBy === 'membership_date' ? 'active ' . $sortOrder : ''; ?>" onclick="sortTable('membership_date')">
                                Joined
                            </th>
                            <th>Savings Balance</th>
                            <th>Active Loans</th>
                            <th class="sortable <?php echo $sortBy === 'status' ? 'active ' . $sortOrder : ''; ?>" onclick="sortTable('status')">
                                Status
                            </th>
                            <th style="width: 60px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="checkbox member-checkbox" value="<?php echo $member['id']; ?>">
                                </td>
                                <td>
                                    <div class="member-info">
                                        <div class="member-avatar">
                                            <?php if ($member['photo']): ?>
                                                <img src="<?php echo getUploadUrl('members') . '/' . $member['photo']; ?>" 
                                                     alt="<?php echo htmlspecialchars($member['full_name']); ?>"
                                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($member['full_name'], 0, 1)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="member-details">
                                            <div class="member-name">
                                                <a href="view.php?id=<?php echo $member['id']; ?>" style="text-decoration: none; color: inherit;">
                                                    <?php echo htmlspecialchars($member['full_name']); ?>
                                                </a>
                                            </div>
                                            <div class="member-number"><?php echo htmlspecialchars($member['member_number']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div style="margin-bottom: 0.25rem;">
                                            <i class="fas fa-phone text-gray-400 mr-1"></i>
                                            <?php echo htmlspecialchars($member['phone']); ?>
                                        </div>
                                        <?php if ($member['email']): ?>
                                            <div style="font-size: 0.8rem; color: var(--gray-500);">
                                                <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                                <?php echo htmlspecialchars($member['email']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div><?php echo formatDate($member['membership_date'], 'M j, Y'); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--gray-500);">
                                            <?php 
                                            $days = floor((time() - strtotime($member['membership_date'])) / 86400);
                                            echo $days . ' days ago';
                                            ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--success-600);">
                                        <?php echo formatCurrency($member['savings_balance'] ?? 0); ?>
                                    </div>
                                    <?php if ($member['target_contributions'] > 0): ?>
                                        <div style="font-size: 0.8rem; color: var(--gray-500);">
                                            +<?php echo formatCurrency($member['target_contributions']); ?> in targets
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($member['active_loans'] > 0): ?>
                                        <div style="font-weight: 600; color: var(--warning-600);">
                                            <?php echo number_format($member['active_loans']); ?> loan<?php echo $member['active_loans'] != 1 ? 's' : ''; ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--gray-500);">
                                            <?php echo formatCurrency($member['total_loan_balance'] ?? 0); ?> balance
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--gray-500);">No active loans</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($member['status']); ?>">
                                        <?php echo htmlspecialchars($member['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-menu">
                                        <button class="action-btn" onclick="toggleDropdown(this)">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="view.php?id=<?php echo $member['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-eye mr-2"></i>
                                                View Details
                                            </a>
                                            <a href="edit.php?id=<?php echo $member['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-edit mr-2"></i>
                                                Edit Member
                                            </a>
                                            <a href="../transactions/deposits.php?member_id=<?php echo $member['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-plus-circle mr-2"></i>
                                                Add Deposit
                                            </a>
                                            <a href="../loans/applications.php?member_id=<?php echo $member['id']; ?>" class="dropdown-item">
                                                <i class="fas fa-hand-holding-usd mr-2"></i>
                                                Loan Application
                                            </a>
                                            <button class="dropdown-item danger" onclick="deleteMember(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['full_name'], ENT_QUOTES); ?>')">
                                                <i class="fas fa-trash mr-2"></i>
                                                Delete Member
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing <?php echo number_format($offset + 1); ?> to <?php echo number_format(min($offset + $limit, $totalMembers)); ?> of <?php echo number_format($totalMembers); ?> members
                </div>
                
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" class="page-btn">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="page-btn" style="border: none; background: none;">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="page-btn" style="border: none; background: none;">...</span>
                        <?php endif; ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>" class="page-btn"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize member selection
    initializeMemberSelection();
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-menu')) {
            document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
                menu.classList.remove('active');
            });
        }
    });
});

function initializeMemberSelection() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const memberCheckboxes = document.querySelectorAll('.member-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        memberCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });
    
    // Individual checkbox functionality
    memberCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedBoxes = document.querySelectorAll('.member-checkbox:checked');
            selectAllCheckbox.checked = checkedBoxes.length === memberCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < memberCheckboxes.length;
            updateBulkActions();
        });
    });
    
    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.member-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count > 0) {
            bulkActions.classList.add('active');
            selectedCount.textContent = count;
        } else {
            bulkActions.classList.remove('active');
        }
    }
}

function toggleDropdown(button) {
    const dropdown = button.nextElementSibling;
    const isActive = dropdown.classList.contains('active');
    
    // Close all other dropdowns
    document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
        menu.classList.remove('active');
    });
    
    // Toggle current dropdown
    if (!isActive) {
        dropdown.classList.add('active');
    }
}

function sortTable(column) {
    const currentSort = new URLSearchParams(window.location.search).get('sort');
    const currentOrder = new URLSearchParams(window.location.search).get('order') || 'asc';
    
    let newOrder = 'asc';
    if (currentSort === column && currentOrder === 'asc') {
        newOrder = 'desc';
    }
    
    const params = new URLSearchParams(window.location.search);
    params.set('sort', column);
    params.set('order', newOrder);
    params.set('page', '1'); // Reset to first page
    
    window.location.href = '?' + params.toString();
}

function changeLimit(limit) {
    const params = new URLSearchParams(window.location.search);
    params.set('limit', limit);
    params.set('page', '1'); // Reset to first page
    
    window.location.href = '?' + params.toString();
}

function clearSelection() {
    document.querySelectorAll('.member-checkbox:checked').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    document.getElementById('selectAll').indeterminate = false;
    document.getElementById('bulkActions').classList.remove('active');
}

function getSelectedMemberIds() {
    const checkedBoxes = document.querySelectorAll('.member-checkbox:checked');
    return Array.from(checkedBoxes).map(checkbox => checkbox.value);
}

function bulkUpdateStatus() {
    const memberIds = getSelectedMemberIds();
    const newStatus = document.querySelector('select[name="new_status"]').value;
    
    if (memberIds.length === 0) {
        showToast('Please select members to update', 'warning');
        return;
    }
    
    if (!newStatus) {
        showToast('Please select a status', 'warning');
        return;
    }
    
    confirmAction(
        `Are you sure you want to change the status of ${memberIds.length} member(s) to ${newStatus}?`,
        function() {
            const form = document.getElementById('bulkForm');
            form.querySelector('input[name="action"]').value = 'bulk_update_status';
            form.querySelector('input[name="member_ids"]').value = JSON.stringify(memberIds);
            form.submit();
        },
        'Update Status'
    );
}

function bulkDelete() {
    const memberIds = getSelectedMemberIds();
    
    if (memberIds.length === 0) {
        showToast('Please select members to delete', 'warning');
        return;
    }
    
    confirmAction(
        `Are you sure you want to delete ${memberIds.length} member(s)? This action cannot be undone and will also delete all associated data.`,
        function() {
            const form = document.getElementById('bulkForm');
            form.querySelector('input[name="action"]').value = 'bulk_delete';
            form.querySelector('input[name="member_ids"]').value = JSON.stringify(memberIds);
            form.submit();
        },
        'Delete Members'
    );
}

function deleteMember(memberId, memberName) {
    confirmAction(
        `Are you sure you want to delete "${memberName}"? This action cannot be undone and will also delete all associated savings, loans, and transaction data.`,
        function() {
            showLoading();
            
            fetch('ajax/member_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    action: 'delete_member',
                    member_id: memberId
                })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    showToast('Member deleted successfully', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(data.message || 'Failed to delete member', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                showToast('An error occurred while deleting the member', 'error');
                console.error('Error:', error);
            });
        },
        'Delete Member'
    );
}

function exportMembers() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    
    showLoading();
    
    fetch('?' + params.toString())
    .then(response => {
        if (response.ok) {
            return response.blob();
        }
        throw new Error('Export failed');
    })
    .then(blob => {
        hideLoading();
        
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `members_export_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showToast('Members exported successfully', 'success');
    })
    .catch(error => {
        hideLoading();
        showToast('Failed to export members', 'error');
        console.error('Export error:', error);
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+A to select all (when not in input)
    if (e.ctrlKey && e.key === 'a' && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
        e.preventDefault();
        document.getElementById('selectAll').click();
    }
    
    // Delete key to delete selected
    if (e.key === 'Delete' && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
        const selectedIds = getSelectedMemberIds();
        if (selectedIds.length > 0) {
            bulkDelete();
        }
    }
    
    // Escape to clear selection
    if (e.key === 'Escape') {
        clearSelection();
        // Close any open dropdowns
        document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
            menu.classList.remove('active');
        });
    }
});

// Auto-refresh data every 30 seconds
setInterval(function() {
    // Only refresh if no modals are open and no selections are made
    if (!document.querySelector('.modal.show') && getSelectedMemberIds().length === 0) {
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('ajax', '1');
        
        fetch(currentUrl.toString())
        .then(response => response.json())
        .then(data => {
            if (data.members_count !== undefined) {
                // Update member count in header if changed
                const countElement = document.querySelector('.table-title');
                if (countElement) {
                    const newText = `${data.members_count} Member${data.members_count !== 1 ? 's' : ''} Found`;
                    if (countElement.textContent !== newText) {
                        countElement.textContent = newText;
                        showToast('Member list updated', 'info', null, 2000);
                    }
                }
            }
        })
        .catch(error => {
            console.log('Auto-refresh failed:', error);
        });
    }
}, 30000);
</script>

<?php include_once '../../includes/footer.php'; ?>