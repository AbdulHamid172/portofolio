<?php
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total messages for pagination
$stmt = $pdo->query("SELECT COUNT(*) as total FROM messages");
$total_messages = $stmt->fetch()['total'];
$total_pages = ceil($total_messages / $limit);

// Get messages for current page
$stmt = $pdo->prepare("SELECT * FROM messages ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll();

// Mark message as read if view parameter is present
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $id = (int)$_GET['view'];
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
}
?>
<?php include '../includes/header.php'; ?>

<style>
    .messages-page {
        padding: 40px 0;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    .page-title h1 {
        font-size: 32px;
        color: #333;
        margin-bottom: 5px;
    }
    
    .messages-actions {
        display: flex;
        gap: 10px;
    }
    
    .search-box {
        position: relative;
        margin-bottom: 20px;
    }
    
    .search-box input {
        width: 100%;
        padding: 12px 40px 12px 15px;
        border: 2px solid #e1e1e1;
        border-radius: 8px;
        font-size: 16px;
    }
    
    .search-box i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
    }
    
    .messages-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .messages-table th,
    .messages-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .messages-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #333;
    }
    
    .messages-table tr:hover {
        background: #f8f9fa;
    }
    
    .message-preview {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #666;
    }
    
    .status-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .status-unread {
        background: #ffc107;
    }
    
    .status-read {
        background: #28a745;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    
    .btn-action {
        padding: 5px 10px;
        border-radius: 3px;
        text-decoration: none;
        font-size: 12px;
        transition: all 0.3s;
    }
    
    .btn-view {
        background: #667eea;
        color: white;
    }
    
    .btn-view:hover {
        background: #5a67d8;
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
        cursor: pointer;
    }
    
    .btn-delete:hover {
        background: #c82333;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin-top: 30px;
    }
    
    .page-link {
        padding: 8px 15px;
        background: white;
        border: 1px solid #ddd;
        text-decoration: none;
        color: #333;
        border-radius: 5px;
        transition: all 0.3s;
    }
    
    .page-link:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    .page-link.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: #666;
    }
    
    .empty-state i {
        font-size: 60px;
        margin-bottom: 20px;
        color: #ddd;
    }
</style>

<div class="messages-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h1>Messages</h1>
                <p>Manage contact form submissions</p>
            </div>
            <div class="messages-actions">
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <!-- Search Box -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search messages...">
            <i class="fas fa-search"></i>
        </div>
        
        <!-- Messages Table -->
        <?php if (empty($messages)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Messages Yet</h3>
                <p>Contact form submissions will appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="messages-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message Preview</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="messagesTable">
                        <?php foreach ($messages as $message): ?>
                            <tr>
                                <td>
                                    <span class="status-indicator <?php echo $message['is_read'] ? 'status-read' : 'status-unread'; ?>"></span>
                                    <?php echo $message['is_read'] ? 'Read' : 'Unread'; ?>
                                </td>
                                <td><?php echo htmlspecialchars($message['name']); ?></td>
                                <td><?php echo htmlspecialchars($message['email']); ?></td>
                                <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                <td class="message-preview"><?php echo htmlspecialchars(substr($message['message'], 0, 50)); ?>...</td>
                                <td><?php echo date('M d, Y', strtotime($message['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view-message.php?id=<?php echo $message['id']; ?>" class="btn-action btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <form method="POST" action="delete-message.php" onsubmit="return confirm('Delete this message?');" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                                            <button type="submit" class="btn-action btn-delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#messagesTable tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Auto refresh every 30 seconds for new messages
    setInterval(() => {
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const newDoc = new DOMParser().parseFromString(html, 'text/html');
                const newTable = newDoc.querySelector('#messagesTable');
                if (newTable) {
                    document.querySelector('#messagesTable').innerHTML = newTable.innerHTML;
                }
            });
    }, 30000);
</script>

<?php include '../includes/footer.php'; ?>