<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abdul Hamid | Portfolio</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/animations.css">
    
    <!-- AOS Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Admin Dashboard CSS (only loads on admin pages) -->
    <?php if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false): ?>
    <style>
        /* Admin-specific styles - IMPROVED */
        .admin-nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 8px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .admin-nav .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: nowrap;
            gap: 10px;
        }
        
        .admin-nav .nav-logo {
            color: white;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .admin-nav .nav-logo i {
            font-size: 18px;
        }
        
        .admin-nav .nav-menu {
            display: flex;
            list-style: none;
            gap: 5px;
            margin: 0;
            padding: 0;
            align-items: center;
            flex-wrap: wrap;
            flex: 1;
            justify-content: flex-start;
        }
        
        .admin-nav .nav-item {
            margin: 0;
        }
        
        .admin-nav .nav-link {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }
        
        .admin-nav .nav-link:hover,
        .admin-nav .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        
        .admin-nav .nav-link i {
            font-size: 14px;
            min-width: 16px;
        }
        
        .admin-nav .user-info {
            color: white;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            flex-shrink: 0;
            padding: 4px 12px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
        }
        
        .admin-nav .user-info i {
            font-size: 14px;
        }
        
        .admin-nav .nav-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 4px;
            padding: 5px;
            flex-shrink: 0;
        }
        
        .admin-nav .nav-toggle span {
            width: 25px;
            height: 3px;
            background: white;
            border-radius: 2px;
            transition: 0.3s;
        }
        
        /* Logout button special styling */
        .admin-nav .nav-link.logout-link {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .admin-nav .nav-link.logout-link:hover {
            background: rgba(220, 53, 69, 0.4);
        }
        
        /* Profile button special styling */
        .admin-nav .nav-link.profile-link {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .admin-nav .nav-link.profile-link:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        /* Desktop: Show all items */
        @media (min-width: 1025px) {
            .admin-nav .nav-menu {
                flex-wrap: nowrap;
            }
            
            .admin-nav .nav-link {
                font-size: 12px;
                padding: 5px 8px;
            }
            
            .admin-nav .nav-link i {
                font-size: 12px;
            }
            
            .admin-nav .nav-link span {
                font-size: 11px;
            }
            
            .admin-nav .nav-logo {
                font-size: 14px;
            }
            
            .admin-nav .nav-logo span {
                font-size: 13px;
            }
        }
        
        /* Tablet */
        @media (max-width: 1024px) {
            .admin-nav .nav-menu {
                gap: 3px;
            }
            
            .admin-nav .nav-link {
                font-size: 12px;
                padding: 4px 7px;
            }
            
            .admin-nav .nav-link span {
                display: none;
            }
            
            .admin-nav .nav-link i {
                font-size: 16px;
                min-width: 20px;
            }
            
            .admin-nav .nav-logo span:not(.logo-icon) {
                font-size: 13px;
            }
            
            .admin-nav .user-info span {
                display: none;
            }
        }
        
        /* Mobile */
        @media (max-width: 768px) {
            .admin-nav .nav-menu {
                position: fixed;
                top: 52px;
                left: -100%;
                width: 280px;
                height: calc(100vh - 52px);
                background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                flex-direction: column;
                align-items: stretch;
                justify-content: flex-start;
                padding: 15px 10px;
                transition: left 0.3s ease;
                z-index: 1000;
                gap: 2px;
                overflow-y: auto;
                box-shadow: 4px 0 20px rgba(0,0,0,0.2);
            }
            
            .admin-nav .nav-menu.active {
                left: 0;
            }
            
            .admin-nav .nav-item {
                width: 100%;
                text-align: left;
            }
            
            .admin-nav .nav-link {
                padding: 12px 15px;
                justify-content: flex-start;
                border-radius: 6px;
                border-bottom: none;
                font-size: 14px;
                width: 100%;
            }
            
            .admin-nav .nav-link span {
                display: inline;
                font-size: 14px;
                margin-left: 10px;
            }
            
            .admin-nav .nav-link i {
                font-size: 18px;
                min-width: 30px;
                text-align: center;
            }
            
            .admin-nav .nav-toggle {
                display: flex;
            }
            
            .admin-nav .nav-toggle.active span:nth-child(1) {
                transform: rotate(45deg) translate(5px, 5px);
            }
            
            .admin-nav .nav-toggle.active span:nth-child(2) {
                opacity: 0;
            }
            
            .admin-nav .nav-toggle.active span:nth-child(3) {
                transform: rotate(-45deg) translate(7px, -6px);
            }
            
            .admin-nav .user-info {
                font-size: 12px;
                padding: 3px 10px;
            }
            
            .admin-nav .user-info span {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .admin-nav .nav-container {
                padding: 0 10px;
                gap: 5px;
            }
            
            .admin-nav .nav-logo {
                font-size: 13px;
            }
            
            .admin-nav .nav-logo i {
                font-size: 15px;
            }
            
            .admin-nav .nav-menu {
                width: 100%;
            }
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <?php if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false): ?>
        <!-- Admin Navigation - WITH PROFILE LINK -->
        <nav class="admin-nav">
            <div class="nav-container">
                <a href="/admin/dashboard.php" class="nav-logo">
                    <i class="fas fa-cogs"></i>
                    <span>Admin</span>
                </a>
                
                <ul class="nav-menu" id="adminNavMenu">
                    <li class="nav-item">
                        <a href="/admin/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/messages.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
                            <i class="fas fa-envelope"></i>
                            <span>Messages</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/manage-about.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage-about.php' ? 'active' : ''; ?>">
                            <i class="fas fa-edit"></i>
                            <span>About</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/manage-skills.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage-skills.php' ? 'active' : ''; ?>">
                            <i class="fas fa-code"></i>
                            <span>Skills</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/manage-projects.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage-projects.php' ? 'active' : ''; ?>">
                            <i class="fas fa-briefcase"></i>
                            <span>Projects</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/upload-image.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'upload-image.php' ? 'active' : ''; ?>">
                            <i class="fas fa-upload"></i>
                            <span>Images</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/images.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'images.php' ? 'active' : ''; ?>">
                            <i class="fas fa-images"></i>
                            <span>Gallery</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/manage-portfolio-images.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage-portfolio-images.php' ? 'active' : ''; ?>">
                            <i class="fas fa-sync"></i>
                            <span>Portfolio</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/change-password.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'change-password.php' ? 'active' : ''; ?>">
                            <i class="fas fa-key"></i>
                            <span>Password</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/profile.php" class="nav-link profile-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/logout.php" class="nav-link logout-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
                
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Admin'; ?></span>
                </div>
                
                <div class="nav-toggle" id="adminNavToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
        
        <script>
            // Mobile navigation toggle for admin
            document.addEventListener('DOMContentLoaded', function() {
                const navToggle = document.getElementById('adminNavToggle');
                const navMenu = document.getElementById('adminNavMenu');
                
                if (navToggle && navMenu) {
                    navToggle.addEventListener('click', function() {
                        navMenu.classList.toggle('active');
                        navToggle.classList.toggle('active');
                    });
                    
                    // Close menu when clicking a link
                    document.querySelectorAll('.admin-nav .nav-link').forEach(link => {
                        link.addEventListener('click', function() {
                            navMenu.classList.remove('active');
                            navToggle.classList.remove('active');
                        });
                    });
                    
                    // Close menu when clicking outside
                    document.addEventListener('click', function(event) {
                        if (!event.target.closest('.admin-nav')) {
                            navMenu.classList.remove('active');
                            navToggle.classList.remove('active');
                        }
                    });
                }
            });
        </script>
        
    <?php else: ?>
        <!-- Main Website Navigation -->
        <nav class="navbar">
            <div class="nav-container">
                <a href="/" class="nav-logo">
                    <span class="logo-text">AH</span>
                    <span class="logo-sub">Portfolio</span>
                </a>
                
                <ul class="nav-menu">
                    <li class="nav-item"><a href="/" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="/about.php" class="nav-link">About</a></li>
                    <li class="nav-item"><a href="/projects.php" class="nav-link">Projects</a></li>
                    <li class="nav-item"><a href="/contact.php" class="nav-link">Contact</a></li>
                    <?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <li class="nav-item"><a href="/admin/dashboard.php" class="nav-link">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="nav-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="main-content">