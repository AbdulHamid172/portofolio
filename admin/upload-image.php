<?php
require_once '../config/db.php';
require_once '../includes/image-helpers.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';
$message_type = '';
$new_image_path = '';
$image_category = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? 'projects';
    $custom_name = $_POST['custom_name'] ?? '';
    $update_portfolio = isset($_POST['update_portfolio']);
    $portfolio_image_type = $_POST['portfolio_image_type'] ?? '';
    
    // Define upload directory (relative to document root)
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/{$category}/";
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file = $_FILES['image'];
    $file_name = basename($file['name']);
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Get file extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Allowed extensions
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    
    if (in_array($file_ext, $allowed_ext)) {
        if ($file_error === 0) {
            // File size limit (5MB)
            if ($file_size > 5242880) {
                $message = "File is too large. Maximum size is 5MB.";
                $message_type = "error";
            } else {
                // Generate filename
                if (!empty($custom_name)) {
                    // Use custom name
                    $new_file_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $custom_name) . '.' . $file_ext;
                } else {
                    // Use original name (sanitized)
                    $original_name = pathinfo($file_name, PATHINFO_FILENAME);
                    $sanitized_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $original_name);
                    $new_file_name = $sanitized_name . '.' . $file_ext;
                    
                    // If file exists, add timestamp
                    if (file_exists($upload_dir . $new_file_name)) {
                        $new_file_name = $sanitized_name . '-' . time() . '.' . $file_ext;
                    }
                }
                
                $file_destination = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $file_destination)) {
                    $new_image_path = "/assets/images/{$category}/{$new_file_name}";
                    
                    // Create thumbnail for images (optional)
                    if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        create_thumbnail($file_destination, $upload_dir . 'thumb_' . $new_file_name, 300, 200);
                    }
                    
                    $message = "✅ Image uploaded successfully!<br>";
                    $message .= "<strong>File:</strong> {$new_file_name}<br>";
                    $message .= "<strong>Path:</strong> <code>{$new_image_path}</code><br>";
                    $message .= "<strong>Size:</strong> " . format_bytes($file_size);
                    $message_type = "success";
                    
                    // Update portfolio if requested
                    if ($update_portfolio && !empty($portfolio_image_type)) {
                        if (update_portfolio_image($portfolio_image_type, $new_image_path)) {
                            $message .= "<br><br>✅ <strong>Portfolio updated!</strong> Image is now active on your website.";
                        } else {
                            $message .= "<br><br>⚠️ <strong>Note:</strong> Image uploaded but portfolio not updated.";
                        }
                    }
                    
                } else {
                    $message = "❌ Failed to upload image. Please check folder permissions.";
                    $message_type = "error";
                }
            }
        } else {
            $message = "❌ Error uploading file. Error code: {$file_error}";
            $message_type = "error";
        }
    } else {
        $message = "❌ Invalid file type. Allowed: JPG, JPEG, PNG, GIF, WEBP, SVG";
        $message_type = "error";
    }
}

// Helper function to create thumbnails
function create_thumbnail($source, $destination, $width, $height) {
    list($src_width, $src_height, $image_type) = getimagesize($source);
    
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }
    
    $thumbnail = imagecreatetruecolor($width, $height);
    
    // Preserve transparency for PNG and GIF
    if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
        imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
    }
    
    imagecopyresampled($thumbnail, $source_image, 0, 0, 0, 0, $width, $height, $src_width, $src_height);
    
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumbnail, $destination, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumbnail, $destination, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumbnail, $destination);
            break;
    }
    
    imagedestroy($source_image);
    imagedestroy($thumbnail);
    
    return true;
}

// Helper function to format bytes
function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Get portfolio image types for dropdown
$portfolio_image_types = [
    'home_profile' => 'Home Page Profile Image',
    'about_profile' => 'About Page Profile Image',
    'project_1' => 'Project 1 Image',
    'project_2' => 'Project 2 Image',
    'project_3' => 'Project 3 Image',
    'project_4' => 'Project 4 Image',
    'project_5' => 'Project 5 Image',
    'project_6' => 'Project 6 Image',
    'hero_background' => 'Hero Background Image',
];
?>

<?php include '../includes/header.php'; ?>

