<?php
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Alter Products Table to allow 'pending'
    $sql_alter = "ALTER TABLE products MODIFY COLUMN status ENUM('pending', 'available', 'rented', 'rejected') DEFAULT 'available'";
    $db->exec($sql_alter);
    echo "Products table altered to allow pending status.<br>";

    // 2. Create Reports Table
    $sql_reports = "CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reporter_id INT,
        product_id INT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('open', 'resolved') DEFAULT 'open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    $db->exec($sql_reports);
    echo "Table 'reports' created successfully.<br>";

    // 3. Add approval_status to users
    $sql_users_alter = "ALTER TABLE users ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved'";
    try {
        $db->exec($sql_users_alter);
        echo "Users table altered to include approval_status.<br>";
    } catch (PDOException $e) {
        // If column already exists, ignore
        echo "Users table already has approval_status or error: " . $e->getMessage() . "<br>";
    }

    // Insert dummy report data just so we have something to show
    $db->exec("INSERT IGNORE INTO reports (reporter_id, title, description) VALUES 
        ((SELECT id FROM users LIMIT 1), 'Item mismatch', 'User reported a different accessory set than shown.'),
        ((SELECT id FROM users LIMIT 1), 'Late pickup complaint', 'Provider requested review of repeated cancellations.')
    ");

    echo "Dummy data inserted.<br>";
    echo "<strong>Database Update Complete!</strong>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
