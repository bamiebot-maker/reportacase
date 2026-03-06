<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireCSO();

$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$category_filter = $_GET['category'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query with filters
$query = "SELECT r.*, c.name as category_name FROM reports r 
          LEFT JOIN categories c ON r.category_id = c.id 
          WHERE 1=1";

$params = [];

if ($status_filter != 'all') {
    $query .= " AND r.status = :status";
    $params[':status'] = $status_filter;
}

if ($category_filter != 'all') {
    $query .= " AND r.category_id = :category_id";
    $params[':category_id'] = $category_filter;
}

if (!empty($date_from)) {
    $query .= " AND DATE(r.created_at) >= :date_from";
    $params[':date_from'] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(r.created_at) <= :date_to";
    $params[':date_to'] = $date_to;
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/cso_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Reports Management</h1>
        <div class="btn-group">
            <a href="analytics.php" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm">
                <i class="fas fa-chart-bar fa-sm text-white-50"></i> View Analytics
            </a>
            <button onclick="window.print()" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-print fa-sm text-white-50"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="investigating" <?= $status_filter == 'investigating' ? 'selected' : '' ?>>Investigating</option>
                        <option value="resolved" <?= $status_filter == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="all" <?= $category_filter == 'all' ? 'selected' : '' ?>>All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="reports.php" class="btn btn-secondary">Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Security Reports</h6>
            <span class="badge bg-primary"><?= count($reports) ?> reports</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover data-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Report Code</th>
                            <th>Category</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date Reported</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?= htmlspecialchars($report['report_code']) ?></td>
                            <td><?= htmlspecialchars($report['category_name']) ?></td>
                            <td><?= htmlspecialchars($report['department']) ?></td>
                            <td><?= htmlspecialchars($report['location']) ?></td>
                            <td><?= getPriorityBadge($report['priority']) ?></td>
                            <td><?= getStatusBadge($report['status']) ?></td>
                            <td><?= formatDate($report['created_at']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="report_details.php?id=<?= $report['id'] ?>" class="btn btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="cases.php?action=view&id=<?= $report['id'] ?>" class="btn btn-warning">
                                        <i class="fas fa-tasks"></i>
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
</div>

<?php include '../includes/footer.php'; ?>