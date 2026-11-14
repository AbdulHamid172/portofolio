<?php
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get message ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: messages.php');
    exit();
}

$id = (int)$_GET['id'];

// Fetch message
try {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $message = $stmt->fetch();
    
    if (!$message) {
        header('Location: messages.php');
        exit();
    }
    
    // Mark as read
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<?php include '../includes/header.php'; ?>

<style>
    .message-view-page {
        padding: 40px 0;
    }
    
    .message-header {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .back-button {
        margin-bottom: 20px;
    }
    
    .message-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .message-sender h2 {
        font-size: 24px;
        margin-bottom: 5px;
        color: #333;
    }
    
    .message-email {
        color: #667eea;
        font-size: 16px;
    }
    
    .message-date {
        color: #666;
        font-size: 14px;
    }
    
    .message-subject {
        font-size: 20px;
        color: #333;
        margin-bottom: 30px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
        border-left: 4px solid #667eea;
    }
    
    .message-content {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        line-height: 1.8;
        color: #333;
    }
    
    .message-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 30px;
    }
    
    .reply-section {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-top: 30px;
    }
    
    .reply-section h3 {
        margin-bottom: 20px;
        color: #333;
    }
    
    .reply-form .form-group {
        margin-bottom: 20px;
    }
    
    .reply-form label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
    }
    
    .reply-form input,
    .reply-form textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e1e1e1;
        border-radius: 5px;
        font-size: 16px;
    }
    
    .reply-form textarea {
        min-height: 150px;
        resize: vertical;
    }
    
    .btn-reply {
        background: #28a745;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
    }
    
    .btn-reply:hover {
        background: #218838;
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
    }
    
    .btn-delete:hover {
        background: #c82333;
    }
</style>

<div class="message-view-page">
    <div class="container">
        <!-- Back Button -->
        <div class="back-button">
            <a href="messages.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Messages
            </a>
        </div>
        
        <!-- Message Header -->
        <div class="message-header">
            <div class="message-meta">
                <div class="message-sender">
                    <h2><?php echo htmlspecialchars($message['name']); ?></h2>
                    <div class="message-email">
                        <?php echo htmlspecialchars($message['email']); ?>
                    </div>
                </div>
                <div class="message-date">
                    Received on <?php echo date('F j, Y, g:i a', strtotime($message['created_at'])); ?>
                </div>
            </div>
            
            <div class="message-subject">
                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($message['subject']); ?>
            </div>
        </div>
        
        <!-- Message Content -->
        <div class="message-content">
            <h3>Message:</h3>
            <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
        </div>
        
        <!-- Actions -->
        <div class="message-actions">
            <form method="POST" action="delete-message.php" onsubmit="return confirm('Delete this message?');" style="display: inline;">
                <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                <button type="submit" class="btn-delete">
                    <i class="fas fa-trash"></i> Delete Message
                </button>
            </form>
            
            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>" 
               class="btn btn-primary">
                <i class="fas fa-reply"></i> Reply via Email
            </a>
        </div>
        
        <!-- Reply Section (Optional) -->
        <div class="reply-section">
            <h3>Quick Reply</h3>
            <form class="reply-form" id="quickReplyForm">
                <div class="form-group">
                    <label for="replySubject">Subject</label>
                    <input type="text" id="replySubject" value="Re: <?php echo htmlspecialchars($message['subject']); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="replyMessage">Your Reply</label>
                    <textarea id="replyMessage" placeholder="Type your reply here..."></textarea>
                </div>
                
                <button type="button" class="btn-reply" onclick="sendQuickReply()">
                    <i class="fas fa-paper-plane"></i> Send Reply
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function sendQuickReply() {
        const subject = document.getElementById('replySubject').value;
        const message = document.getElementById('replyMessage').value;
        const email = '<?php echo htmlspecialchars($message['email']); ?>';
        
        if (!message.trim()) {
            alert('Please enter your reply message.');
            return;
        }
        
        // Open default email client
        window.location.href = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(message)}`;
    }
    
    // Auto-focus reply textarea
    document.getElementById('replyMessage').focus();
</script>

<?php include '../includes/footer.php'; ?>