-- Create database
CREATE DATABASE IF NOT EXISTS `security_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `security_db`;

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
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
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
  KEY `status` (`status`),
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
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`),
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
  KEY `created_at` (`created_at`),
  CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
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

-- Insert default CSO user (password: csofud)
INSERT INTO `users` (`name`, `username`, `password`, `role`, `contact`) VALUES
('Chief Security Officer FUD', 'csofud', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'CSO', '080-FUD-SEC1');

-- Insert sample officers (password: fudcso2)
INSERT INTO `users` (`name`, `username`, `password`, `role`, `contact`) VALUES
('Officer Musa FUD', 'fudsec1', '$2y$10$r.8HeCJYGyRWB.X8w8r.5u2p5p5p5p5p5p5p5p5p5p5p5p5p5p5p5p5', 'Officer', '080-FUD-SEC2'),
('Officer Bello FUD', 'fudsec2', '$2y$10$r.8HeCJYGyRWB.X8w8r.5u2p5p5p5p5p5p5p5p5p5p5p5p5p5p5p5p5', 'Officer', '080-FUD-SEC3'),
('Officer Okoro FUD', 'fudsec3', '$2y$10$r.8HeCJYGyRWB.X8w8r.5u2p5p5p5p5p5p5p5p5p5p5p5p5p5p5p5p5', 'Officer', '080-FUD-SEC4');

-- Insert sample reports for testing
INSERT INTO `reports` (`report_code`, `reporter_name`, `contact`, `department`, `location`, `category_id`, `description`, `status`, `priority`) VALUES
('FUD-65A3B7C123D', 'Ahmed Ibrahim', '08011112222', 'Faculty of Science', 'Science Laboratory Building, Room 205', 1, 'Laptop stolen from laboratory during practical session. Dell Inspiron, black color, 15.6 inch screen. Last seen at 2:00 PM.', 'pending', 'high'),
('FUD-65A3B7C124E', NULL, NULL, 'Library', 'Main Library, Reading Section A', 6, 'Found a black wallet containing ID card and some cash. Wallet is on the security desk.', 'investigating', 'medium'),
('FUD-65A3B7C125F', 'Dr. Fatima Yusuf', '08033334444', 'Faculty of Arts', 'Administration Block, 2nd Floor', 4, 'Student repeatedly waiting outside office and making inappropriate comments. Feeling uncomfortable and harassed.', 'pending', 'high'),
('FUD-65A3B7C126G', NULL, '08055556666', 'Student Hostel', 'Male Hostel Block B, Room 312', 2, 'Fight between two students over room allocation. Physical altercation occurred, one student has minor injuries.', 'investigating', 'high');

-- Insert sample cases
INSERT INTO `cases` (`report_id`, `officer_id`, `notes`, `status`) VALUES
(2, 2, 'Wallet has been secured at security office. Attempting to contact owner via ID card information.', 'investigating'),
(4, 1, 'Spoke with both students. Mediated the conflict. Minor injuries treated at health center. Monitoring situation.', 'investigating');

-- Insert sample notifications
INSERT INTO `notifications` (`user_id`, `message`, `link`) VALUES
(1, 'New high priority report: FUD-65A3B7C123D', 'cso/reports.php'),
(1, 'New high priority report: FUD-65A3B7C125F', 'cso/reports.php'),
(2, 'Case assigned: Found wallet at library', 'officer/cases.php'),
(1, 'Case investigation updated: Student fight at hostel', 'cso/cases.php');

-- Create indexes for better performance
CREATE INDEX idx_reports_category_status ON reports(category_id, status);
CREATE INDEX idx_cases_officer_status ON cases(officer_id, status);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
CREATE INDEX idx_user_activity_user_date ON user_activity(user_id, created_at);