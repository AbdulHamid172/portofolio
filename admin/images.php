<?php
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get all images from folders
$image_categories = ['profile', 'projects', 'background', 'icons'];
$all_images = [];

foreach ($image_categories as $category) {
    $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/{$category}/";
    
    if (file_exists($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && !is_dir($dir . $file)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                    $all_images[] = [
                        'name' => $file,
                        'category' => $category,
                        'path' => "/assets/images/{$category}/{$file}",
                        'size' => filesize($dir . $file),
                        'modified' => filemtime($dir . $file)
                    ];
                }
            }
        }
    }
}

// Sort by modification date (newest first)
usort($all_images, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

// Handle delete request
if (isset($_GET['delete']) && isset($_GET['category'])) {
    $filename = basename($_GET['delete']);
    $category = $_GET['category'];
    $filepath = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/{$category}/{$filename}";
    
    if (file_exists($filepath)) {
        unlink($filepath);
        header('Location: images.php?deleted=true');
        exit();
    }
}
?>

<?php include '../includes/header.php'; ?>

<style>
    .images-page {
        padding: 40px 0;
    }
    
    .images-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
    }
    
    .images-header h1 {
        font-size: 2.5rem;
        color: #333;
    }
    
    .images-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }
    
    .image-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .image-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .image-preview {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-bottom: 1px solid #eee;
    }
    
    .image-info {
        padding: 15px;
    }
    
    .image-name {
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
        word-break: break-all;
    }
    
    .image-meta {
        font-size: 12px;
        color: #666;
        margin-bottom: 10px;
    }
    
    .image-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-action {
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 12px;
        text-decoration: none;
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
    }
    
    .btn-delete:hover {
        background: #c82333;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    
    .empty-state i {
        font-size: 60px;
        margin-bottom: 20px;
        color: #ddd;
    }
</style>

<div class="images-page">
    <div class="container">
        <!-- Page Header -->
        <div class="images-header">
            <div>
                <h1>Manage Images</h1>
                <p>View, manage, and delete uploaded images</p>
            </div>
            <a href="upload-image.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Upload New
            </a>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="message success" style="margin-bottom: 30px;">
                <i class="fas fa-check-circle"></i> Image deleted successfully.
            </div>
        <?php endif; ?>
        
        <!-- Images Grid -->
        <?php if (empty($all_images)): ?>
            <div class="empty-state">
                <i class="fas fa-images"></i>
                <h3>No Images Uploaded</h3>
                <p>Upload your first image to get started.</p>
                <a href="upload-image.php" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-upload"></i> Upload Image
                </a>
            </div>
        <?php else: ?>
            <div class="images-grid">
                <?php foreach ($all_images as $image): ?>
                    <div class="image-card">
                        <img src="<?php echo $image['path']; ?>" 
                             alt="<?php echo htmlspecialchars($image['name']); ?>" 
                             class="image-preview"
                             onerror="this.src='/assets/images/placeholder.jpg'">
                        
                        <div class="image-info">
                            <div class="image-name" title="<?php echo htmlspecialchars($image['name']); ?>">
                                <?php echo htmlspecialchars(substr($image['name'], 0, 20)); ?>
                                <?php if (strlen($image['name']) > 20): ?>...<?php endif; ?>
                            </div>
                            
                            <div class="image-meta">
                                <div>Category: <?php echo ucfirst($image['category']); ?></div>
                                <div>Size: <?php echo format_bytes($image['size']); ?></div>
                                <div>Uploaded: <?php echo date('M d, Y', $image['modified']); ?></div>
                            </div>
                            
                            <div class="image-actions">
                                <a href="<?php echo $image['path']; ?>" 
                                   target="_blank" 
                                   class="btn-action btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="?delete=<?php echo urlencode($image['name']); ?>&category=<?php echo $image['category']; ?>" 
                                   class="btn-action btn-delete"
                                   onclick="return confirm('Delete this image?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>