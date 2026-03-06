<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    try {
        // Clear existing users
        $clear = $db->prepare("DELETE FROM users");
        $clear->execute();
        
        // Insert CSO user
        $cso_password = password_hash('admin123', PASSWORD_DEFAULT);
        $cso_sql = "INSERT INTO users (name, username, password, role, contact) VALUES 
                   ('Chief Security Officer', 'cso', :password, 'CSO', '08012345678')";
        $cso_stmt = $db->prepare($cso_sql);
        $cso_stmt->bindParam(":password", $cso_password);
        $cso_stmt->execute();
        
        // Insert officers
        $officer_password = password_hash('officer123', PASSWORD_DEFAULT);
        $officers_sql = "INSERT INTO users (name, username, password, role, contact) VALUES 
                        ('Officer John Musa', 'officer1', :password, 'Officer', '08023456789'),
                        ('Officer Aisha Bello', 'officer2', :password, 'Officer', '08034567890'),
                        ('Officer Chinedu Okoro', 'officer3', :password, 'Officer', '08045678901')";
        $officers_stmt = $db->prepare($officers_sql);
        $officers_stmt->bindParam(":password", $officer_password);
        $officers_stmt->execute();
        
        echo "<div style='padding: 20px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "<h3>✅ Users Setup Successfully!</h3>";
        echo "<p><strong>CSO Login:</strong> username: <code>cso</code>, password: <code>admin123</code></p>";
        echo "<p><strong>Officer Logins:</strong></p>";
        echo "<ul>";
        echo "<li>username: <code>officer1</code>, password: <code>officer123</code></li>";
        echo "<li>username: <code>officer2</code>, password: <code>officer123</code></li>";
        echo "<li>username: <code>officer3</code>, password: <code>officer123</code></li>";
        echo "</ul>";
        echo "<p><a href='login.php' style='color: #155724; font-weight: bold;'>Go to Login Page</a></p>";
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "<h3>❌ Setup Failed</h3>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
} else {
    echo "<div style='padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>❌ Database Connection Failed</h3>";
    echo "<p>Please check your database configuration in config/database.php</p>";
    echo "</div>";
}
?>