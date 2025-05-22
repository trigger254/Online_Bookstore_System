<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is reader
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reader') {
    header('Location: ../login.php');
    exit();
}

// Get reader's purchase history
$stmt = $pdo->prepare("SELECT p.*, b.title as book_title, b.cover_image 
                       FROM purchases p 
                       JOIN books b ON p.book_id = b.id 
                       WHERE p.reader_id = ? 
                       ORDER BY p.purchase_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$purchases = $stmt->fetchAll();

// Get recommended books
$stmt = $pdo->query("SELECT * FROM books WHERE is_free = 1 OR price > 0 ORDER BY created_at DESC LIMIT 6");
$recommended_books = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2>My Purchase History</h2>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchases as $purchase): ?>
                                <tr>
                                    <td><?php echo $purchase['book_title']; ?></td>
                                    <td>KSh <?php echo number_format($purchase['amount'], 2); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($purchase['purchase_date'])); ?></td>
                                    <td><span class="badge bg-<?php echo $purchase['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($purchase['payment_status']); ?>
                                    </span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="browse_books.php" class="btn btn-primary">Browse Books</a>
                    <a href="view_purchases.php" class="btn btn-info">View All Purchases</a>
                    <a href="profile.php" class="btn btn-secondary">My Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<h2 class="mb-4">Recommended Books</h2>
<div class="row">
    <?php foreach ($recommended_books as $book): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="../assets/images/books/<?php echo $book['cover_image']; ?>" 
                     class="card-img-top book-cover" 
                     alt="<?php echo $book['title']; ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $book['title']; ?></h5>
                    <p class="card-text">
                        <?php echo substr($book['description'], 0, 100) . '...'; ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-<?php echo $book['is_free'] ? 'success' : 'primary'; ?>">
                            <?php echo $book['is_free'] ? 'Free' : 'KSh ' . number_format($book['price'], 2); ?>
                        </span>
                        <a href="view_book.php?id=<?php echo $book['id']; ?>" 
                           class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?> 