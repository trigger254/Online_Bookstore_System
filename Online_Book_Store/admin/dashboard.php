<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_books FROM books");
$total_books = $stmt->fetch()['total_books'];

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE role != 'admin'");
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as total_purchases FROM purchases WHERE payment_status = 'completed'");
$total_purchases = $stmt->fetch()['total_purchases'];

$stmt = $pdo->query("SELECT SUM(amount) as total_revenue FROM purchases WHERE payment_status = 'completed'");
$total_revenue = $stmt->fetch()['total_revenue'] ?? 0;

// Get recent purchases
$stmt = $pdo->query("SELECT p.*, u.username as buyer_name, b.title as book_title 
                     FROM purchases p 
                     JOIN users u ON p.reader_id = u.id 
                     JOIN books b ON p.book_id = b.id 
                     ORDER BY p.purchase_date DESC 
                     LIMIT 5");
$recent_purchases = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Admin Dashboard</h2>
    <div>
        <a href="upload_book.php" class="btn btn-success me-2">Upload Book</a>
        <a href="add_user.php" class="btn btn-primary me-2">Add New User</a>
        <a href="manage_users.php" class="btn btn-secondary">Manage Users</a>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Books</h5>
                <h2 class="card-text"><?php echo $total_books; ?></h2>
                <a href="manage_books.php" class="text-white">View Books →</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h2 class="card-text"><?php echo $total_users; ?></h2>
                <a href="manage_users.php" class="text-white">View Users →</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Total Purchases</h5>
                <h2 class="card-text"><?php echo $total_purchases; ?></h2>
                <a href="manage_purchases.php" class="text-white">View Purchases →</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <h2 class="card-text">KSh <?php echo number_format($total_revenue, 2); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Recent Purchases</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Buyer</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_purchases as $purchase): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($purchase['book_title']); ?></td>
                            <td><?php echo htmlspecialchars($purchase['buyer_name']); ?></td>
                            <td>KSh <?php echo number_format($purchase['amount'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($purchase['purchase_date'])); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $purchase['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($purchase['payment_status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 