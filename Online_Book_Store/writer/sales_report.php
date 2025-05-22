<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is writer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'writer') {
    header('Location: ../login.php');
    exit();
}

// Get writer's sales statistics
$stmt = $pdo->prepare("SELECT 
                            COUNT(*) as total_sales,
                            SUM(amount) as total_revenue,
                            AVG(amount) as average_sale,
                            COUNT(DISTINCT reader_id) as unique_readers
                       FROM purchases p 
                       JOIN books b ON p.book_id = b.id 
                       WHERE b.author_id = ? AND p.payment_status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Get sales by book
$stmt = $pdo->prepare("SELECT b.title, COUNT(*) as sales_count, SUM(p.amount) as total_revenue
                       FROM purchases p 
                       JOIN books b ON p.book_id = b.id 
                       WHERE b.author_id = ? AND p.payment_status = 'completed'
                       GROUP BY b.id, b.title
                       ORDER BY sales_count DESC");
$stmt->execute([$_SESSION['user_id']]);
$sales_by_book = $stmt->fetchAll();

// Get recent sales
$stmt = $pdo->prepare("SELECT p.*, b.title as book_title, u.username as reader_name
                       FROM purchases p 
                       JOIN books b ON p.book_id = b.id 
                       JOIN users u ON p.reader_id = u.id 
                       WHERE b.author_id = ? AND p.payment_status = 'completed'
                       ORDER BY p.purchase_date DESC 
                       LIMIT 10");
$stmt->execute([$_SESSION['user_id']]);
$recent_sales = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>Sales Report</h2>
            <p class="text-muted">View your book sales statistics and history</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Sales</h5>
                    <h2 class="card-text"><?php echo $stats['total_sales']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <h2 class="card-text">KSh <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Average Sale</h5>
                    <h2 class="card-text">KSh <?php echo number_format($stats['average_sale'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Unique Readers</h5>
                    <h2 class="card-text"><?php echo $stats['unique_readers']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sales by Book -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sales by Book</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Sales</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales_by_book as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td><?php echo $book['sales_count']; ?></td>
                                        <td>KSh <?php echo number_format($book['total_revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Sales</h5>
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
                                <?php foreach ($recent_sales as $sale): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sale['book_title']); ?></td>
                                        <td><?php echo htmlspecialchars($sale['reader_name']); ?></td>
                                        <td>KSh <?php echo number_format($sale['amount'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($sale['purchase_date'])); ?></td>
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