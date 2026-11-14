<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

// Get current admin data
$admin = get_admin_user();

if (!$admin) {
    header('Location: dashboard.php');
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $updates = [];
    $params = [];
    
    // Update username
    if (!empty($username) && $username !== $admin['username']) {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $admin['id']]);
        if ($stmt->rowCount() > 0) {
            $message = "Username already taken. Please choose another.";
            $message_type = "error";
        } else {
            $updates[] = "username = ?";
            $params[] = $username;
        }
    }
    
    // Update email
    if (!empty($email) && $email !== $admin['email']) {
        $updates[] = "email = ?";
        $params[] = $email;
    }
    
    // Update phone number
    if (!empty($phone_number) && $phone_number !== $admin['phone_number']) {
        $updates[] = "phone_number = ?";
        $params[] = $phone_number;
    }
    
    // Update password if provided
    if (!empty($current_password) && !empty($new_password)) {
        if (!password_verify($current_password, $admin['password_hash'])) {
            $message = "Current password is incorrect.";
            $message_type = "error";
        } elseif (strlen($new_password) < 6) {
            $message = "New password must be at least 6 characters long.";
            $message_type = "error";
        } elseif ($new_password !== $confirm_password) {
            $message = "Passwords do not match.";
            $message_type = "error";
        } else {
            $updates[] = "password_hash = ?";
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }
    
    // If no errors, update the database
    if (empty($message) && !empty($updates)) {
        try {
            $params[] = $admin['id'];
            $sql = "UPDATE admin_users SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            // Update session username if changed
            if (in_array('username = ?', $updates)) {
                $_SESSION['admin_username'] = $username;
            }
            
            $message = "Profile updated successfully!";
            $message_type = "success";
            
            // Refresh admin data
            $admin = get_admin_user();
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $message_type = "error";
        }
    } elseif (empty($message)) {
        $message = "No changes were made.";
        $message_type = "info";
    }
}
?>

<?php include '../includes/header.php'; ?>

<style>
    .profile-page {
        padding: 40px 0;
        background: #f8f9fa;
        min-height: calc(100vh - 60px);
    }
    
    .profile-container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .profile-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .profile-header .avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 32px;
        color: white;
    }
    
    .profile-header h1 {
        font-size: 1.8rem;
        color: #333;
        margin-bottom: 5px;
    }
    
    .profile-header p {
        color: #666;
        font-size: 14px;
        margin: 0;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
        font-size: 14px;
    }
    
    .form-group label .required {
        color: #dc3545;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e1e1e1;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s ease;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-group .help-text {
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }
    
    .divider {
        margin: 30px 0;
        border-top: 2px solid #f0f0f0;
        position: relative;
    }
    
    .divider span {
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        background: white;
        padding: 0 15px;
        color: #999;
        font-size: 12px;
        text-transform: uppercase;
    }
    
    .btn-save {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    
    .btn-save:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .btn-back {
        display: inline-block;
        padding: 10px 20px;
        background: #f8f9fa;
        color: #666;
        border: 2px solid #e1e1e1;
        border-radius: 8px;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s;
        font-size: 14px;
        font-weight: 500;
    }
    
    .btn-back:hover {
        background: #e9ecef;
    }
    
    .message {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
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
    
    .message.info {
        background: #cce5ff;
        color: #004085;
        border-left-color: #007bff;
    }
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }
    
    @media (max-width: 480px) {
        .profile-container {
            padding: 25px;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn-back {
            text-align: center;
        }
    }
</style>

<div class="profile-page">
    <div class="container">
        <div class="profile-container" data-aos="fade-up">
            <div class="profile-header">
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h1>My Profile</h1>
                <p>Update your account information</p>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($admin['username']); ?>"
                           placeholder="Enter username">
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>"
                           placeholder="Enter email address">
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" 
                           value="<?php echo htmlspecialchars($admin['phone_number'] ?? ''); ?>"
                           placeholder="+254XXXXXXXXX">
                    <div class="help-text">Used for password reset verification</div>
                </div>
                
                <div class="divider">
                    <span>Change Password</span>
                </div>
                
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" 
                           placeholder="Enter current password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" 
                           placeholder="Enter new password">
                    <div class="help-text">Must be at least 6 characters</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm new password">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                    <a href="dashboard.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>