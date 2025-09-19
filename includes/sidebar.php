<?php
/**
 * Chama Management Platform - Sidebar Navigation
 * 
 * Responsive sidebar with role-based navigation
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('CHAMA_ACCESS')) {
    die('Direct access denied');
}

$currentUser = currentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Define navigation menu with permissions
$navigationMenu = [
    [
        'title' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'url' => 'dashboard.php',
        'permission' => null, // Available to all
        'active' => $currentPage === 'dashboard'
    ],
    [
        'title' => 'Members',
        'icon' => 'fas fa-users',
        'permission' => 'manage_members',
        'submenu' => [
            [
                'title' => 'All Members',
                'url' => 'modules/members/index.php',
                'active' => in_array($currentPage, ['members', 'index']) && strpos($_SERVER['REQUEST_URI'], 'members') !== false
            ],
            [
                'title' => 'Add Member',
                'url' => 'modules/members/add.php',
                'active' => $currentPage === 'add' && strpos($_SERVER['REQUEST_URI'], 'members') !== false
            ],
            [
                'title' => 'Member Reports',
                'url' => 'modules/reports/members.php',
                'active' => $currentPage === 'members' && strpos($_SERVER['REQUEST_URI'], 'reports') !== false
            ]
        ]
    ],
    [
        'title' => 'Savings',
        'icon' => 'fas fa-piggy-bank',
        'permission' => 'manage_savings',
        'submenu' => [
            [
                'title' => 'Individual Savings',
                'url' => 'modules/savings/individual.php',
                'active' => $currentPage === 'individual' && strpos($_SERVER['REQUEST_URI'], 'savings') !== false
            ],
            [
                'title' => 'Group Savings',
                'url' => 'modules/savings/group.php',
                'active' => $currentPage === 'group' && strpos($_SERVER['REQUEST_URI'], 'savings') !== false
            ],
            [
                'title' => 'Savings Summary',
                'url' => 'modules/savings/index.php',
                'active' => $currentPage === 'index' && strpos($_SERVER['REQUEST_URI'], 'savings') !== false
            ]
        ]
    ],
    [
        'title' => 'Loans',
        'icon' => 'fas fa-money-bill-wave',
        'permission' => 'manage_loans',
        'submenu' => [
            [
                'title' => 'Loan Applications',
                'url' => 'modules/loans/applications.php',
                'active' => $currentPage === 'applications'
            ],
            [
                'title' => 'Active Loans',
                'url' => 'modules/loans/active.php',
                'active' => $currentPage === 'active'
            ],
            [
                'title' => 'Loan Overview',
                'url' => 'modules/loans/index.php',
                'active' => $currentPage === 'index' && strpos($_SERVER['REQUEST_URI'], 'loans') !== false
            ]
        ]
    ],
    [
        'title' => 'Targets & Goals',
        'icon' => 'fas fa-bullseye',
        'permission' => 'manage_targets',
        'submenu' => [
            [
                'title' => 'All Targets',
                'url' => 'modules/targets/index.php',
                'active' => $currentPage === 'index' && strpos($_SERVER['REQUEST_URI'], 'targets') !== false
            ],
            [
                'title' => 'Create Target',
                'url' => 'modules/targets/create.php',
                'active' => currentPage === 'create' && strpos(
_SERVER['REQUEST_URI'], 'targets') !== false
            ]
        ]
    ],
    [
        'title' => 'Transactions',
        'icon' => 'fas fa-exchange-alt',
        'permission' => 'manage_transactions',
        'submenu' => [
            [
                'title' => 'All Transactions',
                'url' => 'modules/transactions/index.php',
                'active' => currentPage === 'index' && strpos(
_SERVER['REQUEST_URI'], 'transactions') !== false
            ],
            [
                'title' => 'Deposits',
                'url' => 'modules/transactions/deposits.php',
                'active' => $currentPage === 'deposits'
            ],
            [
                'title' => 'Withdrawals',
                'url' => 'modules/transactions/withdrawals.php',
                'active' => $currentPage === 'withdrawals'
            ],
            [
                'title' => 'Transfers',
                'url' => 'modules/transactions/transfers.php',
                'active' => $currentPage === 'transfers'
            ]
        ]
    ],
    [
        'title' => 'Payments',
        'icon' => 'fas fa-credit-card',
        'permission' => 'manage_payments',
        'submenu' => [
            [
                'title' => 'M-Pesa Payments',
                'url' => 'modules/payments/mpesa.php',
                'active' => $currentPage === 'mpesa'
            ],
            [
                'title' => 'Card Payments',
                'url' => 'modules/payments/cards.php',
                'active' => $currentPage === 'cards'
            ],
            [
                'title' => 'Bank Transfers',
                'url' => 'modules/payments/bank.php',
                'active' => $currentPage === 'bank'
            ]
        ]
    ],
    [
        'title' => 'Reports',
        'icon' => 'fas fa-chart-bar',
        'permission' => 'view_reports',
        'submenu' => [
            [
                'title' => 'Financial Reports',
                'url' => 'modules/reports/financial.php',
                'active' => $currentPage === 'financial'
            ],
            [
                'title' => 'Member Reports',
                'url' => 'modules/reports/members.php',
                'active' => currentPage === 'members' && strpos(
_SERVER['REQUEST_URI'], 'reports') !== false
            ],
            [
                'title' => 'Loan Reports',
                'url' => 'modules/reports/loans.php',
                'active' => currentPage === 'loans' && strpos(
_SERVER['REQUEST_URI'], 'reports') !== false
            ],
            [
                'title' => 'Reports Dashboard',
                'url' => 'modules/reports/index.php',
                'active' => currentPage === 'index' && strpos(
_SERVER['REQUEST_URI'], 'reports') !== false
            ]
        ]
    ],
    [
        'title' => 'Settings',
        'icon' => 'fas fa-cogs',
        'permission' => 'manage_settings',
        'submenu' => [
            [
                'title' => 'System Settings',
                'url' => 'modules/settings/system.php',
                'active' => $currentPage === 'system'
            ],
            [
                'title' => 'Payment Configuration',
                'url' => 'modules/settings/payment_config.php',
                'active' => $currentPage === 'payment_config'
            ],
            [
                'title' => 'User Management',
                'url' => 'modules/settings/users.php',
                'active' => $currentPage === 'users'
            ],
            [
                'title' => 'All Settings',
                'url' => 'modules/settings/index.php',
                'active' => currentPage === 'index' && strpos(
_SERVER['REQUEST_URI'], 'settings') !== false
            ]
        ]
    ]
];

// Filter menu based on permissions
function hasMenuPermission($permission) {
if (!$permission) return true;
return hasPermission($permission) || hasPermission('all');
}
?>
<style>
    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        top: var(--header-height);
        left: 0;
        width: var(--sidebar-width);
        height: calc(100vh - var(--header-height));
        background: white;
        border-right: 1px solid var(--gray-200);
        overflow-y: auto;
        transition: all 0.3s ease;
        z-index: 40;
        box-shadow: var(--shadow);
    }
    
    [data-theme="dark"] .sidebar {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .sidebar-collapsed {
        width: 70px;
    }
    
    .sidebar-hidden {
        transform: translateX(-100%);
    }
    
    .sidebar-content {
        padding: 1.5rem 0;
    }
    
    .sidebar-section {
        margin-bottom: 2rem;
    }
    
    .section-title {
        padding: 0 1.5rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--gray-500);
        border-bottom: 1px solid var(--gray-200);
        margin-bottom: 1rem;
    }
    
    [data-theme="dark"] .section-title {
        color: var(--gray-400);
        border-color: var(--gray-700);
    }
    
    .sidebar-collapsed .section-title {
        display: none;
    }
    
    .nav-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .nav-item {
        margin-bottom: 0.25rem;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        color: var(--gray-700);
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .nav-link:hover {
        background: var(--gray-100);
        color: var(--gray-900);
        padding-left: 2rem;
    }
    
    [data-theme="dark"] .nav-link {
        color: var(--gray-300);
    }
    
    [data-theme="dark"] .nav-link:hover {
        background: var(--gray-700);
        color: var(--gray-100);
    }
    
    .nav-link.active {
        background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        border-radius: 0 25px 25px 0;
        margin-right: 1rem;
    }
    
    .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: white;
    }
    
    .nav-icon {
        width: 20px;
        text-align: center;
        margin-right: 0.75rem;
        font-size: 1.1rem;
    }
    
    .sidebar-collapsed .nav-link {
        padding: 0.75rem;
        justify-content: center;
    }
    
    .sidebar-collapsed .nav-icon {
        margin-right: 0;
    }
    
    .nav-text {
        transition: opacity 0.3s ease;
    }
    
    .sidebar-collapsed .nav-text {
        opacity: 0;
        width: 0;
        overflow: hidden;
    }
    
    .nav-arrow {
        margin-left: auto;
        font-size: 0.8rem;
        transition: transform 0.3s ease;
    }
    
    .sidebar-collapsed .nav-arrow {
        display: none;
    }
    
    .nav-item.has-submenu .nav-link.active .nav-arrow,
    .nav-item.has-submenu.open .nav-arrow {
        transform: rotate(90deg);
    }
    
    .submenu {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        background: var(--gray-50);
    }
    
    [data-theme="dark"] .submenu {
        background: var(--gray-900);
    }
    
    .submenu.open {
        max-height: 300px;
    }
    
    .sidebar-collapsed .submenu {
        display: none;
    }
    
    .submenu-item {
        margin-bottom: 0.125rem;
    }
    
    .submenu-link {
        display: flex;
        align-items: center;
        padding: 0.5rem 1.5rem 0.5rem 3.5rem;
        color: var(--gray-600);
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.85rem;
        position: relative;
    }
    
    .submenu-link:hover {
        background: var(--gray-200);
        color: var(--gray-900);
        padding-left: 4rem;
    }
    
    [data-theme="dark"] .submenu-link {
        color: var(--gray-400);
    }
    
    [data-theme="dark"] .submenu-link:hover {
        background: var(--gray-800);
        color: var(--gray-200);
    }
    
    .submenu-link.active {
        background: var(--primary-100);
        color: var(--primary-700);
        font-weight: 600;
    }
    
    .submenu-link.active::before {
        content: '';
        position: absolute;
        left: 2.5rem;
        top: 50%;
        width: 6px;
        height: 6px;
        background: var(--primary-500);
        border-radius: 50%;
        transform: translateY(-50%);
    }
    
    [data-theme="dark"] .submenu-link.active {
        background: rgba(59, 130, 246, 0.2);
        color: var(--primary-400);
    }
    
    .sidebar-toggle {
        position: absolute;
        top: 1rem;
        right: -15px;
        width: 30px;
        height: 30px;
        background: var(--primary-500);
        color: white;
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: var(--shadow);
        font-size: 0.8rem;
    }
    
    .sidebar-toggle:hover {
        background: var(--primary-600);
        transform: scale(1.1);
    }
    
    .sidebar-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--gray-200);
        background: var(--gray-50);
    }
    
    [data-theme="dark"] .sidebar-footer {
        border-color: var(--gray-700);
        background: var(--gray-900);
    }
    
    .sidebar-collapsed .sidebar-footer {
        padding: 1rem 0.5rem;
        text-align: center;
    }
    
    .chama-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .chama-avatar {
        width: 32px;
        height: 32px;
        background: var(--primary-500);
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .chama-details {
        flex: 1;
        min-width: 0;
    }
    
    .chama-name {
        font-weight: 600;
        color: var(--gray-900);
        font-size: 0.85rem;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .chama-members {
        color: var(--gray-500);
        font-size: 0.75rem;
        line-height: 1.2;
    }
    
    [data-theme="dark"] .chama-name {
        color: var(--gray-200);
    }
    
    [data-theme="dark"] .chama-members {
        color: var(--gray-400);
    }
    
    .sidebar-collapsed .chama-details {
        display: none;
    }
    
    /* Mobile Overlay */
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 35;
    }
    
    .sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            width: 280px;
            z-index: 45;
        }
        
        .sidebar.mobile-open {
            transform: translateX(0);
        }
        
        .sidebar-toggle {
            display: none;
        }
    }
    
    /* Tooltip for collapsed sidebar */
    .nav-tooltip {
        position: absolute;
        left: calc(100% + 10px);
        top: 50%;
        transform: translateY(-50%);
        background: var(--gray-900);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 60;
        pointer-events: none;
    }
    
    [data-theme="dark"] .nav-tooltip {
        background: var(--gray-700);
    }
    
    .sidebar-collapsed .nav-link:hover .nav-tooltip {
        opacity: 1;
        visibility: visible;
    }
    
    .nav-tooltip::before {
        content: '';
        position: absolute;
        top: 50%;
        left: -4px;
        transform: translateY(-50%);
        border: 4px solid transparent;
        border-right-color: var(--gray-900);
    }
    
    [data-theme="dark"] .nav-tooltip::before {
        border-right-color: var(--gray-700);
    }