<style>
    .upload-page {
        padding: 40px 0;
        min-height: 80vh;
        background: #f8f9fa;
    }
    
    .page-header {
        margin-bottom: 40px;
        text-align: center;
    }
    
    .page-header h1 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 10px;
    }
    
    .upload-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    @media (max-width: 992px) {
        .upload-container {
            grid-template-columns: 1fr;
            gap: 30px;
        }
    }
    
    .upload-form-container,
    .upload-info {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .upload-form-container h2,
    .upload-info h2 {
        margin-top: 0;
        margin-bottom: 25px;
        color: #333;
        border-bottom: 3px solid #667eea;
        padding-bottom: 10px;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
        font-size: 14px;
    }
    
    .form-group input[type="text"],
    .form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e1e1e1;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s;
        background: white;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .checkbox-group label {
        margin-bottom: 0;
        cursor: pointer;
        font-size: 14px;
    }
    
    .portfolio-update-section {
        margin-top: 25px;
        padding: 20px;
        background: #f8f9ff;
        border-radius: 10px;
        border-left: 4px solid #667eea;
        display: none;
        animation: slideIn 0.3s ease;
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
    
    .portfolio-update-section h4 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #667eea;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .file-upload {
        position: relative;
        border: 3px dashed #e1e1e1;
        border-radius: 10px;
        padding: 40px 20px;
        text-align: center;
        transition: all 0.3s;
        background: #f8f9fa;
    }
    
    .file-upload:hover {
        border-color: #667eea;
        background: #f8f9ff;
    }
    
    .file-upload.dragover {
        border-color: #667eea;
        background: #eef1ff;
    }
    
    .file-input {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }
    
    .upload-icon {
        font-size: 48px;
        color: #667eea;
        margin-bottom: 15px;
    }
    
    .upload-text h3 {
        margin-bottom: 10px;
        color: #333;
        font-size: 20px;
    }
    
    .upload-text p {
        color: #666;
        margin-bottom: 10px;
        font-size: 14px;
    }
    
    .selected-file {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        display: none;
        animation: fadeIn 0.3s ease;
    }
    
    .file-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .file-icon {
        font-size: 24px;
        color: #667eea;
    }
    
    .file-details h4 {
        margin-bottom: 5px;
        color: #333;
        font-size: 16px;
    }
    
    .file-details p {
        color: #666;
        font-size: 14px;
        margin: 0;
    }
    
    .preview-container {
        margin-top: 20px;
        text-align: center;
    }
    
    .image-preview {
        max-width: 100%;
        max-height: 300px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        display: none;
    }
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }
    
    .btn-upload {
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
    
    .btn-upload:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    
    .btn-cancel {
        padding: 15px 30px;
        background: #f8f9fa;
        color: #666;
        border: 2px solid #e1e1e1;
        border-radius: 8px;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s;
    }
    
    .btn-cancel:hover {
        background: #e9ecef;
    }
    
    .message {
        padding: 20px;
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
    
    .success-image-path {
        margin-top: 20px;
        padding: 15px;
        background: #e7f3ff;
        border-radius: 8px;
        border-left: 4px solid #2196F3;
    }
    
    .success-image-path h4 {
        margin-top: 0;
        color: #1976D2;
    }
    
    .path-display {
        font-family: monospace;
        background: white;
        padding: 10px;
        border-radius: 5px;
        margin: 10px 0;
        border: 1px solid #ddd;
        word-break: break-all;
    }
    
    .quick-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
    }
    
    .btn-small {
        padding: 8px 15px;
        font-size: 14px;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .btn-copy {
        background: #2196F3;
        color: white;
        border: none;
        cursor: pointer;
    }
    
    .btn-copy:hover {
        background: #1976D2;
    }
    
    .btn-manage {
        background: #4CAF50;
        color: white;
        text-decoration: none;
    }
    
    .btn-manage:hover {
        background: #388E3C;
    }
    
    .upload-info h2 {
        margin-bottom: 20px;
    }
    
    .category-info {
        margin-bottom: 25px;
    }
    
    .category-info h3 {
        color: #667eea;
        margin-bottom: 10px;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .category-info ul {
        list-style: none;
        padding-left: 20px;
    }
    
    .category-info li {
        margin-bottom: 8px;
        color: #666;
        position: relative;
        font-size: 14px;
    }
    
    .category-info li:before {
        content: "•";
        color: #667eea;
        position: absolute;
        left: -15px;
    }
    
    .requirements {
        background: #f8f9ff;
        padding: 20px;
        border-radius: 10px;
        margin-top: 30px;
    }
    
    .requirements h3 {
        color: #333;
        margin-bottom: 15px;
        font-size: 18px;
    }
    
    .requirements ul {
        color: #666;
        padding-left: 20px;
        font-size: 14px;
    }
</style>

<div class="upload-page">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header" data-aos="fade-up">
            <h1>Upload Images</h1>
            <p>Upload and automatically update your portfolio images</p>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>" data-aos="fade-up">
                <?php echo $message; ?>
                
                <?php if ($message_type === 'success' && $new_image_path): ?>
                    <div class="success-image-path">
                        <h4>📋 Image Information</h4>
                        <p><strong>Full Path:</strong></p>
                        <div class="path-display" id="imagePath"><?php echo $new_image_path; ?></div>
                        
                        <div class="quick-actions">
                            <button class="btn-small btn-copy" onclick="copyToClipboard('imagePath')">
                                <i class="fas fa-copy"></i> Copy Path
                            </button>
                            <a href="manage-portfolio-images.php" class="btn-small btn-manage">
                                <i class="fas fa-sync"></i> Update Portfolio
                            </a>
                            <a href="images.php" class="btn-small" style="background: #6c757d; color: white;">
                                <i class="fas fa-images"></i> View All Images
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="upload-container">
            <!-- Upload Form -->
            <div class="upload-form-container" data-aos="fade-right">
                <h2>Upload New Image</h2>
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <label for="category">Select Category</label>
                        <select id="category" name="category" required>
                            <option value="profile">Profile Images</option>
                            <option value="projects">Project Images</option>
                            <option value="background">Background Images</option>
                            <option value="icons">Icons & Logos</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="custom_name">Custom Filename (Optional)</label>
                        <input type="text" id="custom_name" name="custom_name" 
                               placeholder="e.g., my-project-image (no spaces)">
                        <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                            Leave blank to use original filename
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload Image</label>
                        <div class="file-upload" id="dropZone">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="upload-text">
                                <h3>Drag & Drop</h3>
                                <p>or click to browse files</p>
                                <p style="color: #999; font-size: 12px;">Max size: 5MB • JPG, PNG, GIF, WebP, SVG</p>
                            </div>
                            <input type="file" id="image" name="image" 
                                   accept="image/*" required class="file-input">
                        </div>
                        
                        <div class="selected-file" id="selectedFile">
                            <div class="file-info">
                                <div class="file-icon">
                                    <i class="fas fa-file-image"></i>
                                </div>
                                <div class="file-details">
                                    <h4 id="fileName"></h4>
                                    <p id="fileSize"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="preview-container">
                        <img id="imagePreview" class="image-preview" alt="Image Preview">
                    </div>
                    
                    <!-- Auto Update Portfolio Option -->
                    <div class="checkbox-group">
                        <input type="checkbox" id="updatePortfolio" name="update_portfolio">
                        <label for="updatePortfolio">Update portfolio with this image</label>
                    </div>
                    
                    <div class="portfolio-update-section" id="portfolioUpdateSection">
                        <h4><i class="fas fa-sync"></i> Update Portfolio Image</h4>
                        <div class="form-group">
                            <label for="portfolio_image_type">Select Portfolio Image to Update</label>
                            <select id="portfolio_image_type" name="portfolio_image_type">
                                <option value="">-- Select Image Type --</option>
                                <?php foreach ($portfolio_image_types as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                                This will replace the image on your website immediately
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-upload">
                            <i class="fas fa-upload"></i> Upload & Update
                        </button>
                        <a href="dashboard.php" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Upload Information -->
            <div class="upload-info" data-aos="fade-left">
                <h2>Quick Guide</h2>
                
                <div class="category-info">
                    <h3><i class="fas fa-user-circle"></i> Profile Images</h3>
                    <ul>
                        <li><strong>Home Profile:</strong> Main image on homepage (600x600px recommended)</li>
                        <li><strong>About Profile:</strong> Image on about page (500x500px recommended)</li>
                        <li><strong>Best format:</strong> WebP or JPG with compression</li>
                    </ul>
                </div>
                
                <div class="category-info">
                    <h3><i class="fas fa-briefcase"></i> Project Images</h3>
                    <ul>
                        <li><strong>Project 1-6:</strong> Images for project showcase</li>
                        <li><strong>Recommended:</strong> 16:9 ratio (1200x675px)</li>
                        <li><strong>Best format:</strong> WebP for best quality/size</li>
                    </ul>
                </div>
                
                <div class="category-info">
                    <h3><i class="fas fa-image"></i> Auto-Update Feature</h3>
                    <ul>
                        <li>Check "Update portfolio" to immediately update website</li>
                        <li>Select which portfolio image to replace</li>
                        <li>Changes take effect immediately</li>
                        <li>No coding required!</li>
                    </ul>
                </div>
                
                <div class="requirements">
                    <h3><i class="fas fa-info-circle"></i> Requirements</h3>
                    <ul>
                        <li>Max file size: 5MB</li>
                        <li>Supported formats: JPG, PNG, GIF, WebP, SVG</li>
                        <li>Optimize images before uploading</li>
                        <li>Use descriptive filenames</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('image');
        const dropZone = document.getElementById('dropZone');
        const selectedFile = document.getElementById('selectedFile');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const imagePreview = document.getElementById('imagePreview');
        const customNameInput = document.getElementById('custom_name');
        const updatePortfolioCheckbox = document.getElementById('updatePortfolio');
        const portfolioUpdateSection = document.getElementById('portfolioUpdateSection');
        const categorySelect = document.getElementById('category');
        const portfolioImageTypeSelect = document.getElementById('portfolio_image_type');
        
        // Toggle portfolio update section
        updatePortfolioCheckbox.addEventListener('change', function() {
            if (this.checked) {
                portfolioUpdateSection.style.display = 'block';
            } else {
                portfolioUpdateSection.style.display = 'none';
                portfolioImageTypeSelect.value = '';
            }
        });
        
        // Update portfolio options based on category
        categorySelect.addEventListener('change', function() {
            const category = this.value;
            updatePortfolioOptions(category);
        });
        
        function updatePortfolioOptions(category) {
            // Clear existing options except first
            while (portfolioImageTypeSelect.options.length > 1) {
                portfolioImageTypeSelect.remove(1);
            }
            
            // Add options based on category
            const categoryOptions = {
                'profile': [
                    {value: 'home_profile', label: 'Home Page Profile Image'},
                    {value: 'about_profile', label: 'About Page Profile Image'}
                ],
                'projects': [
                    {value: 'project_1', label: 'Project 1 Image'},
                    {value: 'project_2', label: 'Project 2 Image'},
                    {value: 'project_3', label: 'Project 3 Image'},
                    {value: 'project_4', label: 'Project 4 Image'},
                    {value: 'project_5', label: 'Project 5 Image'},
                    {value: 'project_6', label: 'Project 6 Image'}
                ],
                'background': [
                    {value: 'hero_background', label: 'Hero Background Image'}
                ],
                'icons': []
            };
            
            if (categoryOptions[category]) {
                categoryOptions[category].forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.label;
                    portfolioImageTypeSelect.appendChild(opt);
                });
            }
        }
        
        // Initialize portfolio options
        updatePortfolioOptions(categorySelect.value);
        
        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropZone.classList.add('dragover');
        }
        
        function unhighlight() {
            dropZone.classList.remove('dragover');
        }
        
        // Handle dropped files
        dropZone.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                updateFileInfo(files[0]);
            }
        }
        
        // Handle file input change
        fileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                updateFileInfo(this.files[0]);
            }
        });
        
        // Update file info and preview
        function updateFileInfo(file) {
            // Update file info display
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            selectedFile.style.display = 'block';
            
            // Auto-check portfolio update for profile/project images
            const category = categorySelect.value;
            if (category === 'profile' || category === 'projects') {
                updatePortfolioCheckbox.checked = true;
                portfolioUpdateSection.style.display = 'block';
                updatePortfolioOptions(category);
            }
            
            // Generate preview for images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    
                    // Auto-fill custom name with sanitized filename
                    if (!customNameInput.value) {
                        const nameWithoutExt = file.name.replace(/\.[^/.]+$/, "");
                        const sanitized = nameWithoutExt
                            .toLowerCase()
                            .replace(/[^a-z0-9]/g, '-')
                            .replace(/-+/g, '-')
                            .replace(/^-|-$/g, '');
                        customNameInput.value = sanitized;
                    }
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
        }
        
        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Form validation
        const form = document.getElementById('uploadForm');
        form.addEventListener('submit', function(e) {
            if (!fileInput.files.length) {
                e.preventDefault();
                alert('Please select an image to upload.');
                return;
            }
            
            const file = fileInput.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (file.size > maxSize) {
                e.preventDefault();
                alert('File is too large. Maximum size is 5MB.');
                return;
            }
            
            // Show loading state
            const submitBtn = form.querySelector('.btn-upload');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            submitBtn.disabled = true;
            
            // Re-enable button if form submission fails
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    });
    
    // Copy to clipboard function
    function copyToClipboard(elementId) {
        const element = document.getElementById(elementId);
        const text = element.textContent || element.innerText;
        
        navigator.clipboard.writeText(text).then(() => {
            // Show success message
            const originalText = event.target.innerHTML;
            event.target.innerHTML = '<i class="fas fa-check"></i> Copied!';
            event.target.style.background = '#4CAF50';
            
            setTimeout(() => {
                event.target.innerHTML = originalText;
                event.target.style.background = '#2196F3';
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            alert('Failed to copy text to clipboard');
        });
    }
</script>

<?php include '../includes/footer.php'; ?>