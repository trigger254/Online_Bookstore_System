<?php
require_once 'config/database.php';

// Delete existing admin user if exists
$stmt = $pdo->prepare("DELETE FROM users WHERE username = 'admin'");
$stmt->execute();

// Create new admin user
$username = 'admin';
$password = 'admin123';
$email = 'admin@bookstore.com';
$role = 'admin';

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
$stmt->execute([$username, $hashed_password, $email, $role]);

echo "Admin user created successfully!<br>";
echo "Username: admin<br>";
echo "Password: admin123<br>";
echo "Role: admin";
?> 