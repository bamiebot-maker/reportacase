<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireCSO();

$database = new Database();
$db = $database->getConnection();

// Get report ID or case ID from URL
$report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;
$case_id = isset($_GET['case_id']) ? intval($_GET['case_id']) : 0;

// Fetch report data
$report = null;
$case = null;

if ($report_id > 0) {
    $query = "SELECT r.*, c.name as category_name FROM reports r 
              LEFT JOIN categories c ON r.category_id = c.id 
              WHERE r.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $report_id);
    $stmt->execute();
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($case_id > 0) {
    $query = "SELECT c.*, r.*, cat.name as category_name, u.name as officer_name 
              FROM cases c 
              JOIN reports r ON c.report_id = r.id 
              JOIN categories cat ON r.category_id = cat.id 
              LEFT JOIN users u ON c.officer_id = u.id 
              WHERE c.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $case_id);
    $stmt->execute();
    $case = $stmt->fetch(PDO::FETCH_ASSOC);
    $report_id = $case['report_id'];
    $report = $case;
}

if (!$report) {
    header("Location: cases.php");
    exit();
}

// Get active officers
$officers_query = "SELECT * FROM users WHERE role = 'Officer' AND status = 'active' ORDER BY name";
$officers_stmt = $db->prepare($officers_query);
$officers_stmt->execute();
$officers = $officers_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_POST) {
    if (!isset($_POST['csrf_token']) || !Auth::validateCSRFToken($_POST['csrf_token'])) {
        $error = "Invalid security token. Please refresh and try again.";
    } else {
        $officer_id = intval($_POST['officer_id']);
        $notes = sanitizeInput($_POST['notes']);

        if (empty($officer_id)) {
        $error = "Please select an officer to assign this case to";
    } else {
        if ($case_id > 0) {
            // Update existing case
            $query = "UPDATE cases SET officer_id = :officer_id, notes = :notes, updated_at = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":officer_id", $officer_id);
            $stmt->bindParam(":notes", $notes);
            $stmt->bindParam(":id", $case_id);
        } else {
            // Create new case
            $query = "INSERT INTO cases (report_id, officer_id, notes, status) VALUES (:report_id, :officer_id, :notes, 'pending')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":report_id", $report_id);
            $stmt->bindParam(":officer_id", $officer_id);
            $stmt->bindParam(":notes", $notes);
        }

        if ($stmt->execute()) {
            // Update report status
            $update_report_query = "UPDATE reports SET status = 'investigating' WHERE id = :report_id";
            $update_stmt = $db->prepare($update_report_query);
            $update_stmt->bindParam(":report_id", $report_id);
            $update_stmt->execute();

            // Notify the assigned officer
            $officer_name = '';
            foreach ($officers as $officer) {
                if ($officer['id'] == $officer_id) {
                    $officer_name = $officer['name'];
                    break;
                }
            }

            $notify_query = "INSERT INTO notifications (user_id, message, link) 
                            VALUES (:user_id, :message, 'officer/cases.php')";
            $notify_stmt = $db->prepare($notify_query);
            $notify_stmt->bindParam(":user_id", $officer_id);
            $message = "New case assigned: " . $report['report_code'];
            $notify_stmt->bindParam(":message", $message);
            $notify_stmt->execute();

            $success = "Case assigned successfully to " . $officer_name . "!";
        } else {
            $error = "Error assigning case. Please try again.";
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
        <h1 class="h3 mb-0 text-gray-800"><?= $case_id > 0 ? 'Reassign Case' : 'Assign Case' ?></h1>
        <a href="cases.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Cases
        </a>
    </div>

    <div class="row">
        <!-- Report Details -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Report Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Report Code:</th>
                            <td><?= htmlspecialchars($report['report_code']) ?></td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td><?= htmlspecialchars($report['category_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Department:</th>
                            <td><?= htmlspecialchars($report['department']) ?></td>
                        </tr>
                        <tr>
                            <th>Location:</th>
                            <td><?= htmlspecialchars($report['location']) ?></td>
                        </tr>
                        <tr>
                            <th>Priority:</th>
                            <td><?= getPriorityBadge($report['priority']) ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td><?= getStatusBadge($report['status']) ?></td>
                        </tr>
                        <tr>
                            <th>Date Reported:</th>
                            <td><?= formatDate($report['created_at']) ?></td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <strong>Description:</strong>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($report['description'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignment Form -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Assignment Details</h6>
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
                        <input type="hidden" name="csrf_token" value="<?= Auth::generateCSRFToken() ?>">
                        <div class="mb-3">
                            <label for="officer_id" class="form-label">Assign to Officer <span class="text-danger">*</span></label>
                            <select class="form-select" id="officer_id" name="officer_id" required>
                                <option value="">Select an officer...</option>
                                <?php foreach ($officers as $officer): ?>
                                    <option value="<?= $officer['id'] ?>" 
                                        <?= ($case && $case['officer_id'] == $officer['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($officer['name']) ?> 
                                        (<?= htmlspecialchars($officer['contact'] ?? 'No contact') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select an officer.</div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Assignment Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"
                                      placeholder="Any specific instructions or notes for the officer..."><?= htmlspecialchars($case['notes'] ?? '') ?></textarea>
                        </div>

                        <?php if ($case): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Current Assignment:</strong> 
                            <?php if ($case['officer_name']): ?>
                                This case is currently assigned to <?= htmlspecialchars($case['officer_name']) ?>
                            <?php else: ?>
                                This case is not currently assigned to any officer.
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="cases.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <?= $case_id > 0 ? 'Reassign Case' : 'Assign Case' ?>
                            </button>
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
        } else {
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }
        form.classList.add('was-validated');
    });
});
</script>

<?php include '../includes/footer.php'; ?>