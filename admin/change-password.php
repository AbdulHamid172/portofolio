<?php
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in all password fields.";
        $message_type = "error";
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
        $message_type = "error";
    } elseif (strlen($new_password) < 6) {
        $message = "New password must be at least 6 characters long.";
        $message_type = "error";
    } else {
        try {
            // Get current admin
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($current_password, $admin['password_hash'])) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['admin_id']]);
                
                $message = "Password changed successfully!";
                $message_type = "success";
                
                // Clear form
                $_POST = [];
                
            } else {
                $message = "Current password is incorrect.";
                $message_type = "error";
            }
            
        } catch (PDOException $e) {
            $message = "Database error. Please try again.";
            $message_type = "error";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<style>
    .change-password-page {
        padding: 40px 0;
        background: #f8f9fa;
        min-height: calc(100vh - 60px);
    }
    
    .password-container {
        max-width: 500px;
        margin: 0 auto;
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .page-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .page-header h1 {
        font-size: 2rem;
        color: #333;
        margin-bottom: 10px;
    }
    
    .page-header p {
        color: #666;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e1e1e1;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s;
    }
    
    .form-group input:focus {
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
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn-change {
        flex: 1;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 15px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-change:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    
    .btn-change:disabled {
        background: #cccccc;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .btn-back {
        padding: 15px 30px;
        background: #f8f9fa;
        color: #666;
        border: 2px solid #e1e1e1;
        border-radius: 8px;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s;
    }
    
    .btn-back:hover {
        background: #e9ecef;
    }
    
    .message {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        border-left: 5px solid;
        animation: slideIn 0.5s ease;
    }
    
    @keyframes slideIn {
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
    
    .password-tips {
        background: #f8f9ff;
        padding: 20px;
        border-radius: 10px;
        margin-top: 30px;
        font-size: 14px;
        color: #666;
    }
    
    .password-tips h4 {
        margin-top: 0;
        color: #333;
        margin-bottom: 10px;
    }
    
    .password-tips ul {
        padding-left: 20px;
        margin: 0;
    }
    
    .password-tips li {
        margin-bottom: 8px;
    }
</style>

<div class="change-password-page">
    <div class="container">
        <div class="password-container" data-aos="fade-up">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-lock"></i> Change Password</h1>
                <p>Update your admin password</p>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="changePasswordForm">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required 
                           placeholder="Enter current password" autocomplete="current-password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required 
                           placeholder="Enter new password" autocomplete="new-password">
                    <div class="password-strength">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText">Password strength: Very Weak</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           placeholder="Confirm new password" autocomplete="new-password">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-change" id="submitBtn">
                        <i class="fas fa-save"></i> Change Password
                    </button>
                    <a href="dashboard.php" class="btn-back">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
            
            <div class="password-tips">
                <h4><i class="fas fa-lightbulb"></i> Password Tips</h4>
                <ul>
                    <li>Use at least 6 characters</li>
                    <li>Mix uppercase and lowercase letters</li>
                    <li>Include numbers and special characters</li>
                    <li>Avoid common words and personal information</li>
                    <li>Use a unique password not used elsewhere</li>
                </ul>
            </div>
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
        
        function checkPasswordStrength(password) {
            let strength = 0;
            
            // Length check
            if (password.length >= 6) strength += 25;
            
            // Mixed case
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
            
            // Numbers
            if (/\d/.test(password)) strength += 25;
            
            // Special characters
            if (/[^a-zA-Z0-9]/.test(password)) strength += 25;
            
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
            
            return strength;
        }
        
        function validateForm() {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Check if all fields are filled
            if (!currentPassword || !newPassword || !confirmPassword) {
                submitBtn.disabled = true;
                return;
            }
            
            // Check if passwords match
            if (newPassword !== confirmPassword) {
                submitBtn.disabled = true;
                return;
            }
            
            // Check password strength
            const strength = checkPasswordStrength(newPassword);
            
            // Enable submit button only if strength is good
            if (strength >= 50) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }
        
        // Event listeners
        document.getElementById('current_password').addEventListener('input', validateForm);
        newPasswordInput.addEventListener('input', validateForm);
        confirmPasswordInput.addEventListener('input', validateForm);
        
        // Form submission
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            if (submitBtn.disabled) {
                e.preventDefault();
                alert('Please meet all password requirements.');
            } else {
                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing...';
                submitBtn.disabled = true;
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>