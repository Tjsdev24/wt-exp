<?php
// Include the database class
include_once 'config/db.php';

// Instantiate the class
$database = new Database();

// Call the connection method
$db = $database->getConnection();

// Check if the connection object exists
if($db) {
    echo "Success! The database is connected.";
} else {
    echo "Connection failed. Check your credentials.";
}
?>

