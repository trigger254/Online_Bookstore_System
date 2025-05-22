<?php
require_once 'config/database.php';

// Check if admin user exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch();

if ($admin) {
    echo "Admin user found:<br>";
    echo "ID: " . $admin['id'] . "<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Role: " . $admin['role'] . "<br>";
    echo "Password hash: " . $admin['password'] . "<br>";
    echo "Created at: " . $admin['created_at'] . "<br>";
} else {
    echo "Admin user not found in database!";
}
?> 