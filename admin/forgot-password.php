<?php
require_once '../config/db.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$message_type = '';
$show_verify_form = false;
$user_email = '';
$reset_token = '';

// Step 1: Check if it's a POST request for sending reset code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_code'])) {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    
    if (empty($email) || empty($phone)) {
        $message = "Please enter both email and phone number.";
        $message_type = "error";
    } else {
        try {
            // Check if admin exists with this email and phone
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND phone_number = ?");
            $stmt->execute([$email, $phone]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                // Generate reset token and code
                $reset_token = bin2hex(random_bytes(50));
                $reset_code = rand(100000, 999999); // 6-digit code
                $reset_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                // Save token and code to database
                $stmt = $pdo->prepare("UPDATE admin_users SET reset_token = ?, reset_token_expiry = ?, reset_code = ?, reset_code_expiry = ? WHERE email = ?");
                $stmt->execute([$reset_token, $reset_expiry, $reset_code, $reset_expiry, $email]);
                
                // Store in session for verification
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_token'] = $reset_token;
                
                // Show verification code form
                $show_verify_form = true;
                $user_email = $email;
                
                // In production, you'd send SMS here
                // For demo, display the code
                $message = "A 6-digit verification code has been sent to your phone number.";
                $message_type = "success";
                $message .= "<br><br><strong style='font-size:18px;'>Your verification code:</strong> 
                             <span style='font-size:28px;font-weight:bold;color:#667eea;display:block;text-align:center;padding:10px;background:#f8f9ff;border-radius:8px;margin-top:10px;'>$reset_code</span>";
                $message .= "<br><small style='color:#999;'>This code expires in 15 minutes.</small>";
                
            } else {
                $message = "No admin account found with that email and phone number combination.";
                $message_type = "error";
            }
            
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Step 2: Verify the reset code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $code = trim($_POST['reset_code'] ?? '');
    $email = $_SESSION['reset_email'] ?? '';
    $token = $_SESSION['reset_token'] ?? '';
    
    if (empty($code) || empty($email)) {
        $message = "Please enter the verification code.";
        $message_type = "error";
        $show_verify_form = true;
    } else {
        try {
            // Check if code matches
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND reset_code = ? AND reset_code_expiry > NOW()");
            $stmt->execute([$email, $code]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                // Code verified - redirect to reset password with token
                header('Location: reset-password.php?token=' . $admin['reset_token'] . '&verified=1');
                exit();
            } else {
                $message = "Invalid or expired verification code. Please request a new code.";
                $message_type = "error";
                $show_verify_form = true;
                $user_email = $email;
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $message_type = "error";
            $show_verify_form = true;
        }
    }
}

// Check if we should show the verify form
if (isset($_SESSION['reset_email']) && !empty($_SESSION['reset_email']) && !$show_verify_form && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $show_verify_form = true;
    $user_email = $_SESSION['reset_email'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Admin Panel</title>
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
        
        .forgot-container {
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
        
        .forgot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .forgot-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .forgot-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .forgot-form {
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
        
        .btn-verify {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            width: 100%;
        }
        
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
        }
        
        .btn-back {
            padding: 12px 25px;
            background: #f8f9fa;
            color: #666;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-back:hover {
            background: #e9ecef;
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
        
        .code-input {
            text-align: center;
            font-size: 32px;
            letter-spacing: 15px;
            font-weight: 600;
            padding: 15px;
            border: 2px solid #667eea;
            border-radius: 8px;
            background: #f8f9ff;
        }
        
        .code-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        
        .instructions {
            background: #f8f9ff;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .instructions h4 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .instructions ul {
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 5px;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .divider span {
            padding: 0 15px;
            color: #999;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .phone-hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #999;
        }
        
        .step.active {
            color: #667eea;
            font-weight: 600;
        }
        
        .step .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e1e1e1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        
        .step.active .step-number {
            background: #667eea;
        }
        
        .step.done .step-number {
            background: #28a745;
        }
        
        .resend-link {
            text-align: center;
            margin-top: 15px;
        }
        
        .resend-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .resend-link a:hover {
            text-decoration: underline;
        }
        
        .email-display {
            background: #f8f9ff;
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            border: 1px solid #e1e1e1;
        }
        
        .email-display strong {
            color: #333;
        }
        
        .email-display span {
            color: #667eea;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <h1><i class="fas fa-key"></i> Forgot Password</h1>
            <p>Reset your admin password with phone verification</p>
        </div>
        
        <div class="forgot-form">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step <?php echo $show_verify_form ? 'done' : 'active'; ?>">
                    <span class="step-number">1</span>
                    <span>Verify</span>
                </div>
                <div class="step <?php echo $show_verify_form ? 'active' : ''; ?>">
                    <span class="step-number">2</span>
                    <span>Code</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span>Reset</span>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$show_verify_form): ?>
                <!-- Step 1: Request Reset -->
                <form method="POST" action="">
                    <input type="hidden" name="send_code" value="1">
                    
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               placeholder="admin@yourdomain.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone_number">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone_number" name="phone_number" class="form-control" required 
                               placeholder="+254XXXXXXXXX" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>">
                        <div class="phone-hint">Enter the phone number registered with your account</div>
                    </div>
                    
                    <button type="submit" class="btn-reset">
                        <i class="fas fa-paper-plane"></i> Send Verification Code
                    </button>
                </form>
                
                <div class="instructions">
                    <h4><i class="fas fa-info-circle"></i> How it works</h4>
                    <ul>
                        <li>Enter your registered email and phone number</li>
                        <li>A 6-digit code will be sent to your phone</li>
                        <li>Enter the code to verify your identity</li>
                        <li>Set a new password for your account</li>
                    </ul>
                </div>
                
            <?php else: ?>
                <!-- Step 2: Verify Code -->
                <div class="email-display">
                    <strong>Verifying for:</strong> <span><?php echo htmlspecialchars($user_email); ?></span>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="verify_code" value="1">
                    
                    <div class="form-group">
                        <label for="reset_code">Verification Code <span class="required">*</span></label>
                        <input type="text" id="reset_code" name="reset_code" class="code-input" required 
                               placeholder="------" maxlength="6" pattern="[0-9]{6}" autofocus>
                        <div style="text-align: center; margin-top: 8px; font-size: 13px; color: #666;">
                            Enter the 6-digit code sent to your phone
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-verify">
                        <i class="fas fa-check-circle"></i> Verify Code
                    </button>
                </form>
                
                <div class="divider">
                    <span>or</span>
                </div>
                
                <div class="resend-link">
                    <a href="forgot-password.php" onclick="return confirm('Request a new code? This will invalidate the previous code.');">
                        <i class="fas fa-redo"></i> Request New Code
                    </a>
                </div>
                
                <div class="instructions">
                    <h4><i class="fas fa-mobile-alt"></i> Check your phone</h4>
                    <ul>
                        <li>A 6-digit code was sent to your registered phone</li>
                        <li>The code expires in 15 minutes</li>
                        <li>If you didn't receive the code, request a new one</li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="back-links">
                <a href="login.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
                <a href="/" class="back-link">
                    <i class="fas fa-home"></i> Back to Site
                </a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-format code input
            const codeInput = document.getElementById('reset_code');
            if (codeInput) {
                codeInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value.length === 6) {
                        // Auto-submit when 6 digits entered
                        this.form.submit();
                    }
                });
                
                // Auto-focus code input
                codeInput.focus();
            }
            
            // Phone number formatting hint
            const phoneInput = document.getElementById('phone_number');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9+]/g, '');
                });
            }
        });
    </script>
</body>
</html>