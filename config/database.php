<?php
class Database {
    private $host = "localhost";
    private $db_name = "security_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Log error instead of displaying
            error_log("Database connection error: " . $exception->getMessage());
            // Return null connection for graceful handling
        }
        return $this->conn;
    }

    public function checkConnection() {
        $this->conn = $this->getConnection();
        if ($this->conn === null) {
            return false;
        }
        return true;
    }
}
?>