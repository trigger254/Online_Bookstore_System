<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get all purchases with book and user details
$stmt = $pdo->query("SELECT p.*, b.title as book_title, b.cover_image, 
                            u.username as reader_name, w.username as author_name
                     FROM purchases p 
                     JOIN books b ON p.book_id = b.id 
                     JOIN users u ON p.reader_id = u.id 
                     JOIN users w ON b.author_id = w.id 
                     ORDER BY p.purchase_date DESC");
$purchases = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>Manage Purchases</h2>
            <p class="text-muted">View and manage all book purchases</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Reader</th>
                            <th>Author</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchases as $purchase): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if($purchase['cover_image']): ?>
                                            <img src="../assets/images/books/<?php echo htmlspecialchars($purchase['cover_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($purchase['book_title']); ?>" 
                                                 style="width: 50px; height: 70px; object-fit: cover; margin-right: 10px;">
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($purchase['book_title']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($purchase['reader_name']); ?></td>
                                <td><?php echo htmlspecialchars($purchase['author_name']); ?></td>
                                <td>KSh <?php echo number_format($purchase['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($purchase['purchase_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $purchase['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($purchase['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_purchase.php?id=<?php echo $purchase['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 