<?php
require_once 'config/database.php';
require_once 'config/functions.php';

$database = new Database();
$db = $database->getConnection();

echo "<h3>🔍 Password Debug Information</h3>";

if ($db) {
    // Get all users
    $users = $db->query("SELECT username, password, LENGTH(password) as pass_len FROM users")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Username</th><th>Password Length</th><th>Test admin123</th><th>Test officer123</th></tr>";
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td><strong>{$user['username']}</strong></td>";
        echo "<td>{$user['pass_len']} characters</td>";
        
        // Test admin123
        $test1 = password_verify('admin123', $user['password']);
        echo "<td>" . ($test1 ? '✅ VALID' : '❌ INVALID') . "</td>";
        
        // Test officer123  
        $test2 = password_verify('officer123', $user['password']);
        echo "<td>" . ($test2 ? '✅ VALID' : '❌ INVALID') . "</td>";
        
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h4>Raw Password Hashes:</h4>";
    foreach ($users as $user) {
        echo "<p><strong>{$user['username']}:</strong> {$user['password']}</p>";
    }
    
} else {
    echo "<p style='color: red;'>Database connection failed!</p>";
}

echo "<hr>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>