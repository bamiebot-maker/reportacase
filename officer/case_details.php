<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireOfficer();

$database = new Database();
$db = $database->getConnection();
$officer_id = $_SESSION['user_id'];

// Get case ID from URL
$case_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch case data and verify ownership
$query = "SELECT c.*, r.report_code, r.location, r.description, r.priority, 
                 r.department, r.reporter_name, r.contact, r.created_at as report_date,
                 cat.name as category_name
          FROM cases c 
          JOIN reports r ON c.report_id = r.id 
          JOIN categories cat ON r.category_id = cat.id 
          WHERE c.id = :case_id AND c.officer_id = :officer_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":case_id", $case_id);
$stmt->bindParam(":officer_id", $officer_id);
$stmt->execute();
$case = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$case) {
    header("Location: cases.php");
    exit();
}

// Get evidence files
$evidence_query = "SELECT * FROM evidence_files WHERE case_id = :case_id ORDER BY uploaded_at DESC";
$evidence_stmt = $db->prepare($evidence_query);
$evidence_stmt->bindParam(":case_id", $case_id);
$evidence_stmt->execute();
$evidence_files = $evidence_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/officer_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Case Details</h1>
        <div class="btn-group">
            <a href="cases.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Cases
            </a>
            <a href="update_case.php?id=<?= $case_id ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Update Case
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Case Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Case Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Report Code:</th>
                                    <td><?= htmlspecialchars($case['report_code']) ?></td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td><?= htmlspecialchars($case['category_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td><?= htmlspecialchars($case['department']) ?></td>
                                </tr>
                                <tr>
                                    <th>Priority:</th>
                                    <td><?= getPriorityBadge($case['priority']) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Case Status:</th>
                                    <td><?= getStatusBadge($case['status']) ?></td>
                                </tr>
                                <tr>
                                    <th>Date Reported:</th>
                                    <td><?= formatDate($case['report_date']) ?></td>
                                </tr>
                                <tr>
                                    <th>Date Assigned:</th>
                                    <td><?= formatDate($case['assigned_at']) ?></td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td><?= formatDate($case['updated_at']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6>Location:</h6>
                        <div class="border rounded p-3 bg-light">
                            <?= htmlspecialchars($case['location']) ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6>Incident Description:</h6>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($case['description'])) ?>
                        </div>
                    </div>

                    <?php if ($case['reporter_name'] || $case['contact']): ?>
                    <div class="mb-4">
                        <h6>Reporter Information:</h6>
                        <div class="border rounded p-3 bg-light">
                            <?php if ($case['reporter_name']): ?>
                                <strong>Name:</strong> <?= htmlspecialchars($case['reporter_name']) ?><br>
                            <?php endif; ?>
                            <?php if ($case['contact']): ?>
                                <strong>Contact:</strong> <?= htmlspecialchars($case['contact']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($case['notes']): ?>
                    <div class="mb-4">
                        <h6>Investigation Notes:</h6>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($case['notes'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Evidence & Actions