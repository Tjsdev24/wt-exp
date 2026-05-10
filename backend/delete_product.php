<?php
require_once 'session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'] ?? null;
    $user_id = $_SESSION['user_id'];

    if (!$product_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID.']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        // 1. Verify Ownership & Get Image Path
        $query = "SELECT provider_id, image_path FROM products WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $product_id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product || $product['provider_id'] != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized or product not found.']);
            exit;
        }

        // 2. Decline all pending rentals for this product
        $update_rentals = "UPDATE rentals SET status = 'rejected' WHERE product_id = :id AND status = 'pending'";
        $stmt_rentals = $db->prepare($update_rentals);
        $stmt_rentals->bindParam(":id", $product_id);
        $stmt_rentals->execute();

        // 3. Delete Product
        $delete_query = "DELETE FROM products WHERE id = :id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(":id", $product_id);

        if ($delete_stmt->execute()) {
            // 4. Delete physical image file
            if (!empty($product['image_path'])) {
                $physical_path = '../' . $product['image_path'];
                if (file_exists($physical_path)) {
                    unlink($physical_path);
                }
            }
            echo json_encode(['status' => 'success', 'message' => 'Listing deleted successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete listing.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database connection error.']);
    }
}
?>
