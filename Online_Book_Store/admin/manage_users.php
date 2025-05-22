<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    // Don't allow deletion of admin users
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user && $user['role'] !== 'admin') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    header('Location: manage_users.php');
    exit();
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Users</h2>
    <a href="add_user.php" class="btn btn-primary">Add New User</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                    while ($user = $stmt->fetch()) {
                        echo "<tr>";
                        echo "<td>{$user['username']}</td>";
                        echo "<td>{$user['email']}</td>";
                        echo "<td><span class='badge bg-" . 
                            ($user['role'] == 'admin' ? 'danger' : 
                            ($user['role'] == 'writer' ? 'info' : 'success')) . 
                            "'>{$user['role']}</span></td>";
                        echo "<td>" . date('Y-m-d', strtotime($user['created_at'])) . "</td>";
                        echo "<td>";
                        if ($user['role'] !== 'admin') {
                            echo "<a href='edit_user.php?id={$user['id']}' class='btn btn-sm btn-primary'>Edit</a> ";
                            echo "<a href='manage_users.php?delete={$user['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 