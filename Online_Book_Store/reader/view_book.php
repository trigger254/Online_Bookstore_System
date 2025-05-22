<?php

session_start();
require_once '../config/database.php';

// Redirect if not a reader
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reader') {
    header("Location: ../login.php");
    exit();
}

// Get book ID and fetch book details
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($book_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Fetch book and author info
try {
    $stmt = $pdo->prepare("SELECT b.*, u.username as author_name 
                           FROM books b 
                           JOIN users u ON b.author_id = u.id 
                           WHERE b.id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if (!$book) {
        throw new Exception("Book not found.");
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching book: " . $e->getMessage();
    header("Location: dashboard.php");
    exit();
}

// Check if already purchased
try {
    $stmt = $pdo->prepare("SELECT * FROM purchases 
                           WHERE reader_id = ? AND book_id = ? AND payment_status = 'completed'");
    $stmt->execute([$_SESSION['user_id'], $book_id]);
    $purchase = $stmt->fetch();
} catch (Exception $e) {
    $_SESSION['error'] = "Error checking purchase: " . $e->getMessage();
}

// Handle download request
if (isset($_GET['download']) && $_GET['download'] === 'true') {
    if (file_exists($book['file_path'])) {
        // Set headers for download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($book['title']) . '.pdf"');
        header('Content-Length: ' . filesize($book['file_path']));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output file
        readfile($book['file_path']);
        exit();
    } else {
        $_SESSION['error'] = "Book file not found";
    }
}

// Handle STK Push request on form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone_number'])) {
    try {
        // Clean and validate phone number
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone_number']);
        if (strlen($phone) === 8) {
            $phone = '2547' . $phone;
        } elseif (substr($phone, 0, 4) !== '2547') {
            $phone = '2547' . substr($phone, 1);
        }

        if (!preg_match('/^2547[0-9]{8}$/', $phone)) {
            throw new Exception("Invalid phone number format. Use 2547XXXXXXXX");
        }

        // Include access token script
        include 'accessToken.php';

        // Prepare STK push payload
        date_default_timezone_set('Africa/Nairobi');
        $processrequestUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $callbackurl = 'https://ad38-102-0-14-100.ngrok-free.app/Online_Book_Store/reader/callback.php';
        $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
        $BusinessShortCode = '174379';
        $Timestamp = date('YmdHis');
        $Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);

        $Amount = (int)$book['price'];
        $PartyA = $phone;
        $PartyB = $BusinessShortCode;
        $AccountReference = 'Online_Bookstore_System';
        $TransactionDesc = 'Book Purchase';
        $stkpushheader = [
            'Content-Type:application/json',
            'Authorization:Bearer ' . $access_token
        ];

        // STK request payload
        $curl_post_data = [
            'BusinessShortCode' => $BusinessShortCode,
            'Password' => $Password,
            'Timestamp' => $Timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $Amount,
            'PartyA' => $PartyA,
            'PartyB' => $PartyB,
            'PhoneNumber' => $PartyA,
            'CallBackURL' => $callbackurl,
            'AccountReference' => $AccountReference,
            'TransactionDesc' => $TransactionDesc
        ];

        // Send STK push
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $processrequestUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $stkpushheader);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));

        $curl_response = curl_exec($curl);
        $data = json_decode($curl_response);

        if (isset($data->ResponseCode) && $data->ResponseCode == "0") {
            // Save purchase record with 'pending' status
            $stmt = $pdo->prepare("INSERT INTO purchases (reader_id, book_id, amount, phone_number, payment_status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $book_id, $book['price'], $phone]);

            $_SESSION['success'] = "STK push sent. Please complete payment on your phone.";
        } else {
            throw new Exception("STK push failed. Try again later.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Payment Error: " . $e->getMessage();
    }

    // Redirect back to the book details page after the form submission
    header("Location: view_book.php?id=" . $book_id);
    exit();
}

include '../includes/header.php';
?>

<!-- HTML for the book details and payment form -->
<div class="container py-5">
    <h2 class="mb-4"><?php echo $book['title']; ?></h2>
    <p>Author: <?php echo $book['author_name']; ?></p>
    <p>Description: <?php echo $book['description']; ?></p>
    <p>Price: KSh <?php echo number_format($book['price'], 2); ?></p>

    <?php if ($purchase): ?>
        <div class="alert alert-success">
            <h4 class="alert-heading">Payment Successful!</h4>
            <p>Your payment has been processed successfully. You can now download your book.</p>
            <hr>
            <a href="?id=<?php echo $book_id; ?>&download=true" class="btn btn-primary">
                <i class="fas fa-download me-2"></i>Download Book
            </a>
        </div>
    <?php else: ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="phone_number" class="form-label">Enter your phone number (e.g., 254712345678)</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
            </div>
            <button type="submit" class="btn btn-primary">Confirm Payment</button>
        </form>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
