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
                 r.department, cat.name as category_name
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

$error = '';
$success = '';

if ($_POST) {
    $status = sanitizeInput($_POST['status']);
    $notes = sanitizeInput($_POST['notes']);

    // Update case
    $update_query = "UPDATE cases SET status = :status, notes = :notes, updated_at = NOW() WHERE id = :case_id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(":status", $status);
    $update_stmt->bindParam(":notes", $notes);
    $update_stmt->bindParam(":case_id", $case_id);

    if ($update_stmt->execute()) {
        // Update report status
        $update_report_query = "UPDATE reports SET status = :status WHERE id = (SELECT report_id FROM cases WHERE id = :case_id)";
        $update_report_stmt = $db->prepare($update_report_query);
        $update_report_stmt->bindParam(":status", $status);
        $update_report_stmt->bindParam(":case_id", $case_id);
        $update_report_stmt->execute();

        $success = "Case updated successfully!";
        
        // Refresh case data
        $stmt->execute();
        $case = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Error updating case. Please try again.";
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/officer_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Update Case</h1>
        <a href="cases.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Cases
        </a>
    </div>

    <div class="row">
        <!-- Case Details -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Case Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Report Code:</th>
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
                            <th>Location:</th>
                            <td><?= htmlspecialchars($case['location']) ?></td>
                        </tr>
                        <tr>
                            <th>Priority:</th>
                            <td><?= getPriorityBadge($case['priority']) ?></td>
                        </tr>
                        <tr>
                            <th>Current Status:</th>
                            <td><?= getStatusBadge($case['status']) ?></td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <strong>Incident Description:</strong>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($case['description'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evidence Files -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Evidence Files</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($evidence_files)): ?>
                        <p class="text-muted">No evidence files uploaded yet.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($evidence_files as $file): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-paperclip me-2"></i>
                                    <?= htmlspecialchars($file['filename']) ?>
                                    <br>
                                    <small class="text-muted">Uploaded: <?= formatDate($file['uploaded_at']) ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Update Form -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Case Status</h6>
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
                            <label for="status" class="form-label">Case Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" <?= $case['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="investigating" <?= $case['status'] == 'investigating' ? 'selected' : '' ?>>Investigating</option>
                                <option value="resolved" <?= $case['status'] == 'resolved' ? 'selected' : '' ?>>Resolved</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Investigation Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="6"
                                      placeholder="Enter your investigation notes, findings, or updates..."><?= htmlspecialchars($case['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> Updating the case status will automatically update the corresponding report status.
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="cases.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Case</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Evidence Upload -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upload Evidence</h6>
                </div>
                <div class="card-body">
                    <form action="../ajax/upload_evidence.php" method="POST" enctype="multipart/form-data" id="evidenceForm">
                        <input type="hidden" name="case_id" value="<?= $case_id ?>">
                        
                        <div class="mb-3">
                            <label for="evidence_file" class="form-label">Select File</label>
                            <input type="file" class="form-control" id="evidence_file" name="evidence_file" 
                                   accept=".jpg,.jpeg,.png,.gif,.pdf,.mp4,.mpeg" required>
                            <div class="form-text">
                                Allowed file types: JPG, PNG, GIF, PDF, MP4, MPEG (Max: 10MB)
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-upload"></i> Upload Evidence
                        </button>
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

    // Evidence upload form
    const evidenceForm = document.getElementById('evidenceForm');
    evidenceForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        submitBtn.disabled = true;
        
        fetch('../ajax/upload_evidence.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                evidenceForm.reset();
                // Reload page to show new evidence
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message);
            }
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred during upload.');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.card-body').prepend(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>

<?php include '../includes/footer.php'; ?>