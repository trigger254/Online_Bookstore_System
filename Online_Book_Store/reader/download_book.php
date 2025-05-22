<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and has a download pending
if (!isset($_SESSION['user_id']) || !isset($_SESSION['download_book'])) {
    header("Location: dashboard.php");
    exit();
}

$download_info = $_SESSION['download_book'];
$book_id = $download_info['book_id'];
$file_path = $download_info['file_path'];
$title = $download_info['title'];

// Verify the purchase is completed
try {
    $stmt = $pdo->prepare("SELECT * FROM purchases 
        WHERE reader_id = ? AND book_id = ? AND payment_status = 'completed'");
    $stmt->execute([$_SESSION['user_id'], $book_id]);
    $purchase = $stmt->fetch();

    if (!$purchase) {
        throw new Exception("Purchase not found or not completed");
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: dashboard.php");
    exit();
}

// Handle download request
if (isset($_GET['download']) && $_GET['download'] === 'true') {
    if (file_exists($file_path)) {
        // Clear the download session
        unset($_SESSION['download_book']);
        
        // Set headers for download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($title) . '.pdf"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output file
        readfile($file_path);
        exit();
    } else {
        $_SESSION['error'] = "Book file not found";
        header("Location: dashboard.php");
        exit();
    }
}

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="card-title text-success mb-4">Payment Successful!</h2>
                    <p class="lead">Your payment has been processed successfully.</p>
                    <p>You can now download your book: <strong><?php echo htmlspecialchars($title); ?></strong></p>
                    
                    <div class="mt-4">
                        <a href="?download=true" class="btn btn-primary btn-lg">
                            <i class="fas fa-download me-2"></i>Download Book
                        </a>
                    </div>
                    
                    <div class="mt-3">
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show success message when page loads
window.onload = function() {
    alert("Payment successful! You can now download your book.");
}
</script>

<?php include '../includes/footer.php'; ?> 