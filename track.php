<?php
// Include all necessary files
require_once 'config/database.php';
require_once 'config/functions.php';

session_start();

$report = null;
$evidence_files = [];
$report_code = '';

if ($_POST || isset($_GET['code'])) {
    $report_code = $_POST['report_code'] ?? $_GET['code'] ?? '';
    $report_code = sanitizeInput($report_code);
    
    if (!empty($report_code)) {
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $query = "SELECT r.*, c.name as category_name, 
                             cat.id as case_id, cat.status as case_status, 
                             cat.notes as case_notes, cat.updated_at as case_updated,
                             u.name as officer_name, u.contact as officer_contact
                      FROM reports r 
                      LEFT JOIN categories c ON r.category_id = c.id 
                      LEFT JOIN cases cat ON r.id = cat.report_id 
                      LEFT JOIN users u ON cat.officer_id = u.id 
                      WHERE r.report_code = :report_code";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":report_code", $report_code);
            $stmt->execute();
            $report = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($report && isset($report['case_id'])) {
                // Get evidence files if case exists
                $evidence_query = "SELECT * FROM evidence_files WHERE case_id = :case_id ORDER BY uploaded_at DESC";
                $evidence_stmt = $db->prepare($evidence_query);
                $evidence_stmt->bindParam(":case_id", $report['case_id']);
                $evidence_stmt->execute();
                $evidence_files = $evidence_stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow border-0">
                <div class="card-header bg-info text-white py-3">
                    <h4 class="mb-0"><i class="fas fa-search"></i> Track Report Status</h4>
                </div>
                <div class="card-body p-4">
                    <!-- Search Form -->
                    <form method="POST" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label">Enter Your Report Tracking Code</label>
                                <input type="text" class="form-control form-control-lg" name="report_code" 
                                       value="<?= htmlspecialchars($report_code) ?>" 
                                       placeholder="e.g., FUD-65A3B7C123D" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-search"></i> Track Report
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Enter the tracking code you received when submitting your report
                            </small>
                        </div>
                    </form>

                    <?php if (isset($report_code) && empty($report_code)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Please enter a tracking code to search.
                        </div>
                    <?php endif; ?>

                    <!-- Results -->
                    <?php if ($report): ?>
                        <div class="tracking-results">
                            <!-- Status Overview -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <h4 class="text-primary">Report: <?= htmlspecialchars($report['report_code']) ?></h4>
                                                    <p class="mb-1"><strong>Category:</strong> <?= htmlspecialchars($report['category_name']) ?></p>
                                                    <p class="mb-1"><strong>Location:</strong> <?= htmlspecialchars($report['location']) ?></p>
                                                    <p class="mb-0"><strong>Submitted:</strong> <?= formatDate($report['created_at']) ?></p>
                                                </div>
                                                <div class="col-md-4 text-center">
                                                    <div class="display-6 fw-bold text-<?= 
                                                        $report['status'] == 'resolved' ? 'success' : 
                                                        ($report['status'] == 'investigating' ? 'warning' : 'secondary') 
                                                    ?>">
                                                        <?= ucfirst($report['status']) ?>
                                                    </div>
                                                    <small class="text-muted">Current Status</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Timeline -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="mb-3">Progress Timeline</h5>
                                    <div class="timeline">
                                        <div class="timeline-item completed">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <h6>Report Submitted</h6>
                                                <p class="text-muted mb-0"><?= formatDate($report['created_at']) ?></p>
                                            </div>
                                        </div>
                                        <div class="timeline-item <?= $report['case_status'] ? 'completed' : '' ?>">
                                            <div class="timeline-marker <?= $report['case_status'] ? 'bg-success' : 'bg-secondary' ?>"></div>
                                            <div class="timeline-content">
                                                <h6>Under Review</h6>
                                                <p class="text-muted mb-0">
                                                    <?= $report['case_status'] ? 'Assigned for investigation' : 'Pending review by security team' ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="timeline-item <?= $report['case_status'] == 'investigating' || $report['case_status'] == 'resolved' ? 'completed' : '' ?>">
                                            <div class="timeline-marker <?= $report['case_status'] == 'investigating' ? 'bg-warning' : ($report['case_status'] == 'resolved' ? 'bg-success' : 'bg-secondary') ?>"></div>
                                            <div class="timeline-content">
                                                <h6>Investigation</h6>
                                                <p class="text-muted mb-0">
                                                    <?= $report['case_status'] == 'investigating' ? 'Currently being investigated' : 
                                                       ($report['case_status'] == 'resolved' ? 'Investigation completed' : 'Awaiting investigation') ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="timeline-item <?= $report['status'] == 'resolved' ? 'completed' : '' ?>">
                                            <div class="timeline-marker <?= $report['status'] == 'resolved' ? 'bg-success' : 'bg-secondary' ?>"></div>
                                            <div class="timeline-content">
                                                <h6>Resolved</h6>
                                                <p class="text-muted mb-0">
                                                    <?= $report['status'] == 'resolved' ? 'Case has been resolved' : 'Case pending resolution' ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Case Details -->
                            <?php if ($report['case_id']): ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0"><i class="fas fa-tasks"></i> Case Details</h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if ($report['officer_name']): ?>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <p><strong>Assigned Officer:</strong> <?= htmlspecialchars($report['officer_name']) ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Case Status:</strong> <?= getStatusBadge($report['case_status']) ?></p>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($report['case_notes']): ?>
                                                <div class="mb-3">
                                                    <strong>Investigation Notes:</strong>
                                                    <div class="border rounded p-3 bg-light mt-1">
                                                        <?= nl2br(htmlspecialchars($report['case_notes'])) ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($evidence_files)): ?>
                                                <div class="mb-3">
                                                    <strong>Evidence Files:</strong>
                                                    <div class="mt-2">
                                                        <?php foreach ($evidence_files as $file): ?>
                                                            <span class="badge bg-secondary me-2">
                                                                <i class="fas fa-paperclip"></i> <?= htmlspecialchars($file['filename']) ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <p class="text-muted mb-0">
                                                <small>Last updated: <?= formatDate($report['case_updated']) ?></small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Report Details -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Report Details</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Department:</strong> <?= htmlspecialchars($report['department']) ?></p>
                                                    <p><strong>Priority:</strong> <?= getPriorityBadge($report['priority']) ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <?php if ($report['reporter_name']): ?>
                                                        <p><strong>Reporter:</strong> <?= htmlspecialchars($report['reporter_name']) ?></p>
                                                    <?php endif; ?>
                                                    <?php if ($report['contact']): ?>
                                                        <p><strong>Contact:</strong> <?= htmlspecialchars($report['contact']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <strong>Description:</strong>
                                                    <div class="border rounded p-3 bg-light mt-1">
                                                        <?= nl2br(htmlspecialchars($report['description'])) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif (isset($report_code) && !empty($report_code)): ?>
                        <div class="alert alert-warning text-center py-4">
                            <i class="fas fa-search fa-3x mb-3 text-warning"></i>
                            <h4>Report Not Found</h4>
                            <p class="mb-3">No report found with tracking code: <strong><?= htmlspecialchars($report_code) ?></strong></p>
                            <div class="d-grid gap-2 d-md-flex justify-content-center">
                                <a href="report.php" class="btn btn-primary me-md-2">
                                    <i class="fas fa-exclamation-triangle"></i> Submit New Report
                                </a>
                                <button onclick="history.back()" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Try Another Code
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    margin-bottom: 20px;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #6c757d;
}
.timeline-item.completed .timeline-marker {
    background-color: #198754;
}
.timeline-content {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
}
</style>

<?php include 'includes/footer.php'; ?>