<?php
require_once 'config/db.php';
require_once 'includes/image-helpers.php';
require_once 'includes/functions.php';

// Get dynamic project images
$project_images = [
    'project_1' => get_dynamic_image('project_1', '/assets/images/projects/project1.jpg'),
    'project_2' => get_dynamic_image('project_2', '/assets/images/projects/project2.jpg'),
    'project_3' => get_dynamic_image('project_3', '/assets/images/projects/project3.jpg'),
    'project_4' => get_dynamic_image('project_4', '/assets/images/projects/project4.jpg'),
    'project_5' => get_dynamic_image('project_5', '/assets/images/projects/project5.jpg'),
    'project_6' => get_dynamic_image('project_6', '/assets/images/projects/project6.jpg'),
];

// Get all projects from database
$projects = get_projects();
$project_categories = ['web', 'mobile', 'design', 'fullstack'];
$category_labels = [
    'web' => 'Web Development',
    'mobile' => 'Mobile Apps',
    'design' => 'UI/UX Design',
    'fullstack' => 'Full Stack'
];
?>
<?php include 'includes/header.php'; ?>

<!-- Projects Hero -->
<section class="page-hero">
    <div class="container">
        <h1 class="page-title" data-aos="fade-up">My Projects</h1>
        <p class="page-subtitle" data-aos="fade-up" data-aos-delay="100">
            Showcasing my work and creations
        </p>
    </div>
</section>

<!-- Projects Filter -->
<section class="section projects-section">
    <div class="container">
        <div class="projects-filter" data-aos="fade-up">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="web">Web Development</button>
            <button class="filter-btn" data-filter="mobile">Mobile Apps</button>
            <button class="filter-btn" data-filter="design">UI/UX Design</button>
            <button class="filter-btn" data-filter="fullstack">Full Stack</button>
        </div>
        
        <?php if (empty($projects)): ?>
            <div class="empty-state" style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-briefcase" style="font-size: 60px; color: #ddd; margin-bottom: 20px;"></i>
                <h3 style="color: #333;">No Projects Yet</h3>
                <p style="color: #666;">Projects will appear here once added from the admin panel.</p>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $index => $project): ?>
                    <div class="project-item" data-category="<?php echo $project['category']; ?>" data-aos="fade-up" data-aos-delay="<?php echo ($index % 3) * 100; ?>">
                        <div class="project-card">
                            <div class="project-image">
                                <img src="<?php echo $project['image_path'] ?: '/assets/images/placeholder.jpg'; ?>" 
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
                                <div class="project-tags">
                                    <?php if ($project['technologies']): ?>
                                        <?php 
                                            $techs = explode(',', $project['technologies']);
                                            foreach ($techs as $tech):
                                                $tech = trim($tech);
                                        ?>
                                            <span class="tag"><?php echo htmlspecialchars($tech); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="tag"><?php echo $category_labels[$project['category']] ?? $project['category']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Project Modal -->
<div class="project-modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <div class="modal-body">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>