<?php
require_once 'database.php';

try {
    // Read the SQL file
    $sql = file_get_contents('update_purchases_table.sql');
    
    // Execute the SQL
    $pdo->exec($sql);
    
    echo "Database updated successfully!\n";
} catch (PDOException $e) {
    die("Database update failed: " . $e->getMessage() . "\n");
}
?> 