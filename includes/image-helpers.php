<?php
/**
 * Get dynamic image URL from database or fallback
 */
function get_dynamic_image($image_type, $default_path = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT image_path FROM portfolio_images WHERE image_type = ? AND is_active = 1");
        $stmt->execute([$image_type]);
        $result = $stmt->fetch();
        
        if ($result && file_exists($_SERVER['DOCUMENT_ROOT'] . $result['image_path'])) {
            return $result['image_path'];
        }
    } catch (PDOException $e) {
        error_log("Image fetch error: " . $e->getMessage());
    }
    
    // Return default if no image found
    return $default_path;
}

/**
 * Update image in database
 */
function update_portfolio_image($image_type, $image_path) {
    global $pdo;
    
    try {
        // Check if image type exists
        $stmt = $pdo->prepare("SELECT id FROM portfolio_images WHERE image_type = ?");
        $stmt->execute([$image_type]);
        
        if ($stmt->rowCount() > 0) {
            // Update existing
            $stmt = $pdo->prepare("UPDATE portfolio_images SET image_path = ?, uploaded_at = NOW() WHERE image_type = ?");
            $stmt->execute([$image_path, $image_type]);
        } else {
            // Insert new
            $stmt = $pdo->prepare("INSERT INTO portfolio_images (image_type, image_path) VALUES (?, ?)");
            $stmt->execute([$image_type, $image_path]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Image update error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all portfolio images
 */
function get_all_portfolio_images() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM portfolio_images ORDER BY image_type");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get all images error: " . $e->getMessage());
        return [];
    }
}
?>