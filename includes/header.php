<?php
/**
 * Chama Management Platform - Header Component
 * 
 * Main header for dashboard pages with navigation and user controls
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('CHAMA_ACCESS')) {
    die('Direct access denied');
}

// Ensure user is logged in
requireLogin();

$currentUser = currentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Meta Tags -->
    <meta name="description" content="<?php echo APP_DESCRIPTION; ?>">
    <meta name="author" content="Chama Development Team">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?php echo csrfToken(); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo ASSETS_URL; ?>/images/favicon.png">
    <link rel="apple-touch-icon" href="<?php echo ASSETS_URL; ?>/images/apple-touch-icon.png">
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link href="<?php echo ASSETS_URL; ?>/css/style.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/dark-mode.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>/css/responsive.css" rel="stylesheet">
    
    <!-- Charts.js for Analytics -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <style>
        :root {
            /* Light Theme Colors */
            --primary-50: #eff6ff;
            --primary-100: #dbeafe;
            --primary-500: #3b82f6;
            --primary-600: #2563eb;
            --primary-700: #1d4ed8;
            --primary-900: #1e3a8a;
            
            /* Success Colors */
            --success-50: #f0fdf4;
            --success-500: #22c55e;
            --success-600: #16a34a;
            
            /* Warning Colors */
            --warning-50: #fffbeb;
            --warning-500: #f59e0b;
            --warning-600: #d97706;
            
            /* Error Colors */
            --error-50: #fef2f2;
            --error-500: #ef4444;
            --error-600: #dc2626;
            
            /* Neutral Colors */
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            
            /* Layout */
            --sidebar-width: 260px;
            --header-height: 70px;
            --border-radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        /* Dark Theme */
        [data-theme="dark"] {
            --gray-50: #1f2937;
            --gray-100: #374151;
            --gray-200: #4b5563;
            --gray-300: #6b7280;
            --gray-400: #9ca3af;
            --gray-500: #d1d5db;
            --gray-600: #e5e7eb;
            --gray-700: #f3f4f6;
            --gray-800: #f9fafb;
            --gray-900: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-900);
            line-height: 1.6;
            transition: all 0.3s ease;
        }
        
        /* Header Styles */
        .main-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: var(--header-height);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--shadow-lg);
        }
        
        .header-content {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            max-width: 100%;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .mobile-menu-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
            text-decoration: none;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            backdrop-filter: blur(10px);
        }
        
        .logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            margin-left: 0.5rem;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: 2rem;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }
        
        .breadcrumb a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .breadcrumb a:hover {
            color: white;
        }
        
        .breadcrumb-separator {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .header-btn {
            position: relative;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .header-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }
        
        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 0.1rem 0.35rem;
            border-radius: 10px;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .theme-toggle {
            position: relative;
            width: 50px;
            height: 25px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .theme-toggle::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 21px;
            height: 21px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        [data-theme="dark"] .theme-toggle::before {
            transform: translateX(25px);
        }
        
        .user-menu {
            position: relative;
        }
        
        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            border-color: rgba(255, 255, 255, 0.6);
            transform: scale(1.05);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .user-info:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .user-name {
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            line-height: 1.2;
        }
        
        .user-role {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
            line-height: 1.2;
        }
        
        .dropdown-icon {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
            margin-left: 0.5rem;
            transition: transform 0.3s ease;
        }
        
        .user-menu.active .dropdown-icon {
            transform: rotate(180deg);
        }
        
        /* Dropdown Menu */
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            border: 1px solid var(--gray-200);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 60;
        }
        
        [data-theme="dark"] .dropdown-menu {
            background: var(--gray-800);
            border-color: var(--gray-700);
        }
        
        .dropdown-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-header {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }
        
        [data-theme="dark"] .dropdown-header {
            border-color: var(--gray-700);
        }
        
        .dropdown-user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .dropdown-avatar {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        .dropdown-user-details h4 {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.9rem;
            margin-bottom: 0.2rem;
        }
        
        .dropdown-user-details p {
            color: var(--gray-500);
            font-size: 0.8rem;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
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
        
        .dropdown-item i {
            width: 16px;
            text-align: center;
        }
        
        .dropdown-divider {
            height: 1px;
            background: var(--gray-200);
            margin: 0.5rem 0;
        }
        
        [data-theme="dark"] .dropdown-divider {
            background: var(--gray-700);
        }
        
        .logout-item {
            color: var(--error-600);
            border-top: 1px solid var(--gray-200);
        }
        
        .logout-item:hover {
            background: var(--error-50);
            color: var(--error-700);
        }
        
        [data-theme="dark"] .logout-item {
            border-color: var(--gray-700);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            
            .breadcrumb {
                display: none;
            }
            
            .user-details {
                display: none;
            }
            
            .header-content {
                padding: 0 1rem;
            }
            
            .logo-text {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 480px) {
            .header-actions {
                gap: 0.5rem;
            }
            
            .header-btn {
                width: 38px;
                height: 38px;
            }
            
            .user-avatar {
                width: 38px;
                height: 38px;
            }
        }
        
        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            transform: translate(-50%, -50%);
        }
        
        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        /* Notification Styles */
        .notification-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid var(--gray-200);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 60;
        }
        
        [data-theme="dark"] .notification-dropdown {
            background: var(--gray-800);
            border-color: var(--gray-700);
        }
        
        .notification-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .notification-header {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        [data-theme="dark"] .notification-header {
            border-color: var(--gray-700);
        }
        
        .notification-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--gray-100);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .notification-item:hover {
            background: var(--gray-50);
        }
        
        [data-theme="dark"] .notification-item {
            border-color: var(--gray-700);
        }
        
        [data-theme="dark"] .notification-item:hover {
            background: var(--gray-700);
        }
        
        .notification-item.unread {
            background: var(--primary-50);
            border-left: 3px solid var(--primary-500);
        }
        
        [data-theme="dark"] .notification-item.unread {
            background: rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body>
    <!-- Main Header -->
    <header class="main-header">
        <div class="header-content">
            <!-- Left Section -->
            <div class="header-left">
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Logo -->
                <a href="dashboard.php" class="logo-section">
                    <div class="logo-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="logo-text"><?php echo APP_NAME; ?></span>
                </a>
                
                <!-- Breadcrumb -->
                <nav class="breadcrumb" id="breadcrumb">
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                    <?php if ($currentPage !== 'dashboard'): ?>
                        <span class="breadcrumb-separator">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <span><?php echo ucfirst(str_replace('_', ' ', $currentPage)); ?></span>
                    <?php endif; ?>
                </nav>
            </div>
            
            <!-- Right Section -->
            <div class="header-right">
                <div class="header-actions">
                    <!-- Search Button -->
                    <button class="header-btn" id="searchBtn" title="Search">
                        <i class="fas fa-search"></i>
                    </button>
                    
                    <!-- Notifications -->
                    <div class="relative">
                        <button class="header-btn" id="notificationBtn" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge" id="notificationBadge">3</span>
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h4 class="font-semibold text-gray-900">Notifications</h4>
                                <button class="text-sm text-primary-600 hover:text-primary-700">
                                    Mark all read
                                </button>
                            </div>
                            
                            <div class="notification-list" id="notificationList">
                                <!-- Notifications will be loaded here -->
                            </div>
                            
                            <div class="p-3 text-center border-t border-gray-200">
                                <a href="notifications.php" class="text-sm text-primary-600 hover:text-primary-700">
                                    View all notifications
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Settings -->
                    <button class="header-btn" id="settingsBtn" title="Settings">
                        <i class="fas fa-cog"></i>
                    </button>
                    
                    <!-- Theme Toggle -->
                    <div class="theme-toggle" id="themeToggle" title="Toggle Dark Mode"></div>
                    
                    <!-- Fullscreen -->
                    <button class="header-btn" id="fullscreenBtn" title="Toggle Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
                
                <!-- User Menu -->
                <div class="user-menu" id="userMenu">
                    <div class="user-info" id="userMenuBtn">
                        <div class="user-details">
                            <div class="user-name"><?php echo htmlspecialchars($currentUser['full_name']); ?></div>
                            <div class="user-role"><?php echo htmlspecialchars($currentUser['role']); ?></div>
                        </div>
                        <img src="<?php echo $currentUser['avatar'] ? getUploadUrl('members') . '/' . $currentUser['avatar'] : ASSETS_URL . '/images/default-avatar.png'; ?>" 
                             alt="User Avatar" 
                             class="user-avatar"
                             id="userAvatar">
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </div>
                    
                    <!-- User Dropdown -->
                    <div class="dropdown-menu" id="userDropdown">
                        <div class="dropdown-header">
                            <div class="dropdown-user-info">
                                <img src="<?php echo $currentUser['avatar'] ? getUploadUrl('members') . '/' . $currentUser['avatar'] : ASSETS_URL . '/images/default-avatar.png'; ?>" 
                                     alt="User Avatar" 
                                     class="dropdown-avatar">
                                <div class="dropdown-user-details">
                                    <h4><?php echo htmlspecialchars($currentUser['full_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($currentUser['email']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>My Profile</span>
                        </a>
                        
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            <span>Account Settings</span>
                        </a>
                        
                        <a href="help.php" class="dropdown-item">
                            <i class="fas fa-question-circle"></i>
                            <span>Help & Support</span>
                        </a>
                        
                        <div class="dropdown-divider"></div>
                        
                        <a href="logout.php" class="dropdown-item logout-item" id="logoutBtn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Sign Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Page Content Wrapper -->
    <div class="page-wrapper" style="margin-top: var(--header-height);">
        <!-- Sidebar will be included here -->
        <?php include_once INCLUDES_PATH . '/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <main class="main-content" id="mainContent">