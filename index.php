<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container-fluid p-0">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center text-white">
            <img src="assets/images/fud-logo.png" alt="FUD Logo" class="mb-4" style="height: 120px; border: none; border-radius:70px; ">
            <h1 class="display-4 fw-bold mb-4">Federal University Dutse Security Division</h1>
            <p class="lead mb-5">Ensuring a safe and secure environment for learning, research, and community engagement</p>
            <div class="d-grid gap-3 d-md-flex justify-content-center">
                <a href="report.php" class="btn btn-warning btn-lg px-5 py-3">
                    <i class="fas fa-exclamation-triangle"></i> Report Security Case
                </a>
                <a href="track.php" class="btn btn-outline-light btn-lg px-5 py-3">
                    <i class="fas fa-search"></i> Track Report Status
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container py-5">
        <div class="row text-center">
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow">
                    <div class="card-body">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h4 class="card-title">24/7 Security</h4>
                        <p class="card-text">Round-the-clock security surveillance and rapid response to ensure campus safety.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow">
                    <div class="card-body">
                        <i class="fas fa-user-secret fa-3x text-primary mb-3"></i>
                        <h4 class="card-title">Anonymous Reporting</h4>
                        <p class="card-text">Report security concerns anonymously with secure tracking codes for follow-up.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow">
                    <div class="card-body">
                        <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                        <h4 class="card-title">Quick Response</h4>
                        <p class="card-text">Dedicated security officers assigned to handle reported cases promptly.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Contacts -->
    <div class="bg-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mx-auto text-center">
                    <h3 class="mb-4">Emergency Contacts</h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border-danger">
                                <div class="card-body">
                                    <i class="fas fa-phone-alt fa-2x text-danger mb-2"></i>
                                    <h5>Emergency Line</h5>
                                    <p class="mb-0">+2349136447931</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <i class="fas fa-first-aid fa-2x text-warning mb-2"></i>
                                    <h5>FUD Medical Emergency</h5>
                                    <p class="mb-0">+2349136447931</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-info">
                                <div class="card-body">
                                    <i class="fas fa-fire-extinguisher fa-2x text-info mb-2"></i>
                                    <h5>FUD Fire Service</h5>
                                    <p class="mb-0">+2349136447931</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>