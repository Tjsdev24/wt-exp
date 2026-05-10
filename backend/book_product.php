<?php
require_once 'session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to book an item.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'] ?? null;
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $total_price = $_POST['total_price'] ?? null;

    if (!$product_id || !$start_date || !$end_date || !$total_price) {
        echo json_encode(['status' => 'error', 'message' => 'Missing booking details.']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        $user_id = $_SESSION['user_id'];
        $query = "INSERT INTO rentals (user_id, product_id, start_date, end_date, total_price) VALUES (:user_id, :product_id, :start_date, :end_date, :total_price)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);
        $stmt->bindParam(":total_price", $total_price);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Booking request sent successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to process booking.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database connection error.']);
    }
}
?>
