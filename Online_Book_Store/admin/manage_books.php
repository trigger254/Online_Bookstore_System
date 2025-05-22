<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle book deletion
if (isset($_GET['delete'])) {
    $book_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    header('Location: manage_books.php');
    exit();
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Books</h2>
    <a href="add_book.php" class="btn btn-primary">Add New Book</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT b.*, u.username as author_name 
                                       FROM books b 
                                       JOIN users u ON b.author_id = u.id 
                                       ORDER BY b.created_at DESC");
                    while ($book = $stmt->fetch()) {
                        echo "<tr>";
                        echo "<td><img src='../assets/images/books/" . htmlspecialchars($book['cover_image']) . "' alt='" . htmlspecialchars($book['title']) . "' style='width: 50px; height: 70px; object-fit: cover;'></td>";
                        echo "<td>" . htmlspecialchars($book['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($book['author_name']) . "</td>";
                        echo "<td>KSh " . number_format($book['price'], 2) . "</td>";
                        echo "<td><span class='badge bg-" . ($book['is_free'] ? 'success' : 'primary') . "'>" . ($book['is_free'] ? 'Free' : 'Paid') . "</span></td>";
                        echo "<td>
                                <a href='edit_book.php?id=" . $book['id'] . "' class='btn btn-sm btn-primary'>Edit</a>
                                <a href='manage_books.php?delete=" . $book['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this book?\")'>Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 