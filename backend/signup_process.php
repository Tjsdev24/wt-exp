<?php
require_once 'session.php';
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'renter');

    if (empty($fullname) || empty($email) || empty($password)) {
        echo "All fields are required.";
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo "Email is already registered.";
            exit;
        }

        // Insert new user
        $approval_status = ($role === 'provider') ? 'pending' : 'approved';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, email, password, role, approval_status) VALUES (:username, :email, :password, :role, :approval_status)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":username", $fullname);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":approval_status", $approval_status);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Failed to create account.";
        }
    } else {
        echo "Database connection error.";
    }
}
?>
