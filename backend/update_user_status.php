<?php
require_once 'session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'approve' or 'reject'

    if (!$user_id || !$action) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters.']);
        exit;
    }

    $status = ($action === 'approve') ? 'approved' : 'rejected';

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        $query = "UPDATE users SET approval_status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $user_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'User ' . ucfirst($status) . ' successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update user status.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database connection error.']);
    }
}
?>
