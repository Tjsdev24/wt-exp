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
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price_per_day'] ?? null;
    $category = $_POST['category'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (!$product_id || empty($title) || !$price) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        // 1. Verify Ownership
        $check = $db->prepare("SELECT provider_id, image_path FROM products WHERE id = :id");
        $check->bindParam(":id", $product_id);
        $check->execute();
        $product = $check->fetch(PDO::FETCH_ASSOC);

        if (!$product || $product['provider_id'] != $user_id) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
            exit;
        }

        $image_path = $product['image_path'];

        // 2. Handle Image Update if new file provided
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $new_filename = uniqid('prod_', true) . '.' . $ext;
                $upload_path = '../uploads/' . $new_filename;

                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                    // Delete old image
                    if (!empty($product['image_path'])) {
                        $old_path = '../' . $product['image_path'];
                        if (file_exists($old_path)) unlink($old_path);
                    }
                    $image_path = 'uploads/' . $new_filename;
                }
            }
        }

        // 3. Update DB
        $query = "UPDATE products SET title = :title, description = :desc, price_per_day = :price, category = :cat, image_path = :img WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":desc", $description);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":cat", $category);
        $stmt->bindParam(":img", $image_path);
        $stmt->bindParam(":id", $product_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Product updated!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
        }
    }
}
?>
