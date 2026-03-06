<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireCSO();

$database = new Database();
$db = $database->getConnection();

// Get all categories
$query = "SELECT c.*, 
                 (SELECT COUNT(*) FROM reports WHERE category_id = c.id) as report_count
          FROM categories c 
          ORDER BY c.status DESC, c.name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle category status toggle
if (isset($_GET['toggle_status'])) {
    $category_id = intval($_GET['toggle_status']);
    $query = "UPDATE categories SET status = IF(status='active', 'inactive', 'active') WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $category_id);
    $stmt->execute();
    
    header("Location: categories.php");
    exit();
}

// Handle category deletion
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    
    // Check if category has reports
    $check_query = "SELECT COUNT(*) as count FROM reports WHERE category_id = :category_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":category_id", $category_id);
    $check_stmt->execute();
    $report_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($report_count == 0) {
        $delete_query = "DELETE FROM categories WHERE id = :id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(":id", $category_id);
        $delete_stmt->execute();
        
        $_SESSION['success_message'] = "Category deleted successfully";
    } else {
        $_SESSION['error_message'] = "Cannot delete category. It has $report_count reports associated with it.";
    }
    
    header("Location: categories.php");
    exit();
}
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/cso_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Report Categories Management</h1>
        <a href="add_category.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Category
        </a>
    </div>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Categories Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Categories</h6>
            <span class="badge bg-primary"><?= count($categories) ?> categories</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover data-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Reports Count</th>
                            <th>Status</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['name']) ?></td>
                            <td><?= htmlspecialchars($category['description'] ?? 'No description') ?></td>
                            <td>
                                <span class="badge bg-info"><?= $category['report_count'] ?> reports</span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $category['status'] == 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($category['status']) ?>
                                </span>
                            </td>
                            <td><?= formatDate($category['created_at']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit_category.php?id=<?= $category['id'] ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="categories.php?toggle_status=<?= $category['id'] ?>" 
                                       class="btn btn-<?= $category['status'] == 'active' ? 'danger' : 'success' ?>"
                                       onclick="return confirm('Are you sure you want to <?= $category['status'] == 'active' ? 'deactivate' : 'activate' ?> this category?')">
                                        <i class="fas fa-<?= $category['status'] == 'active' ? 'times' : 'check' ?>"></i>
                                    </a>
                                    <a href="categories.php?delete=<?= $category['id'] ?>" 
                                       class="btn btn-danger confirm-delete"
                                       onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Categories</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($categories) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Categories</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($categories, function($c) { return $c['status'] == 'active'; })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Reports</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= array_sum(array_column($categories, 'report_count')) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>