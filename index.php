<?php
/**
 * Chama Management Platform - Login Page
 * 
 * Secure login interface with modern design
 * 
 * @author Chama Development Team

@version 1.0.0
*/

define('CHAMA_ACCESS', true);
require_once 'config/config.php';
// Redirect if already logged in
if (isLoggedIn()) {
redirect('dashboard.php');
}
// Handle login form submission
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    username=sanitizeInput(username = sanitizeInput(
username=sanitizeInput(_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    remember=isset(remember = isset(
remember=isset(_POST['remember']);

// Validate CSRF token
if (!session()->validateCsrfToken($_POST['_token'] ?? '')) {
    $error = 'Invalid request. Please try again.';
} elseif (empty($username) || empty($password)) {
    $error = 'Please enter both username and password.';
} else {
    $result = authenticateUser($username, $password);
    
    if ($result['success']) {
        session()->login($result['user']);
        
        // Set remember me cookie
        if ($remember) {
            setcookie('remember_user', $username, time() + (30 * 24 * 3600), '/', '', false, true);
        }
        
        redirect('dashboard.php');
    } else {
        $error = $result['message'];
    }
}
}
// Get remembered username
$rememberedUser = $_COOKIE['remember_user'] ?? '';
// Get error from URL
if (isset($_GET['error'])) {
switch ($_GET['error']) {
case 'login_required':
$error = 'Please log in to access this page.';
break;
case 'session_expired':
$error = 'Your session has expired. Please log in again.';
break;
case 'access_denied':
$error = 'Access denied. Insufficient permissions.';
break;
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
<!-- Meta Tags -->
<meta name="description" content="<?php echo APP_DESCRIPTION; ?>">
<meta name="author" content="Chama Development Team">
<meta name="robots" content="noindex, nofollow">

<!-- CSS Dependencies -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --dark-gradient: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        --success-gradient: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        --glass-bg: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Inter', sans-serif;
        background: var(--primary-gradient);
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }
    
    /* Animated Background */
    .bg-animation {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
    }
    
    .bg-animation::before {
        content: '';
        position: absolute;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.05) 50%, transparent 70%);
        animation: slide 20s infinite linear;
        transform: translateX(-50%) translateY(-50%);
    }
    
    @keyframes slide {
        0% { transform: translateX(-100%) translateY(-100%); }
        100% { transform: translateX(0%) translateY(0%); }
    }
    
    .floating-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 1;
    }
    
    .shape {
        position: absolute;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        animation: float 15s infinite ease-in-out;
    }
    
    .shape:nth-child(1) {
        width: 80px;
        height: 80px;
        top: 20%;
        left: 10%;
        animation-delay: 0s;
    }
    
    .shape:nth-child(2) {
        width: 120px;
        height: 120px;
        top: 60%;
        right: 10%;
        animation-delay: 4s;
    }
    
    .shape:nth-child(3) {
        width: 60px;
        height: 60px;
        bottom: 20%;
        left: 20%;
        animation-delay: 8s;
    }
    
    .shape:nth-child(4) {
        width: 100px;
        height: 100px;
        top: 10%;
        right: 30%;
        animation-delay: 12s;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        33% { transform: translateY(-30px) rotate(120deg); }
        66% { transform: translateY(30px) rotate(240deg); }
    }
    
    /* Glass Card Effect */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 10;
    }
    
    .glass-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        border-radius: 20px 20px 0 0;
    }
    
    /* Form Styling */
    .form-group {
        position: relative;
        margin-bottom: 2rem;
    }
    
    .form-input {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        color: white;
        font-size: 1rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    
    .form-input:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .form-input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .form-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    
    .form-input:focus + .form-icon {
        color: white;
        transform: translateY(-50%) scale(1.1);
    }
    
    /* Button Styling */
    .btn-primary {
        width: 100%;
        padding: 1rem;
        background: var(--secondary-gradient);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
    }
    
    .btn-primary:hover::before {
        left: 100%;
    }
    
    .btn-primary:active {
        transform: translateY(0);
    }
    
    /* Loading Animation */
    .btn-loading {
        position: relative;
        color: transparent;
    }
    
    .btn-loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top: 2px solid white;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
    
    /* Alert Styling */
    .alert {
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 1px solid;
        backdrop-filter: blur(10px);
    }
    
    .alert-error {
        background: rgba(248, 113, 113, 0.1);
        border-color: rgba(248, 113, 113, 0.3);
        color: #fca5a5;
    }
    
    .alert-success {
        background: rgba(72, 187, 120, 0.1);
        border-color: rgba(72, 187, 120, 0.3);
        color: #9ae6b4;
    }
    
    /* Checkbox Styling */
    .checkbox-container {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
        cursor: pointer;
    }
    
    .checkbox-input {
        display: none;
    }
    
    .checkbox-custom {
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 4px;
        margin-right: 0.5rem;
        position: relative;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.1);
    }
    
    .checkbox-input:checked + .checkbox-custom {
        background: var(--secondary-gradient);
        border-color: rgba(255, 255, 255, 0.5);
    }
    
    .checkbox-input:checked + .checkbox-custom::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 12px;
    }
    
    .checkbox-label {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
        user-select: none;
    }
    
    /* Logo Animation */
    .logo-container {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .logo {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        background: var(--secondary-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        animation: logoFloat 3s ease-in-out infinite;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    @keyframes logoFloat {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-10px) rotate(5deg); }
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .glass-card {
            margin: 1rem;
            padding: 2rem 1.5rem;
        }
        
        .shape {
            display: none;
        }
    }
    
    /* Dark Mode Support */
    @media (prefers-color-scheme: dark) {
        body {
            background: var(--dark-gradient);
        }
    }
    
    /* Password Toggle */
    .password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.7);
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 10;
    }
    
    .password-toggle:hover {
        color: white;
        transform: translateY(-50%) scale(1.1);
    }
    
    /* Footer */
    .footer {
        position: absolute;
        bottom: 2rem;
        left: 0;
        right: 0;
        text-align: center;
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.9rem;
    }
    
    .footer a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .footer a:hover {
        color: white;
    }
