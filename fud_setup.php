<?php
// Complete FUD Security System Setup - All in One
$host = "localhost";
$dbname = "security_db"; 
$username = "root";
$password = "";

try {
    // Connect to MySQL (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and recreate database
    echo "<h3>Setting up FUD Security Database...</h3>";
    $pdo->exec("DROP DATABASE IF EXISTS $dbname");
    $pdo->exec("CREATE DATABASE $dbname");
    $pdo->exec("USE $dbname");
    echo "<p>✅ Database created successfully</p>";
    
    // Execute SQL directly
    echo "<h3>Creating FUD Security System Tables...</h3>";
    
    // SQL for complete FUD system
    $sql = "
    -- Users table
    CREATE TABLE `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `username` varchar(50) NOT NULL,
      `password` varchar(255) NOT NULL,
      `role` enum('CSO','Officer') NOT NULL,
      `contact` varchar(20) DEFAULT NULL,
      `status` enum('active','inactive') DEFAULT 'active',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Categories table
    CREATE TABLE `categories` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `description` text DEFAULT NULL,
      `status` enum('active','inactive') DEFAULT 'active',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Reports table
    CREATE TABLE `reports` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `report_code` varchar(20) NOT NULL,
      `reporter_name` varchar(100) DEFAULT NULL,
      `contact` varchar(20) DEFAULT NULL,
      `department` varchar(100) NOT NULL,
      `location` varchar(255) NOT NULL,
      `category_id` int(11) DEFAULT NULL,
      `description` text NOT NULL,
      `status` enum('pending','investigating','resolved') DEFAULT 'pending',
      `priority` enum('low','medium','high') DEFAULT 'medium',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `report_code` (`report_code`),
      KEY `category_id` (`category_id`),
      CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Cases table
    CREATE TABLE `cases` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `report_id` int(11) NOT NULL,
      `officer_id` int(11) NOT NULL,
      `notes` text DEFAULT NULL,
      `evidence` text DEFAULT NULL,
      `status` enum('pending','investigating','resolved') DEFAULT 'pending',
      `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `report_id` (`report_id`),
      KEY `officer_id` (`officer_id`),
      CONSTRAINT `cases_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `reports` (`id`) ON DELETE CASCADE,
      CONSTRAINT `cases_ibfk_2` FOREIGN KEY (`officer_id`) REFERENCES `users` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Notifications table
    CREATE TABLE `notifications` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `message` text NOT NULL,
      `link` varchar(255) DEFAULT NULL,
      `is_read` tinyint(1) DEFAULT 0,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Evidence files table
    CREATE TABLE `evidence_files` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `case_id` int(11) NOT NULL,
      `filename` varchar(255) NOT NULL,
      `file_path` varchar(500) NOT NULL,
      `file_type` varchar(50) DEFAULT NULL,
      `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `case_id` (`case_id`),
      CONSTRAINT `evidence_files_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- User activity log table
    CREATE TABLE `user_activity` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `activity` varchar(255) NOT NULL,
      `ip_address` varchar(45) DEFAULT NULL,
      `user_agent` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "<p>✅ Tables created successfully</p>";
    
    // Insert data
    echo "<h3>Inserting FUD Data...</h3>";
    
    // Insert categories
    $categories_sql = "
    INSERT INTO `categories` (`name`, `description`) VALUES
    ('Theft', 'Cases involving stolen property or belongings'),
    ('Assault', 'Physical violence incidents and fights'),
    ('Vandalism', 'Property damage and destruction cases'),
    ('Harassment', 'Bullying, intimidation or harassment incidents'),
    ('Emergency', 'Urgent security matters requiring immediate attention'),
    ('Lost and Found', 'Lost or found items on campus'),
    ('Trespassing', 'Unauthorized access to premises or restricted areas'),
    ('Fire Safety', 'Fire hazards, alarms and safety concerns'),
    ('Traffic Violation', 'Vehicle-related incidents and parking issues'),
    ('Noise Complaint', 'Excessive noise disturbances'),
    ('Suspicious Activity', 'Suspicious persons or activities on campus'),
    ('Other', 'Other security concerns not categorized');
    ";
    $pdo->exec($categories_sql);
    echo "<p>✅ Categories inserted</p>";
    
    // Insert users with hashed passwords
    $cso_password = password_hash('csofud', PASSWORD_DEFAULT);
    $officer_password = password_hash('fudcso2', PASSWORD_DEFAULT);
    
    $users_sql = "
    INSERT INTO `users` (`name`, `username`, `password`, `role`, `contact`) VALUES
    ('Chief Security Officer FUD', 'csofud', '$cso_password', 'CSO', '080-FUD-SEC1'),
    ('Officer Musa FUD', 'fudsec1', '$officer_password', 'Officer', '080-FUD-SEC2'),
    ('Officer Bello FUD', 'fudsec2', '$officer_password', 'Officer', '080-FUD-SEC3'),
    ('Officer Okoro FUD', 'fudsec3', '$officer_password', 'Officer', '080-FUD-SEC4');
    ";
    $pdo->exec($users_sql);
    echo "<p>✅ FUD Users created</p>";
    
    // Insert sample reports
    $reports_sql = "
    INSERT INTO `reports` (`report_code`, `reporter_name`, `contact`, `department`, `location`, `category_id`, `description`, `status`, `priority`) VALUES
    ('FUD-65A3B7C123D', 'Ahmed Ibrahim', '08011112222', 'Faculty of Science', 'Science Laboratory Building, Room 205', 1, 'Laptop stolen from laboratory during practical session. Dell Inspiron, black color, 15.6 inch screen. Last seen at 2:00 PM.', 'pending', 'high'),
    ('FUD-65A3B7C124E', NULL, NULL, 'Library', 'Main Library, Reading Section A', 6, 'Found a black wallet containing ID card and some cash. Wallet is on the security desk.', 'investigating', 'medium'),
    ('FUD-65A3B7C125F', 'Dr. Fatima Yusuf', '08033334444', 'Faculty of Arts', 'Administration Block, 2nd Floor', 4, 'Student repeatedly waiting outside office and making inappropriate comments. Feeling uncomfortable and harassed.', 'pending', 'high'),
    ('FUD-65A3B7C126G', NULL, '08055556666', 'Student Hostel', 'Male Hostel Block B, Room 312', 2, 'Fight between two students over room allocation. Physical altercation occurred, one student has minor injuries.', 'investigating', 'high');
    ";
    $pdo->exec($reports_sql);
    echo "<p>✅ Sample reports inserted</p>";
    
    // Insert sample cases
    $cases_sql = "
    INSERT INTO `cases` (`report_id`, `officer_id`, `notes`, `status`) VALUES
    (2, 2, 'Wallet has been secured at security office. Attempting to contact owner via ID card information.', 'investigating'),
    (4, 1, 'Spoke with both students. Mediated the conflict. Minor injuries treated at health center. Monitoring situation.', 'investigating');
    ";
    $pdo->exec($cases_sql);
    echo "<p>✅ Sample cases assigned</p>";
    
    // Insert sample notifications
    $notifications_sql = "
    INSERT INTO `notifications` (`user_id`, `message`, `link`) VALUES
    (1, 'New high priority report: FUD-65A3B7C123D', 'cso/reports.php'),
    (1, 'New high priority report: FUD-65A3B7C125F', 'cso/reports.php'),
    (2, 'Case assigned: Found wallet at library', 'officer/cases.php'),
    (1, 'Case investigation updated: Student fight at hostel', 'cso/cases.php');
    ";
    $pdo->exec($notifications_sql);
    echo "<p>✅ Sample notifications created</p>";
    
    // Create indexes
    $indexes_sql = "
    CREATE INDEX idx_reports_category_status ON reports(category_id, status);
    CREATE INDEX idx_cases_officer_status ON cases(officer_id, status);
    CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
    CREATE INDEX idx_user_activity_user_date ON user_activity(user_id, created_at);
    ";
    $pdo->exec($indexes_sql);
    echo "<p>✅ Performance indexes created</p>";
    
    // Verify the setup
    echo "<h3>Verifying FUD Setup...</h3>";
    
    $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC);
    $categoryCount = $pdo->query("SELECT COUNT(*) as count FROM categories")->fetch(PDO::FETCH_ASSOC);
    $reportCount = $pdo->query("SELECT COUNT(*) as count FROM reports")->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>✅ FUD Users: " . $userCount['count'] . "</p>";
    echo "<p>✅ Categories: " . $categoryCount['count'] . "</p>";
    echo "<p>✅ Sample Reports: " . $reportCount['count'] . "</p>";
    
    // Test password verification
    echo "<h3>Testing FUD Login Credentials...</h3>";
    $users = $pdo->query("SELECT username, password FROM users")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        if ($user['username'] == 'csofud') {
            $valid = password_verify('csofud', $user['password']);
            echo "<p>CSO FUD: " . ($valid ? '✅ Password VALID' : '❌ Password INVALID') . "</p>";
        } else {
            $valid = password_verify('fudcso2', $user['password']);
            echo "<p>{$user['username']}: " . ($valid ? '✅ Password VALID' : '❌ Password INVALID') . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2>🎉 FUD Security Management System Ready!</h2>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>FUD Login Credentials:</h3>";
    echo "<p><strong>CSO Access (Full Administrator):</strong></p>";
    echo "<p>Username: <code style='background: #f8f9fa; padding: 5px 10px; border-radius: 3px;'>csofud</code></p>";
    echo "<p>Password: <code style='background: #f8f9fa; padding: 5px 10px; border-radius: 3px;'>csofud</code></p>";
    echo "<p><strong>Security Officer Access:</strong></p>";
    echo "<p>Usernames: <code style='background: #f8f9fa; padding: 5px 10px; border-radius: 3px;'>fudsec1</code>, <code style='background: #f8f9fa; padding: 5px 10px; border-radius: 3px;'>fudsec2</code>, <code style='background: #f8f9fa; padding: 5px 10px; border-radius: 3px;'>fudsec3</code></p>";
    echo "<p>Password: <code style='background: #f8f9fa; padding: 5px 10px; border-radius: 3px;'>fudcso2</code> (for all officers)</p>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<a href='login.php' style='font-size: 20px; padding: 15px 30px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>";
    echo "🚀 Launch FUD Security System";
    echo "</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; color: #721c24;'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Please check your MySQL is running and credentials are correct.</p>";
    echo "</div>";
}
?>