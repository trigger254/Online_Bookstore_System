<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get purchase ID from URL
$purchase_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get purchase details with related information
$stmt = $pdo->prepare("SELECT p.*, b.title as book_title, b.description as book_description, 
                              b.cover_image, b.file_path, b.is_free,
                              u.username as reader_name, u.email as reader_email,
                              w.username as author_name, w.email as author_email
                       FROM purchases p 
                       JOIN books b ON p.book_id = b.id 
                       JOIN users u ON p.reader_id = u.id 
                       JOIN users w ON b.author_id = w.id 
                       WHERE p.id = ?");
$stmt->execute([$purchase_id]);
$purchase = $stmt->fetch();

if (!$purchase) {
    header('Location: manage_purchases.php');
    exit();
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>Purchase Details</h2>
            <p class="text-muted">View detailed information about this purchase</p>
        </div>
        <div class="col text-end">
            <a href="manage_purchases.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Purchases
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Book Information</h5>
                </div>
                <div class="card-body">
                    <?php if($purchase['cover_image']): ?>
                        <img src="../assets/images/books/<?php echo htmlspecialchars($purchase['cover_image']); ?>" 
                             alt="<?php echo htmlspecialchars($purchase['book_title']); ?>" 
                             class="img-fluid mb-3">
                    <?php endif; ?>
                    <h5><?php echo htmlspecialchars($purchase['book_title']); ?></h5>
                    <p class="text-muted">By <?php echo htmlspecialchars($purchase['author_name']); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($purchase['book_description'])); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-<?php echo $purchase['is_free'] ? 'success' : 'primary'; ?>">
                            <?php echo $purchase['is_free'] ? 'Free' : 'KSh ' . number_format($purchase['amount'], 2); ?>
                        </span>
                        <a href="../assets/books/<?php echo htmlspecialchars($purchase['file_path']); ?>" 
                           class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-download me-2"></i>Download
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Purchase Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Purchase Details</h6>
                            <p><strong>Purchase ID:</strong> #<?php echo $purchase['id']; ?></p>
                            <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($purchase['purchase_date'])); ?></p>
                            <p><strong>Amount:</strong> KSh <?php echo number_format($purchase['amount'], 2); ?></p>
                            <p>
                                <strong>Status:</strong> 
                                <span class="badge bg-<?php echo $purchase['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($purchase['payment_status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Reader Information</h6>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($purchase['reader_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($purchase['reader_email']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Author Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($purchase['author_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($purchase['author_email']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 