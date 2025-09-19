<?php
/**
 * Chama Management Platform - Footer Component
 * 
 * Global footer with scripts and closing tags
 * 
 * @author Chama Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('CHAMA_ACCESS')) {
    die('Direct access denied');
}
?>
        </main>
    </div>
    
    <!-- Global Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <div class="loading-text">Loading...</div>
        </div>
    </div>
    
    <!-- Toast Notifications Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Modal Container -->
    <div class="modal-backdrop" id="modalBackdrop"></div>
    
    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalTitle">Confirm Action</h5>
                    <button type="button" class="modal-close" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="confirmModalMessage">Are you sure you want to continue?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmModalBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div class="search-input-group w-100">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" id="globalSearch" placeholder="Search members, transactions, loans..." autocomplete="off">
                        <button type="button" class="modal-close" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-body pt-2">
                    <div class="search-results" id="searchResults">
                        <div class="search-suggestions">
                            <h6 class="search-section-title">Quick Actions</h6>
                            <a href="modules/members/add.php" class="search-suggestion">
                                <i class="fas fa-user-plus"></i>
                                <span>Add New Member</span>
                            </a>
                            <a href="modules/transactions/deposits.php" class="search-suggestion">
                                <i class="fas fa-plus-circle"></i>
                                <span>Record Deposit</span>
                            </a>
                            <a href="modules/loans/applications.php" class="search-suggestion">
                                <i class="fas fa-hand-holding-usd"></i>
                                <span>Process Loan Application</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Main Content Layout */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
            transition: margin-left 0.3s ease;
            background: var(--gray-50);
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        [data-theme="dark"] .loading-overlay {
            background: rgba(31, 41, 55, 0.9);
        }
        
        .loading-spinner {
            text-align: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--gray-200);
            border-top: 4px solid var(--primary-500);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            color: var(--gray-700);
            font-weight: 500;
        }
        
        [data-theme="dark"] .loading-text {
            color: var(--gray-300);
        }
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: calc(var(--header-height) + 1rem);
            right: 1rem;
            z-index: 1050;
            max-width: 400px;
        }
        
        .toast {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            margin-bottom: 1rem;
            overflow: hidden;
            transform: translateX(100%);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-500);
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.hide {
            transform: translateX(100%);
            opacity: 0;
        }
        
        [data-theme="dark"] .toast {
            background: var(--gray-800);
            border-color: var(--primary-400);
        }
        
        .toast.toast-success {
            border-left-color: var(--success-500);
        }
        
        .toast.toast-warning {
            border-left-color: var(--warning-500);
        }
        
        .toast.toast-error {
            border-left-color: var(--error-500);
        }
        
        .toast-header {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }
        
        [data-theme="dark"] .toast-header {
            border-color: var(--gray-700);
            background: var(--gray-900);
        }
        
        .toast-icon {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }
        
        .toast-icon.success {
            color: var(--success-500);
        }
        
        .toast-icon.warning {
            color: var(--warning-500);
        }
        
        .toast-icon.error {
            color: var(--error-500);
        }
        
        .toast-title {
            flex: 1;
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.9rem;
        }
        
        [data-theme="dark"] .toast-title {
            color: var(--gray-100);
        }
        
        .toast-close {
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .toast-close:hover {
            color: var(--gray-600);
        }
        
        .toast-body {
            padding: 1rem;
            color: var(--gray-700);
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        [data-theme="dark"] .toast-body {
            color: var(--gray-300);
        }
        
        /* Modal Styles */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-backdrop.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-dialog {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal.show .modal-dialog {
            transform: scale(1);
        }
        
        [data-theme="dark"] .modal-dialog {
            background: var(--gray-800);
        }
        
        .modal-dialog.modal-sm {
            max-width: 300px;
        }
        
        .modal-dialog.modal-lg {
            max-width: 800px;
        }
        
        .modal-dialog.modal-xl {
            max-width: 1200px;
        }
        
        .modal-content {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }
        
        [data-theme="dark"] .modal-header {
            border-color: var(--gray-700);
        }
        
        .modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
        }
        
        [data-theme="dark"] .modal-title {
            color: var(--gray-100);
        }
        
        .modal-close {
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background: var(--gray-100);
            color: var(--gray-600);
        }
        
        [data-theme="dark"] .modal-close:hover {
            background: var(--gray-700);
            color: var(--gray-300);
        }
        
        .modal-body {
            flex: 1;
            padding: 1.5rem;
            color: var(--gray-700);
        }
        
        [data-theme="dark"] .modal-body {
            color: var(--gray-300);
        }
        
        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 1rem;
            padding: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }
        
        [data-theme="dark"] .modal-footer {
            border-color: var(--gray-700);
        }
        
        /* Search Modal */
        .search-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .search-icon {
            position: absolute;
            left: 1rem;
            color: var(--gray-400);
            z-index: 10;
        }
        
        .search-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            background: var(--gray-50);
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary-500);
            background: white;
        }
        
        [data-theme="dark"] .search-input {
            background: var(--gray-700);
            border-color: var(--gray-600);
            color: var(--gray-100);
        }
        
        [data-theme="dark"] .search-input:focus {
            background: var(--gray-600);
            border-color: var(--primary-400);
        }
        
        .search-results {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .search-suggestions {
            margin-bottom: 1.5rem;
        }
        
        .search-section-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-500);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .search-suggestion {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: 8px;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.3s ease;
            margin-bottom: 0.25rem;
        }
        
        .search-suggestion:hover {
            background: var(--gray-100);
            color: var(--gray-900);
        }
        
        [data-theme="dark"] .search-suggestion {
            color: var(--gray-300);
        }
        
        [data-theme="dark"] .search-suggestion:hover {
            background: var(--gray-700);
            color: var(--gray-100);
        }
        
        .search-suggestion i {
            margin-right: 0.75rem;
            width: 16px;
            text-align: center;
            color: var(--primary-500);
        }
        
        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            line-height: 1.5;
            min-height: 38px;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-primary {
            background: var(--primary-500);
            color: white;
            border-color: var(--primary-500);
        }
        
        .btn-primary:hover:not(:disabled) {
            background: var(--primary-600);
            border-color: var(--primary-600);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
            border-color: var(--gray-300);
        }
        
        .btn-secondary:hover:not(:disabled) {
            background: var(--gray-200);
            border-color: var(--gray-400);
        }
        
        [data-theme="dark"] .btn-secondary {
            background: var(--gray-700);
            color: var(--gray-300);
            border-color: var(--gray-600);
        }
        
        [data-theme="dark"] .btn-secondary:hover:not(:disabled) {
            background: var(--gray-600);
            border-color: var(--gray-500);
        }
        
        .btn-success {
            background: var(--success-500);
            color: white;
            border-color: var(--success-500);
        }
        
        .btn-success:hover:not(:disabled) {
            background: var(--success-600);
            border-color: var(--success-600);
        }
        
        .btn-warning {
            background: var(--warning-500);
            color: white;
            border-color: var(--warning-500);
        }
        
        .btn-warning:hover:not(:disabled) {
            background: var(--warning-600);
            border-color: var(--warning-600);
        }
        
        .btn-danger {
            background: var(--error-500);
            color: white;
            border-color: var(--error-500);
        }
        
        .btn-danger:hover:not(:disabled) {
            background: var(--error-600);
            border-color: var(--error-600);
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
            min-height: 32px;
        }
        
        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            min-height: 44px;
        }
        
        /* Utility Classes */
        .fade {
            transition: opacity 0.3s ease;
        }
        
        .fade:not(.show) {
            opacity: 0;
        }
        
        .d-none {
            display: none !important;
        }
        
        .d-block {
            display: block !important;
        }
        
        .d-flex {
            display: flex !important;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .text-right {
            text-align: right !important;
        }
        
        .ml-auto {
            margin-left: auto !important;
        }
        
        .mr-2 {
            margin-right: 0.5rem !important;
        }
        
        .w-100 {
            width: 100% !important;
        }
        
        /* Responsive utilities */
        @media (max-width: 768px) {
            .toast-container {
                right: 0.5rem;
                left: 0.5rem;
                max-width: none;
            }
            
            .modal {
                padding: 0.5rem;
            }
            
            .modal-dialog {
                margin: 0;
                max-height: 100vh;
            }
            
            .modal-header,
            .modal-body,
            .modal-footer {
                padding: 1rem;
            }
        }
    </style>
    
    <!-- JavaScript Dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="<?php echo ASSETS_URL; ?>/js/app.js"></script>
    <script src="<?php echo ASSETS_URL; ?>/js/ajax-handlers.js"></script>
    <script src="<?php echo ASSETS_URL; ?>/js/theme-toggle.js"></script>
    <script src="<?php echo ASSETS_URL; ?>/js/charts.js"></script>
    
    <script>
        // Global JavaScript initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize global features
            initializeToasts();
            initializeModals();
            initializeSearch();
            initializeConfirmActions();
            initializeAjaxSetup();
            initializeKeyboardShortcuts();
            
            // Auto-hide flash messages
            hideFlashMessages();
            
            // Initialize tooltips
            initializeTooltips();
            
            // Check for updates periodically
            checkForUpdates();
        });
        
        // Toast notification system
        function initializeToasts() {
            window.showToast = function(message, type = 'info', title = null, duration = 5000) {
                const container = document.getElementById('toastContainer');
                const toastId = 'toast_' + Date.now();
                
                const icons = {
                    success: 'fas fa-check-circle',
                    error: 'fas fa-exclamation-circle',
                    warning: 'fas fa-exclamation-triangle',
                    info: 'fas fa-info-circle'
                };
                
                const titles = {
                    success: 'Success',
                    error: 'Error',
                    warning: 'Warning',
                    info: 'Information'
                };
                
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.id = toastId;
                toast.innerHTML = `
                    <div class="toast-header">
                        <i class="toast-icon ${type} ${icons[type]}"></i>
                        <div class="toast-title">${title || titles[type]}</div>
                        <button type="button" class="toast-close" onclick="hideToast('${toastId}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="toast-body">${message}</div>
                `;
                
                container.appendChild(toast);
                
                // Show toast
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);
                
                // Auto hide
                if (duration > 0) {
                    setTimeout(() => {
                        hideToast(toastId);
                    }, duration);
                }
            };
            
            window.hideToast = function(toastId) {
                const toast = document.getElementById(toastId);
                if (toast) {
                    toast.classList.add('hide');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }
            };
        }
        
        // Modal system
        function initializeModals() {
            window.showModal = function(modalId) {
                const modal = document.getElementById(modalId);
                const backdrop = document.getElementById('modalBackdrop');
                
                if (modal && backdrop) {
                    backdrop.classList.add('show');
                    modal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                }
            };
            
            window.hideModal = function(modalId) {
                const modal = document.getElementById(modalId);
                const backdrop = document.getElementById('modalBackdrop');
                
                if (modal && backdrop) {
                    modal.classList.remove('show');
                    backdrop.classList.remove('show');
                    document.body.style.overflow = '';
                }
            };
            
            // Close modal on backdrop click
            document.getElementById('modalBackdrop').addEventListener('click', function() {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    hideModal(openModal.id);
                }
            });
            
            // Close modal on X button click
            document.addEventListener('click', function(e) {
                if (e.target.matches('[data-dismiss="modal"]') || e.target.closest('[data-dismiss="modal"]')) {
                    const modal = e.target.closest('.modal');
                    if (modal) {
                        hideModal(modal.id);
                    }
                }
            });
            
            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const openModal = document.querySelector('.modal.show');
                    if (openModal) {
                        hideModal(openModal.id);
                    }
                }
            });
        }
        
        // Global search functionality
        function initializeSearch() {
            const searchBtn = document.getElementById('searchBtn');
            const searchModal = document.getElementById('searchModal');
            const searchInput = document.getElementById('globalSearch');
            
            if (searchBtn && searchModal) {
                searchBtn.addEventListener('click', function() {
                    showModal('searchModal');
                    setTimeout(() => {
                        searchInput.focus();
                    }, 300);
                });
            }
            
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        performGlobalSearch(this.value);
                    }, 300);
                });
            }
        }
        
        function performGlobalSearch(query) {
            if (query.length < 2) {
                document.getElementById('searchResults').innerHTML = `
                    <div class="search-suggestions">
                        <h6 class="search-section-title">Quick Actions</h6>
                        <a href="modules/members/add.php" class="search-suggestion">
                            <i class="fas fa-user-plus"></i>
                            <span>Add New Member</span>
                        </a>
                        <a href="modules/transactions/deposits.php" class="search-suggestion">
                            <i class="fas fa-plus-circle"></i>
                            <span>Record Deposit</span>
                        </a>
                        <a href="modules/loans/applications.php" class="search-suggestion">
                            <i class="fas fa-hand-holding-usd"></i>
                            <span>Process Loan Application</span>
                        </a>
                    </div>
                `;
                return;
            }
            
            // Perform AJAX search
            fetch('api/search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ query: query })
            })
            .then(response => response.json())
            .then(data => {
                displaySearchResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
            });
        }
        
        function displaySearchResults(results) {
            const container = document.getElementById('searchResults');
            let html = '';
            
            if (results.members && results.members.length > 0) {
                html += '<div class="search-suggestions"><h6 class="search-section-title">Members</h6>';
                results.members.forEach(member => {
                    html += `
                        <a href="modules/members/view.php?id=${member.id}" class="search-suggestion">
                            <i class="fas fa-user"></i>
                            <span>${member.full_name} - ${member.member_number}</span>
                        </a>
                    `;
                });
                html += '</div>';
            }
            
            if (results.transactions && results.transactions.length > 0) {
                html += '<div class="search-suggestions"><h6 class="search-section-title">Transactions</h6>';
                results.transactions.forEach(transaction => {
                    html += `
                        <a href="modules/transactions/view.php?id=${transaction.id}" class="search-suggestion">
                            <i class="fas fa-exchange-alt"></i>
                            <span>${transaction.transaction_number} - ${transaction.amount}</span>
                        </a>
                    `;
                });
                html += '</div>';
            }
            
            if (!html) {
                html = '<div class="text-center p-4 text-gray-500">No results found</div>';
            }
            
            container.innerHTML = html;
        }
        
        // Confirmation dialogs
        function initializeConfirmActions() {
            window.confirmAction = function(message, callback, title = 'Confirm Action') {
                document.getElementById('confirmModalTitle').textContent = title;
                document.getElementById('confirmModalMessage').textContent = message;
                
                const confirmBtn = document.getElementById('confirmModalBtn');
                confirmBtn.onclick = function() {
                    hideModal('confirmModal');
                    if (typeof callback === 'function') {
                        callback();
                    }
                };
                
                showModal('confirmModal');
            };
        }
        
        // AJAX setup
        function initializeAjaxSetup() {
            // Set CSRF token for all AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrfToken) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-Token': csrfToken
                    }
                });
            }
            
            // Global AJAX error handler
            $(document).ajaxError(function(event, xhr, settings, error) {
                console.error('AJAX Error:', error);
                showToast('An error occurred. Please try again.', 'error');
            });
        }
        
        // Loading overlay
        window.showLoading = function() {
            document.getElementById('loadingOverlay').classList.add('active');
        };
        
        window.hideLoading = function() {
            document.getElementById('loadingOverlay').classList.remove('active');
        };
        
        // Auto-hide flash messages
        function hideFlashMessages() {
            <?php if (getFlashMessage('success')): ?>
                showToast('<?php echo addslashes(getFlashMessage('success')); ?>', 'success');
            <?php endif; ?>
            
            <?php if (getFlashMessage('error')): ?>
                showToast('<?php echo addslashes(getFlashMessage('error')); ?>', 'error');
            <?php endif; ?>
            
            <?php if (getFlashMessage('warning')): ?>
                showToast('<?php echo addslashes(getFlashMessage('warning')); ?>', 'warning');
            <?php endif; ?><?php if (getFlashMessage('info')): ?>
            showToast('<?php echo addslashes(getFlashMessage('info')); ?>', 'info');
        <?php endif; ?>
    }
    
    // Keyboard shortcuts
    function initializeKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Ctrl+K for search
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                document.getElementById('searchBtn').click();
            }
            
            // Ctrl+N for new member
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                window.location.href = 'modules/members/add.php';
            }
            
            // Ctrl+D for dashboard
            if (e.ctrlKey && e.key === 'd') {
                e.preventDefault();
                window.location.href = 'dashboard.php';
            }
            
            // Ctrl+S for settings
            if (e.ctrlKey && e.key === 's' && e.shiftKey) {
                e.preventDefault();
                window.location.href = 'modules/settings/index.php';
            }
        });
    }
    
    // Tooltip initialization
    function initializeTooltips() {
        const tooltipElements = document.querySelectorAll('[title]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', function(e) {
                createTooltip(e.target, e.target.getAttribute('title'));
            });
            
            element.addEventListener('mouseleave', function() {
                removeTooltip();
            });
        });
    }
    
    function createTooltip(element, text) {
        removeTooltip();
        
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        tooltip.style.cssText = `
            position: absolute;
            background: var(--gray-900);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            white-space: nowrap;
            z-index: 1060;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        tooltip.style.left = rect.left + (rect.width - tooltipRect.width) / 2 + 'px';
        tooltip.style.top = rect.top - tooltipRect.height - 8 + 'px';
        
        setTimeout(() => {
            tooltip.style.opacity = '1';
        }, 100);
    }
    
    function removeTooltip() {
        const existingTooltip = document.querySelector('.tooltip');
        if (existingTooltip) {
            existingTooltip.remove();
        }
    }
    
    // Check for updates
    function checkForUpdates() {
        // Check every 5 minutes
        setInterval(() => {
            fetch('api/check-updates.php')
            .then(response => response.json())
            .then(data => {
                if (data.hasUpdates) {
                    showToast('New updates are available. Please refresh the page.', 'info', 'Updates Available', 0);
                }
            })
            .catch(error => {
                console.log('Update check failed:', error);
            });
        }, 300000);
    }
    
    // Header functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Notification dropdown
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        if (notificationBtn && notificationDropdown) {
            notificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('active');
                loadNotifications();
            });
        }
        
        // User dropdown
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        const userMenu = document.getElementById('userMenu');
        
        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
                userMenu.classList.toggle('active');
            });
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function() {
            if (notificationDropdown) notificationDropdown.classList.remove('active');
            if (userDropdown) userDropdown.classList.remove('active');
            if (userMenu) userMenu.classList.remove('active');
        });
        
        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                // Update theme cookie
                document.cookie = `chama_theme=${newTheme}; path=/; max-age=${30 * 24 * 3600}`;
            });
        }
        
        // Fullscreen toggle
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().then(() => {
                        this.querySelector('i').className = 'fas fa-compress';
                        this.title = 'Exit Fullscreen';
                    });
                } else {
                    document.exitFullscreen().then(() => {
                        this.querySelector('i').className = 'fas fa-expand';
                        this.title = 'Toggle Fullscreen';
                    });
                }
            });
        }
        
        // Logout confirmation
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                confirmAction(
                    'Are you sure you want to sign out?',
                    () => {
                        window.location.href = 'logout.php';
                    },
                    'Sign Out'
                );
            });
        }
    });
    
    function loadNotifications() {
        fetch('api/notifications.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('notificationList');
            if (data.notifications && data.notifications.length > 0) {
                container.innerHTML = data.notifications.map(notification => `
                    <div class="notification-item ${notification.is_read ? '' : 'unread'}">
                        <div class="font-medium text-sm">${notification.title}</div>
                        <div class="text-xs text-gray-500 mt-1">${notification.message}</div>
                        <div class="text-xs text-gray-400 mt-1">${notification.created_at}</div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="p-4 text-center text-gray-500">No notifications</div>';
            }
            
            // Update badge
            const badge = document.getElementById('notificationBadge');
            const unreadCount = data.unread_count || 0;
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Failed to load notifications:', error);
        });
    }
    
    // Load theme from storage
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Auto-save forms
    window.autoSaveForm = function(formId, interval = 30000) {
        const form = document.getElementById(formId);
        if (!form) return;
        
        setInterval(() => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            localStorage.setItem(`autosave_${formId}`, JSON.stringify({
                data: data,
                timestamp: Date.now()
            }));
        }, interval);
    };
    
    // Restore auto-saved form data
    window.restoreAutoSave = function(formId) {
        const saved = localStorage.getItem(`autosave_${formId}`);
        if (!saved) return false;
        
        try {
            const { data, timestamp } = JSON.parse(saved);
            const form = document.getElementById(formId);
            
            // Only restore if less than 1 hour old
            if (Date.now() - timestamp > 3600000) {
                localStorage.removeItem(`autosave_${formId}`);
                return false;
            }
            
            Object.keys(data).forEach(key => {
                const input = form.querySelector(`[name="${key}"]`);
                if (input && input.type !== 'password') {
                    input.value = data[key];
                }
            });
            
            return true;
        } catch (error) {
            console.error('Failed to restore auto-save:', error);
            return false;
        }
    };
    
    // Clear auto-save on successful form submission
    window.clearAutoSave = function(formId) {
        localStorage.removeItem(`autosave_${formId}`);
    };
    
    // Performance monitoring
    window.addEventListener('load', function() {
        // Send performance data
        setTimeout(() => {
            const perfData = performance.getEntriesByType('navigation')[0];
            if (perfData) {
                const loadTime = perfData.loadEventEnd - perfData.fetchStart;
                console.log(`Page load time: ${loadTime}ms`);
            }
        }, 0);
    });
    
    // Online/offline status
    window.addEventListener('online', function() {
        showToast('Connection restored', 'success', 'Online', 3000);
    });
    
    window.addEventListener('offline', function() {
        showToast('Connection lost. Some features may not work.', 'warning', 'Offline', 0);
    });
    
    // Prevent multiple form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const submitBtn = form.querySelector('[type="submit"]');
        
        if (submitBtn && !submitBtn.disabled) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            
            // Re-enable after 5 seconds as fallback
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.dataset.originalText || 'Submit';
            }, 5000);
        }
    });
    
    // Initialize page-specific features
    if (window.initializePage && typeof window.initializePage === 'function') {
        window.initializePage();
    }
</script>

<!-- Page-specific scripts -->
<?php if (isset($additionalScripts) && is_array($additionalScripts)): ?>
    <?php foreach ($additionalScripts as $script): ?>
        <script src="<?php echo htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Inline scripts -->
<?php if (isset($inlineScripts)): ?>
    <script><?php echo $inlineScripts; ?></script>
<?php endif; ?>
</body>
</html>