</style>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-content">
        <!-- Main Navigation -->
        <div class="sidebar-section">
            <div class="section-title">Navigation</div>
        <ul class="nav-menu">
            <?php foreach ($navigationMenu as $menuItem): ?>
                <?php if (hasMenuPermission($menuItem['permission'])): ?>
                    <li class="nav-item <?php echo isset($menuItem['submenu']) ? 'has-submenu' : ''; ?> <?php echo $menuItem['active'] ?? false ? 'open' : ''; ?>">
                        <?php if (isset($menuItem['submenu'])): ?>
                            <a href="#" class="nav-link <?php echo ($menuItem['active'] ?? false) || (isset($menuItem['submenu']) && array_filter($menuItem['submenu'], function($sub) { return $sub['active'] ?? false; })) ? 'active' : ''; ?>" 
                               data-submenu="true">
                                <i class="nav-icon <?php echo $menuItem['icon']; ?>"></i>
                                <span class="nav-text"><?php echo $menuItem['title']; ?></span>
                                <i class="nav-arrow fas fa-chevron-right"></i>
                                <div class="nav-tooltip"><?php echo $menuItem['title']; ?></div>
                            </a>
                            
                            <ul class="submenu <?php echo (isset($menuItem['submenu']) && array_filter($menuItem['submenu'], function($sub) { return $sub['active'] ?? false; })) ? 'open' : ''; ?>">
                                <?php foreach ($menuItem['submenu'] as $subItem): ?>
                                    <li class="submenu-item">
                                        <a href="<?php echo $subItem['url']; ?>" 
                                           class="submenu-link <?php echo $subItem['active'] ?? false ? 'active' : ''; ?>">
                                            <?php echo $subItem['title']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <a href="<?php echo $menuItem['url']; ?>" 
                               class="nav-link <?php echo $menuItem['active'] ?? false ? 'active' : ''; ?>">
                                <i class="nav-icon <?php echo $menuItem['icon']; ?>"></i>
                                <span class="nav-text"><?php echo $menuItem['title']; ?></span>
                                <div class="nav-tooltip"><?php echo $menuItem['title']; ?></div>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <!-- Quick Actions -->
    <div class="sidebar-section">
        <div class="section-title">Quick Actions</div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="modules/members/add.php" class="nav-link">
                    <i class="nav-icon fas fa-user-plus"></i>
                    <span class="nav-text">Add Member</span>
                    <div class="nav-tooltip">Add New Member</div>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="modules/transactions/deposits.php" class="nav-link">
                    <i class="nav-icon fas fa-plus-circle"></i>
                    <span class="nav-text">New Deposit</span>
                    <div class="nav-tooltip">Record Deposit</div>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="modules/loans/applications.php" class="nav-link">
                    <i class="nav-icon fas fa-hand-holding-usd"></i>
                    <span class="nav-text">Loan Request</span>
                    <div class="nav-tooltip">Process Loan</div>
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
    <i class="fas fa-chevron-left"></i>
