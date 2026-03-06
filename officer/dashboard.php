<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireOfficer();

$database = new Database();
$db = $database->getConnection();
$officer_id = $_SESSION['user_id'];

// Get officer stats
$stats = [];
$queries = [
    'assigned_cases' => "SELECT COUNT(*) as count FROM cases WHERE officer_id = :officer_id",
    'pending_cases' => "SELECT COUNT(*) as count FROM cases WHERE officer_id = :officer_id AND status = 'pending'",
    'investigating_cases' => "SELECT COUNT(*) as count FROM cases WHERE officer_id = :officer_id AND status = 'investigating'",
    'resolved_cases' => "SELECT COUNT(*) as count FROM cases WHERE officer_id = :officer_id AND status = 'resolved'"
];

foreach ($queries as $key => $query) {
    $stmt = $db->prepare($query);
    $stmt->bindParam(":officer_id", $officer_id);
    $stmt->execute();
    $stats[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Get assigned cases
$cases_query = "SELECT c.*, r.report_code, r.location, r.description, cat.name as category_name 
                FROM cases c 
                JOIN reports r ON c.report_id = r.id 
                JOIN categories cat ON r.category_id = cat.id 
                WHERE c.officer_id = :officer_id 
                ORDER BY c.updated_at DESC LIMIT 5";
$cases_stmt = $db->prepare($cases_query);
$cases_stmt->bindParam(":officer_id", $officer_id);
$cases_stmt->execute();
$assigned_cases = $cases_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent notifications
$notifications_query = "SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC LIMIT 5";
$notifications_stmt = $db->prepare($notifications_query);
$notifications_stmt->bindParam(":user_id", $officer_id);
$notifications_stmt->execute();
$recent_notifications = $notifications_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/officer_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Officer Dashboard</h1>
        <span class="badge bg-primary">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></span>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Assigned Cases</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['assigned_cases'] ?></div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['pending_cases'] ?></div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['investigating_cases'] ?></div>
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
                                Resolved Cases</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['resolved_cases'] ?></div>
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
                    <h6 class="m-0 font-weight-bold text-primary">Your Assigned Cases</h6>
                    <a href="cases.php" class="btn btn-sm btn-primary">View All Cases</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Report Code</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Case Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assigned_cases as $case): ?>
                                <tr>
                                    <td><?= htmlspecialchars($case['report_code']) ?></td>
                                    <td><?= htmlspecialchars($case['category_name']) ?></td>
                                    <td><?= htmlspecialchars($case['location']) ?></td>
                                    <td><?= getStatusBadge($case['status']) ?></td>
                                    <td><?= formatDate($case['updated_at']) ?></td>
                                    <td>
                                        <a href="update_case.php?id=<?= $case['id'] ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Update
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
                                <button class="btn btn-sm btn-outline-secondary" onclick="markNotificationRead(<?= $notification['id'] ?>, this)">
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
                            <i class="fas fa-tasks"></i> View My Cases
                        </a>
                        <a href="profile.php" class="btn btn-success btn-block">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markNotificationRead(notificationId, btnElement) {
    $.ajax({
        url: '../ajax/mark_notification_read.php',
        type: 'POST',
        data: { notification_id: notificationId },
        success: function() {
            $(btnElement).closest('.list-group-item').fadeOut(300, function() { $(this).remove(); });
        }
    });
}

function markAllNotificationsRead() {
    $.ajax({
        url: '../ajax/mark_all_notifications_read.php',
        type: 'POST',
        success: function() {
            $('.list-group-item').fadeOut(300, function() { $(this).remove(); });
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>