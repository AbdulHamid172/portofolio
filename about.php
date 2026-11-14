<?php
require_once 'config/db.php';
require_once 'includes/image-helpers.php';
require_once 'includes/functions.php';

// Get dynamic image for about page
$about_profile_image = get_dynamic_image('about_profile', '/assets/images/profile/placeholder.jpg');

// Get about content from database
$about_content = [];
$about_sections = ['intro', 'education', 'focus', 'aspiration'];
foreach ($about_sections as $key) {
    $about_content[$key] = get_about_content($key, '');
}

// Get skills grouped by category
$skills_grouped = get_skills_grouped();

// Category labels
$category_labels = [
    'frontend' => 'Frontend',
    'backend' => 'Backend & Database',
    'tools' => 'Tools & Others'
];

// Get projects for display
$projects = get_projects(6);

// Get education timeline from database
$education_timeline = get_education_timeline();
?>
<?php include 'includes/header.php'; ?>

<!-- About Hero -->
<section class="page-hero">
    <div class="container">
        <h1 class="page-title" data-aos="fade-up">About Me</h1>
        <p class="page-subtitle" data-aos="fade-up" data-aos-delay="100">
            Information Technology Student | Aspiring Developer
        </p>
    </div>
</section>

<!-- About Details -->
<section class="section about-details">
    <div class="container">
        <div class="about-grid">
            <div class="about-image" data-aos="fade-right">
                <div class="image-container">
                    <img src="<?php echo $about_profile_image; ?>" 
                         alt="Abdul Hamid" 
                         onerror="this.src='/assets/images/placeholder.jpg'">
                </div>
            </div>
            
            <div class="about-info" data-aos="fade-left">
                <h2 class="about-heading">Hi, I'm Abdul Hamid</h2>
                
                <?php if (!empty($about_content['intro'])): ?>
                    <p class="about-intro"><?php echo nl2br(htmlspecialchars($about_content['intro'])); ?></p>
                <?php else: ?>
                    <p class="about-intro">I'm currently studying Information Technology at Mount Kenya University, Coast Campus. After completing my secondary education, I decided to pursue my passion for technology and software development.</p>
                <?php endif; ?>
                
                <div class="about-features">
                    <div class="feature">
                        <i class="fas fa-graduation-cap"></i>
                        <div>
                            <h4>Current Education</h4>
                            <p><?php echo !empty($about_content['education']) ? htmlspecialchars($about_content['education']) : 'Mount Kenya University, Coast Campus - Diploma in Information Technology'; ?></p>
                        </div>
                    </div>
                    <div class="feature">
                        <i class="fas fa-laptop-code"></i>
                        <div>
                            <h4>Focus Areas</h4>
                            <p><?php echo !empty($about_content['focus']) ? htmlspecialchars($about_content['focus']) : 'Web Development, Database Systems, Software Engineering'; ?></p>
                        </div>
                    </div>
                    <div class="feature">
                        <i class="fas fa-rocket"></i>
                        <div>
                            <h4>Aspiration</h4>
                            <p><?php echo !empty($about_content['aspiration']) ? htmlspecialchars($about_content['aspiration']) : 'Becoming a skilled full-stack developer and IT professional'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Skills Section -->
<section class="section skills-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title">My Skills</h2>
            <p class="section-subtitle">Technologies I'm learning and working with</p>
        </div>
        
        <div class="skills-grid">
            <?php if (empty($skills_grouped)): ?>
                <p style="text-align: center; color: #666; grid-column: 1/-1;">No skills added yet. Please add skills from the admin panel.</p>
            <?php else: ?>
                <?php $delay = 0; ?>
                <?php foreach ($skills_grouped as $category => $category_skills): ?>
                    <div class="skill-category" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                        <h3 class="category-title"><?php echo $category_labels[$category] ?? ucfirst($category); ?></h3>
                        <div class="skill-list">
                            <?php foreach ($category_skills as $skill): ?>
                                <div class="skill-item">
                                    <div class="skill-header">
                                        <span><?php echo htmlspecialchars($skill['name']); ?></span>
                                        <span><?php echo $skill['percentage']; ?>%</span>
                                    </div>
                                    <div class="skill-progress">
                                        <div class="progress-bar" style="width: <?php echo $skill['percentage']; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php $delay += 100; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Projects Section -->
<section class="section projects-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title">My Projects</h2>
            <p class="section-subtitle">Projects I've built and contributed to</p>
        </div>
        
        <?php if (empty($projects)): ?>
            <p style="text-align: center; color: #666;">No projects added yet. Check back soon!</p>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $index => $project): ?>
                    <div class="project-card" data-aos="zoom-in" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="project-image">
                            <?php 
                                $project_image = $project['image_path'] ?: '/assets/images/placeholder.jpg';
                            ?>
                            <img src="<?php echo $project_image; ?>" 
                                 alt="<?php echo htmlspecialchars($project['title']); ?>" 
                                 onerror="this.src='/assets/images/placeholder.jpg'">
                            <?php if ($project['is_featured']): ?>
                                <span class="featured-badge">Featured</span>
                            <?php endif; ?>
                            <div class="project-overlay">
                                <div class="overlay-content">
                                    <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                    <p><?php echo htmlspecialchars(substr($project['description'], 0, 80)) . (strlen($project['description']) > 80 ? '...' : ''); ?></p>
                                    <div class="project-actions">
                                        <?php if ($project['project_url']): ?>
                                            <a href="<?php echo $project['project_url']; ?>" target="_blank" class="btn btn-small">Live Demo</a>
                                        <?php endif; ?>
                                        <?php if ($project['github_url']): ?>
                                            <a href="<?php echo $project['github_url']; ?>" target="_blank" class="btn btn-small btn-outline">GitHub</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="project-info">
                            <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                            <p class="project-description">
                                <?php echo htmlspecialchars($project['description']); ?>
                            </p>
                            <?php if ($project['technologies']): ?>
                                <div class="project-tags">
                                    <?php 
                                        $techs = explode(',', $project['technologies']);
                                        foreach ($techs as $tech):
                                            $tech = trim($tech);
                                    ?>
                                        <span class="tag"><?php echo htmlspecialchars($tech); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Education Timeline -->
<section class="section timeline-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2 class="section-title">Education & Journey</h2>
            <p class="section-subtitle">My academic and learning path</p>
        </div>
        
        <div class="timeline">
            <?php if (empty($education_timeline)): ?>
                <p style="text-align: center; color: #666;">No education entries added yet.</p>
            <?php else: ?>
                <?php foreach ($education_timeline as $index => $item): ?>
                    <div class="timeline-item" data-aos="<?php echo $index % 2 == 0 ? 'fade-right' : 'fade-left'; ?>">
                        <div class="timeline-content">
                            <span class="timeline-date"><?php echo htmlspecialchars($item['date']); ?></span>
                            <h3 class="timeline-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <?php if (!empty($item['subtitle'])): ?>
                                <h4 class="timeline-subtitle"><?php echo htmlspecialchars($item['subtitle']); ?></h4>
                            <?php endif; ?>
                            <?php if (!empty($item['description'])): ?>
                                <p class="timeline-description">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>