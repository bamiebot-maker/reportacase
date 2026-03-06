<?php
include 'config/database.php';
include 'config/functions.php';

$database = new Database();
$db = $database->getConnection();

// Get active categories
$categories_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$categories_stmt = $db->prepare($categories_query);
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Departments list
$departments = [
    'Administration',
    'Faculty of Science',
    'Faculty of Arts',
    'Faculty of Social Sciences', 
    'Faculty of Education',
    'Faculty of Agriculture',
    'Library',
    'Student Affairs',
    'Health Center',
    'Works and Services',
    'Other'
];
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Report Security Incident</h4>
                        <a href="track.php" class="btn btn-light btn-sm">
                            <i class="fas fa-search"></i> Track Existing Report
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="report_success.php" id="reportForm" class="needs-validation" novalidate>
                        <!-- Reporter Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user-circle"></i> Your Information (Optional)
                                </h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" class="form-control" name="reporter_name" placeholder="Optional - Leave blank for anonymous">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact Information</label>
                                <input type="text" class="form-control" name="contact" placeholder="Phone or Email (Optional)">
                            </div>
                        </div>

                        <!-- Incident Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-map-marker-alt"></i> Incident Details
                                </h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department/Unit <span class="text-danger">*</span></label>
                                <select class="form-select" name="department" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept ?>"><?= $dept ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a department.</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Incident Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a category.</div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Exact Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="location" 
                                       placeholder="e.g., Faculty of Science Building, Room 101, Main Library Ground Floor" required>
                                <div class="invalid-feedback">Please provide the incident location.</div>
                            </div>
                        </div>

                        <!-- Incident Description -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-file-alt"></i> Incident Description
                            </h5>
                            <div class="mb-3">
                                <label class="form-label">Detailed Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" rows="6" 
                                          placeholder="Please provide a detailed description of the incident including:
- What happened
- When it occurred (date and time)
- People involved (if any)
- Any other relevant information..." required></textarea>
                                <div class="invalid-feedback">Please provide a detailed description of the incident.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Priority Level</label>
                                <select class="form-select" name="priority">
                                    <option value="medium">Medium - Requires attention within 24 hours</option>
                                    <option value="high">High - Requires immediate attention</option>
                                    <option value="low">Low - Minor issue, no immediate danger</option>
                                </select>
                            </div>
                        </div>

                        <!-- Confidentiality Notice -->
                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="fas fa-info-circle fa-2x me-3 mt-1"></i>
                                <div>
                                    <h6 class="alert-heading">Confidential Reporting</h6>
                                    <p class="mb-1">Your report will be handled confidentially. A unique tracking code will be generated for you to follow up on your report.</p>
                                    <small class="text-muted">All reports are reviewed by the Security Division within 24 hours.</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php" class="btn btn-outline-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-warning btn-lg px-5">
                                <i class="fas fa-paper-plane"></i> Submit Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('reportForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
        
        // Disable submit button on valid submission to prevent duplicates
        if (this.checkValidity()) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        }
    } else {
        // Validation passed, show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    }
    this.classList.add('was-validated');
});
</script>

<?php include 'includes/footer.php'; ?>