</style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation"></div>
<!-- Floating Shapes -->
<div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
</div>

<!-- Login Container -->
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="glass-card w-full max-w-md p-8">
        <!-- Logo -->
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-users"></i>
            </div>
            <h1 class="text-2xl font-bold text-white mb-2"><?php echo APP_NAME; ?></h1>
            <p class="text-gray-200 opacity-80 text-sm">Secure Access Portal</p>
        </div>
        
        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" id="loginForm" autocomplete="on">
            <?php echo csrfField(); ?>
            
            <!-- Username Field -->
            <div class="form-group">
                <input 
                    type="text" 
                    name="username" 
                    class="form-input" 
                    placeholder="Username or Email"
                    value="<?php echo htmlspecialchars($rememberedUser); ?>"
                    required 
                    autocomplete="username"
                    autofocus
                >
                <i class="form-icon fas fa-user"></i>
            </div>
            
            <!-- Password Field -->
            <div class="form-group">
                <input 
                    type="password" 
                    name="password" 
                    id="password"
                    class="form-input" 
                    placeholder="Password"
                    required 
                    autocomplete="current-password"
                >
                <i class="form-icon fas fa-lock"></i>
                <i class="password-toggle fas fa-eye" id="passwordToggle"></i>
            </div>
            
            <!-- Remember Me -->
            <div class="checkbox-container">
                <input type="checkbox" name="remember" id="remember" class="checkbox-input" <?php echo $rememberedUser ? 'checked' : ''; ?>>
                <div class="checkbox-custom"></div>
                <label for="remember" class="checkbox-label">Remember me</label>
            </div>
            
            <!-- Login Button -->
            <button type="submit" class="btn-primary" id="loginBtn">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In
            </button>
        </form>
        
        <!-- Additional Links -->
        <div class="mt-6 text-center">
            <a href="#" class="text-gray-200 hover:text-white transition-colors text-sm">
                <i class="fas fa-question-circle mr-1"></i>
                Forgot Password?
            </a>
        </div>
        
        <!-- Version Info -->
        <div class="mt-8 text-center text-xs text-gray-300 opacity-60">
            Version <?php echo APP_VERSION; ?> | 
            <span id="currentTime"></span>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
    <p class="mt-1">
        <a href="#" class="hover:underline">Privacy Policy</a> | 
        <a href="#" class="hover:underline">Terms of Service</a>
    </p>
</div>

<!-- JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        const currentTimeElement = document.getElementById('currentTime');
        
        // Password toggle functionality
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Form submission with loading state
        loginForm.addEventListener('submit', function(e) {
            loginBtn.classList.add('btn-loading');
            loginBtn.disabled = true;
            
            // Add slight delay for UX
            setTimeout(() => {
                // Form will submit naturally
            }, 500);
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Enter key on any input submits form
            if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
                loginForm.submit();
            }
            
            // Focus username field with Ctrl+U
            if (e.ctrlKey && e.key === 'u') {
                e.preventDefault();
                document.querySelector('input[name="username"]').focus();
            }
            
            // Focus password field with Ctrl+P
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                passwordInput.focus();
            }
        });
        
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-KE', {
                timeZone: 'Africa/Nairobi',
                hour12: true,
                hour: '2-digit',
                minute: '2-digit'
            });
            currentTimeElement.textContent = timeString + ' EAT';
        }
        
        updateTime();
        setInterval(updateTime, 1000);
        
        // Input animations
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
            
            // Floating label effect
            input.addEventListener('input', function() {
                if (this.value.length > 0) {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });
        });
        
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });
        
        // Add subtle parallax effect to floating shapes
        document.addEventListener('mousemove', function(e) {
            const shapes = document.querySelectorAll('.shape');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;
            
            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.5;
                const x = (mouseX - 0.5) * speed * 10;
                const y = (mouseY - 0.5) * speed * 10;
                
                shape.style.transform = `translateX(${x}px) translateY(${y}px)`;
            });
        });
        
        // Preload dashboard for faster navigation
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = 'dashboard.php';
        document.head.appendChild(link);
        
        // Check for system notifications
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
        
        // Service worker registration for PWA capabilities
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(function(error) {
                console.log('Service Worker registration failed:', error);
            });
        }
        
        // Accessibility improvements
        document.addEventListener('keydown', function(e) {
            // Skip to main content with Alt+M
            if (e.altKey && e.key === 'm') {
                e.preventDefault();
                document.querySelector('.glass-card').focus();
            }
        });
        
        // Add focus indicators for keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });
        
        document.addEventListener('mousedown', function() {
            document.body.classList.remove('keyboard-navigation');
        });
        
        // Performance monitoring
        window.addEventListener('load', function() {
            const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
            console.log('Page load time:', loadTime + 'ms');
            
            // Send performance data if analytics is enabled
            if (typeof gtag !== 'undefined') {
                gtag('event', 'page_load_time', {
                    'value': loadTime,
                    'event_category': 'Performance'
                });
            }
        });
    });
    
    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('JavaScript Error:', e.error);
        
        // Show user-friendly error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-error';
        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>An unexpected error occurred. Please refresh the page.';
        
        const form = document.getElementById('loginForm');
        form.parentNode.insertBefore(errorDiv, form);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    });
</script>
</body>
</html>