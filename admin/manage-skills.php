<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

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
        $stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Skill deleted successfully!";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
        $message_type = "error";
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $category = trim($_POST['category'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $percentage = (int)($_POST['percentage'] ?? 0);
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name) || empty($category)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } elseif ($percentage < 0 || $percentage > 100) {
        $message = "Percentage must be between 0 and 100.";
        $message_type = "error";
    } else {
        try {
            if ($id > 0) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE skills SET category = ?, name = ?, percentage = ?, display_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$category, $name, $percentage, $display_order, $is_active, $id]);
                $message = "Skill updated successfully!";
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO skills (category, name, percentage, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$category, $name, $percentage, $display_order, $is_active]);
                $message = "Skill added successfully!";
            }
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $message_type = "error";
        }
    }
}

// Get all skills
$skills = get_skills();

// Category options
$categories = ['frontend', 'backend', 'tools'];
$category_labels = [
    'frontend' => 'Frontend',
    'backend' => 'Backend & Database',
    'tools' => 'Tools & Others'
];
?>

<?php include '../includes/header.php'; ?>

<style>
    .manage-skills-page {
        padding: 40px 0;
        background: #f8f9fa;
        min-height: calc(100vh - 60px);
    }
    
    .skills-container {
        max-width: 900px;
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
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 15px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #333;
        font-size: 14px;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid #e1e1e1;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
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
        padding-top: 5px;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .btn-add {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 5px;
    }
    
    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    
    .skills-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
    }
    
    .skills-table th {
        background: #f8f9fa;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #e9ecef;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .skills-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        color: #555;
        font-size: 14px;
        vertical-align: middle;
    }
    
    .skills-table tr:hover {
        background: #f8f9fa;
    }
    
    .badge-active {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .badge-active.active {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-active.inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    
    .btn-action {
        padding: 5px 10px;
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
    
    .edit-row {
        background: #f8f9ff !important;
    }
    
    .edit-row td {
        padding: 10px 15px;
    }
    
    .btn-save-edit {
        background: #28a745;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.3s;
    }
    
    .btn-save-edit:hover {
        background: #218838;
    }
    
    .btn-cancel-edit {
        background: #6c757d;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.3s;
    }
    
    .btn-cancel-edit:hover {
        background: #5a6268;
    }
    
    .inline-form {
        display: inline;
    }
</style>

<div class="manage-skills-page">
    <div class="container">
        <div class="skills-container" data-aos="fade-up">
            <div class="page-header">
                <h1><i class="fas fa-code"></i> Manage Skills</h1>
                <p>Add, edit, or remove skills displayed on your About page</p>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Add Skill Form -->
            <form method="POST" action="">
                <h3 style="margin-bottom: 15px; color: #333;">Add New Skill</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>"><?php echo $category_labels[$cat]; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Skill Name *</label>
                        <input type="text" id="name" name="name" placeholder="e.g., JavaScript" required>
                    </div>
                    <div class="form-group">
                        <label for="percentage">Percentage *</label>
                        <input type="number" id="percentage" name="percentage" placeholder="85" min="0" max="100" required>
                    </div>
                </div>
                <div class="form-row" style="align-items: end;">
                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" id="display_order" name="display_order" placeholder="1" min="0">
                    </div>
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" checked>
                            <label for="is_active" style="margin-bottom: 0;">Active</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-add">
                            <i class="fas fa-plus"></i> Add Skill
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Skills List -->
            <h3 style="margin: 40px 0 20px; color: #333;">Your Skills</h3>
            
            <?php if (empty($skills)): ?>
                <div class="empty-state">
                    <i class="fas fa-code"></i>
                    <h3>No Skills Added</h3>
                    <p>Add your first skill using the form above.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="skills-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Skill</th>
                                <th>Level</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($skills as $skill): ?>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $skill['id']): ?>
                                    <!-- Edit Row -->
                                    <tr class="edit-row">
                                        <form method="POST" action="">
                                            <input type="hidden" name="id" value="<?php echo $skill['id']; ?>">
                                            <td>
                                                <select name="category" required>
                                                    <?php foreach ($categories as $cat): ?>
                                                        <option value="<?php echo $cat; ?>" <?php echo $cat == $skill['category'] ? 'selected' : ''; ?>>
                                                            <?php echo $category_labels[$cat]; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="name" value="<?php echo htmlspecialchars($skill['name']); ?>" required style="width: 100%; padding: 5px 10px; border: 2px solid #667eea; border-radius: 5px;">
                                            </td>
                                            <td>
                                                <input type="number" name="percentage" value="<?php echo $skill['percentage']; ?>" min="0" max="100" required style="width: 70px; padding: 5px 10px; border: 2px solid #667eea; border-radius: 5px;">
                                            </td>
                                            <td>
                                                <input type="number" name="display_order" value="<?php echo $skill['display_order']; ?>" min="0" style="width: 60px; padding: 5px 10px; border: 2px solid #667eea; border-radius: 5px;">
                                            </td>
                                            <td>
                                                <div class="checkbox-group">
                                                    <input type="checkbox" name="is_active" <?php echo $skill['is_active'] ? 'checked' : ''; ?>>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="submit" class="btn-save-edit">
                                                        <i class="fas fa-save"></i> Save
                                                    </button>
                                                    <a href="manage-skills.php" class="btn-cancel-edit">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </a>
                                                </div>
                                            </td>
                                        </form>
                                    </tr>
                                <?php else: ?>
                                    <!-- Display Row -->
                                    <tr>
                                        <td><?php echo $category_labels[$skill['category']] ?? ucfirst($skill['category']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($skill['name']); ?></strong></td>
                                        <td>
                                            <span style="color: #667eea; font-weight: 600;"><?php echo $skill['percentage']; ?>%</span>
                                            <div style="width: 100px; height: 6px; background: #eee; border-radius: 3px; margin-top: 3px; overflow: hidden;">
                                                <div style="width: <?php echo $skill['percentage']; ?>%; height: 100%; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 3px;"></div>
                                            </div>
                                        </td>
                                        <td><?php echo $skill['display_order']; ?></td>
                                        <td>
                                            <span class="badge-active <?php echo $skill['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $skill['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="?edit=<?php echo $skill['id']; ?>" class="btn-action btn-edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $skill['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this skill?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>