<?php
// Include all necessary files at the top
require_once 'config/database.php';
require_once 'config/functions.php';
require_once 'config/auth.php';

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    Auth::redirectByRole();
}

$error = '';

if ($_POST) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT id, name, username, password, role, status FROM users WHERE username = :username AND status = 'active'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                // Record login activity
                $activity_query = "INSERT INTO user_activity (user_id, activity, ip_address) VALUES (:user_id, 'Logged in', :ip_address)";
                $activity_stmt = $db->prepare($activity_query);
                $activity_stmt->bindParam(":user_id", $user['id']);
                $activity_stmt->bindParam(":ip_address", $_SERVER['REMOTE_ADDR']);
                $activity_stmt->execute();
                
                if ($user['role'] == 'CSO') {
                    header("Location: cso/dashboard.php");
                } else {
                    header("Location: officer/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Username not found or account inactive";
        }
    } else {
        $error = "Database connection failed";
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <img src="assets/images/fud-logo.png" alt="FUD Logo" class="mb-3" style="height: 60px; border: none; border-radius: 70px;">
                    <h4 class="mb-0">Security Division Login</h4>
                    <small class="opacity-75">Authorized Personnel Only</small>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="loginForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg py-2">
                            <i class="fas fa-sign-in-alt"></i> Login to Security Portal
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> For login issues, contact the Chief Security Officer
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Public Access Links -->
            <div class="text-center mt-4">
                <p class="text-muted">Public Access</p>
                <div class="d-grid gap-2 d-md-flex justify-content-center">
                    <a href="report.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-exclamation-triangle"></i> Report Case
                    </a>
                    <a href="track.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-search"></i> Track Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});
</script>

<?php include 'includes/footer.php'; ?>