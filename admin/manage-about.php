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

// Handle about content update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_about'])) {
    $sections = ['intro', 'education', 'focus', 'aspiration'];
    
    try {
        foreach ($sections as $key) {
            if (isset($_POST[$key])) {
                $content = trim($_POST[$key]);
                $stmt = $pdo->prepare("INSERT INTO about_content (section_key, content) VALUES (?, ?) 
                                       ON DUPLICATE KEY UPDATE content = VALUES(content)");
                $stmt->execute([$key, $content]);
            }
        }
        $message = "About content updated successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
        $message_type = "error";
    }
}

// Handle education add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_education'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $date = trim($_POST['date'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($date) || empty($title)) {
        $message = "Date and Title are required.";
        $message_type = "error";
    } else {
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE education_timeline SET date = ?, title = ?, subtitle = ?, description = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$date, $title, $subtitle, $description, $display_order, $is_active, $id]);
                $message = "Education entry updated successfully!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO education_timeline (date, title, subtitle, description, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$date, $title, $subtitle, $description, $display_order, $is_active]);
                $message = "Education entry added successfully!";
            }
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Handle education delete
if (isset($_GET['delete_education']) && is_numeric($_GET['delete_education'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM education_timeline WHERE id = ?");
        $stmt->execute([$_GET['delete_education']]);
        $message = "Education entry deleted successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
        $message_type = "error";
    }
}

// Get current about content
$about_content = [];
$sections = ['intro', 'education', 'focus', 'aspiration'];
foreach ($sections as $key) {
    $about_content[$key] = get_about_content($key, '');
}

// Get education timeline
$education_timeline = get_education_timeline();

$section_labels = [
    'intro' => 'Introduction / Bio',
    'education' => 'Education',
    'focus' => 'Focus Areas',
    'aspiration' => 'Aspiration / Goal'
];

$section_placeholders = [
    'intro' => 'Write a brief introduction about yourself...',
    'education' => 'Your current education and institution...',
    'focus' => 'Your main areas of focus in IT...',
    'aspiration' => 'Your career goals and aspirations...'
];
?>

<?php include '../includes/header.php'; ?>

<style>
    .manage-about-page {
        padding: 40px 0;
        background: #f8f9fa;
        min-height: calc(100vh - 60px);
    }
    
    .about-container {
        max-width: 900px;
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
    
    .section-divider {
        margin: 40px 0 30px;
        padding-bottom: 15px;
        border-bottom: 3px solid #667eea;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .section-divider h2 {
        font-size: 1.5rem;
        color: #333;
        margin: 0;
    }
    
    .section-divider i {
        color: #667eea;
        font-size: 24px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    
    .form-group textarea,
    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e1e1e1;
        border-radius: 8px;
        font-size: 16px;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s;
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .form-group textarea:focus,
    .form-group input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
    
    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 14px 30px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    
    .btn-add {
        background: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-add:hover {
        background: #218838;
        transform: translateY(-2px);
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-delete:hover {
        background: #c82333;
    }
    
    .btn-edit {
        background: #ffc107;
        color: #333;
        border: none;
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
        margin-right: 5px;
    }
    
    .btn-edit:hover {
        background: #e0a800;
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
    
    .education-list {
        margin-top: 20px;
    }
    
    .education-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
        border-left: 4px solid #667eea;
        transition: all 0.3s;
    }
    
    .education-item:hover {
        background: #eef1ff;
    }
    
    .education-item .info h4 {
        margin: 0 0 5px 0;
        color: #333;
        font-size: 16px;
    }
    
    .education-item .info .date {
        font-size: 12px;
        color: #667eea;
        font-weight: 600;
    }
    
    .education-item .info p {
        margin: 5px 0 0;
        color: #666;
        font-size: 14px;
    }
    
    .education-item .actions {
        display: flex;
        gap: 5px;
        flex-shrink: 0;
    }
    
    .edit-form {
        background: #f8f9ff;
        padding: 20px;
        border-radius: 10px;
        margin: 15px 0;
        border: 2px solid #667eea;
    }
    
    .edit-form h4 {
        margin-top: 0;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .edit-form .close-edit {
        color: #dc3545;
        text-decoration: none;
        font-size: 14px;
    }
    
    .help-text {
        background: #f8f9ff;
        padding: 20px;
        border-radius: 10px;
        margin-top: 30px;
        font-size: 14px;
        color: #666;
    }
    
    .help-text h4 {
        margin-top: 0;
        color: #333;
        margin-bottom: 10px;
    }
    
    .char-count {
        text-align: right;
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }
    
    .status-badge {
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-top: 5px;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        flex-shrink: 0;
    }
    
    .checkbox-group label {
        margin-bottom: 0;
        cursor: pointer;
        font-weight: 400;
    }
    
    .tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        border-bottom: 2px solid #e1e1e1;
        padding-bottom: 10px;
    }
    
    .tab-btn {
        padding: 10px 25px;
        background: none;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        color: #666;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .tab-btn:hover {
        background: #f0f0f0;
    }
    
    .tab-btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
        animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="manage-about-page">
    <div class="container">
        <div class="about-container" data-aos="fade-up">
            <div class="page-header">
                <h1><i class="fas fa-edit"></i> Manage About Content</h1>
                <p>Update your profile information displayed on the About page</p>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active" data-tab="about">About Content</button>
                <button class="tab-btn" data-tab="education">Education Timeline</button>
            </div>
            
            <!-- Tab 1: About Content -->
            <div class="tab-content active" id="tab-about">
                <form method="POST" action="">
                    <input type="hidden" name="update_about" value="1">
                    
                    <?php foreach ($sections as $key): ?>
                        <div class="form-group">
                            <label for="<?php echo $key; ?>">
                                <i class="fas fa-quote-right" style="color: #667eea; margin-right: 8px;"></i>
                                <?php echo $section_labels[$key]; ?>
                            </label>
                            <textarea id="<?php echo $key; ?>" 
                                      name="<?php echo $key; ?>" 
                                      rows="4"
                                      placeholder="<?php echo $section_placeholders[$key]; ?>"
                                      maxlength="500"><?php echo htmlspecialchars($about_content[$key] ?? ''); ?></textarea>
                            <div class="char-count">
                                <span id="count_<?php echo $key; ?>"><?php echo strlen($about_content[$key] ?? ''); ?></span> / 500
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save About Content
                    </button>
                </form>
            </div>
            
            <!-- Tab 2: Education Timeline -->
            <div class="tab-content" id="tab-education">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                    <h3 style="margin: 0; color: #333;">Education Entries</h3>
                    <button class="btn-add" onclick="showAddEducationForm()">
                        <i class="fas fa-plus"></i> Add New Entry
                    </button>
                </div>
                
                <!-- Add/Edit Education Form -->
                <?php 
                    $edit_education = null;
                    if (isset($_GET['edit_education']) && is_numeric($_GET['edit_education'])) {
                        try {
                            $stmt = $pdo->prepare("SELECT * FROM education_timeline WHERE id = ?");
                            $stmt->execute([$_GET['edit_education']]);
                            $edit_education = $stmt->fetch();
                        } catch (PDOException $e) {}
                    }
                ?>
                
                <?php if ($edit_education || isset($_GET['add'])): ?>
                    <div class="edit-form">
                        <h4>
                            <i class="fas fa-<?php echo $edit_education ? 'edit' : 'plus'; ?>"></i>
                            <?php echo $edit_education ? 'Edit Education Entry' : 'Add New Education Entry'; ?>
                            <a href="manage-about.php" class="close-edit">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </h4>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="save_education" value="1">
                            <?php if ($edit_education): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_education['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date">Date/Year *</label>
                                    <input type="text" id="date" name="date" required 
                                           placeholder="e.g., 2024 - Present"
                                           value="<?php echo $edit_education ? htmlspecialchars($edit_education['date']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="display_order">Display Order</label>
                                    <input type="number" id="display_order" name="display_order" min="0"
                                           value="<?php echo $edit_education ? $edit_education['display_order'] : '0'; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="title">Title *</label>
                                <input type="text" id="title" name="title" required 
                                       placeholder="e.g., Diploma in Information Technology"
                                       value="<?php echo $edit_education ? htmlspecialchars($edit_education['title']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="subtitle">Subtitle / Institution</label>
                                <input type="text" id="subtitle" name="subtitle" 
                                       placeholder="e.g., Mount Kenya University, Coast Campus"
                                       value="<?php echo $edit_education ? htmlspecialchars($edit_education['subtitle']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="4" 
                                          placeholder="Describe your education experience..."><?php echo $edit_education ? htmlspecialchars($edit_education['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="is_active" name="is_active" <?php echo ($edit_education && $edit_education['is_active']) || !$edit_education ? 'checked' : ''; ?>>
                                        <label for="is_active">Active (show on website)</label>
                                    </div>
                                </div>
                                <div class="form-group" style="display: flex; align-items: center; gap: 10px; padding-top: 5px;">
                                    <button type="submit" class="btn-save" style="padding: 10px 25px;">
                                        <i class="fas fa-save"></i> <?php echo $edit_education ? 'Update' : 'Add'; ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <!-- Education List -->
                <div class="education-list">
                    <?php if (empty($education_timeline)): ?>
                        <p style="text-align: center; color: #666; padding: 40px 0;">
                            <i class="fas fa-graduation-cap" style="font-size: 40px; display: block; margin-bottom: 15px; color: #ddd;"></i>
                            No education entries yet. Click "Add New Entry" to get started.
                        </p>
                    <?php else: ?>
                        <?php foreach ($education_timeline as $item): ?>
                            <div class="education-item">
                                <div class="info">
                                    <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                    <span class="date"><?php echo htmlspecialchars($item['date']); ?></span>
                                    <?php if ($item['subtitle']): ?>
                                        <p><strong><?php echo htmlspecialchars($item['subtitle']); ?></strong></p>
                                    <?php endif; ?>
                                    <?php if ($item['description']): ?>
                                        <p style="margin-top: 5px; font-size: 13px; color: #666;">
                                            <?php echo htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : ''); ?>
                                        </p>
                                    <?php endif; ?>
                                    <span class="status-badge <?php echo $item['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                                <div class="actions">
                                    <a href="?edit_education=<?php echo $item['id']; ?>" class="btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?delete_education=<?php echo $item['id']; ?>" class="btn-delete" onclick="return confirm('Delete this education entry?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="help-text">
                <h4><i class="fas fa-lightbulb"></i> Tips</h4>
                <ul style="padding-left: 20px; margin: 10px 0;">
                    <li>Keep your introduction concise and engaging (under 500 characters)</li>
                    <li>Education entries appear in order of display order (lowest first)</li>
                    <li>Inactive entries won't show on the website</li>
                    <li>Changes take effect immediately on your About page</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab switching
    document.addEventListener('DOMContentLoaded', function() {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active from all tabs
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active to clicked tab
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById('tab-' + tabId).classList.add('active');
            });
        });
        
        // Character count for textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                const id = this.id;
                const count = this.value.length;
                const counter = document.getElementById('count_' + id);
                if (counter) {
                    counter.textContent = count;
                    if (count > 450) {
                        counter.style.color = '#ffc107';
                    } else if (count > 480) {
                        counter.style.color = '#dc3545';
                    } else {
                        counter.style.color = '#999';
                    }
                }
            });
        });
    });
    
    function showAddEducationForm() {
        window.location.href = '?add=1';
    }
</script>

<?php include '../includes/footer.php'; ?>