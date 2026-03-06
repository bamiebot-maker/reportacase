<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireOfficer();

$database = new Database();
$db = $database->getConnection();
$officer_id = $_SESSION['user_id'];

// Fetch officer data
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $officer_id);
$stmt->execute();
$officer = $stmt->fetch(PDO::FETCH_ASSOC);

// Get officer stats
$stats_query = "SELECT 
    COUNT(*) as total_cases,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_cases,
    SUM(CASE WHEN status = 'investigating' THEN 1 ELSE 0 END) as investigating_cases
    FROM cases WHERE officer_id = :officer_id";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->bindParam(":officer_id", $officer_id);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_POST && isset($_POST['update_profile'])) {
    $name = sanitizeInput($_POST['name']);
    $contact = sanitizeInput($_POST['contact']);

    if (empty($name)) {
        $error = "Please enter your name";
    } else {
        $update_query = "UPDATE users SET name = :name, contact = :contact WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(":name", $name);
        $update_stmt->bindParam(":contact", $contact);
        $update_stmt->bindParam(":id", $officer_id);

        if ($update_stmt->execute()) {
            $_SESSION['name'] = $name;
            $success = "Profile updated successfully!";
            // Refresh officer data
            $stmt->execute();
            $officer = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Error updating profile. Please try again.";
        }
    }
}

if ($_POST && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all password fields";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long";
    } else {
        // Verify current password
        if (password_verify($current_password, $officer['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_query = "UPDATE users SET password = :password WHERE id = :id";
            $password_stmt = $db->prepare($password_query);
            $password_stmt->bindParam(":password", $hashed_password);
            $password_stmt->bindParam(":id", $officer_id);

            if ($password_stmt->execute()) {
                $success = "Password changed successfully!";
            } else {
                $error = "Error changing password. Please try again.";
            }
        } else {
            $error = "Current password is incorrect";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include 'includes/officer_navbar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Profile</h1>
    </div>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
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

                    <form method="POST">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?= htmlspecialchars($officer['name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($officer['username']) ?>" readonly>
                                <small class="text-muted">Username cannot be changed</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Information</label>
                            <input type="text" class="form-control" name="contact" 
                                   value="<?= htmlspecialchars($officer['contact'] ?? '') ?>" 
                                   placeholder="Phone number or email">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($officer['role']) ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Account Created</label>
                            <input type="text" class="form-control" value="<?= formatDate($officer['created_at']) ?>" readonly>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="change_password" value="1">
                        <div class="mb-3">
                            <label class="form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="new_password" required minlength="6">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn btn-warning">Change Password</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="col-lg-4">
            <!-- Officer Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">My Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <img class="img-profile rounded-circle mb-3" 
                             src="../assets/images/user-profile.png" 
                             style="width: 100px; height: 100px;">
                        <h4><?= htmlspecialchars($officer['name']) ?></h4>
                        <p class="text-muted">Security Officer</p>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Cases:</span>
                            <strong><?= $stats['total_cases'] ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Resolved Cases:</span>
                            <strong class="text-success"><?= $stats['resolved_cases'] ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Investigating:</span>
                            <strong class="text-warning"><?= $stats['investigating_cases'] ?></strong>
                        </div>
                        <?php if ($stats['total_cases'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Resolution Rate:</span>
                            <strong class="text-info">
                                <?= round(($stats['resolved_cases'] / $stats['total_cases']) * 100, 1) ?>%
                            </strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Links</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="cases.php" class="btn btn-primary">
                            <i class="fas fa-tasks"></i> My Cases
                        </a>
                        <a href="dashboard.php" class="btn btn-success">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="../logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>