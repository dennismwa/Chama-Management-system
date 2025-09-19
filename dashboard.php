<?php
/**
 * Chama Management Platform - Main Dashboard
 * 
 * Overview dashboard with key metrics and quick actions
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

define('CHAMA_ACCESS', true);
require_once 'config/config.php';

// Ensure user is logged in
requireLogin();

$pageTitle = 'Dashboard';
$currentUser = currentUser();
$chamaGroupId = currentChamaGroup();

// Initialize dashboard data
$dashboardData = [];
$error = '';

try {
    $db = Database::getInstance();
    
    // Get key metrics
    $dashboardData = [
        'total_members' => $db->fetchValue(
            "SELECT COUNT(*) FROM members WHERE chama_group_id = ? AND status = 'Active'",
            [$chamaGroupId]
        ) ?: 0,
        
        'total_savings' => $db->fetchValue(
            "SELECT COALESCE(SUM(balance), 0) FROM member_savings ms 
             JOIN members m ON ms.member_id = m.id 
             WHERE m.chama_group_id = ?",
            [$chamaGroupId]
        ) ?: 0,
        
        'active_loans' => $db->fetchValue(
            "SELECT COUNT(*) FROM loans l 
             JOIN members m ON l.member_id = m.id 
             WHERE m.chama_group_id = ? AND l.status = 'Active'",
            [$chamaGroupId]
        ) ?: 0,
        
        'total_loan_amount' => $db->fetchValue(
            "SELECT COALESCE(SUM(balance), 0) FROM loans l 
             JOIN members m ON l.member_id = m.id 
             WHERE m.chama_group_id = ? AND l.status = 'Active'",
            [$chamaGroupId]
        ) ?: 0,
        
        'pending_applications' => $db->fetchValue(
            "SELECT COUNT(*) FROM loan_applications la 
             JOIN members m ON la.member_id = m.id 
             WHERE m.chama_group_id = ? AND la.status = 'Pending'",
            [$chamaGroupId]
        ) ?: 0,
        
        'this_month_deposits' => $db->fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM transactions t 
             JOIN members m ON t.member_id = m.id 
             WHERE m.chama_group_id = ? 
             AND t.transaction_type = 'Deposit' 
             AND t.status = 'Completed'
             AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE()) 
             AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE())",
            [$chamaGroupId]
        ) ?: 0,
        
        'active_targets' => $db->fetchValue(
            "SELECT COUNT(*) FROM targets WHERE chama_group_id = ? AND status = 'Active'",
            [$chamaGroupId]
        ) ?: 0,
        
        'target_progress' => $db->fetchValue(
            "SELECT COALESCE(SUM(current_amount), 0) FROM targets 
             WHERE chama_group_id = ? AND status = 'Active'",
            [$chamaGroupId]
        ) ?: 0
    ];
    
    // Get recent transactions
    $recentTransactions = $db->fetchAll(
        "SELECT t.*, m.full_name as member_name, m.member_number 
         FROM transactions t 
         LEFT JOIN members m ON t.member_id = m.id 
         WHERE t.chama_group_id = ? 
         ORDER BY t.created_at DESC 
         LIMIT 10",
        [$chamaGroupId]
    );
    
    // Get upcoming loan payments
    $upcomingPayments = $db->fetchAll(
        "SELECT lrs.*, l.loan_number, m.full_name as member_name, m.phone 
         FROM loan_repayment_schedule lrs
         JOIN loans l ON lrs.loan_id = l.id
         JOIN members m ON l.member_id = m.id
         WHERE m.chama_group_id = ? 
         AND lrs.status IN ('Pending', 'Overdue')
         AND lrs.due_date <= DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)
         ORDER BY lrs.due_date ASC
         LIMIT 10",
        [$chamaGroupId]
    );
    
    // Get recent members
    $recentMembers = $db->fetchAll(
        "SELECT * FROM members 
         WHERE chama_group_id = ? 
         ORDER BY created_at DESC 
         LIMIT 5",
        [$chamaGroupId]
    );
    
    // Get monthly savings trend (last 6 months)
    $savingstrend = $db->fetchAll(
        "SELECT 
            DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
            SUM(CASE WHEN t.transaction_type = 'Deposit' THEN t.amount ELSE 0 END) as deposits,
            SUM(CASE WHEN t.transaction_type = 'Withdrawal' THEN t.amount ELSE 0 END) as withdrawals
         FROM transactions t
         JOIN members m ON t.member_id = m.id
         WHERE m.chama_group_id = ?
         AND t.transaction_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
         AND t.status = 'Completed'
         GROUP BY DATE_FORMAT(t.transaction_date, '%Y-%m')
         ORDER BY month ASC",
        [$chamaGroupId]
    );
    
} catch (Exception $e) {
    $error = "Failed to load dashboard data: " . $e->getMessage();
    logError($error);
}

include_once INCLUDES_PATH . '/header.php';
?>

<style>
    .dashboard-container {
        padding: 0;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-700) 100%);
        color: white;
        padding: 2rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(50%, -50%);
    }
    
    .welcome-content {
        position: relative;
        z-index: 2;
    }
    
    .welcome-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .welcome-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 1.5rem;
    }
    
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }
    
    .quick-stat {
        background: rgba(255, 255, 255, 0.15);
        padding: 1rem;
        border-radius: 10px;
        backdrop-filter: blur(10px);
    }
    
    .quick-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .quick-stat-label {
        font-size: 0.9rem;
        opacity: 0.8;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }
    
    [data-theme="dark"] .stat-card {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--primary-500);
    }
    
    .stat-card.success::before {
        background: var(--success-500);
    }
    
    .stat-card.warning::before {
        background: var(--warning-500);
    }
    
    .stat-card.danger::before {
        background: var(--error-500);
    }
    
    .stat-header {
        display: flex;
        align-items: center;
        justify-content: between;
        margin-bottom: 1rem;
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        background: var(--primary-500);
        margin-right: 1rem;
    }
    
    .stat-icon.success {
        background: var(--success-500);
    }
    
    .stat-icon.warning {
        background: var(--warning-500);
    }
    
    .stat-icon.danger {
        background: var(--error-500);
    }
    
    .stat-info {
        flex: 1;
    }
    
    .stat-title {
        font-size: 0.9rem;
        color: var(--gray-600);
        margin-bottom: 0.25rem;
        font-weight: 500;
    }
    
    [data-theme="dark"] .stat-title {
        color: var(--gray-400);
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1.2;
    }
    
    [data-theme="dark"] .stat-value {
        color: var(--gray-100);
    }
    
    .stat-change {
        font-size: 0.8rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .stat-change.positive {
        color: var(--success-600);
    }
    
    .stat-change.negative {
        color: var(--error-600);
    }
    
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .chart-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        border: 1px solid var(--gray-200);
    }
    
    [data-theme="dark"] .chart-card {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .card-header {
        display: flex;
        align-items: center;
        justify-content: between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-200);
    }
    
    [data-theme="dark"] .card-header {
        border-color: var(--gray-700);
    }
    
    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
    }
    
    [data-theme="dark"] .card-title {
        color: var(--gray-100);
    }
    
    .card-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .action-btn {
        background: var(--gray-100);
        border: none;
        border-radius: 6px;
        padding: 0.5rem;
        color: var(--gray-600);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .action-btn:hover {
        background: var(--gray-200);
        color: var(--gray-900);
    }
    
    [data-theme="dark"] .action-btn {
        background: var(--gray-700);
        color: var(--gray-400);
    }
    
    [data-theme="dark"] .action-btn:hover {
        background: var(--gray-600);
        color: var(--gray-200);
    }
    
    .transactions-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .transaction-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid var(--gray-100);
        transition: all 0.3s ease;
    }
    
    .transaction-item:hover {
        background: var(--gray-50);
        margin: 0 -1rem;
        padding-left: 1rem;
        padding-right: 1rem;
        border-radius: 8px;
    }
    
    [data-theme="dark"] .transaction-item {
        border-color: var(--gray-700);
    }
    
    [data-theme="dark"] .transaction-item:hover {
        background: var(--gray-700);
    }
    
    .transaction-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.1rem;
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
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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
        font-size: 1rem;
    }
    
    .transaction-amount.positive {
        color: var(--success-600);
    }
    
    .transaction-amount.negative {
        color: var(--error-600);
    }
    
    .payments-list {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .payment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem;
        background: var(--gray-50);
        border-radius: 8px;
        margin-bottom: 0.5rem;
        border-left: 4px solid var(--warning-500);
    }
    
    [data-theme="dark"] .payment-item {
        background: var(--gray-700);
    }
    
    .payment-item.overdue {
        border-left-color: var(--error-500);
        background: var(--error-50);
    }
    
    [data-theme="dark"] .payment-item.overdue {
        background: rgba(239, 68, 68, 0.1);
    }
    
    .payment-info {
        flex: 1;
    }
    
    .payment-member {
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }
    
    [data-theme="dark"] .payment-member {
        color: var(--gray-100);
    }
    
    .payment-details {
        font-size: 0.8rem;
        color: var(--gray-600);
    }
    
    [data-theme="dark"] .payment-details {
        color: var(--gray-400);
    }
    
    .payment-amount {
        font-weight: 700;
        color: var(--gray-900);
    }
    
    [data-theme="dark"] .payment-amount {
        color: var(--gray-100);
    }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: var(--gray-500);
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .quick-action {
        background: white;
        border: 2px dashed var(--gray-300);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        text-decoration: none;
        color: var(--gray-700);
    }
    
    .quick-action:hover {
        border-color: var(--primary-500);
        background: var(--primary-50);
        color: var(--primary-700);
        transform: translateY(-2px);
    }
    
    [data-theme="dark"] .quick-action {
        background: var(--gray-800);
        border-color: var(--gray-600);
        color: var(--gray-300);
    }
    
    [data-theme="dark"] .quick-action:hover {
        border-color: var(--primary-400);
        background: rgba(59, 130, 246, 0.1);
        color: var(--primary-400);
    }
    
    .quick-action-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
        display: block;
    }
    
    .quick-action-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .quick-action-desc {
        font-size: 0.9rem;
        opacity: 0.8;
    }
    
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.5rem;
        }
        
        .welcome-title {
            font-size: 1.5rem;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .stat-value {
            font-size: 1.5rem;
        }
    }
</style>

<div class="dashboard-container">
    <?php if ($error): ?>
        <div class="alert alert-error mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="welcome-content">
            <h1 class="welcome-title">
                Welcome back, <?php echo htmlspecialchars($currentUser['full_name']); ?>!
            </h1>
            <p class="welcome-subtitle">
                Here's what's happening with your chama today.
            </p>
            
            <div class="quick-stats">
                <div class="quick-stat">
                    <div class="quick-stat-value"><?php echo number_format($dashboardData['total_members']); ?></div>
                    <div class="quick-stat-label">Active Members</div>
                </div>
                <div class="quick-stat">
                    <div class="quick-stat-value"><?php echo formatCurrency($dashboardData['total_savings']); ?></div>
                    <div class="quick-stat-label">Total Savings</div>
                </div>
                <div class="quick-stat">
                    <div class="quick-stat-value"><?php echo number_format($dashboardData['active_loans']); ?></div>
                    <div class="quick-stat-label">Active Loans</div>
                </div>
                <div class="quick-stat">
                    <div class="quick-stat-value"><?php echo formatCurrency($dashboardData['this_month_deposits']); ?></div>
                    <div class="quick-stat-label">This Month Deposits</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Key Metrics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Total Members</div>
                    <div class="stat-value"><?php echo number_format($dashboardData['total_members']); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+2 this week</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-header">
                <div class="stat-icon success">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Total Savings</div>
                    <div class="stat-value"><?php echo formatCurrency($dashboardData['total_savings']); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+15% this month</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-header">
                <div class="stat-icon warning">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Active Loans</div>
                    <div class="stat-value"><?php echo formatCurrency($dashboardData['total_loan_amount']); ?></div>
                    <div class="stat-change">
                        <span><?php echo number_format($dashboardData['active_loans']); ?> loans</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="stat-card <?php echo $dashboardData['pending_applications'] > 0 ? 'danger' : ''; ?>">
            <div class="stat-header">
                <div class="stat-icon <?php echo $dashboardData['pending_applications'] > 0 ? 'danger' : ''; ?>">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-title">Pending Applications</div>
                    <div class="stat-value"><?php echo number_format($dashboardData['pending_applications']); ?></div>
                    <div class="stat-change">
                        <span>Requires attention</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="modules/members/add.php" class="quick-action">
            <i class="fas fa-user-plus quick-action-icon"></i>
            <div class="quick-action-title">Add Member</div>
            <div class="quick-action-desc">Register a new chama member</div>
        </a>
        
        <a href="modules/transactions/deposits.php" class="quick-action">
            <i class="fas fa-plus-circle quick-action-icon"></i>
            <div class="quick-action-title">Record Deposit</div>
            <div class="quick-action-desc">Add member savings deposit</div>
        </a>
        
        <a href="modules/loans/applications.php" class="quick-action">
            <i class="fas fa-file-alt quick-action-icon"></i>
            <div class="quick-action-title">Process Loan</div>
            <div class="quick-action-desc">Review loan applications</div>
        </a>
        
        <a href="modules/reports/financial.php" class="quick-action">
            <i class="fas fa-chart-line quick-action-icon"></i>
            <div class="quick-action-title">View Reports</div>
            <div class="quick-action-desc">Generate financial reports</div>
        </a>
    </div>
    
    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Recent Transactions -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">Recent Transactions</h3>
                <div class="card-actions">
                    <button class="action-btn" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <a href="modules/transactions/index.php" class="action-btn" title="View All">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
            
            <div class="transactions-list">
                <?php if (empty($recentTransactions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-exchange-alt"></i>
                        <p>No recent transactions</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <div class="transaction-item">
                            <div class="transaction-icon <?php echo strtolower($transaction['transaction_type']); ?>">
                                <?php
                                $icon = 'fas fa-exchange-alt';
                                switch ($transaction['transaction_type']) {
                                    case 'Deposit':
                                        $icon = 'fas fa-arrow-down';
                                        break;
                                    case 'Withdrawal':
                                        $icon = 'fas fa-arrow-up';
                                        break;
                                    case 'Transfer':
                                        $icon = 'fas fa-exchange-alt';
                                        break;
                                }
                                ?>
                                <i class="<?php echo $icon; ?>"></i>
                            </div>
                            <div class="transaction-info">
                                <div class="transaction-title">
                                    <?php echo htmlspecialchars($transaction['description'] ?: $transaction['transaction_type']); ?>
                                </div>
                                <div class="transaction-meta">
                                    <?php if ($transaction['member_name']): ?>
                                        <?php echo htmlspecialchars($transaction['member_name']); ?> •
                                    <?php endif; ?>
                                    <?php echo formatDateTime($transaction['transaction_date'], 'M j, Y g:i A'); ?>
                                </div>
                            </div>
                            <div class="transaction-amount <?php echo in_array($transaction['transaction_type'], ['Deposit']) ? 'positive' : 'negative'; ?>">
                                <?php echo formatCurrency($transaction['amount']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Upcoming Payments -->
        <div class="chart-card">
            <div class="card-header">
                <h3 class="card-title">Upcoming Payments</h3>
                <div class="card-actions">
                    <button class="action-btn" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <a href="modules/loans/active.php" class="action-btn" title="View All">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
            
            <div class="payments-list">
                <?php if (empty($upcomingPayments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check"></i>
                        <p>No upcoming payments</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingPayments as $payment): ?>
                        <?php 
                        $isOverdue = strtotime($payment['due_date']) < time();
                        $daysUntil = ceil((strtotime($payment['due_date']) - time()) / (24 * 3600));
                        ?>
                        <div class="payment-item <?php echo $isOverdue ? 'overdue' : ''; ?>">
                            <div class="payment-info">
                                <div class="payment-member"><?php echo htmlspecialchars($payment['member_name']); ?></div>
                                <div class="payment-details">
                                    Loan: <?php echo htmlspecialchars($payment['loan_number']); ?> •
                                    Due: <?php echo formatDate($payment['due_date'], 'M j'); ?>
                                    <?php if ($isOverdue): ?>
                                        <span style="color: var(--error-600); font-weight: 600;">• OVERDUE</span>
                                    <?php elseif ($daysUntil <= 3): ?>
                                        <span style="color: var(--warning-600); font-weight: 600;">• Due in <?php echo $daysUntil; ?> day<?php echo $daysUntil != 1 ? 's' : ''; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="payment-amount"><?php echo formatCurrency($payment['total_amount']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Savings Trend Chart -->
    <div class="chart-card">
        <div class="card-header">
            <h3 class="card-title">Savings Trend (Last 6 Months)</h3>
            <div class="card-actions">
                <button class="action-btn" title="Download Chart">
                    <i class="fas fa-download"></i>
                </button>
                <button class="action-btn" title="Settings">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
        </div>
        
        <div style="position: relative; height: 300px;">
            <canvas id="savingsChart"></canvas>
        </div>
    </div>
    
    <!-- Recent Members -->
    <?php if (!empty($recentMembers)): ?>
    <div class="chart-card">
        <div class="card-header">
            <h3 class="card-title">Recent Members</h3>
            <div class="card-actions">
                <a href="modules/members/index.php" class="action-btn" title="View All">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
        </div>
        
        <div class="members-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
            <?php foreach ($recentMembers as $member): ?>
                <div class="member-card" style="background: var(--gray-50); border-radius: 10px; padding: 1rem; text-align: center; border: 1px solid var(--gray-200);">
                    <div class="member-avatar" style="width: 60px; height: 60px; border-radius: 50%; background: var(--primary-500); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; font-size: 1.5rem; font-weight: 600;">
                        <?php echo strtoupper(substr($member['full_name'], 0, 1)); ?>
                    </div>
                    <div class="member-name" style="font-weight: 600; color: var(--gray-900); margin-bottom: 0.25rem;">
                        <?php echo htmlspecialchars($member['full_name']); ?>
                    </div>
                    <div class="member-number" style="font-size: 0.8rem; color: var(--gray-500); margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($member['member_number']); ?>
                    </div>
                    <div class="member-date" style="font-size: 0.75rem; color: var(--gray-400);">
                        Joined <?php echo formatDate($member['membership_date'], 'M j'); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize savings trend chart
    const ctx = document.getElementById('savingsChart');
    if (ctx) {
        const chartData = <?php echo json_encode($savingstrend); ?>;
        
        const labels = chartData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });
        
        const depositsData = chartData.map(item => parseFloat(item.deposits) || 0);
        const withdrawalsData = chartData.map(item => parseFloat(item.withdrawals) || 0);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Deposits',
                        data: depositsData,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Withdrawals',
                        data: withdrawalsData,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            font: {
                                family: 'Inter',
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                const value = new Intl.NumberFormat('en-KE', {
                                    style: 'currency',
                                    currency: 'KES'
                                }).format(context.raw);
                                return context.dataset.label + ': ' + value;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Inter',
                                size: 11
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: 'Inter',
                                size: 11
                            },
                            callback: function(value) {
                                return new Intl.NumberFormat('en-KE', {
                                    style: 'currency',
                                    currency: 'KES',
                                    notation: 'compact'
                                }).format(value);
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                elements: {
                    point: {
                        radius: 4,
                        hoverRadius: 6
                    }
                }
            }
        });
    }
    
    // Auto-refresh dashboard data every 5 minutes
    setInterval(function() {
        refreshDashboardData();
    }, 300000);
    
    function refreshDashboardData() {
        fetch('api/dashboard-refresh.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardMetrics(data.metrics);
                loadRecentTransactions();
                loadUpcomingPayments();
            }
        })
        .catch(error => {
            console.error('Failed to refresh dashboard:', error);
        });
    }
    
    function updateDashboardMetrics(metrics) {
        // Update stat values
        const statValues = document.querySelectorAll('.stat-value');
        statValues.forEach((element, index) => {
            const key = element.closest('.stat-card').dataset.metric;
            if (metrics[key] !== undefined) {
                element.textContent = formatNumber(metrics[key]);
            }
        });
        
        // Update quick stats
        const quickStats = document.querySelectorAll('.quick-stat-value');
        quickStats.forEach((element, index) => {
            const key = element.closest('.quick-stat').dataset.metric;
            if (metrics[key] !== undefined) {
                element.textContent = formatNumber(metrics[key]);
            }
        });
    }
    
    function formatNumber(value) {
        if (typeof value === 'number') {
            return new Intl.NumberFormat('en-KE').format(value);
        }
        return value;
    }
    
    function loadRecentTransactions() {
        fetch('api/recent-transactions.php')
        .then(response => response.json())
        .then(data => {
            const container = document.querySelector('.transactions-list');
            if (data.transactions && data.transactions.length > 0) {
                container.innerHTML = data.transactions.map(transaction => `
                    <div class="transaction-item">
                        <div class="transaction-icon ${transaction.type.toLowerCase()}">
                            <i class="${getTransactionIcon(transaction.type)}"></i>
                        </div>
                        <div class="transaction-info">
                            <div class="transaction-title">${transaction.description}</div>
                            <div class="transaction-meta">
                                ${transaction.member_name ? transaction.member_name + ' • ' : ''}
                                ${formatDateTime(transaction.date)}
                            </div>
                        </div>
                        <div class="transaction-amount ${transaction.type === 'Deposit' ? 'positive' : 'negative'}">
                            ${formatCurrency(transaction.amount)}
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Failed to load transactions:', error);
        });
    }
    
    function loadUpcomingPayments() {
        fetch('api/upcoming-payments.php')
        .then(response => response.json())
        .then(data => {
            const container = document.querySelector('.payments-list');
            if (data.payments && data.payments.length > 0) {
                container.innerHTML = data.payments.map(payment => `
                    <div class="payment-item ${payment.is_overdue ? 'overdue' : ''}">
                        <div class="payment-info">
                            <div class="payment-member">${payment.member_name}</div>
                            <div class="payment-details">
                                Loan: ${payment.loan_number} • Due: ${formatDate(payment.due_date)}
                                ${payment.is_overdue ? '<span style="color: var(--error-600); font-weight: 600;">• OVERDUE</span>' : ''}
                            </div>
                        </div>
                        <div class="payment-amount">${formatCurrency(payment.amount)}</div>
                    </div>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Failed to load payments:', error);
        });
    }
    
    function getTransactionIcon(type) {
        switch (type) {
            case 'Deposit':
                return 'fas fa-arrow-down';
            case 'Withdrawal':
                return 'fas fa-arrow-up';
            case 'Transfer':
                return 'fas fa-exchange-alt';
            default:
                return 'fas fa-exchange-alt';
        }
    }
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-KE', {
            style: 'currency',
            currency: 'KES'
        }).format(amount);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
    
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }
    
    // Add click handlers for stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('click', function() {
            const metric = this.dataset.metric;
            if (metric) {
                navigateToDetails(metric);
            }
        });
    });
    
    function navigateToDetails(metric) {
        switch (metric) {
            case 'members':
                window.location.href = 'modules/members/index.php';
                break;
            case 'savings':
                window.location.href = 'modules/savings/index.php';
                break;
            case 'loans':
                window.location.href = 'modules/loans/index.php';
                break;
            case 'applications':
                window.location.href = 'modules/loans/applications.php';
                break;
            default:
                break;
        }
    }
    
    // Add refresh handlers
    document.querySelectorAll('.action-btn[title="Refresh"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.chart-card');
            if (card.querySelector('.transactions-list')) {
                loadRecentTransactions();
            } else if (card.querySelector('.payments-list')) {
                loadUpcomingPayments();
            }
            
            // Add loading animation
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-sync-alt"></i>';
            }, 1000);
        });
    });
    
    // Initialize tooltips for chart actions
    document.querySelectorAll('[title]').forEach(element => {
        element.addEventListener('mouseenter', function() {
            const title = this.getAttribute('title');
            if (title) {
                createTooltip(this, title);
            }
        });
        
        element.addEventListener('mouseleave', function() {
            removeTooltip();
        });
    });
    
    // Page-specific initialization
    window.initializePage = function() {
        console.log('Dashboard initialized');
        
        // Load initial data
        loadRecentTransactions();
        loadUpcomingPayments();
        
        // Set up periodic updates
        setInterval(loadRecentTransactions, 60000); // Every minute
        setInterval(loadUpcomingPayments, 120000); // Every 2 minutes
    };
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Alt + M for members
        if (e.altKey && e.key === 'm') {
            e.preventDefault();
            window.location.href = 'modules/members/index.php';
        }
        
        // Alt + T for transactions
        if (e.altKey && e.key === 't') {
            e.preventDefault();
            window.location.href = 'modules/transactions/index.php';
        }
        
        // Alt + L for loans
        if (e.altKey && e.key === 'l') {
            e.preventDefault();
            window.location.href = 'modules/loans/index.php';
        }
        
        // Alt + R for reports
        if (e.altKey && e.key === 'r') {
            e.preventDefault();
            window.location.href = 'modules/reports/index.php';
        }
    });
});
</script>

<?php
$additionalScripts = [
    ASSETS_URL . '/js/dashboard.js'
];

include_once INCLUDES_PATH . '/footer.php';
?>