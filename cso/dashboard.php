<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireCSO();

$database = new Database();
$db = $database->getConnection();

// Get stats
$stats = [];
$queries = [
    'total_reports' => "SELECT COUNT(*) as count FROM reports",
    'pending_reports' => "SELECT COUNT(*) as count FROM reports WHERE status = 'pending'",
    'investigating_reports' => "SELECT COUNT(*) as count FROM reports WHERE status = 'investigating'",
    'resolved_reports' => "SELECT COUNT(*) as count FROM reports WHERE status = 'resolved'",
    'total_officers' => "SELECT COUNT(*) as count FROM users WHERE role = 'Officer' AND status = 'active'",
    'active_cases' => "SELECT COUNT(*) as count FROM cases WHERE status != 'resolved'"
];

foreach ($queries as $key => $query) {
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Get recent reports
$recent_reports_query = "SELECT r.*, c.name as category_name FROM reports r 
                         LEFT JOIN categories c ON r.category_id = c.id 
                         ORDER BY r.created_at DESC LIMIT 5";
$recent_reports_stmt = $db->prepare($recent_reports_query);
$recent_reports_stmt->execute();
$recent_reports = $recent_reports_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent notifications
$notifications_query = "SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC LIMIT 5";
$notifications_stmt = $db->prepare($notifications_query);
$notifications_stmt->bindParam(":user_id", $_SESSION['user_id']);
$notifications_stmt->execute();
$recent_notifications = $notifications_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/cso_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="reports.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Reports</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_reports'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                                Pending Reports</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['pending_reports'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Resolved Cases</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['resolved_reports'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Security Officers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_officers'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Reports -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Reports</h6>
                    <a href="reports.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_reports as $report): ?>
                                <tr>
                                    <td><?= htmlspecialchars($report['report_code']) ?></td>
                                    <td><?= htmlspecialchars($report['category_name']) ?></td>
                                    <td><?= htmlspecialchars($report['location']) ?></td>
                                    <td><?= getStatusBadge($report['status']) ?></td>
                                    <td><?= formatDate($report['created_at']) ?></td>
                                    <td>
                                        <a href="cases.php?action=view&id=<?= $report['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="assign_case.php?report_id=<?= $report['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-user-shield"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications & Quick Actions -->
        <div class="col-lg-4">
            <!-- Notifications -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Notifications</h6>
                    <a href="#" onclick="markAllNotificationsRead()" class="btn btn-sm btn-outline-primary">Mark All Read</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_notifications)): ?>
                        <p class="text-muted text-center">No new notifications</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_notifications as $notification): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><?= htmlspecialchars($notification['message']) ?></div>
                                    <small class="text-muted"><?= formatDate($notification['created_at']) ?></small>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary" onclick="markNotificationRead(<?= $notification['id'] ?>)">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="cases.php" class="btn btn-primary btn-block">
                            <i class="fas fa-tasks"></i> Manage Cases
                        </a>
                        <a href="officers.php" class="btn btn-success btn-block">
                            <i class="fas fa-user-shield"></i> Manage Officers
                        </a>
                        <a href="reports.php" class="btn btn-info btn-block">
                            <i class="fas fa-chart-bar"></i> View Reports
                        </a>
                        <a href="categories.php" class="btn btn-warning btn-block">
                            <i class="fas fa-tags"></i> Manage Categories
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markNotificationRead(notificationId) {
    $.ajax({
        url: '../ajax/mark_notification_read.php',
        type: 'POST',
        data: { notification_id: notificationId },
        success: function() {
            location.reload();
        }
    });
}

function markAllNotificationsRead() {
    $.ajax({
        url: '../ajax/mark_all_notifications_read.php',
        type: 'POST',
        data: { user_id: <?= $_SESSION['user_id'] ?> },
        success: function() {
            location.reload();
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>