<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reader') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

// Get all purchases for the reader
$stmt = $pdo->prepare("SELECT p.*, b.title as book_title, b.cover_image, u.username as author_name 
                       FROM purchases p 
                       JOIN books b ON p.book_id = b.id 
                       JOIN users u ON b.author_id = u.id 
                       WHERE p.reader_id = ? 
                       ORDER BY p.purchase_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$purchases = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>My Purchases</h2>
            <p class="text-muted">View all the books you've purchased</p>
        </div>
        <div class="col text-end">
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <?php if(empty($purchases)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>You haven't purchased any books yet.
            <a href="browse_books.php" class="alert-link">Browse books</a> to make your first purchase!
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($purchases as $purchase): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if($purchase['cover_image']): ?>
                            <img src="../assets/images/books/<?php echo htmlspecialchars($purchase['cover_image']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($purchase['book_title']); ?>">
                        <?php else: ?>
                            <img src="../assets/images/default-cover.jpg" 
                                 class="card-img-top" 
                                 alt="Default Cover">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($purchase['book_title']); ?></h5>
                            <p class="card-text text-muted">By <?php echo htmlspecialchars($purchase['author_name']); ?></p>
                            <p class="card-text">
                                <strong>Purchase Date:</strong> <?php echo date('M d, Y', strtotime($purchase['purchase_date'])); ?><br>
                                <strong>Amount:</strong> KSh <?php echo number_format($purchase['amount'], 2); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-<?php echo $purchase['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($purchase['payment_status']); ?>
                                </span>
                                <a href="view_book.php?id=<?php echo $purchase['book_id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-book me-2"></i>View Book
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?> 