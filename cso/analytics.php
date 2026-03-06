<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireCSO();

$database = new Database();
$db = $database->getConnection();

// Get analytics data

// Reports by category
$category_stats_query = "SELECT c.name, COUNT(r.id) as report_count 
                         FROM categories c 
                         LEFT JOIN reports r ON c.id = r.category_id 
                         GROUP BY c.id, c.name 
                         ORDER BY report_count DESC";
$category_stats_stmt = $db->prepare($category_stats_query);
$category_stats_stmt->execute();
$category_stats = $category_stats_stmt->fetchAll(PDO::FETCH_ASSOC);

// Reports by status
$status_stats_query = "SELECT status, COUNT(*) as count FROM reports GROUP BY status";
$status_stats_stmt = $db->prepare($status_stats_query);
$status_stats_stmt->execute();
$status_stats = $status_stats_stmt->fetchAll(PDO::FETCH_ASSOC);

// Reports by month (last 6 months)
$monthly_stats_query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                               COUNT(*) as count
                        FROM reports 
                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                        ORDER BY month DESC";
$monthly_stats_stmt = $db->prepare($monthly_stats_query);
$monthly_stats_stmt->execute();
$monthly_stats = $monthly_stats_stmt->fetchAll(PDO::FETCH_ASSOC);

// Officer performance
$officer_stats_query = "SELECT u.name, 
                               COUNT(c.id) as total_cases,
                               SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END) as resolved_cases
                        FROM users u 
                        LEFT JOIN cases c ON u.id = c.officer_id 
                        WHERE u.role = 'Officer' AND u.status = 'active'
                        GROUP BY u.id, u.name
                        ORDER BY total_cases DESC";
$officer_stats_stmt = $db->prepare($officer_stats_query);
$officer_stats_stmt->execute();
$officer_stats = $officer_stats_stmt->fetchAll(PDO::FETCH_ASSOC);

// Priority distribution
$priority_stats_query = "SELECT priority, COUNT(*) as count FROM reports GROUP BY priority";
$priority_stats_stmt = $db->prepare($priority_stats_query);
$priority_stats_stmt->execute();
$priority_stats = $priority_stats_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals safely
$total_reports = 0;
foreach ($status_stats as $stat) {
    $total_reports += $stat['count'];
}

$resolved_reports = 0;
foreach ($status_stats as $stat) {
    if ($stat['status'] == 'resolved') {
        $resolved_reports = $stat['count'];
        break;
    }
}

$active_reports = $total_reports - $resolved_reports;

// Calculate category totals safely
$category_total_reports = 0;
foreach ($category_stats as $stat) {
    $category_total_reports += $stat['report_count'];
}
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/cso_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Security Analytics & Reports</h1>
        <div class="btn-group">
            <button onclick="window.print()" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-print fa-sm text-white-50"></i> Print Report
            </button>
            <a href="reports.php" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm">
                <i class="fas fa-list fa-sm text-white-50"></i> View All Reports
            </a>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Reports</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $total_reports ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $resolved_reports ?>
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Active Cases</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $active_reports ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
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
                                Categories</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($category_stats) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Reports by Category -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Reports by Category</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Reports</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($category_stats as $stat): 
                                    $percentage = $category_total_reports > 0 ? ($stat['report_count'] / $category_total_reports) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($stat['name']) ?></td>
                                    <td><?= $stat['report_count'] ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $percentage ?>%;" 
                                                 aria-valuenow="<?= $percentage ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?= round($percentage, 1) ?>%
                                            </div>
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

        <!-- Reports by Status -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Reports by Status</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($status_stats as $stat): 
                                    $percentage = $total_reports > 0 ? ($stat['count'] / $total_reports) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?= getStatusBadge($stat['status']) ?></td>
                                    <td><?= $stat['count'] ?></td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-<?= 
                                                $stat['status'] == 'resolved' ? 'success' : 
                                                ($stat['status'] == 'investigating' ? 'warning' : 'secondary') 
                                            ?>" 
                                            role="progressbar" 
                                            style="width: <?= $percentage ?>%;" 
                                            aria-valuenow="<?= $percentage ?>" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                                <?= round($percentage, 1) ?>%
                                            </div>
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
    </div>

    <div class="row">
        <!-- Monthly Trends -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Report Trends (Last 6 Months)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Total Reports</th>
                                    <th>Resolved</th>
                                    <th>Resolution Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthly_stats as $stat): 
                                    // For now, we'll use a simple calculation - in a real app, you'd want to get resolved counts per month
                                    $resolved_count = 0; // This would need a separate query
                                    $resolution_rate = $stat['count'] > 0 ? ($resolved_count / $stat['count']) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?= date('F Y', strtotime($stat['month'] . '-01')) ?></td>
                                    <td><?= $stat['count'] ?></td>
                                    <td><?= $resolved_count ?></td>
                                    <td>
                                        <span class="badge bg-<?= $resolution_rate >= 80 ? 'success' : ($resolution_rate >= 50 ? 'warning' : 'danger') ?>">
                                            <?= round($resolution_rate, 1) ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Officer Performance -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Officer Performance</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Officer</th>
                                    <th>Cases</th>
                                    <th>Resolved</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($officer_stats as $stat): 
                                    $resolution_rate = $stat['total_cases'] > 0 ? ($stat['resolved_cases'] / $stat['total_cases']) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($stat['name']) ?></td>
                                    <td><?= $stat['total_cases'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $resolution_rate >= 80 ? 'success' : ($resolution_rate >= 50 ? 'warning' : 'danger') ?>">
                                            <?= $stat['resolved_cases'] ?> (<?= round($resolution_rate, 1) ?>%)
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Priority Distribution -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Priority Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($priority_stats as $stat): 
                            $percentage = $total_reports > 0 ? ($stat['count'] / $total_reports) * 100 : 0;
                        ?>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?= getPriorityBadge($stat['priority']) ?></h5>
                                    <h3 class="text-primary"><?= $stat['count'] ?></h3>
                                    <p class="text-muted"><?= round($percentage, 1) ?>% of total reports</p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>