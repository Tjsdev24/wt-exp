<?php
class Database {
    // 1. Define your database credentials
    private $host = "localhost";
    private $db_name = "rentit"; // Replace with the name you used in phpMyAdmin
    private $username = "root";              // Default XAMPP/WAMP username
    private $password = "";                  // Default XAMPP/WAMP password (usually blank)
    public $conn;

    // 2. The connection method
    public function getConnection() {
        $this->conn = null;

        try {
            // Attempt to build the connection
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            
            // Set error mode to exception to catch bugs easily
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            
        } catch(PDOException $exception) {
            // If it fails, output the error message
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>