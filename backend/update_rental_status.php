<?php
require_once 'session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rental_id = $_POST['rental_id'] ?? null;
    $new_status = $_POST['status'] ?? null; // 'accepted' or 'rejected'

    if (!$rental_id || !in_array($new_status, ['accepted', 'rejected'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request parameters.']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        // Verify that the logged-in user is actually the provider of the product in this rental
        $user_id = $_SESSION['user_id'];
        
        $check_query = "SELECT p.provider_id FROM rentals r JOIN products p ON r.product_id = p.id WHERE r.id = :rental_id";
        $stmt = $db->prepare($check_query);
        $stmt->bindParam(":rental_id", $rental_id);
        $stmt->execute();
        $provider = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$provider || $provider['provider_id'] != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized action on this rental.']);
            exit;
        }

        // Update status
        $update_query = "UPDATE rentals SET status = :status WHERE id = :rental_id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(":status", $new_status);
        $update_stmt->bindParam(":rental_id", $rental_id);

        if ($update_stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Rental status updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error updating status.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database connection error.']);
    }
}
?>
