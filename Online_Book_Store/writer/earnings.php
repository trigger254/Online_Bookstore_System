<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is writer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'writer') {
    header('Location: ../login.php');
    exit();
}

// Get writer's total earnings
$stmt = $pdo->prepare("SELECT 
                            SUM(amount) as total_earnings,
                            COUNT(*) as total_sales,
                            AVG(amount) as average_earning
                       FROM purchases p 
                       JOIN books b ON p.book_id = b.id 
                       WHERE b.author_id = ? AND p.payment_status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$earnings = $stmt->fetch();

// Get earnings by book
$stmt = $pdo->prepare("SELECT b.title, COUNT(*) as sales_count, SUM(p.amount) as total_earnings
                       FROM purchases p 
                       JOIN books b ON p.book_id = b.id 
                       WHERE b.author_id = ? AND p.payment_status = 'completed'
                       GROUP BY b.id, b.title
                       ORDER BY total_earnings DESC");
$stmt->execute([$_SESSION['user_id']]);
$earnings_by_book = $stmt->fetchAll();

// Get recent earnings
$stmt = $pdo->prepare("SELECT p.*, b.title as book_title, u.username as reader_name
                       FROM purchases p 
                       JOIN books b ON p.book_id = b.id 
                       JOIN users u ON p.reader_id = u.id 
                       WHERE b.author_id = ? AND p.payment_status = 'completed'
                       ORDER BY p.purchase_date DESC 
                       LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$recent_earnings = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>My Earnings</h2>
            <p class="text-muted">Track your book sales earnings and payment history</p>
        </div>
    </div>

    <!-- Earnings Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Earnings</h5>
                    <h2 class="card-text">KSh <?php echo number_format($earnings['total_earnings'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Sales</h5>
                    <h2 class="card-text"><?php echo $earnings['total_sales']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Average Earning per Sale</h5>
                    <h2 class="card-text">KSh <?php echo number_format($earnings['average_earning'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Earnings by Book -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Earnings by Book</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Sales</th>
                                    <th>Earnings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($earnings_by_book as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td><?php echo $book['sales_count']; ?></td>
                                        <td>KSh <?php echo number_format($book['total_earnings'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Earnings -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Earnings</h5>
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_earnings as $earning): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($earning['book_title']); ?></td>
                                        <td><?php echo htmlspecialchars($earning['reader_name']); ?></td>
                                        <td>KSh <?php echo number_format($earning['amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($earning['purchase_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 