</button>

<!-- Sidebar Footer -->
<div class="sidebar-footer">
    <div class="chama-info">
        <div class="chama-avatar">
            <?php 
            $chamaName = "Demo Chama"; // This should come from database
            echo strtoupper(substr($chamaName, 0, 1)); 
            ?>
        </div>
        <div class="chama-details">
            <div class="chama-name"><?php echo htmlspecialchars($chamaName); ?></div>
            <div class="chama-members">
                <?php 
                // Get member count from database
                try {
                    $memberCount = db()->fetchValue(
                        "SELECT COUNT(*) FROM members WHERE chama_group_id = ? AND status = 'Active'",
                        [currentChamaGroup()]
                    );
                    echo $memberCount . ' members';
                } catch (Exception $e) {
                    echo '0 members';
                }
                ?>
            </div>
        </div>
    </div>
</div>
</aside>
<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mainContent = document.getElementById('mainContent');
    
    // Toggle sidebar collapse/expand
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('sidebar-collapsed');
        
        // Update toggle icon
        const icon = this.querySelector('i');
        if (sidebar.classList.contains('sidebar-collapsed')) {
            icon.className = 'fas fa-chevron-right';
            this.title = 'Expand Sidebar';
            
            // Adjust main content margin
            if (mainContent) {
                mainContent.style.marginLeft = '70px';
            }
            
            // Store preference
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            icon.className = 'fas fa-chevron-left';
            this.title = 'Collapse Sidebar';
            
            // Reset main content margin
            if (mainContent) {
                mainContent.style.marginLeft = '260px';
            }
            
            // Store preference
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    });
    
    // Mobile menu toggle
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
            sidebarOverlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        });
    }
    
    // Close mobile menu when overlay is clicked
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('mobile-open');
        this.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    // Handle submenu toggles
    const submenuLinks = document.querySelectorAll('[data-submenu="true"]');
    submenuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const navItem = this.closest('.nav-item');
            const submenu = navItem.querySelector('.submenu');
            
            if (sidebar.classList.contains('sidebar-collapsed')) {
                return; // Don't toggle submenus when collapsed
            }
            
            // Close other open submenus
            document.querySelectorAll('.nav-item.open').forEach(item => {
                if (item !== navItem) {
                    item.classList.remove('open');
                    const otherSubmenu = item.querySelector('.submenu');
                    if (otherSubmenu) {
                        otherSubmenu.classList.remove('open');
                    }
                }
            });
            
            // Toggle current submenu
            navItem.classList.toggle('open');
            if (submenu) {
                submenu.classList.toggle('open');
            }
        });
    });
    
    // Restore sidebar state from localStorage
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');
    if (sidebarCollapsed === 'true') {
        sidebar.classList.add('sidebar-collapsed');
        sidebarToggle.querySelector('i').className = 'fas fa-chevron-right';
        sidebarToggle.title = 'Expand Sidebar';
        if (mainContent) {
            mainContent.style.marginLeft = '70px';
        }
    } else if (mainContent && window.innerWidth > 768) {
        mainContent.style.marginLeft = '260px';
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('mobile-open');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
            if (mainContent) {
                mainContent.style.marginLeft = '0';
            }
        } else {
            // Desktop view
            if (mainContent) {
                mainContent.style.marginLeft = sidebar.classList.contains('sidebar-collapsed') ? '70px' : '260px';
            }
        }
    });
    
    // Auto-close mobile menu on navigation
    const navLinks = document.querySelectorAll('.nav-link:not([data-submenu]), .submenu-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Toggle sidebar with Ctrl+B
        if (e.ctrlKey && e.key === 'b') {
            e.preventDefault();
            if (window.innerWidth > 768) {
                sidebarToggle.click();
            } else {
                mobileMenuBtn.click();
            }
        }
        
        // Close mobile menu with Escape
        if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Add smooth scroll behavior for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>