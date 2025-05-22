<?php
require_once '../config/database.php';

// Check if categories table exists
$stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
if ($stmt->rowCount() == 0) {
    echo "Categories table does not exist!";
    exit();
}

// Check if categories table has data
$stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
$count = $stmt->fetch()['count'];

if ($count == 0) {
    echo "Categories table is empty!";
    exit();
}

// Display all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

echo "<h2>Available Categories:</h2>";
echo "<ul>";
foreach ($categories as $category) {
    echo "<li>ID: " . $category['id'] . " - Name: " . htmlspecialchars($category['name']) . "</li>";
}
echo "</ul>";
?> 