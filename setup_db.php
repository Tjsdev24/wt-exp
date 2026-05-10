<?php
// Include your connection class
// Change this path to 'database.php' if you didn't put it in a config folder
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Create Users Table
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('renter', 'provider', 'admin') DEFAULT 'renter',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql_users);
    echo "Table 'users' created successfully.<br>";

    // 2. Create Products Table
    $sql_products = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price_per_day DECIMAL(10, 2) NOT NULL,
        category VARCHAR(50),
        status ENUM('available', 'rented') DEFAULT 'available',
        provider_id INT,
        FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $db->exec($sql_products);
    echo "Table 'products' created successfully.<br>";

    // 3. Create Rentals Table
    $sql_rentals = "CREATE TABLE IF NOT EXISTS rentals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        product_id INT,
        start_date DATE,
        end_date DATE,
        total_price DECIMAL(10, 2),
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )";
    $db->exec($sql_rentals);
    echo "Table 'rentals' created successfully.<br>";

    echo "<strong>Database Schema Setup Complete!</strong>";

} catch (PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}
?>