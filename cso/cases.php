<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireCSO();

$database = new Database();
$db = $database->getConnection();

// Get all cases with report and officer details
$query = "SELECT c.*, r.report_code, r.location, r.description, r.priority, 
                 cat.name as category_name, u.name as officer_name
          FROM cases c 
          JOIN reports r ON c.report_id = r.id 
          JOIN categories cat ON r.category_id = cat.id 
          LEFT JOIN users u ON c.officer_id = u.id 
          ORDER BY c.updated_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unassigned reports
$unassigned_query = "SELECT r.*, c.name as category_name 
                     FROM reports r 
                     LEFT JOIN categories c ON r.category_id = c.id 
                     WHERE r.id NOT IN (SELECT report_id FROM cases) 
                     AND r.status != 'resolved' 
                     ORDER BY r.created_at DESC";
$unassigned_stmt = $db->prepare($unassigned_query);
$unassigned_stmt->execute();
$unassigned_reports = $unassigned_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/cso_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Case Management</h1>
        <div class="btn-group">
            <a href="assign_case.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-user-shield fa-sm text-white-50"></i> Assign Case
            </a>
        </div>
    </div>

    <!-- Cases Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Cases</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($cases) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
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
                                Pending Cases</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($cases, function($c) { return $c['status'] == 'pending'; })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Investigating</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($cases, function($c) { return $c['status'] == 'investigating'; })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-search fa-2x text-gray-300"></i>
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
                                Resolved</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($cases, function($c) { return $c['status'] == 'resolved'; })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Assigned Cases -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Assigned Cases</h6>
                    <span class="badge bg-primary"><?= count($cases) ?> cases</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover data-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>Case ID</th>
                                    <th>Report Code</th>
                                    <th>Category</th>
                                    <th>Assigned Officer</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cases as $case): ?>
                                <tr>
                                    <td>#<?= $case['id'] ?></td>
                                    <td><?= htmlspecialchars($case['report_code']) ?></td>
                                    <td><?= htmlspecialchars($case['category_name']) ?></td>
                                    <td>
                                        <?php if ($case['officer_name']): ?>
                                            <?= htmlspecialchars($case['officer_name']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= getStatusBadge($case['status']) ?></td>
                                    <td><?= formatDate($case['updated_at']) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="case_details.php?id=<?= $case['id'] ?>" class="btn btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="assign_case.php?case_id=<?= $case['id'] ?>" class="btn btn-warning">
                                                <i class="fas fa-edit"></i>
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

        <!-- Unassigned Reports -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">Unassigned Reports</h6>
                    <span class="badge bg-warning"><?= count($unassigned_reports) ?> reports</span>
                </div>
                <div class="card-body">
                    <?php if (empty($unassigned_reports)): ?>
                        <p class="text-muted text-center">No unassigned reports</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($unassigned_reports as $report): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($report['report_code']) ?></h6>
                                    <small><?= getPriorityBadge($report['priority']) ?></small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars($report['category_name']) ?></p>
                                <small class="text-muted"><?= htmlspecialchars($report['location']) ?></small>
                                <div class="mt-2">
                                    <a href="assign_case.php?report_id=<?= $report['id'] ?>" class="btn btn-sm btn-primary">
                                        Assign Officer
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>