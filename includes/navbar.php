<?php
// Check if user is logged in by checking session
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">
              <img src="assets/images/fud-logo.png" style="border: none; border-radius: 70px;" alt="FUD Logo"  height="40"  class="d-inline-block align-text-top me-2">
            Security Division
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="report.php"><i class="fas fa-exclamation-triangle"></i> Report Case</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="track.php"><i class="fas fa-search"></i> Track Report</a>
                </li>
                
                <?php if ($isLoggedIn): ?>
                    <!-- Show user-specific links -->
                    <?php if ($userRole === 'CSO'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cso/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        </li>
                    <?php elseif ($userRole === 'Officer'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="officer/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>