<?php
// Include auth check
require_once '../config/auth.php';
Auth::requireCSO();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../assets/images/fud-logo.png" alt="FUD Logo" height="40" class="d-inline-block align-text-top me-2">
            CSO Dashboard
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-clipboard-list"></i> Reports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="reports.php">All Reports</a></li>
                        <li><a class="dropdown-item" href="cases.php">Case Management</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="managementDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs"></i> Management
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="officers.php">Security Officers</a></li>
                        <li><a class="dropdown-item" href="categories.php">Categories</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount">
                            0
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" id="notificationList" style="min-width: 300px;">
                        <li><a class="dropdown-item text-center" href="#">Loading notifications...</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['name']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>