<?php
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/auth.php';
Auth::requireCSO();

$database = new Database();
$db = $database->getConnection();

// Get officer ID from URL
$officer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch officer data
$query = "SELECT * FROM users WHERE id = :id AND role = 'Officer'";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $officer_id);
$stmt->execute();
$officer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$officer) {
    header("Location: officers.php");
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    $name = sanitizeInput($_POST['name']);
    $username = sanitizeInput($_POST['username']);
    $contact = sanitizeInput($_POST['contact']);
    $status = sanitizeInput($_POST['status']);
    $change_password = isset($_POST['change_password']);

    // Validate inputs
    if (empty($name) || empty($username)) {
        $error = "Please fill in all required fields";
    } else {
        // Check if username already exists (excluding current officer)
        $check_query = "SELECT id FROM users WHERE username = :username AND id != :id";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(":username", $username);
        $check_stmt->bindParam(":id", $officer_id);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            $error = "Username already exists. Please choose a different username.";
        } else {
            // Build update query
            if ($change_password && !empty($_POST['new_password'])) {
                $new_password = $_POST['new_password'];
                if (strlen($new_password) < 6) {
                    $error = "Password must be at least 6 characters long";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET name = :name, username = :username, contact = :contact, 
                              status = :status, password = :password WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(":password", $hashed_password);
                }
            } else {
                $query = "UPDATE users SET name = :name, username = :username, contact = :contact, 
                          status = :status WHERE id = :id";
                $stmt = $db->prepare($query);
            }

            if (empty($error)) {
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":username", $username);
                $stmt->bindParam(":contact", $contact);
                $stmt->bindParam(":status", $status);
                $stmt->bindParam(":id", $officer_id);

                if ($stmt->execute()) {
                    $success = "Officer information updated successfully!";
                    // Refresh officer data
                    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
                    $stmt->bindParam(":id", $officer_id);
                    $stmt->execute();
                    $officer = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error = "Error updating officer information. Please try again.";
                }
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
        <h1 class="h3 mb-0 text-gray-800">Edit Security Officer</h1>
        <a href="officers.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Officers
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Officer Information</h6>
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($officer['name']) ?>" required>
                                <div class="invalid-feedback">Please provide the officer's full name.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= htmlspecialchars($officer['username']) ?>" required>
                                <div class="invalid-feedback">Please choose a username.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact" class="form-label">Contact Information</label>
                                <input type="text" class="form-control" id="contact" name="contact" 
                                       value="<?= htmlspecialchars($officer['contact'] ?? '') ?>"
                                       placeholder="Phone number or email">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?= $officer['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $officer['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                                <div class="invalid-feedback">Please select a status.</div>
                            </div>
                        </div>

                        <!-- Password Change Section -->
                        <div class="mb-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="change_password" name="change_password">
                                <label class="form-check-label" for="change_password">
                                    Change Password
                                </label>
                            </div>

                            <div id="password_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                                        <div class="form-text">Leave blank to keep current password.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> Officer was created on <?= formatDate($officer['created_at']) ?>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="officers.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Officer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password fields
document.getElementById('change_password').addEventListener('change', function() {
    const passwordFields = document.getElementById('password_fields');
    if (this.checked) {
        passwordFields.style.display = 'block';
    } else {
        passwordFields.style.display = 'none';
    }
});

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
});
</script>

<?php include '../includes/footer.php'; ?>