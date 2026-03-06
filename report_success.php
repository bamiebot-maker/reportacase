<?php
include 'config/database.php';
include 'config/functions.php';

$success = false;
$error = '';
$report_code = '';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $report_code = generateReportCode();
        $reporter_name = sanitizeInput($_POST['reporter_name'] ?? '');
        $contact = sanitizeInput($_POST['contact'] ?? '');
        $department = sanitizeInput($_POST['department']);
        $location = sanitizeInput($_POST['location']);
        $category_id = intval($_POST['category_id']);
        $description = sanitizeInput($_POST['description']);
        $priority = sanitizeInput($_POST['priority'] ?? 'medium');
        
        $query = "INSERT INTO reports (report_code, reporter_name, contact, department, location, category_id, description, priority) 
                  VALUES (:report_code, :reporter_name, :contact, :department, :location, :category_id, :description, :priority)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":report_code", $report_code);
        $stmt->bindParam(":reporter_name", $reporter_name);
        $stmt->bindParam(":contact", $contact);
        $stmt->bindParam(":department", $department);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":priority", $priority);
        
        if ($stmt->execute()) {
            // Notify CSO about new report
            $notify_query = "INSERT INTO notifications (user_id, message, link) 
                            SELECT id, CONCAT('New ', :priority, ' priority report: ', :report_code), 'cso/reports.php' 
                            FROM users WHERE role = 'CSO'";
            $notify_stmt = $db->prepare($notify_query);
            $notify_stmt->bindParam(":report_code", $report_code);
            $notify_stmt->bindParam(":priority", $priority);
            $notify_stmt->execute();
            
            $success = true;
        } else {
            $error = "There was an error submitting your report. Please try again.";
        }
    } else {
        $error = "Database connection failed. Please try again later.";
    }
} else {
    header("Location: report.php");
    exit();
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if ($success): ?>
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white text-center py-4">
                    <i class="fas fa-check-circle fa-4x mb-3"></i>
                    <h3 class="mb-0">Report Submitted Successfully!</h3>
                </div>
                <div class="card-body p-5 text-center">
                    <div class="alert alert-success border-0">
                        <h4 class="alert-heading">Your Tracking Code</h4>
                        <h2 class="display-4 text-primary fw-bold"><?= $report_code ?></h2>
                        <p class="mb-0">Keep this code safe to track your report status</p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <i class="fas fa-search fa-2x text-primary mb-2"></i>
                                    <h5>Track Your Report</h5>
                                    <p class="small">Use the tracking code to check report status</p>
                                    <a href="track.php" class="btn btn-outline-primary btn-sm">Track Report</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <i class="fas fa-plus-circle fa-2x text-warning mb-2"></i>
                                    <h5>New Report</h5>
                                    <p class="small">Submit another security report</p>
                                    <a href="report.php" class="btn btn-outline-warning btn-sm">New Report</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-4 bg-light rounded">
                        <h5>What Happens Next?</h5>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Your report has been received by the Security Division</li>
                            <li class="mb-2"><i class="fas fa-clock text-warning me-2"></i> It will be reviewed within 24 hours</li>
                            <li class="mb-2"><i class="fas fa-user-shield text-primary me-2"></i> A security officer may be assigned to investigate</li>
                            <li class="mb-0"><i class="fas fa-bell text-info me-2"></i> Use your tracking code to check for updates</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <i class="fas fa-phone-alt me-2"></i>
                            <strong>Emergency?</strong> For immediate assistance, call Security Emergency Line: <strong>080-1234-5678</strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php elseif (!empty($error)): ?>
            <div class="card shadow border-0">
                <div class="card-header bg-danger text-white text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h3 class="mb-0">Submission Failed</h3>
                </div>
                <div class="card-body p-5 text-center">
             <?php
// Include all necessary files
require_once 'config/database.php';
require_once 'config/functions.php';

session_start();

$success = false;
$error = '';
$report_code = '';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $report_code = generateReportCode();
        $reporter_name = sanitizeInput($_POST['reporter_name'] ?? '');
        $contact = sanitizeInput($_POST['contact'] ?? '');
        $department = sanitizeInput($_POST['department']);
        $location = sanitizeInput($_POST['location']);
        $category_id = intval($_POST['category_id']);
        $description = sanitizeInput($_POST['description']);
        $priority = sanitizeInput($_POST['priority'] ?? 'medium');
        
        $query = "INSERT INTO reports (report_code, reporter_name, contact, department, location, category_id, description, priority) 
                  VALUES (:report_code, :reporter_name, :contact, :department, :location, :category_id, :description, :priority)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":report_code", $report_code);
        $stmt->bindParam(":reporter_name", $reporter_name);
        $stmt->bindParam(":contact", $contact);
        $stmt->bindParam(":department", $department);
        $stmt->bindParam(":location", $location);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":priority", $priority);
        
        if ($stmt->execute()) {
            // Notify CSO about new report using CONCAT function
            $notify_query = "INSERT INTO notifications (user_id, message, link) 
                            SELECT id, CONCAT('New ', :priority, ' priority report: ', :report_code), 'cso/reports.php' 
                            FROM users WHERE role = 'CSO'";
            $notify_stmt = $db->prepare($notify_query);
            $notify_stmt->bindParam(":report_code", $report_code);
            $notify_stmt->bindParam(":priority", $priority);
            $notify_stmt->execute();
            
            $success = true;
        } else {
            $error = "There was an error submitting your report. Please try again.";
        }
    } else {
        $error = "Database connection failed. Please try again later.";
    }
} else {
    header("Location: report.php");
    exit();
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if ($success): ?>
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white text-center py-4">
                    <i class="fas fa-check-circle fa-4x mb-3"></i>
                    <h3 class="mb-0">Report Submitted Successfully!</h3>
                </div>
                <div class="card-body p-5 text-center">
                    <div class="alert alert-success border-0">
                        <h4 class="alert-heading">Your Tracking Code</h4>
                        <h2 class="display-4 text-primary fw-bold"><?= $report_code ?></h2>
                        <p class="mb-0">Keep this code safe to track your report status</p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <i class="fas fa-search fa-2x text-primary mb-2"></i>
                                    <h5>Track Your Report</h5>
                                    <p class="small">Use the tracking code to check report status</p>
                                    <a href="track.php" class="btn btn-outline-primary btn-sm">Track Report</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <i class="fas fa-plus-circle fa-2x text-warning mb-2"></i>
                                    <h5>New Report</h5>
                                    <p class="small">Submit another security report</p>
                                    <a href="report.php" class="btn btn-outline-warning btn-sm">New Report</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-4 bg-light rounded">
                        <h5>What Happens Next?</h5>
                        <ul class="list-unstyled text-start">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Your report has been received by the Security Division</li>
                            <li class="mb-2"><i class="fas fa-clock text-warning me-2"></i> It will be reviewed within 24 hours</li>
                            <li class="mb-2"><i class="fas fa-user-shield text-primary me-2"></i> A security officer may be assigned to investigate</li>
                            <li class="mb-0"><i class="fas fa-bell text-info me-2"></i> Use your tracking code to check for updates</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <i class="fas fa-phone-alt me-2"></i>
                            <strong>Emergency?</strong> For immediate assistance, call Security Emergency Line: <strong>080-1234-5678</strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php elseif (!empty($error)): ?>
            <div class="card shadow border-0">
                <div class="card-header bg-danger text-white text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h3 class="mb-0">Submission Failed</h3>
                </div>
                <div class="card-body p-5 text-center">
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">Error Submitting Report</h4>
                        <p><?= $error ?></p>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-center">
                        <a href="report.php" class="btn btn-primary me-md-2">
                            <i class="fas fa-arrow-left"></i> Try Again
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Return Home
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>       <div class="alert alert-danger">
                        <h4 class="alert-heading">Error Submitting Report</h4>
                        <p><?= $error ?></p>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-center">
                        <a href="report.php" class="btn btn-primary me-md-2">
                            <i class="fas fa-arrow-left"></i> Try Again
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Return Home
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>