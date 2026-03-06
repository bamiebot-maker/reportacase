<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireCSO();

$database = new Database();
$db = $database->getConnection();

// Get all officers
$query = "SELECT * FROM users WHERE role = 'Officer' ORDER BY status DESC, name ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$officers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle officer status toggle
if (isset($_GET['toggle_status'])) {
    $officer_id = intval($_GET['toggle_status']);
    $query = "UPDATE users SET status = IF(status='active', 'inactive', 'active') WHERE id = :id AND role = 'Officer'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $officer_id);
    $stmt->execute();
    
    header("Location: officers.php");
    exit();
}
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/cso_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Security Officers Management</h1>
        <a href="add_officer.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Officer
        </a>
    </div>

    <!-- Officers Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Security Officers</h6>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="?filter=all">All Officers</a></li>
                    <li><a class="dropdown-item" href="?filter=active">Active Only</a></li>
                    <li><a class="dropdown-item" href="?filter=inactive">Inactive Only</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover data-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($officers as $officer): ?>
                        <tr>
                            <td><?= htmlspecialchars($officer['name']) ?></td>
                            <td><?= htmlspecialchars($officer['username']) ?></td>
                            <td><?= htmlspecialchars($officer['contact'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge bg-<?= $officer['status'] == 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($officer['status']) ?>
                                </span>
                            </td>
                            <td><?= formatDate($officer['created_at']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit_officer.php?id=<?= $officer['id'] ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="officers.php?toggle_status=<?= $officer['id'] ?>" 
                                       class="btn btn-<?= $officer['status'] == 'active' ? 'danger' : 'success' ?>"
                                       onclick="return confirm('Are you sure you want to <?= $officer['status'] == 'active' ? 'deactivate' : 'activate' ?> this officer?')">
                                        <i class="fas fa-<?= $officer['status'] == 'active' ? 'times' : 'check' ?>"></i>
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
                                Total Officers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($officers) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
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
                                Active Officers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($officers, function($o) { return $o['status'] == 'active'; })) ?>
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
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Inactive Officers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($officers, function($o) { return $o['status'] == 'inactive'; })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>