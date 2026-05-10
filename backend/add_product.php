<?php
require_once 'session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to add a product.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price_per_day = $_POST['price_per_day'] ?? null;
    $category = trim($_POST['category'] ?? '');

    if (empty($title) || empty($price_per_day) || empty($category)) {
        echo json_encode(['status' => 'error', 'message' => 'Title, price, and category are required.']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        $provider_id = $_SESSION['user_id'];
        $image_path = null;

        // Handle Image Upload
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['product_image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                if ($_FILES['product_image']['size'] <= 5 * 1024 * 1024) { // 5MB limit
                    $new_filename = uniqid('prod_', true) . '.' . $ext;
                    $upload_path = '../uploads/' . $new_filename;

                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                        $image_path = 'uploads/' . $new_filename;
                    }
                }
            }
        }

        $query = "INSERT INTO products (title, description, price_per_day, category, provider_id, image_path) VALUES (:title, :description, :price, :category, :provider_id, :image_path)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price", $price_per_day);
        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":provider_id", $provider_id);
        $stmt->bindParam(":image_path", $image_path);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Product added successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add product.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database connection error.']);
    }
}
?>
