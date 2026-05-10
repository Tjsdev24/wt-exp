<?php
require_once 'session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

if ($db) {
    // Optionally filter by category if provided in GET request
    $category = $_GET['category'] ?? null;
    
    $query = "SELECT id, title, description, price_per_day, category, status FROM products WHERE status = 'available'";
    if ($category) {
        $query .= " AND category = :category";
    }
    
    $stmt = $db->prepare($query);
    if ($category) {
        $stmt->bindParam(":category", $category);
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert DB IDs to a string prefixed with 'p' to match frontend expected data structure
    foreach ($products as &$product) {
        $product['id'] = 'p' . $product['id'];
        $product['name'] = $product['title'];
        $product['price'] = $product['price_per_day'];
        
        // Add a placeholder image since DB doesn't store one right now
        // Based on category, we can assign an image
        if ($product['category'] == 'electronics') {
            $product['image'] = 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=500&fit=crop';
        } else if ($product['category'] == 'tools') {
            $product['image'] = 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=500&fit=crop';
        } else if ($product['category'] == 'transport') {
            $product['image'] = 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=500&fit=crop';
        } else {
            $product['image'] = 'https://images.unsplash.com/photo-1504280516766-981882cd4b3b?w=500&fit=crop';
        }
        $product['location'] = '📍 Local';
    }
    
    echo json_encode(['status' => 'success', 'data' => $products]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database connection error']);
}
?>
