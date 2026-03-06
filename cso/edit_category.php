<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireCSO();

$database = new Database();
$db = $database->getConnection();

// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch category data
$query = "SELECT * FROM categories WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $category_id);
$stmt->execute();
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: categories.php");
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $status = sanitizeInput($_POST['status']);

    // Validate inputs
    if (empty($name)) {
        $error = "Please enter a category name";
    } else {
        // Check if category name already exists (excluding current category)
        $check_query = "SELECT id FROM categories WHERE name = :name AND id != :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(":name", $name);
        $check_stmt->bindParam(":id", $category_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            $error = "Category name already exists. Please choose a different name.";
        } else {
            // Update category
            $query = "UPDATE categories SET name = :name, description = :description, status = :status WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":id", $category_id);

            if ($stmt->execute()) {
                $success = "Category updated successfully!";
                // Refresh category data
                $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
                $stmt->bindParam(":id", $category_id);
                $stmt->execute();
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Error updating category. Please try again.";
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/cso_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Category</h1>
        <a href="categories.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Categories
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Category Information</h6>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($category['name']) ?>" required>
                            <div class="invalid-feedback">Please provide a category name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                      placeholder="Brief description of this category..."><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?= $category['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $category['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> Category was created on <?= formatDate($category['created_at']) ?>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="categories.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.needs-validation');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>

<?php include '../includes/footer.php'; ?>