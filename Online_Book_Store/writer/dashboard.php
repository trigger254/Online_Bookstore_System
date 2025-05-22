<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is writer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'writer') {
    header('Location: ../login.php');
    exit();
}

// Get writer's books
$stmt = $pdo->prepare("SELECT * FROM books WHERE author_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$books = $stmt->fetchAll();

// Get total sales
$stmt = $pdo->prepare("SELECT COUNT(*) as total_sales, SUM(amount) as total_revenue 
                       FROM purchases p 
                       JOIN books b ON p.book_id = b.id 
                       WHERE b.author_id = ? AND p.payment_status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$sales_stats = $stmt->fetch();

// Get recent sales
$stmt = $pdo->prepare("SELECT p.*, b.title as book_title, u.username as reader_name 
                       FROM purchases p 
                       JOIN books b ON p.book_id = b.id 
                       JOIN users u ON p.reader_id = u.id 
                       WHERE b.author_id = ? 
                       ORDER BY p.purchase_date DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recent_sales = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Books</h5>
                <h2 class="card-text"><?php echo count($books); ?></h2>
                <a href="upload_book.php" class="text-white">Upload New Book →</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Sales</h5>
                <h2 class="card-text"><?php echo $sales_stats['total_sales']; ?></h2>
                <a href="sales_report.php" class="text-white">View Sales Report →</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <h2 class="card-text">KSh <?php echo number_format($sales_stats['total_revenue'] ?? 0, 2); ?></h2>
                <a href="earnings.php" class="text-white">View Earnings →</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">My Books</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><?php echo $book['title']; ?></td>
                                    <td>KSh <?php echo number_format($book['price'], 2); ?></td>
                                    <td><span class="badge bg-<?php echo $book['is_free'] ? 'success' : 'primary'; ?>">
                                        <?php echo $book['is_free'] ? 'Free' : 'Paid'; ?>
                                    </span></td>
                                    <td>
                                        <a href="edit_book.php?id=<?php echo $book['id']; ?>" 
                                           class="btn btn-sm btn-primary">Edit</a>
                                        <a href="delete_book.php?id=<?php echo $book['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Sales</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Reader</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_sales as $sale): ?>
                                <tr>
                                    <td><?php echo $sale['book_title']; ?></td>
                                    <td><?php echo $sale['reader_name']; ?></td>
                                    <td>KSh <?php echo number_format($sale['amount'], 2); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($sale['purchase_date'])); ?></td>
                                    <td><span class="badge bg-<?php echo $sale['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($sale['payment_status']); ?>
                                    </span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 