<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/image-helpers.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        // Get project image to delete
        $stmt = $pdo->prepare("SELECT image_path FROM projects WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $project = $stmt->fetch();
        
        if ($project && $project['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $project['image_path'])) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $project['image_path']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Project deleted successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
        $message_type = "error";
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $project_url = trim($_POST['project_url'] ?? '');
    $github_url = trim($_POST['github_url'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image upload
    $image_path = $_POST['current_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file = $_FILES['image'];
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/projects/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $filename = 'project_' . time() . '.' . $ext;
            $destination = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Delete old image if exists
                if ($image_path && file_exists($_SERVER['DOCUMENT_ROOT'] . $image_path)) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . $image_path);
                }
                $image_path = '/assets/images/projects/' . $filename;
            }
        }
    }
    
    if (empty($title) || empty($description)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } else {
        try {
            if ($id > 0) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, image_path = ?, category = ?, technologies = ?, project_url = ?, github_url = ?, display_order = ?, is_featured = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$title, $description, $image_path, $category, $technologies, $project_url, $github_url, $display_order, $is_featured, $is_active, $id]);
                $message = "Project updated successfully!";
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO projects (title, description, image_path, category, technologies, project_url, github_url, display_order, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $image_path, $category, $technologies, $project_url, $github_url, $display_order, $is_featured, $is_active]);
                $message = "Project added successfully!";
            }
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Get all projects
$projects = get_projects();

// Project categories
$categories = ['web', 'mobile', 'design', 'fullstack'];
$category_labels = [
    'web' => 'Web Development',
    'mobile' => 'Mobile Apps',
    'design' => 'UI/UX Design',
    'fullstack' => 'Full Stack'
];
?>

<?php include '../includes/header.php'; ?>

