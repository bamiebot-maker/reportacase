<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireOfficer();

$database = new Database();
$db = $database->getConnection();
$officer_id = $_SESSION['user_id'];

// Get all cases assigned to this officer
$query = "SELECT c.*, r.report_code, r.location, r.description, r.priority, 
                 cat.name as category_name
          FROM cases c 
          JOIN reports r ON c.report_id = r.id 
          JOIN categories cat ON r.category_id = cat.id 
          WHERE c.officer_id = :officer_id 
          ORDER BY c.updated_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(":officer_id", $officer_id);
$stmt->execute();
$cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get evidence counts for each case
$evidence_counts = [];
foreach ($cases as $case) {
    $evidence_query = "SELECT COUNT(*) as count FROM evidence_files WHERE case_id = :case_id";
    $evidence_stmt = $db->prepare($evidence_query);
    $evidence_stmt->bindParam(":case_id", $case['id']);
    $evidence_stmt->execute();
    $result = $evidence_stmt->fetch(PDO::FETCH_ASSOC);
    $evidence_counts[$case['id']] = $result['count'];
}
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/officer_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Assigned Cases</h1>
        <span class="badge bg-primary"><?= count($cases) ?> cases</span>
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
                                Pending</div>
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

    <!-- Cases Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Assigned Cases</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover data-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Report Code</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Evidence</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cases as $case): ?>
                        <tr>
                            <td><?= htmlspecialchars($case['report_code']) ?></td>
                            <td><?= htmlspecialchars($case['category_name']) ?></td>
                            <td><?= htmlspecialchars($case['location']) ?></td>
                            <td><?= getPriorityBadge($case['priority']) ?></td>
                            <td><?= getStatusBadge($case['status']) ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <i class="fas fa-paperclip"></i> <?= $evidence_counts[$case['id']] ?>
                                </span>
                            </td>
                            <td><?= formatDate($case['updated_at']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="update_case.php?id=<?= $case['id'] ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Update
                                    </a>
                                    <a href="case_details.php?id=<?= $case['id'] ?>" class="btn btn-info">
                                        <i class="fas fa-eye"></i> View
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