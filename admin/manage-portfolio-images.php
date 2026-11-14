<?php
require_once '../config/db.php';
require_once '../includes/image-helpers.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Handle image update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_image'])) {
    $image_type = $_POST['image_type'];
    $image_path = $_POST['image_path'];
    
    if (update_portfolio_image($image_type, $image_path)) {
        $success_message = "Image updated successfully!";
    } else {
        $error_message = "Failed to update image.";
    }
}

// Get all portfolio images
$portfolio_images = get_all_portfolio_images();
?>

<?php include '../includes/header.php'; ?>

<style>
    .manage-images-page {
        padding: 40px 0;
        background: #f8f9fa;
        min-height: calc(100vh - 60px);
    }
    
    .page-header {
        margin-bottom: 40px;
    }
    
    .page-header h1 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 10px;
    }
    
    .images-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }
    
    .image-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .image-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .image-preview-container {
        height: 200px;
        overflow: hidden;
        position: relative;
        background: #f8f9fa;
    }
    
    .image-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .image-card:hover .image-preview {
        transform: scale(1.05);
    }
    
    .image-info {
        padding: 20px;
    }
    
    .image-type {
        font-size: 14px;
        color: #667eea;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    
    .image-path {
        font-size: 12px;
        color: #666;
        word-break: break-all;
        margin-bottom: 15px;
        font-family: monospace;
        background: #f8f9fa;
        padding: 8px;
        border-radius: 5px;
        border-left: 3px solid #667eea;
    }
    
    .update-form {
        margin-top: 15px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-size: 12px;
        font-weight: 600;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-group input {
        width: 100%;
        padding: 10px;
        border: 2px solid #e1e1e1;
        border-radius: 5px;
        font-size: 14px;
        font-family: monospace;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .btn-update {
        width: 100%;
        padding: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-update:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    .last-updated {
        font-size: 11px;
        color: #999;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        grid-column: 1 / -1;
    }
    
    .empty-state i {
        font-size: 60px;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    .message {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        border-left: 5px solid;
        animation: slideIn 0.5s ease;
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
</style>

<div class="manage-images-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Manage Portfolio Images</h1>
            <p>Update images that appear on your portfolio pages</p>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Images Grid -->
        <?php if (empty($portfolio_images)): ?>
            <div class="empty-state">
                <i class="fas fa-images"></i>
                <h3>No Portfolio Images Configured</h3>
                <p>Add image types to get started.</p>
                <a href="upload-image.php" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-upload"></i> Upload Images First
                </a>
            </div>
        <?php else: ?>
            <div class="images-container">
                <?php foreach ($portfolio_images as $image): ?>
                    <div class="image-card" data-aos="fade-up">
                        <div class="image-preview-container">
                            <img src="<?php echo $image['image_path']; ?>" 
                                 alt="<?php echo htmlspecialchars($image['image_type']); ?>"
                                 class="image-preview"
                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEwMCIgaGVpZ2h0PSIxMDAiIGZpbGw9IiNGMEYwRjAiLz48cGF0aCBkPSJNNjUgMzVINjBDNTcuMjM4NiAzNSA1NSAzNy4yMzg2IDU1IDQwVjYwQzU1IDYyLjc2MTQgNTcuMjM4NiA2NSA2MCA2NUg3MEM3Mi43NjE0IDY1IDc1IDYyLjc2MTQgNzUgNjBWNDBDNzUgMzcuMjM4NiA3Mi43NjE0IDM1IDcwIDM1SDY1Wk02NSA0MFY1MEg3MFY0MEg2NVpNNjAgNDBINjVWNDBINjBaTTM1IDQwQzM1IDM3LjIzODYgMzcuMjM4NiAzNSA0MCAzNUg1MEM1Mi43NjE0IDM1IDU1IDM3LjIzODYgNTUgNDBWNjBDNTUgNjIuNzYxNCA1Mi43NjE0IDY1IDUwIDY1SDQwQzM3LjIzODYgNjUgMzUgNjIuNzYxNCAzNSA2MFY0MFpNNDAgNTBINDRWNDBINDBWNTBaTTUwIDQwSDQ0VjUwSDUwVjQwWiIgZmlsbD0iI0IyQjJCMiIvPjwvc3ZnPg=='">
                        </div>
                        
                        <div class="image-info">
                            <div class="image-type">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars(str_replace('_', ' ', $image['image_type'])); ?>
                            </div>
                            
                            <div class="image-path" title="<?php echo htmlspecialchars($image['image_path']); ?>">
                                <?php echo htmlspecialchars($image['image_path']); ?>
                            </div>
                            
                            <form method="POST" class="update-form">
                                <input type="hidden" name="image_type" value="<?php echo $image['image_type']; ?>">
                                
                                <div class="form-group">
                                    <label for="path_<?php echo $image['id']; ?>">Image Path</label>
                                    <input type="text" 
                                           id="path_<?php echo $image['id']; ?>" 
                                           name="image_path" 
                                           value="<?php echo htmlspecialchars($image['image_path']); ?>"
                                           placeholder="/assets/images/profile/your-image.jpg"
                                           required>
                                </div>
                                
                                <button type="submit" name="update_image" class="btn-update">
                                    <i class="fas fa-sync"></i> Update Image
                                </button>
                            </form>
                            
                            <div class="last-updated">
                                <i class="far fa-clock"></i>
                                Last updated: <?php echo date('M d, Y H:i', strtotime($image['uploaded_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Quick Help -->
            <div style="margin-top: 40px; padding: 25px; background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
                <h3 style="margin-top: 0; color: #333; margin-bottom: 15px;">
                    <i class="fas fa-question-circle"></i> How to Update Images
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <h4 style="color: #667eea; font-size: 16px; margin-bottom: 10px;">Step 1: Upload Image</h4>
                        <p style="color: #666; font-size: 14px; margin: 0;">
                            Go to <a href="upload-image.php">Upload Images</a> and upload your new image.
                        </p>
                    </div>
                    <div>
                        <h4 style="color: #667eea; font-size: 16px; margin-bottom: 10px;">Step 2: Copy Path</h4>
                        <p style="color: #666; font-size: 14px; margin: 0;">
                            Copy the image path from the upload success message or from <a href="images.php">Manage Images</a>.
                        </p>
                    </div>
                    <div>
                        <h4 style="color: #667eea; font-size: 16px; margin-bottom: 10px;">Step 3: Update Here</h4>
                        <p style="color: #666; font-size: 14px; margin: 0;">
                            Paste the new path in the form above and click "Update Image".
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-update preview when path changes
        document.querySelectorAll('input[name="image_path"]').forEach(input => {
            input.addEventListener('input', function() {
                const imageCard = this.closest('.image-card');
                const previewImg = imageCard.querySelector('.image-preview');
                previewImg.src = this.value;
            });
        });
        
        // Form validation
        document.querySelectorAll('.update-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const pathInput = this.querySelector('input[name="image_path"]');
                const path = pathInput.value.trim();
                
                if (!path.startsWith('/assets/images/')) {
                    e.preventDefault();
                    alert('Image path must start with /assets/images/');
                    pathInput.focus();
                    return;
                }
                
                if (!path.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i)) {
                    e.preventDefault();
                    alert('Please enter a valid image path (jpg, png, gif, webp, svg)');
                    pathInput.focus();
                    return;
                }
            });
        });
        
        // Add quick path helper
        const pathInputs = document.querySelectorAll('input[name="image_path"]');
        pathInputs.forEach(input => {
            // Add data attribute for image type
            const form = input.closest('form');
            const imageType = form.querySelector('input[name="image_type"]').value;
            
            if (imageType.includes('profile')) {
                input.placeholder = '/assets/images/profile/profile-new.jpg';
            } else if (imageType.includes('project')) {
                input.placeholder = '/assets/images/projects/my-project.jpg';
            } else if (imageType.includes('background')) {
                input.placeholder = '/assets/images/background/hero-bg.jpg';
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>