<style>
    .manage-projects-page {
        padding: 40px 0;
        background: #f8f9fa;
        min-height: calc(100vh - 60px);
    }
    
    .projects-container {
        max-width: 1000px;
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
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e1e1e1;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
        font-family: 'Poppins', sans-serif;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
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
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-top: 8px;
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
    }
    
    .btn-submit {
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
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    
    .projects-list {
        margin-top: 40px;
    }
    
    .project-item {
        display: flex;
        gap: 20px;
        padding: 20px;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        margin-bottom: 15px;
        transition: all 0.3s;
        align-items: center;
    }
    
    .project-item:hover {
        background: #f8f9fa;
        border-color: #667eea;
    }
    
    .project-thumb {
        width: 120px;
        height: 80px;
        flex-shrink: 0;
        border-radius: 8px;
        overflow: hidden;
        background: #e9ecef;
    }
    
    .project-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .project-details {
        flex: 1;
    }
    
    .project-details h4 {
        margin: 0 0 5px 0;
        color: #333;
    }
    
    .project-details p {
        margin: 0 0 5px 0;
        color: #666;
        font-size: 14px;
    }
    
    .project-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 12px;
        color: #666;
    }
    
    .project-meta .tag {
        background: #f0f0f0;
        padding: 2px 10px;
        border-radius: 20px;
    }
    
    .project-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    
    .btn-action {
        padding: 6px 12px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 12px;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }
    
    .btn-edit {
        background: #ffc107;
        color: #333;
    }
    
    .btn-edit:hover {
        background: #e0a800;
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
    }
    
    .btn-delete:hover {
        background: #c82333;
    }
    
    .btn-featured {
        background: #28a745;
        color: white;
    }
    
    .btn-featured:hover {
        background: #218838;
    }
    
    .badge-featured {
        background: #ffc107;
        color: #333;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
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
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #666;
    }
    
    .empty-state i {
        font-size: 50px;
        margin-bottom: 20px;
        color: #ddd;
    }
    
    .image-preview {
        max-width: 200px;
        max-height: 150px;
        border-radius: 8px;
        margin-top: 10px;
        border: 2px solid #e1e1e1;
    }
    
    .file-upload-wrapper {
        position: relative;
        border: 2px dashed #e1e1e1;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s;
    }
    
    .file-upload-wrapper:hover {
        border-color: #667eea;
        background: #f8f9ff;
    }
    
    .file-upload-wrapper input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    
    .file-upload-text {
        color: #666;
    }
    
    .file-upload-text i {
        font-size: 30px;
        color: #667eea;
        margin-bottom: 10px;
        display: block;
    }
    
    .edit-section {
        background: #f8f9ff;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        border: 2px solid #667eea;
    }
    
    .edit-section h3 {
        margin-top: 0;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .edit-section h3 .close-edit {
        margin-left: auto;
        color: #dc3545;
        text-decoration: none;
        font-size: 14px;
    }
</style>

<div class="manage-projects-page">
    <div class="container">
        <div class="projects-container" data-aos="fade-up">
            <div class="page-header">
                <h1><i class="fas fa-briefcase"></i> Manage Projects</h1>
                <p>Add, edit, or remove projects displayed on your website</p>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Add/Edit Project Form -->
            <?php 
                $edit_project = null;
                if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
                    $edit_project = get_project($_GET['edit']);
                }
            ?>
            
            <div class="<?php echo $edit_project ? 'edit-section' : ''; ?>">
                <h3>
                    <i class="fas fa-<?php echo $edit_project ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $edit_project ? 'Edit Project' : 'Add New Project'; ?>
                    <?php if ($edit_project): ?>
                        <a href="manage-projects.php" class="close-edit">
                            <i class="fas fa-times"></i> Cancel Edit
                        </a>
                    <?php endif; ?>
                </h3>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($edit_project): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_project['id']; ?>">
                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($edit_project['image_path'] ?? ''); ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Project Title *</label>
                        <input type="text" id="title" name="title" required 
                               placeholder="e.g., E-commerce Platform"
                               value="<?php echo $edit_project ? htmlspecialchars($edit_project['title']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" required 
                                  placeholder="Describe your project..."><?php echo $edit_project ? htmlspecialchars($edit_project['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" <?php echo ($edit_project && $edit_project['category'] == $cat) ? 'selected' : ''; ?>>
                                        <?php echo $category_labels[$cat]; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="technologies">Technologies</label>
                            <input type="text" id="technologies" name="technologies" 
                                   placeholder="e.g., PHP, MySQL, JavaScript (comma separated)"
                                   value="<?php echo $edit_project ? htmlspecialchars($edit_project['technologies']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="project_url">Live Demo URL</label>
                            <input type="url" id="project_url" name="project_url" 
                                   placeholder="https://your-project.com"
                                   value="<?php echo $edit_project ? htmlspecialchars($edit_project['project_url']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="github_url">GitHub URL</label>
                            <input type="url" id="github_url" name="github_url" 
                                   placeholder="https://github.com/your/project"
                                   value="<?php echo $edit_project ? htmlspecialchars($edit_project['github_url']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Project Image</label>
                        <div class="file-upload-wrapper">
                            <div class="file-upload-text">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click or drag to upload image</p>
                                <p style="font-size: 12px; color: #999;">JPG, PNG, GIF, WebP (Max 5MB)</p>
                            </div>
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>
                        <?php if ($edit_project && $edit_project['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($edit_project['image_path']); ?>" 
                                 alt="Current image" class="image-preview">
                            <p style="font-size: 12px; color: #666; margin-top: 5px;">Current image</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" id="display_order" name="display_order" min="0"
                                   value="<?php echo $edit_project ? $edit_project['display_order'] : '0'; ?>">
                        </div>
                        <div class="form-group" style="display: flex; gap: 30px; padding-top: 8px;">
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_featured" name="is_featured" <?php echo ($edit_project && $edit_project['is_featured']) ? 'checked' : ''; ?>>
                                <label for="is_featured">Featured Project</label>
                            </div>
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_active" name="is_active" <?php echo ($edit_project && $edit_project['is_active']) || !$edit_project ? 'checked' : ''; ?>>
                                <label for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-<?php echo $edit_project ? 'save' : 'plus'; ?>"></i>
                        <?php echo $edit_project ? 'Update Project' : 'Add Project'; ?>
                    </button>
                </form>
            </div>
            
            <!-- Projects List -->
            <div class="projects-list">
                <h3 style="margin-bottom: 20px; color: #333;">Your Projects</h3>
                
                <?php if (empty($projects)): ?>
                    <div class="empty-state">
                        <i class="fas fa-briefcase"></i>
                        <h3>No Projects Added</h3>
                        <p>Add your first project using the form above.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <div class="project-item">
                            <div class="project-thumb">
                                <img src="<?php echo $project['image_path'] ?: '/assets/images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($project['title']); ?>"
                                     onerror="this.src='/assets/images/placeholder.jpg'">
                            </div>
                            <div class="project-details">
                                <h4>
                                    <?php echo htmlspecialchars($project['title']); ?>
                                    <?php if ($project['is_featured']): ?>
                                        <span class="badge-featured">★ Featured</span>
                                    <?php endif; ?>
                                </h4>
                                <p><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . (strlen($project['description']) > 100 ? '...' : ''); ?></p>
                                <div class="project-meta">
                                    <span class="tag"><?php echo $category_labels[$project['category']] ?? $project['category'] ?: 'Uncategorized'; ?></span>
                                    <?php if ($project['technologies']): ?>
                                        <span class="tag"><?php echo htmlspecialchars(substr($project['technologies'], 0, 30)) . (strlen($project['technologies']) > 30 ? '...' : ''); ?></span>
                                    <?php endif; ?>
                                    <span>Order: <?php echo $project['display_order']; ?></span>
                                    <span class="<?php echo $project['is_active'] ? 'active' : 'inactive'; ?>" style="font-size: 11px;">
                                        <?php echo $project['is_active'] ? '✅ Active' : '❌ Inactive'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="project-actions">
                                <a href="manage-projects.php?edit=<?php echo $project['id']; ?>" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="?delete=<?php echo $project['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this project?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image preview for upload
        const imageInput = document.getElementById('image');
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Remove existing preview
                        const existingPreview = document.querySelector('.image-preview');
                        if (existingPreview) {
                            existingPreview.remove();
                        }
                        
                        // Create new preview
                        const preview = document.createElement('img');
                        preview.src = e.target.result;
                        preview.className = 'image-preview';
                        preview.style.marginTop = '10px';
                        
                        this.parentElement.parentElement.appendChild(preview);
                    }.bind(this);
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>