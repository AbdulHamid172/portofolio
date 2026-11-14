<?php
require_once '../config/db.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$message_type = '';
$token_valid = false;
$admin_data = null;
$token = $_GET['token'] ?? '';

// Check if verified flag is present
$verified = isset($_GET['verified']) && $_GET['verified'] == 1;

// Check if token exists and is valid
if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->execute([$token]);
        $admin_data = $stmt->fetch();
        
        if ($admin_data) {
            $token_valid = true;
            
            // Handle password reset
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (empty($new_password) || empty($confirm_password)) {
                    $message = "Please fill in all password fields.";
                    $message_type = "error";
                } elseif ($new_password !== $confirm_password) {
                    $message = "Passwords do not match.";
                    $message_type = "error";
                } elseif (strlen($new_password) < 6) {
                    $message = "Password must be at least 6 characters long.";
                    $message_type = "error";
                } else {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password and clear reset token
                    $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL, reset_code = NULL, reset_code_expiry = NULL WHERE reset_token = ?");
                    $stmt->execute([$hashed_password, $token]);
                    
                    // Show success with username and password
                    $message = "✅ Password reset successfully!<br><br>";
                    $message .= "<div class='credentials-box'>";
                    $message .= "<h4><i class='fas fa-user-check'></i> Your Login Credentials</h4>";
                    $message .= "<div class='credential-row'>";
                    $message .= "<span class='label'>Username:</span>";
                    $message .= "<span class='value' id='usernameDisplay'>" . htmlspecialchars($admin_data['username']) . "</span>";
                    $message .= "<button class='copy-btn' onclick='copyText(\"usernameDisplay\")'><i class='fas fa-copy'></i></button>";
                    $message .= "</div>";
                    $message .= "<div class='credential-row'>";
                    $message .= "<span class='label'>Password:</span>";
                    $message .= "<span class='value' id='passwordDisplay'>" . htmlspecialchars($new_password) . "</span>";
                    $message .= "<button class='copy-btn' onclick='copyText(\"passwordDisplay\")'><i class='fas fa-copy'></i></button>";
                    $message .= "</div>";
                    $message .= "</div>";
                    $message .= "<p style='color: #666; font-size: 14px;'><i class='fas fa-exclamation-triangle' style='color: #ffc107;'></i> Please save these credentials and keep them secure.</p>";
                    $message_type = "success";
                    $token_valid = false; // Token is now used
                }
            }
            
        } else {
            $message = "Invalid or expired reset token. Please request a new reset link.";
            $message_type = "error";
        }
        
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
        $message_type = "error";
    }
} else {
    $message = "No reset token provided.";
    $message_type = "error";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Admin Panel</title>
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
        
        .reset-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
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
        
        .reset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .reset-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .reset-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .reset-form {
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
        
        .password-strength {
            height: 4px;
            background: #eee;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0%;
            background: #dc3545;
            transition: all 0.3s;
        }
        
        .strength-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .btn-reset {
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
        
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-reset:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
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
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .message.info {
            background: #cce5ff;
            color: #004085;
            border-left-color: #007bff;
        }
        
        .back-links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .back-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .password-requirements {
            background: #f8f9ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .password-requirements h4 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .password-requirements ul {
            padding-left: 20px;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
        }
        
        .requirement-met {
            color: #28a745;
        }
        
        .requirement-not-met {
            color: #dc3545;
        }
        
        .success-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .success-actions .btn {
            flex: 1;
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-copy-all {
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            flex: 1;
        }
        
        .btn-copy-all:hover {
            background: #218838;
        }
        
        .credentials-box {
            background: #f8f9ff;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #667eea;
        }
        
        .credentials-box h4 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .credential-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            padding: 10px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e1e1e1;
        }
        
        .credential-row .label {
            font-weight: 500;
            color: #555;
            min-width: 80px;
        }
        
        .credential-row .value {
            flex: 1;
            font-family: monospace;
            font-size: 16px;
            color: #333;
            word-break: break-all;
        }
        
        .credential-row .copy-btn {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .credential-row .copy-btn:hover {
            background: #667eea;
            color: white;
        }

        .verified-badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        @media (max-width: 480px) {
            .success-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1><i class="fas fa-lock"></i> Reset Password</h1>
            <p>Create a new secure password</p>
        </div>
        
        <div class="reset-form">
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                    
                    <?php if ($message_type === 'success'): ?>
                        <div class="success-actions">
                            <a href="login.php" class="btn btn-login">
                                <i class="fas fa-sign-in-alt"></i> Go to Login
                            </a>
                            <button onclick="copyAllCredentials()" class="btn-copy-all">
                                <i class="fas fa-copy"></i> Copy All
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($token_valid && $verified): ?>
                <div class="verified-badge">
                    <i class="fas fa-check-circle"></i> Identity Verified
                </div>
                
                <div class="password-requirements">
                    <h4><i class="fas fa-shield-alt"></i> Password Requirements</h4>
                    <ul>
                        <li id="req-length" class="requirement-not-met">At least 6 characters</li>
                        <li id="req-upper" class="requirement-not-met">At least one uppercase letter</li>
                        <li id="req-lower" class="requirement-not-met">At least one lowercase letter</li>
                        <li id="req-number" class="requirement-not-met">At least one number</li>
                        <li id="req-match" class="requirement-not-met">Passwords must match</li>
                    </ul>
                </div>
                
                <form method="POST" action="" id="resetForm">
                    <input type="hidden" name="reset_password" value="1">
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required 
                               placeholder="Enter new password" autocomplete="new-password">
                        <div class="password-strength">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText">Password strength: Very Weak</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required 
                               placeholder="Confirm new password" autocomplete="new-password">
                    </div>
                    
                    <button type="submit" class="btn-reset" id="submitBtn" disabled>
                        <i class="fas fa-save"></i> Reset Password
                    </button>
                </form>
            <?php elseif (!$message || $message_type === 'error'): ?>
                <div class="message error" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php if (empty($token)): ?>
                        No reset token provided. Please request a new reset link.
                    <?php elseif (!$verified): ?>
                        Please verify your identity first through the forgot password page.
                    <?php else: ?>
                        Invalid or expired reset token. Please request a new reset link.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="back-links">
                <a href="forgot-password.php" class="back-link">
                    <i class="fas fa-key"></i> Request New Link
                </a>
                <a href="login.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const submitBtn = document.getElementById('submitBtn');
            const reqLength = document.getElementById('req-length');
            const reqUpper = document.getElementById('req-upper');
            const reqLower = document.getElementById('req-lower');
            const reqNumber = document.getElementById('req-number');
            const reqMatch = document.getElementById('req-match');
            
            if (!newPasswordInput) return;
            
            function checkPasswordStrength(password) {
                let strength = 0;
                let requirements = {
                    length: password.length >= 6,
                    upper: /[A-Z]/.test(password),
                    lower: /[a-z]/.test(password),
                    number: /\d/.test(password)
                };
                
                // Update requirement indicators
                reqLength.className = requirements.length ? 'requirement-met' : 'requirement-not-met';
                reqUpper.className = requirements.upper ? 'requirement-met' : 'requirement-not-met';
                reqLower.className = requirements.lower ? 'requirement-met' : 'requirement-not-met';
                reqNumber.className = requirements.number ? 'requirement-met' : 'requirement-not-met';
                
                // Calculate strength
                if (requirements.length) strength += 25;
                if (requirements.upper && requirements.lower) strength += 25;
                if (requirements.number) strength += 25;
                if (password.length >= 10) strength += 25;
                
                // Update strength bar
                strengthBar.style.width = strength + '%';
                
                // Update strength text and color
                if (strength < 25) {
                    strengthBar.style.background = '#dc3545';
                    strengthText.textContent = 'Password strength: Very Weak';
                } else if (strength < 50) {
                    strengthBar.style.background = '#ffc107';
                    strengthText.textContent = 'Password strength: Weak';
                } else if (strength < 75) {
                    strengthBar.style.background = '#17a2b8';
                    strengthText.textContent = 'Password strength: Good';
                } else {
                    strengthBar.style.background = '#28a745';
                    strengthText.textContent = 'Password strength: Strong';
                }
                
                return {
                    strength: strength,
                    requirements: requirements
                };
            }
            
            function checkPasswordMatch() {
                const password = newPasswordInput.value;
                const confirm = confirmPasswordInput.value;
                
                if (password && confirm) {
                    if (password === confirm) {
                        reqMatch.className = 'requirement-met';
                        return true;
                    } else {
                        reqMatch.className = 'requirement-not-met';
                        return false;
                    }
                }
                return false;
            }
            
            function validateForm() {
                const password = newPasswordInput.value;
                const isMatch = checkPasswordMatch();
                const result = checkPasswordStrength(password);
                
                // Enable submit button only if all requirements are met
                const allMet = result.requirements.length && 
                              result.requirements.upper && 
                              result.requirements.lower && 
                              result.requirements.number && 
                              isMatch;
                
                submitBtn.disabled = !allMet;
            }
            
            // Event listeners
            newPasswordInput.addEventListener('input', validateForm);
            confirmPasswordInput.addEventListener('input', validateForm);
            
            // Initial validation
            validateForm();
            
            // Form submission
            document.getElementById('resetForm').addEventListener('submit', function(e) {
                if (submitBtn.disabled) {
                    e.preventDefault();
                    alert('Please meet all password requirements.');
                } else {
                    // Show loading state
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
                    submitBtn.disabled = true;
                }
            });
        });
        
        // Copy single text function
        function copyText(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            
            navigator.clipboard.writeText(text).then(() => {
                const btn = event.target.closest('.copy-btn');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.style.color = '#28a745';
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.style.color = '#667eea';
                }, 2000);
            }).catch(err => {
                // Fallback
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                textArea.remove();
                alert('Copied!');
            });
        }
        
        // Copy all credentials
        function copyAllCredentials() {
            const username = document.getElementById('usernameDisplay')?.textContent || '';
            const password = document.getElementById('passwordDisplay')?.textContent || '';
            const text = `Username: ${username}\nPassword: ${password}`;
            
            navigator.clipboard.writeText(text).then(() => {
                const btn = document.querySelector('.btn-copy-all');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                btn.style.background = '#218838';
                
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.style.background = '#28a745';
                }, 3000);
            }).catch(err => {
                alert('Failed to copy. Please copy manually.');
            });
        }
    </script>
</body>
</html>