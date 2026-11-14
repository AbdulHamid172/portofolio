<?php
require_once '../config/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$remember_username = '';

// Check if username is stored in cookie
if (isset($_COOKIE['admin_username'])) {
    $remember_username = $_COOKIE['admin_username'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_username']) ? true : false;
    
    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_id'] = $admin['id'];
                
                // Set cookie if remember username is checked
                if ($remember) {
                    setcookie('admin_username', $username, time() + (86400 * 30), '/'); // 30 days
                } else {
                    // Remove cookie if exists
                    if (isset($_COOKIE['admin_username'])) {
                        setcookie('admin_username', '', time() - 3600, '/');
                    }
                }
                
                // Update last login
                $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$admin['id']]);
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again.';
        }
    } else {
        $error = 'Please enter both username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .login-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group label .required {
            color: #dc3545;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-control-icon {
            position: relative;
        }
        
        .form-control-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .form-control-icon .form-control {
            padding-left: 45px;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: -5px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .form-options .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .form-options .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        .form-options .checkbox-group label {
            color: #666;
            font-size: 14px;
            cursor: pointer;
            margin: 0;
            font-weight: 400;
        }
        
        .form-options .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .form-options .forgot-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-login i {
            margin-right: 8px;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-help {
            background: #f8f9ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }
        
        .login-help h4 {
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .login-help ul {
            padding-left: 20px;
            margin: 0;
        }
        
        .login-help li {
            margin-bottom: 5px;
        }
        
        .back-home {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .back-home a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
        }
        
        .back-home a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .remember-username-info {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
            margin-left: 26px;
        }
        
        .login-divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .login-divider::before,
        .login-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .login-divider span {
            padding: 0 15px;
            color: #999;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .quick-login-hint {
            text-align: center;
            font-size: 13px;
            color: #999;
        }
        
        .quick-login-hint strong {
            color: #667eea;
            font-weight: 600;
        }

        /* Mobile Responsive */
        @media (max-width: 480px) {
            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .login-container {
                margin: 10px;
            }
            
            .login-header {
                padding: 20px;
            }
            
            .login-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-lock"></i> Admin Login</h1>
            <p>Portfolio Management System</p>
        </div>
        
        <div class="login-form">
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <div class="form-control-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" class="form-control" required autofocus 
                               placeholder="Enter username" value="<?php echo htmlspecialchars($remember_username); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="form-control-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" required 
                               placeholder="Enter password">
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-group">
                        <input type="checkbox" id="remember_username" name="remember_username" 
                               <?php echo !empty($remember_username) ? 'checked' : ''; ?>>
                        <label for="remember_username">Remember Username</label>
                    </label>
                    <a href="forgot-password.php" class="forgot-link">
                        <i class="fas fa-question-circle"></i> Forgot Password?
                    </a>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <?php if (!empty($remember_username)): ?>
                <div class="remember-username-info">
                    <i class="fas fa-info-circle"></i> Username remembered. Uncheck to forget.
                </div>
            <?php endif; ?>
            
            <div class="login-divider">
                <span>Need Help?</span>
            </div>
            
            <div class="login-help">
                <h4><i class="fas fa-lightbulb"></i> Having trouble logging in?</h4>
                <ul>
                    <li>Check your username and password</li>
                    <li>Use "Forgot Password" to reset</li>
                    <li>Contact support if issues persist</li>
                    <li>Username is case-sensitive</li>
                </ul>
            </div>
            
            <div class="back-home">
                <a href="/">
                    <i class="fas fa-arrow-left"></i> Back to Portfolio
                </a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');
            const rememberCheckbox = document.getElementById('remember_username');
            
            // Auto-focus username if empty, else password
            if (usernameInput.value) {
                passwordInput.focus();
            } else {
                usernameInput.focus();
            }
            
            // Add focus animation to inputs
            document.querySelectorAll('.form-control').forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });
            
            // Form validation with loading state
            loginForm.addEventListener('submit', function(e) {
                const username = usernameInput.value.trim();
                const password = passwordInput.value.trim();
                
                if (!username || !password) {
                    e.preventDefault();
                    alert('Please enter both username and password.');
                    return;
                }
                
                // Show loading state
                loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
                loginBtn.disabled = true;
                
                // Re-enable button if form submission fails
                setTimeout(() => {
                    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Login';
                    loginBtn.disabled = false;
                }, 10000);
            });
            
            // Keyboard shortcut: Enter to submit
            passwordInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    loginForm.submit();
                }
            });
            
            // Remember checkbox tooltip
            rememberCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    // Show a subtle notification
                    const info = document.querySelector('.remember-username-info');
                    if (info) {
                        info.style.display = 'block';
                    }
                }
            });
            
            // Pre-fill username from cookie and auto-check remember
            const savedUsername = '<?php echo htmlspecialchars($remember_username); ?>';
            if (savedUsername) {
                rememberCheckbox.checked = true;
            }
        });
        
        // Toggle password visibility (optional feature)
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.form-control-icon .fa-lock');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-lock';
            }
        }
        
        // Add password toggle button (optional)
        document.addEventListener('DOMContentLoaded', function() {
            const passwordContainer = document.querySelector('.form-control-icon');
            if (passwordContainer) {
                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
                toggleBtn.style.cssText = `
                    position: absolute;
                    right: 15px;
                    top: 50%;
                    transform: translateY(-50%);
                    background: none;
                    border: none;
                    color: #999;
                    cursor: pointer;
                    padding: 5px;
                    font-size: 16px;
                `;
                toggleBtn.title = 'Toggle password visibility';
                toggleBtn.onclick = function() {
                    const input = this.parentElement.querySelector('input');
                    const icon = this.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.className = 'fas fa-eye-slash';
                    } else {
                        input.type = 'password';
                        icon.className = 'fas fa-eye';
                    }
                };
                passwordContainer.appendChild(toggleBtn);
            }
        });
    </script>
</body>
</html>