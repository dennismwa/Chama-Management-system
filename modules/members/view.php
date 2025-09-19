<?php
/**
 * Chama Management Platform - Member Profile View
 * 
 * Detailed view of member information and activities
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

define('CHAMA_ACCESS', true);
require_once '../../config/config.php';

// Ensure user is logged in and has permission
requireLogin();
requirePermission('manage_members');

$pageTitle = 'Member Profile';
$currentUser = currentUser();
$chamaGroupId = currentChamaGroup();

$memberId = (int)($_GET['id'] ?? 0);
$member = null;
$memberStats = [];
$recentTransactions = [];
$activeLoans = [];
$targetContributions = [];
$error = '';

if (!$memberId) {
    redirect('index.php', 'Invalid member ID', 'error');
}

try {
    $db = Database::getInstance();
    
    // Get member details
    $member = $db->fetchOne(
        "SELECT m.*, ms.balance as savings_balance, ms.total_deposits, ms.total_withdrawals, ms.interest_earned
         FROM members m 
         LEFT JOIN member_savings ms ON m.id = ms.member_id
         WHERE m.id = ? AND m.chama_group_id = ?",
        [$memberId, $chamaGroupId]
    );
    
    if (!$member) {
        redirect('index.php', 'Member not found', 'error');
    }
    
    $pageTitle = $member['full_name'] . ' - Member Profile';
    
    // Get member statistics
    $memberStats = $db->fetchOne(
        "SELECT 
            COUNT(DISTINCT t.id) as total_transactions,
            COALESCE(SUM(CASE WHEN t.transaction_type = 'Deposit' THEN t.amount ELSE 0 END), 0) as total_deposits,
            COALESCE(SUM(CASE WHEN t.transaction_type = 'Withdrawal' THEN t.amount ELSE 0 END), 0) as total_withdrawals,
            COUNT(DISTINCT l.id) as total_loans,
            COALESCE(SUM(CASE WHEN l.status = 'Active' THEN l.balance ELSE 0 END), 0) as active_loan_balance,
            COALESCE(SUM(tc.amount), 0) as target_contributions
         FROM members m
         LEFT JOIN transactions t ON m.id = t.member_id AND t.status = 'Completed'
         LEFT JOIN loans l ON m.id = l.member_id
         LEFT JOIN target_contributions tc ON m.id = tc.member_id
         WHERE m.id = ?
         GROUP BY m.id",
        [$memberId]
    ) ?: [
        'total_transactions' => 0,
        'total_deposits' => 0,
        'total_withdrawals' => 0,
        'total_loans' => 0,
        'active_loan_balance' => 0,
        'target_contributions' => 0
    ];
    
    // Get recent transactions
    $recentTransactions = $db->fetchAll(
        "SELECT t.*, u.full_name as processed_by_name
         FROM transactions t
         LEFT JOIN users u ON t.processed_by = u.id
         WHERE t.member_id = ? AND t.status = 'Completed'
         ORDER BY t.created_at DESC
         LIMIT 10",
        [$memberId]
    );
    
    // Get active loans
    $activeLoans = $db->fetchAll(
        "SELECT l.*, lp.product_name,
                COALESCE(next_payment.due_date, NULL) as next_payment_date,
                COALESCE(next_payment.total_amount, 0) as next_payment_amount
         FROM loans l
         JOIN loan_products lp ON l.loan_product_id = lp.id
         LEFT JOIN (
             SELECT loan_id, MIN(due_date) as due_date, total_amount
             FROM loan_repayment_schedule 
             WHERE status IN ('Pending', 'Overdue')
             GROUP BY loan_id
         ) next_payment ON l.id = next_payment.loan_id
         WHERE l.member_id = ? AND l.status = 'Active'
         ORDER BY l.disbursement_date DESC",
        [$memberId]
    );
    
    // Get target contributions
    $targetContributions = $db->fetchAll(
        "SELECT tc.*, t.target_name, t.target_amount, t.current_amount
         FROM target_contributions tc
         JOIN targets t ON tc.target_id = t.id
         WHERE tc.member_id = ?
         ORDER BY tc.contribution_date DESC
         LIMIT 5",
        [$memberId]
    );
    
} catch (Exception $e) {
    $error = "Failed to load member details: " . $e->getMessage();
    logError($error);
}

include_once '../../includes/header.php';
?>

<style>
    .member-profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0;
    }
    
    .profile-header {
        background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-700) 100%);
        color: white;
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }
    
    .profile-content {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 2rem;
    }
    
    .member-avatar-large {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 700;
        color: white;
        border: 4px solid rgba(255, 255, 255, 0.3);
        flex-shrink: 0;
    }
    
    .member-avatar-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    
    .member-header-info {
        flex: 1;
        min-width: 0;
    }
    
    .member-name {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .member-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 1rem;
        opacity: 0.9;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }
    
    .profile-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }
    
    .profile-actions .btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
    }
    
    .profile-actions .btn:hover {
        background: rgba(255, 255, 255, 0.3);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-1px);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
    
    [data-theme="dark"] .stat-card {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin: 0 auto 1rem;
    }
    
    .stat-icon.primary {
        background: var(--primary-500);
    }
    
    .stat-icon.success {
        background: var(--success-500);
    }
    
    .stat-icon.warning {
        background: var(--warning-500);
    }
    
    .stat-icon.info {
        background: #3b82f6;
    }
    
    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }
    
    [data-theme="dark"] .stat-value {
        color: var(--gray-100);
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 500;
    }
    
    [data-theme="dark"] .stat-label {
        color: var(--gray-400);
    }
    
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .info-card {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
        overflow: hidden;
    }
    
    [data-theme="dark"] .info-card {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    [data-theme="dark"] .card-header {
        border-color: var(--gray-700);
    }
    
    .card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    [data-theme="dark"] .card-title {
        color: var(--gray-100);
    }
    
    .card-content {
        padding: 1.5rem;
    }
    
    .info-grid {
        display: grid;
        gap: 1rem;
    }
    
    .info-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        background: var(--gray-50);
        border-radius: 8px;
        border-left: 4px solid var(--primary-500);
    }
    
    [data-theme="dark"] .info-item {
        background: var(--gray-700);
    }
    
    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: var(--primary-100);
        color: var(--primary-600);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    [data-theme="dark"] .info-icon {
        background: rgba(59, 130, 246, 0.2);
        color: var(--primary-400);
    }
    
    .info-details {
        flex: 1;
        min-width: 0;
    }
    
    .info-label {
        font-size: 0.75rem;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    
    .info-value {
        font-weight: 600;
        color: var(--gray-900);
        word-break: break-word;
    }
    
    [data-theme="dark"] .info-value {
        color: var(--gray-100);
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
    
    .transaction-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .transaction-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid var(--gray-100);
    }
    
    .transaction-item:last-child {
        border-bottom: none;
    }
    
    [data-theme="dark"] .transaction-item {
        border-color: var(--gray-700);
    }
    
    .transaction-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1rem;
        color: white;
    }
    
    .transaction-icon.deposit {
        background: var(--success-500);
    }
    
    .transaction-icon.withdrawal {
        background: var(--error-500);
    }
    
    .transaction-icon.transfer {
        background: var(--primary-500);
    }
    
    .transaction-info {
        flex: 1;
        min-width: 0;
    }
    
    .transaction-title {
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }
    
    [data-theme="dark"] .transaction-title {
        color: var(--gray-100);
    }
    
    .transaction-meta {
        font-size: 0.8rem;
        color: var(--gray-500);
    }
    
    .transaction-amount {
        font-weight: 700;
        text-align: right;
    }
    
    .transaction-amount.positive {
        color: var(--success-600);
    }
    
    .transaction-amount.negative {
        color: var(--error-600);
    }
    
    .loan-card {
        background: var(--gray-50);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        border-left: 4px solid var(--warning-500);
    }
    
    [data-theme="dark"] .loan-card {
        background: var(--gray-700);
    }
    
    .loan-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    
    .loan-title {
        font-weight: 600;
        color: var(--gray-900);
    }
    
    [data-theme="dark"] .loan-title {
        color: var(--gray-100);
    }
    
    .loan-status {
        padding: 0.2rem 0.5rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        background: var(--warning-100);
        color: var(--warning-700);
    }
    
    .loan-details {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
        font-size: 0.8rem;
    }
    
    .loan-detail {
        display: flex;
        justify-content: space-between;
    }
    
    .loan-detail-label {
        color: var(--gray-600);
    }
    
    .loan-detail-value {
        font-weight: 600;
        color: var(--gray-900);
    }
    
    [data-theme="dark"] .loan-detail-value {
        color: var(--gray-100);
    }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--gray-500);
    }
    
    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    @media (max-width: 768px) {
        .profile-content {
            flex-direction: column;
            text-align: center;
        }
        
        .member-meta {
            justify-content: center;
        }
        
        .profile-actions {
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .loan-details {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="member-profile-container">
    <?php if ($error): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php elseif ($member): ?>
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-content">
                <div class="member-avatar-large">
                    <?php if ($member['photo']): ?>
                        <img src="<?php echo getUploadUrl('members') . '/' . $member['photo']; ?>" 
                             alt="<?php echo htmlspecialchars($member['full_name']); ?>">
                    <?php else: ?>
                        <?php echo strtoupper(substr($member['full_name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                
                <div class="member-header-info">
                    <h1 class="member-name"><?php echo htmlspecialchars($member['full_name']); ?></h1>
                    
                    <div class="member-meta">
                        <div class="meta-item">
                            <i class="fas fa-id-badge"></i>
                            <span><?php echo htmlspecialchars($member['member_number']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($member['phone']); ?></span>
                        </div>
                        <?php if ($member['email']): ?>
                        <div class="meta-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($member['email']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>Joined <?php echo formatDate($member['membership_date'], 'M j, Y'); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="status-badge <?php echo strtolower($member['status']); ?>">
                                <?php echo htmlspecialchars($member['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="profile-actions">
                        <a href="edit.php?id=<?php echo $member['id']; ?>" class="btn">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Member
                        </a>
                        <a href="../transactions/deposits.php?member_id=<?php echo $member['id']; ?>" class="btn">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Add Deposit
                        </a>
                        <a href="../loans/applications.php?member_id=<?php echo $member['id']; ?>" class="btn">
                            <i class="fas fa-hand-holding-usd mr-2"></i>
                            New Loan
                        </a>
                        <button class="btn" onclick="printProfile()">
                            <i class="fas fa-print mr-2"></i>
                            Print Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <div class="stat-value"><?php echo formatCurrency($member['savings_balance'] ?? 0); ?></div>
                <div class="stat-label">Current Savings</div>
            </div>
            
            <div class="stat-card primary">
                <div class="stat-icon primary">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="stat-value"><?php echo number_format($memberStats['total_transactions']); ?></div>
                <div class="stat-label">Total Transactions</div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon warning">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value"><?php echo formatCurrency($memberStats['active_loan_balance']); ?></div>
                <div class="stat-label">Active Loans</div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon info">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="stat-value"><?php echo formatCurrency($memberStats['target_contributions']); ?></div>
                <div class="stat-label">Target Contributions</div>
            </div>
        </div>
        
        <!-- Main Content Grid -->
<div class="content-grid">
    <!-- Left Column: Transactions & Loans -->
    <div>
        <!-- Recent Transactions -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i>
                    Recent Transactions
                </h3>
                <a href="../transactions/index.php?member_id=<?php echo $member['id']; ?>" class="btn btn-sm btn-secondary">
                    View All
                </a>
            </div>
            <div class="card-content">
                <?php if (empty($recentTransactions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-exchange-alt"></i>
                        <p>No transactions found</p>
                    </div>
                <?php else: ?>
                    <div class="transaction-list">
                        <?php foreach ($recentTransactions as $transaction): ?>
                            <div class="transaction-item">
                                <div class="transaction-icon <?php echo strtolower($transaction['transaction_type']); ?>">
                                    <?php
                                    $iconMap = [
                                        'Deposit' => 'fas fa-plus',
                                        'Withdrawal' => 'fas fa-minus',
                                        'Transfer' => 'fas fa-exchange-alt'
                                    ];
                                    $icon = $iconMap[$transaction['transaction_type']] ?? 'fas fa-circle';
                                    ?>
                                    <i class="<?php echo $icon; ?>"></i>
                                </div>
                                <div class="transaction-info">
                                    <div class="transaction-title">
                                        <?php echo htmlspecialchars($transaction['transaction_type']); ?>
                                        <?php if ($transaction['description']): ?>
                                            - <?php echo htmlspecialchars($transaction['description']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="transaction-meta">
                                        <?php echo formatDate($transaction['created_at'], 'M j, Y g:i A'); ?>
                                        <?php if ($transaction['processed_by_name']): ?>
                                            • By <?php echo htmlspecialchars($transaction['processed_by_name']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="transaction-amount <?php echo $transaction['transaction_type'] === 'Deposit' ? 'positive' : 'negative'; ?>">
                                    <?php echo ($transaction['transaction_type'] === 'Deposit' ? '+' : '-') . formatCurrency($transaction['amount']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Active Loans -->
        <?php if (!empty($activeLoans)): ?>
        <div class="info-card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-hand-holding-usd"></i>
                    Active Loans
                </h3>
                <a href="../loans/index.php?member_id=<?php echo $member['id']; ?>" class="btn btn-sm btn-secondary">
                    View All
                </a>
            </div>
            <div class="card-content">
                <?php foreach ($activeLoans as $loan): ?>
                    <div class="loan-card">
                        <div class="loan-header">
                            <div class="loan-title"><?php echo htmlspecialchars($loan['product_name']); ?></div>
                            <div class="loan-status">Active</div>
                        </div>
                        <div class="loan-details">
                            <div class="loan-detail">
                                <span class="loan-detail-label">Loan Number:</span>
                                <span class="loan-detail-value"><?php echo htmlspecialchars($loan['loan_number']); ?></span>
                            </div>
                            <div class="loan-detail">
                                <span class="loan-detail-label">Balance:</span>
                                <span class="loan-detail-value"><?php echo formatCurrency($loan['balance']); ?></span>
                            </div>
                            <div class="loan-detail">
                                <span class="loan-detail-label">Monthly Payment:</span>
                                <span class="loan-detail-value"><?php echo formatCurrency($loan['monthly_payment']); ?></span>
                            </div>
                            <div class="loan-detail">
                                <span class="loan-detail-label">Next Payment:</span>
                                <span class="loan-detail-value">
                                    <?php if ($loan['next_payment_date']): ?>
                                        <?php echo formatDate($loan['next_payment_date'], 'M j, Y'); ?>
                                    <?php else: ?>
                                        <span style="color: var(--success-600);">Completed</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Target Contributions -->
        <?php if (!empty($targetContributions)): ?>
        <div class="info-card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bullseye"></i>
                    Target Contributions
                </h3>
                <a href="../targets/index.php?member_id=<?php echo $member['id']; ?>" class="btn btn-sm btn-secondary">
                    View All
                </a>
            </div>
            <div class="card-content">
                <?php foreach ($targetContributions as $contribution): ?>
                    <div class="transaction-item">
                        <div class="transaction-icon info">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="transaction-info">
                            <div class="transaction-title">
                                <?php echo htmlspecialchars($contribution['target_name']); ?>
                            </div>
                            <div class="transaction-meta">
                                <?php echo formatDate($contribution['contribution_date'], 'M j, Y'); ?>
                                • Target: <?php echo formatCurrency($contribution['target_amount']); ?>
                            </div>
                        </div>
                        <div class="transaction-amount positive">
                            <?php echo formatCurrency($contribution['amount']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Right Column: Member Information -->
    <div>
        <!-- Personal Information -->
        <div class="info-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i>
                    Personal Information
                </h3>
                <a href="edit.php?id=<?php echo $member['id']; ?>" class="btn btn-sm btn-secondary">
                    <i class="fas fa-edit"></i>
                </a>
            </div>
            <div class="card-content">
                <div class="info-grid">
                    <?php if ($member['email']): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($member['email']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($member['id_number']): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">ID Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($member['id_number']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($member['date_of_birth']): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-birthday-cake"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value">
                                <?php 
                                echo formatDate($member['date_of_birth'], 'M j, Y');
                                $age = floor((time() - strtotime($member['date_of_birth'])) / 31536000);
                                echo " ($age years old)";
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($member['gender']): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-venus-mars"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">Gender</div>
                            <div class="info-value"><?php echo htmlspecialchars($member['gender']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($member['occupation']): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">Occupation</div>
                            <div class="info-value"><?php echo htmlspecialchars($member['occupation']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($member['address']): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">Address</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($member['address'])); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Emergency Contacts -->
        <?php if ($member['emergency_contact_name'] || $member['next_of_kin']): ?>
        <div class="info-card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-phone"></i>
                    Emergency Contacts
                </h3>
            </div>
            <div class="card-content">
                <div class="info-grid">
                    <?php if ($member['emergency_contact_name']): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">Emergency Contact</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($member['emergency_contact_name']); ?>
                                <?php if ($member['emergency_contact_phone']): ?>
                                    <br><small style="color: var(--gray-500);"><?php echo htmlspecialchars($member['emergency_contact_phone']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($member['next_of_kin']): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">Next of Kin</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($member['next_of_kin']); ?>
                                <?php if ($member['relationship_to_kin']): ?>
                                    <br><small style="color: var(--gray-500);"><?php echo htmlspecialchars($member['relationship_to_kin']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Financial Summary -->
        <div class="info-card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Financial Summary
                </h3>
            </div>
            <div class="card-content">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">Total Deposits</div>
                            <div class="info-value"><?php echo formatCurrency($member['total_deposits'] ?? 0); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-minus-circle"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">Total Withdrawals</div>
                            <div class="info-value"><?php echo formatCurrency($member['total_withdrawals'] ?? 0); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">Interest Earned</div>
                            <div class="info-value"><?php echo formatCurrency($member['interest_earned'] ?? 0); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-money-check"></i>
                        </div>
                        <div class="info-details">
                            <div class="info-label">Membership Fee</div>
                            <div class="info-value"><?php echo formatCurrency($member['membership_fee_paid'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notes -->
        <?php if ($member['notes']): ?>
        <div class="info-card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sticky-note"></i>
                    Notes
                </h3>
            </div>
            <div class="card-content">
                <div style="color: var(--gray-700); line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($member['notes'])); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
        
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize profile page
    initializeProfileActions();
    setupAutoRefresh();
    
    // Add back button functionality
    const backBtn = document.querySelector('.btn[href*="index.php"]');
    if (backBtn) {
        backBtn.addEventListener('click', function(e) {
            if (document.referrer.includes('members')) {
                e.preventDefault();
                history.back();
            }
        });
    }
});

function initializeProfileActions() {
    // Add click handlers for quick actions
    const quickActionBtns = document.querySelectorAll('.profile-actions .btn');
    quickActionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.onclick) return; // Skip if has onclick handler
            
            // Add loading animation for navigation
            if (this.href) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>' + 
                               this.textContent.trim();
            }
        });
    });
}

function printProfile() {
    // Create a print-friendly version
    const printWindow = window.open('', '_blank');
    const memberName = '<?php echo addslashes($member['full_name'] ?? ''); ?>';
    const memberNumber = '<?php echo addslashes($member['member_number'] ?? ''); ?>';
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Member Profile - ${memberName}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .info-section { margin-bottom: 25px; }
                .info-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #333; }
                .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
                .info-item { display: flex; justify-content: space-between; padding: 8px; border-bottom: 1px solid #eee; }
                .info-label { font-weight: bold; }
                .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0; }
                .stat-box { text-align: center; padding: 15px; border: 1px solid #ddd; }
                .stat-value { font-size: 24px; font-weight: bold; color: #333; }
                .stat-label { font-size: 12px; color: #666; margin-top: 5px; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Member Profile</h1>
                <h2>${memberName}</h2>
                <p>Member Number: ${memberNumber}</p>
                <p>Generated on: ${new Date().toLocaleDateString()}</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value"><?php echo formatCurrency($member['savings_balance'] ?? 0); ?></div>
                    <div class="stat-label">Current Savings</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo number_format($memberStats['total_transactions']); ?></div>
                    <div class="stat-label">Total Transactions</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo formatCurrency($memberStats['active_loan_balance']); ?></div>
                    <div class="stat-label">Active Loans</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo formatCurrency($memberStats['target_contributions']); ?></div>
                    <div class="stat-label">Target Contributions</div>
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-title">Personal Information</div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Full Name:</span>
                        <span><?php echo htmlspecialchars($member['full_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone:</span>
                        <span><?php echo htmlspecialchars($member['phone']); ?></span>
                    </div>
                    <?php if ($member['email']): ?>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span><?php echo htmlspecialchars($member['email']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="info-label">Membership Date:</span>
                        <span><?php echo formatDate($member['membership_date'], 'M j, Y'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span><?php echo htmlspecialchars($member['status']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-title">Financial Summary</div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Total Deposits:</span>
                        <span><?php echo formatCurrency($member['total_deposits'] ?? 0); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Withdrawals:</span>
                        <span><?php echo formatCurrency($member['total_withdrawals'] ?? 0); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Interest Earned:</span>
                        <span><?php echo formatCurrency($member['interest_earned'] ?? 0); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Membership Fee Paid:</span>
                        <span><?php echo formatCurrency($member['membership_fee_paid'] ?? 0); ?></span>
                    </div>
                </div>
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

function setupAutoRefresh() {
    // Refresh member data every 2 minutes
    setInterval(function() {
        refreshMemberData();
    }, 120000);
}

function refreshMemberData() {
    const memberId = <?php echo $memberId; ?>;
    
    fetch(`ajax/member_actions.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            action: 'get_member_stats',
            member_id: memberId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateMemberStats(data.stats);
        }
    })
    .catch(error => {
        console.log('Auto-refresh failed:', error);
    });
}

function updateMemberStats(stats) {
    // Update stat values
    const statValues = document.querySelectorAll('.stat-value');
    if (statValues[0]) statValues[0].textContent = formatCurrency(stats.savings_balance || 0);
    if (statValues[1]) statValues[1].textContent = formatNumber(stats.total_transactions || 0);
    if (statValues[2]) statValues[2].textContent = formatCurrency(stats.active_loan_balance || 0);
    if (statValues[3]) statValues[3].textContent = formatCurrency(stats.target_contributions || 0);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
    }).format(amount);
}

function formatNumber(value) {
    return new Intl.NumberFormat('en-KE').format(value);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // E to edit member
    if (e.key === 'e' && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
        e.preventDefault();
        window.location.href = 'edit.php?id=<?php echo $memberId; ?>';
    }
    
    // P to print
    if (e.key === 'p' && e.ctrlKey) {
        e.preventDefault();
        printProfile();
    }
    
    // Escape or B to go back
    if (e.key === 'Escape' || e.key === 'b') {
        history.back();
    }
});

// Page-specific initialization
window.initializePage = function() {
    console.log('Member profile page initialized');
    
    // Load additional data if needed
    loadRecentActivity();
};

function loadRecentActivity() {
    // This could load more detailed recent activity
    // For now, we'll just log that it's available
    console.log('Recent activity data available');
}
</script>

<?php include_once '../../includes/footer.php'; ?> 