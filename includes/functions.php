<?php
/**
 * Get the absolute URL for an image
 * 
 * @param string $path Relative path from assets/images/
 * @param array $params Optional query parameters
 * @return string Full URL to the image
 */
function image_url($path, $params = []) {
    $url = '/assets/images/' . ltrim($path, '/');
    
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    return $url;
}

/**
 * Get the absolute path for an image
 * 
 * @param string $path Relative path from assets/images/
 * @return string Full filesystem path to the image
 */
function image_path($path) {
    return $_SERVER['DOCUMENT_ROOT'] . '/assets/images/' . ltrim($path, '/');
}

/**
 * Check if image exists and return URL or placeholder
 * 
 * @param string $path Relative path from assets/images/
 * @param string $placeholder Default image if not found
 * @return string URL to image or placeholder
 */
function get_image($path, $placeholder = '/assets/images/placeholder.jpg') {
    $fullPath = image_path($path);
    
    if (file_exists($fullPath)) {
        return image_url($path);
    }
    
    // Return placeholder if image doesn't exist
    return $placeholder;
}

/**
 * Generate responsive image tags
 * 
 * @param string $basePath Base image path without extension
 * @param string $alt Alt text
 * @param string $class CSS class
 * @param array $sizes Array of sizes ['800x600', '400x300']
 * @return string Complete <picture> HTML
 */
function responsive_image($basePath, $alt = '', $class = '', $sizes = []) {
    $html = '<picture>';
    
    // Add WebP versions if supported
    if (!empty($sizes)) {
        foreach ($sizes as $size) {
            list($width, $height) = explode('x', $size);
            $webpPath = str_replace('.jpg', ".{$size}.webp", $basePath);
            
            if (file_exists(image_path($webpPath))) {
                $html .= '<source srcset="' . image_url($webpPath) . '" ' .
                        'type="image/webp" media="(max-width: ' . $width . 'px)">';
            }
        }
    }
    
    // Default image
    $html .= '<img src="' . get_image($basePath . '.jpg') . '" ' .
             'alt="' . htmlspecialchars($alt) . '" ' .
             'class="' . $class . '" ' .
             'loading="lazy">';
    
    $html .= '</picture>';
    
    return $html;
}

/**
 * Get about content by key
 */
function get_about_content($key, $default = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT content FROM about_content WHERE section_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        if ($result) {
            return $result['content'];
        }
    } catch (PDOException $e) {
        // Silently fail
    }
    
    return $default;
}

/**
 * Get all skills
 */
function get_skills($category = null) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM skills WHERE is_active = 1 ORDER BY display_order ASC, category ASC";
        if ($category) {
            $sql = "SELECT * FROM skills WHERE category = ? AND is_active = 1 ORDER BY display_order ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$category]);
        } else {
            $stmt = $pdo->query($sql);
        }
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get skills grouped by category
 */
function get_skills_grouped() {
    $skills = get_skills();
    $grouped = [];
    
    foreach ($skills as $skill) {
        $cat = $skill['category'];
        if (!isset($grouped[$cat])) {
            $grouped[$cat] = [];
        }
        $grouped[$cat][] = $skill;
    }
    
    return $grouped;
}

/**
 * Get all active projects
 */
function get_projects($limit = null, $featured_only = false) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM projects WHERE is_active = 1";
        
        if ($featured_only) {
            $sql .= " AND is_featured = 1";
        }
        
        $sql .= " ORDER BY display_order ASC, created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get a single project by ID
 */
function get_project($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get all education timeline entries
 */
function get_education_timeline() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM education_timeline WHERE is_active = 1 ORDER BY display_order ASC, id ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Return default data if table doesn't exist
        return [
            [
                'id' => 1,
                'date' => '2024 - Present',
                'title' => 'Diploma in Information Technology',
                'subtitle' => 'Mount Kenya University, Coast Campus',
                'description' => 'Currently pursuing a Diploma in Information Technology. Learning software development, database management, networking, and IT infrastructure.',
                'display_order' => 1,
                'is_active' => 1
            ],
            [
                'id' => 2,
                'date' => '2022 - 2023',
                'title' => 'Certificate in Information Technology',
                'subtitle' => 'Mount Kenya University, Coast Campus',
                'description' => 'Completed Certificate in Information Technology with a focus on foundational IT skills, programming basics, and computer applications.',
                'display_order' => 2,
                'is_active' => 1
            ],
            [
                'id' => 3,
                'date' => 'Completed',
                'title' => 'Secondary Education',
                'subtitle' => 'High School Graduate',
                'description' => 'Successfully completed secondary school education with a focus on sciences and mathematics, laying the foundation for IT studies.',
                'display_order' => 3,
                'is_active' => 1
            ]
        ];
    }
}

/**
 * Get a single education entry by ID
 */
function get_education_entry($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM education_timeline WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Format bytes to human readable
 */
function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate a random string
 */
function generate_random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get current date time formatted
 */
function get_current_datetime() {
    return date('Y-m-d H:i:s');
}

/**
 * Get current date formatted
 */
function get_current_date() {
    return date('Y-m-d');
}

/**
 * Get time ago string
 */
function time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "Just Now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "1 day ago" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}

/**
 * Truncate text with ellipsis
 */
function truncate_text($text, $length = 100, $ellipsis = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $ellipsis;
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Get admin user data
 */
function get_admin_user() {
    global $pdo;
    
    if (!is_admin()) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get total count of messages
 */
function get_total_messages($unread_only = false) {
    global $pdo;
    
    try {
        $sql = "SELECT COUNT(*) as total FROM messages";
        if ($unread_only) {
            $sql .= " WHERE is_read = 0";
        }
        $stmt = $pdo->query($sql);
        return $stmt->fetch()['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get total count of projects
 */
function get_total_projects($active_only = true) {
    global $pdo;
    
    try {
        $sql = "SELECT COUNT(*) as total FROM projects";
        if ($active_only) {
            $sql .= " WHERE is_active = 1";
        }
        $stmt = $pdo->query($sql);
        return $stmt->fetch()['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get total count of skills
 */
function get_total_skills($active_only = true) {
    global $pdo;
    
    try {
        $sql = "SELECT COUNT(*) as total FROM skills";
        if ($active_only) {
            $sql .= " WHERE is_active = 1";
        }
        $stmt = $pdo->query($sql);
        return $stmt->fetch()['total'];
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get site URL
 */
function site_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . $host . '/' . ltrim($path, '/');
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Set flash message
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message
 */
function display_flash_message() {
    $flash = get_flash_message();
    if ($flash) {
        echo '<div class="message ' . htmlspecialchars($flash['type']) . '">';
        echo htmlspecialchars($flash['message']);
        echo '</div>';
    }
}

/**
 * Get category label for projects
 */
function get_project_category_label($category) {
    $labels = [
        'web' => 'Web Development',
        'mobile' => 'Mobile Apps',
        'design' => 'UI/UX Design',
        'fullstack' => 'Full Stack'
    ];
    return $labels[$category] ?? ucfirst($category);
}

/**
 * Get category label for skills
 */
function get_skill_category_label($category) {
    $labels = [
        'frontend' => 'Frontend',
        'backend' => 'Backend & Database',
        'tools' => 'Tools & Others'
    ];
    return $labels[$category] ?? ucfirst($category);
}

/**
 * Check if a string starts with a given substring
 */
function starts_with($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

/**
 * Check if a string ends with a given substring
 */
function ends_with($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}

/**
 * Convert a string to slug
 */
function create_slug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}
?>