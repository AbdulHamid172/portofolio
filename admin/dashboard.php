<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get statistics
try {
    // Total messages
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages");
    $total_messages = $stmt->fetch()['total'];
    
    // Unread messages
    $stmt = $pdo->query("SELECT COUNT(*) as unread FROM messages WHERE is_read = 0");
    $unread_messages = $stmt->fetch()['unread'];
    
    // Today's messages
    $stmt = $pdo->query("SELECT COUNT(*) as today FROM messages WHERE DATE(created_at) = CURDATE()");
    $today_messages = $stmt->fetch()['today'];
    
    // Total projects
    $total_projects = get_total_projects();
    
    // Total skills
    $total_skills = get_total_skills();
    
    // Total images
    $image_categories = ['profile', 'projects', 'background', 'icons'];
    $total_images = 0;
    foreach ($image_categories as $category) {
        $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/{$category}/";
        if (file_exists($dir)) {
            $files = scandir($dir);
            $category_count = count(array_filter($files, function($file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                return $file !== '.' && $file !== '..' && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
            }));
            $total_images += $category_count;
        }
    }
    
    // Recent messages
    $stmt = $pdo->prepare("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_messages = $stmt->fetchAll();
    
    // Recent projects
    $stmt = $pdo->prepare("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $recent_projects = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<?php include '../includes/header.php'; ?>

<style>
    .admin-dashboard {
        padding: 30px 0;
        background: #f8f9fa;
        min-height: calc(100vh - 60px);
    }
    
    .dashboard-container {
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    /* Welcome Section */
    .welcome-section {
        background: white;
        padding: 25px 30px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .welcome-text h1 {
        font-size: 24px;
        color: #333;
        margin-bottom: 5px;
    }
    
    .welcome-text p {
        color: #666;
        font-size: 15px;
        margin: 0;
    }
    
    .welcome-text .greeting-time {
        color: #667eea;
        font-weight: 600;
    }
    
    .quick-actions-top {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .btn-quick {
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-quick-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-quick-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .btn-quick-success {
        background: #28a745;
        color: white;
    }
    
    .btn-quick-success:hover {
        background: #218838;
        transform: translateY(-2px);
    }
    
    .btn-quick-warning {
        background: #ffc107;
        color: #333;
    }
    
    .btn-quick-warning:hover {
        background: #e0a800;
        transform: translateY(-2px);
    }
    
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px 25px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    
    .stat-icon.blue {
        background: #e8edff;
        color: #667eea;
    }
    
    .stat-icon.green {
        background: #d4edda;
        color: #28a745;
    }
    
    .stat-icon.orange {
        background: #fff3cd;
        color: #ffc107;
    }
    
    .stat-icon.red {
        background: #f8d7da;
        color: #dc3545;
    }
    
    .stat-icon.purple {
        background: #e8d5f5;
        color: #764ba2;
    }
    
    .stat-info h3 {
        font-size: 24px;
        font-weight: 700;
        color: #333;
        margin: 0;
        font-family: 'Raleway', sans-serif;
    }
    
    .stat-info p {
        margin: 0;
        font-size: 13px;
        color: #888;
    }
    
    .stat-info .trend {
        font-size: 11px;
        padding: 2px 10px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 3px;
    }
    
    .trend.up {
        background: #d4edda;
        color: #155724;
    }
    
    .trend.down {
        background: #f8d7da;
        color: #721c24;
    }
    
    .trend.neutral {
        background: #e2e3e5;
        color: #383d41;
    }
    
    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
        margin-bottom: 30px;
    }
    
    @media (max-width: 992px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .dashboard-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .section-title {
        font-size: 18px;
        color: #333;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title i {
        color: #667eea;
    }
    
    .view-all {
        color: #667eea;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: color 0.3s;
    }
    
    .view-all:hover {
        color: #764ba2;
        text-decoration: underline;
    }
    
    /* Messages Table */
    .messages-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .messages-table th {
        background: #f8f9fa;
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #e9ecef;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .messages-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e9ecef;
        color: #555;
        font-size: 13px;
    }
    
    .messages-table tr:hover {
        background: #f8f9fa;
    }
    
    .status-badge {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }
    
    .status-read {
        background: #d4edda;
        color: #155724;
    }
    
    .status-unread {
        background: #fff3cd;
        color: #856404;
    }
    
    .btn-small {
        padding: 4px 12px;
        border-radius: 5px;
        font-size: 12px;
        text-decoration: none;
        transition: all 0.3s;
        display: inline-block;
    }
    
    .btn-small-primary {
        background: #667eea;
        color: white;
    }
    
    .btn-small-primary:hover {
        background: #5a67d8;
    }
    
    /* Quick Actions Grid */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    
    .action-btn {
        padding: 15px;
        border-radius: 10px;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s;
        border: 2px solid #e9ecef;
        background: white;
    }
    
    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .action-btn i {
        font-size: 24px;
        display: block;
        margin-bottom: 8px;
    }
    
    .action-btn .label {
        font-size: 12px;
        font-weight: 500;
        color: #555;
    }
    
    .action-btn.blue {
        border-color: #667eea;
    }
    
    .action-btn.blue i {
        color: #667eea;
    }
    
    .action-btn.green {
        border-color: #28a745;
    }
    
    .action-btn.green i {
        color: #28a745;
    }
    
    .action-btn.orange {
        border-color: #ffc107;
    }
    
    .action-btn.orange i {
        color: #ffc107;
    }
    
    .action-btn.red {
        border-color: #dc3545;
    }
    
    .action-btn.red i {
        color: #dc3545;
    }
    
    .action-btn.purple {
        border-color: #764ba2;
    }
    
    .action-btn.purple i {
        color: #764ba2;
    }
    
    .action-btn:hover.blue {
        background: #667eea;
        color: white;
    }
    
    .action-btn:hover.blue i {
        color: white;
    }
    
    .action-btn:hover.green {
        background: #28a745;
        color: white;
    }
    
    .action-btn:hover.green i {
        color: white;
    }
    
    .action-btn:hover.orange {
        background: #ffc107;
        color: white;
    }
    
    .action-btn:hover.orange i {
        color: white;
    }
    
    .action-btn:hover.red {
        background: #dc3545;
        color: white;
    }
    
    .action-btn:hover.red i {
        color: white;
    }
    
    .action-btn:hover.purple {
        background: #764ba2;
        color: white;
    }
    
    .action-btn:hover.purple i {
        color: white;
    }
    
    .action-btn:hover .label {
        color: white;
    }
    
    /* Recent Projects */
    .recent-project-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .recent-project-item:last-child {
        border-bottom: none;
    }
    
    .recent-project-img {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
        background: #e9ecef;
    }
    
    .recent-project-info {
        flex: 1;
    }
    
    .recent-project-info h4 {
        margin: 0 0 3px 0;
        font-size: 14px;
        color: #333;
    }
    
    .recent-project-info p {
        margin: 0;
        font-size: 12px;
        color: #888;
    }
    
    .empty-state {
        text-align: center;
        padding: 30px 20px;
        color: #666;
    }
    
    .empty-state i {
        font-size: 40px;
        color: #ddd;
        margin-bottom: 15px;
        display: block;
    }
    
    .empty-state h4 {
        margin-bottom: 5px;
        color: #555;
    }
    
    .empty-state p {
        font-size: 14px;
        margin: 0;
    }
    
    /* System Info */
    .system-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 15px;
    }
    
    .system-info-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 13px;
    }
    
    .system-info-item .label {
        color: #888;
    }
    
    .system-info-item .value {
        color: #333;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }
        
        .quick-actions-grid {
            grid-template-columns: 1fr 1fr;
        }
        
        .system-info {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-actions-grid {
            grid-template-columns: 1fr;
        }
        
        .welcome-section {
            flex-direction: column;
            text-align: center;
        }
        
        .quick-actions-top {
            justify-content: center;
        }
    }
</style>

<div class="admin-dashboard">
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section" data-aos="fade-up">
            <div class="welcome-text">
                <h1>
                    <span class="greeting-time">
                        <?php
                            $hour = date('H');
                            if ($hour < 12) echo '🌅 Good Morning';
                            elseif ($hour < 17) echo '☀️ Good Afternoon';
                            else echo '🌙 Good Evening';
                        ?>
                    </span>
                    , <?php echo htmlspecialchars($_SESSION['admin_username']); ?> 👋
                </h1>
                <p>Here's what's happening with your portfolio today.</p>
            </div>
            <div class="quick-actions-top">
                <a href="upload-image.php" class="btn-quick btn-quick-primary">
                    <i class="fas fa-upload"></i> Upload
                </a>
                <a href="messages.php" class="btn-quick btn-quick-success">
                    <i class="fas fa-envelope"></i> Messages
                    <?php if ($unread_messages > 0): ?>
                        <span style="background: red; color: white; border-radius: 50%; padding: 1px 8px; font-size: 11px;">
                            <?php echo $unread_messages; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="manage-projects.php" class="btn-quick btn-quick-warning">
                    <i class="fas fa-plus"></i> Add Project
                </a>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card" data-aos="fade-up">
                <div class="stat-icon blue">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_projects; ?></h3>
                    <p>Total Projects</p>
                </div>
            </div>
            
            <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-icon green">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_messages; ?></h3>
                    <p>Total Messages</p>
                    <?php if ($unread_messages > 0): ?>
                        <span class="trend down"><?php echo $unread_messages; ?> unread</span>
                    <?php else: ?>
                        <span class="trend up">All read</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-icon purple">
                    <i class="fas fa-code"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_skills; ?></h3>
                    <p>Total Skills</p>
                </div>
            </div>
            
            <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-icon orange">
                    <i class="fas fa-images"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_images; ?></h3>
                    <p>Total Images</p>
                </div>
            </div>
        </div>
        
        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Recent Messages -->
                <div class="dashboard-section" data-aos="fade-up">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-inbox"></i>
                            Recent Messages
                        </h3>
                        <a href="messages.php" class="view-all">View All →</a>
                    </div>
                    
                    <?php if (empty($recent_messages)): ?>
                        <div class="empty-state">
                            <i class="fas fa-envelope-open"></i>
                            <h4>No Messages</h4>
                            <p>Contact form submissions will appear here.</p>
                        </div>
                    <?php else: ?>
                        <table class="messages-table">
                            <thead>
                                <tr>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_messages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message['name']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($message['subject'], 0, 25)) . (strlen($message['subject']) > 25 ? '...' : ''); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $message['is_read'] ? 'status-read' : 'status-unread'; ?>">
                                                <?php echo $message['is_read'] ? 'Read' : 'Unread'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="view-message.php?id=<?php echo $message['id']; ?>" class="btn-small btn-small-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Projects -->
                <div class="dashboard-section" data-aos="fade-up" data-aos-delay="100">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-code"></i>
                            Recent Projects
                        </h3>
                        <a href="manage-projects.php" class="view-all">View All →</a>
                    </div>
                    
                    <?php if (empty($recent_projects)): ?>
                        <div class="empty-state">
                            <i class="fas fa-briefcase"></i>
                            <h4>No Projects</h4>
                            <p>Add your first project to showcase your work.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_projects as $project): ?>
                            <div class="recent-project-item">
                                <img src="<?php echo $project['image_path'] ?: '/assets/images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($project['title']); ?>" 
                                     class="recent-project-img"
                                     onerror="this.src='/assets/images/placeholder.jpg'">
                                <div class="recent-project-info">
                                    <h4><?php echo htmlspecialchars($project['title']); ?></h4>
                                    <p>
                                        <?php echo htmlspecialchars(substr($project['description'], 0, 50)) . (strlen($project['description']) > 50 ? '...' : ''); ?>
                                        <?php if ($project['is_featured']): ?>
                                            <span style="background: #ffc107; padding: 1px 8px; border-radius: 10px; font-size: 10px; font-weight: 600;">★ Featured</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="right-column">
                <!-- Quick Actions -->
                <div class="dashboard-section" data-aos="fade-up" data-aos-delay="200">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-bolt"></i>
                            Quick Actions
                        </h3>
                    </div>
                    
                    <div class="quick-actions-grid">
                        <a href="manage-about.php" class="action-btn blue">
                            <i class="fas fa-edit"></i>
                            <span class="label">About Content</span>
                        </a>
                        <a href="manage-skills.php" class="action-btn purple">
                            <i class="fas fa-code"></i>
                            <span class="label">Manage Skills</span>
                        </a>
                        <a href="manage-projects.php" class="action-btn green">
                            <i class="fas fa-briefcase"></i>
                            <span class="label">Manage Projects</span>
                        </a>
                        <a href="upload-image.php" class="action-btn orange">
                            <i class="fas fa-upload"></i>
                            <span class="label">Upload Image</span>
                        </a>
                        <a href="messages.php" class="action-btn red">
                            <i class="fas fa-envelope"></i>
                            <span class="label">View Messages</span>
                        </a>
                        <a href="change-password.php" class="action-btn blue">
                            <i class="fas fa-key"></i>
                            <span class="label">Change Password</span>
                        </a>
                    </div>
                </div>
                
                <!-- System Info -->
                <div class="dashboard-section" data-aos="fade-up" data-aos-delay="300">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-server"></i>
                            System Info
                        </h3>
                    </div>
                    
                    <div class="system-info">
                        <div class="system-info-item">
                            <span class="label">PHP Version</span>
                            <span class="value"><?php echo phpversion(); ?></span>
                        </div>
                        <div class="system-info-item">
                            <span class="label">Server</span>
                            <span class="value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
                        </div>
                        <div class="system-info-item">
                            <span class="label">Database</span>
                            <span class="value">MySQL</span>
                        </div>
                        <div class="system-info-item">
                            <span class="label">Last Login</span>
                            <span class="value">
                                <?php 
                                    $admin = get_admin_user();
                                    echo $admin && $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'First time';
                                ?>
                            </span>
                        </div>
                        <div class="system-info-item" style="grid-column: 1 / -1;">
                            <span class="label">Site URL</span>
                            <span class="value" style="font-size: 12px;"><?php echo site_url(); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>