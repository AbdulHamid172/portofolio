<?php
require_once 'config/db.php';
require_once 'includes/image-helpers.php';
require_once 'includes/functions.php';

// Get dynamic images
$home_profile_image = get_dynamic_image('home_profile', '/assets/images/profile/profile-main.jpg');

// Get featured projects from database ONLY
$featured_projects = get_projects(3, true);

// If no featured projects, get any active projects (up to 3)
if (empty($featured_projects)) {
    $featured_projects = get_projects(3);
}

// Get about content for the homepage
$intro = get_about_content('intro', '');
?>
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-background">
        <div class="particles" id="particles-js"></div>
    </div>
    
    <div class="container">
        <div class="hero-content" data-aos="fade-up">
            <div class="hero-text">
                <h1 class="hero-title animate-typing">
                    <span class="greeting">Hello, I'm</span>
                    <span class="name">Abdul Hamid</span>
                </h1>
                <div class="hero-subtitle">
                    <span class="typewriter-text">IT Student & Aspiring Developer</span>
                    <span class="cursor">|</span>
                </div>
                <p class="hero-description">
                    A passionate Information Technology student at Mount Kenya University, Coast Campus. 
                    Currently learning and building skills in web development, databases, and software solutions.
                </p>
                <div class="hero-buttons">
                    <a href="#about" class="btn btn-primary">Explore My Work</a>
                    <a href="/contact.php" class="btn btn-outline">Get In Touch</a>
                </div>
            </div>
            
            <div class="hero-image" data-aos="fade-left">
                <div class="image-wrapper">
                    <div class="image-glow"></div>
                    <span class="sparkle"></span>
                    <span class="sparkle"></span>
                    <span class="sparkle"></span>
                    <img src="<?php echo $home_profile_image; ?>" 
                         alt="Abdul Hamid" class="profile-image" 
                         onerror="this.src='/assets/images/placeholder.jpg'">
                </div>
            </div>
        </div>
    </div>
    
    <div class="scroll-indicator">
        <div class="mouse">
            <div class="wheel"></div>
        </div>
        <div class="arrows">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</section>

<!-- About Preview -->
<section id="about" class="section about-preview">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title">About Me</h2>
            <p class="section-subtitle">Get to know me better</p>
        </div>
        
        <div class="about-content">
            <div class="about-text" data-aos="fade-right">
                <h3>Information Technology Student</h3>
                <?php if (!empty($intro)): ?>
                    <p><?php echo nl2br(htmlspecialchars($intro)); ?></p>
                <?php else: ?>
                    <p>
                        I'm currently pursuing a degree in Information Technology at Mount Kenya University, 
                        Coast Campus. Completed my secondary education and now focusing on building a strong 
                        foundation in software development and IT infrastructure.
                    </p>
                    <p>
                        My goal is to become a skilled full-stack developer and contribute to innovative 
                        technological solutions. I'm passionate about learning new technologies and applying 
                        them to solve real-world problems.
                    </p>
                <?php endif; ?>
                
                <div class="skills-preview">
                    <?php 
                        $skills = get_skills();
                        if (!empty($skills)) {
                            // Show top 3 skills
                            $display_skills = array_slice($skills, 0, 3);
                            foreach ($display_skills as $skill):
                    ?>
                        <div class="skill">
                            <span class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></span>
                            <div class="skill-bar">
                                <div class="skill-level" style="width: <?php echo $skill['percentage']; ?>%"></div>
                            </div>
                        </div>
                    <?php 
                            endforeach;
                        } else {
                            // Fallback skills if database is empty
                    ?>
                        <div class="skill">
                            <span class="skill-name">Web Development</span>
                            <div class="skill-bar">
                                <div class="skill-level" style="width: 85%"></div>
                            </div>
                        </div>
                        <div class="skill">
                            <span class="skill-name">Database Management</span>
                            <div class="skill-bar">
                                <div class="skill-level" style="width: 80%"></div>
                            </div>
                        </div>
                        <div class="skill">
                            <span class="skill-name">Programming</span>
                            <div class="skill-bar">
                                <div class="skill-level" style="width: 75%"></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            
            <div class="about-stats" data-aos="fade-left">
                <?php 
                    // Get project count from database
                    $project_count = count(get_projects());
                ?>
                <div class="stat-card">
                    <div class="stat-number" data-count="<?php echo $project_count > 0 ? $project_count : 10; ?>">0</div>
                    <div class="stat-label">Projects Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" data-count="2">0</div>
                    <div class="stat-label">Years Learning</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" data-count="5">0</div>
                    <div class="stat-label">Happy Clients</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" data-count="100">0</div>
                    <div class="stat-label">Cups of Coffee</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Projects Preview - ONLY FROM DATABASE -->
<section class="section projects-preview">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title">Featured Projects</h2>
            <p class="section-subtitle">Some of my recent work</p>
        </div>
        
        <?php if (empty($featured_projects)): ?>
            <div style="text-align: center; padding: 40px 20px;">
                <i class="fas fa-briefcase" style="font-size: 50px; color: #ddd; margin-bottom: 15px; display: block;"></i>
                <p style="color: #666; font-size: 1.1rem;">No projects added yet. Check back soon!</p>
                <p style="color: #999; font-size: 0.9rem;">Projects will appear here once added from the admin panel.</p>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($featured_projects as $index => $project): ?>
                    <div class="project-card" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="project-image">
                            <img src="<?php echo $project['image_path'] ?: '/assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($project['title']); ?>" 
                                 onerror="this.src='/assets/images/placeholder.jpg'">
                            <?php if ($project['is_featured']): ?>
                                <span class="featured-badge">Featured</span>
                            <?php endif; ?>
                            <div class="project-overlay">
                                <a href="/projects.php" class="project-link">View Details</a>
                            </div>
                        </div>
                        <div class="project-info">
                            <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                            <p class="project-description">
                                <?php echo htmlspecialchars(substr($project['description'], 0, 80)) . (strlen($project['description']) > 80 ? '...' : ''); ?>
                            </p>
                            <div class="project-tags">
                                <?php if ($project['technologies']): ?>
                                    <?php 
                                        $techs = explode(',', $project['technologies']);
                                        foreach (array_slice($techs, 0, 3) as $tech):
                                            $tech = trim($tech);
                                    ?>
                                        <span class="tag"><?php echo htmlspecialchars($tech); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="tag"><?php echo $project['category'] ?: 'Project'; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="text-center" data-aos="fade-up">
            <a href="/projects.php" class="btn btn-secondary">View All Projects</a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section cta-section">
    <div class="container">
        <div class="cta-content" data-aos="fade-up">
            <h2 class="cta-title">Let's Create Something Amazing Together</h2>
            <p class="cta-text">
                Have a project in mind? I'd love to hear about it. Send me a message and 
                let's discuss how we can work together.
            </p>
            <a href="/contact.php" class="btn btn-primary btn-large">
                Start a Project <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>