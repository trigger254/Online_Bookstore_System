<?php
require_once '../config/database.php';
header("Content-Type: application/json");

// Log the raw input data
$rawData = file_get_contents('php://input');
file_put_contents("Mpesastkresponse.json", $rawData); // For debugging

// Log the raw data for debugging
file_put_contents("callback_debug.txt", 
    date('Y-m-d H:i:s') . "\nRaw Data:\n" . $rawData . "\n\n", 
    FILE_APPEND);

// Try to decode the JSON
$data = json_decode($rawData);
if (json_last_error() !== JSON_ERROR_NONE) {
    file_put_contents("callback_error.log", 
        date('Y-m-d H:i:s') . " - JSON Decode Error: " . json_last_error_msg() . "\nRaw Data: " . $rawData . "\n", 
        FILE_APPEND);
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "Invalid JSON received"]);
    exit();
}

if ($data && isset($data->Body->stkCallback)) {
    $stkCallback = $data->Body->stkCallback;

    // Log the callback data
    file_put_contents("callback_debug.txt", 
        date('Y-m-d H:i:s') . "\nCallback Data:\n" . print_r($stkCallback, true) . "\n\n", 
        FILE_APPEND);

    $MerchantRequestID = $stkCallback->MerchantRequestID ?? '';
    $CheckoutRequestID = $stkCallback->CheckoutRequestID ?? '';
    $ResultCode = $stkCallback->ResultCode ?? '';
    $ResultDesc = $stkCallback->ResultDesc ?? '';

    $Amount = null;
    $MpesaReceiptNumber = null;
    $PhoneNumber = null;

    if ($ResultCode == 0 && isset($stkCallback->CallbackMetadata->Item)) {
        foreach ($stkCallback->CallbackMetadata->Item as $item) {
            if ($item->Name == "Amount") $Amount = $item->Value;
            if ($item->Name == "MpesaReceiptNumber") $MpesaReceiptNumber = $item->Value;
            if ($item->Name == "PhoneNumber") $PhoneNumber = $item->Value;
        }

        // Log the extracted values
        file_put_contents("callback_debug.txt", 
            date('Y-m-d H:i:s') . "\nExtracted Values:\n" .
            "Amount: $Amount\n" .
            "MpesaReceiptNumber: $MpesaReceiptNumber\n" .
            "PhoneNumber: $PhoneNumber\n\n", 
            FILE_APPEND);

        if ($MpesaReceiptNumber && $PhoneNumber) {
            try {
                // First, find the pending purchase with matching phone number
                $stmt = $pdo->prepare("SELECT p.id, p.book_id, b.file_path, b.title 
                    FROM purchases p 
                    JOIN books b ON p.book_id = b.id 
                    WHERE p.payment_status = 'pending' 
                    AND p.phone_number = ? 
                    ORDER BY p.id DESC LIMIT 1");
                $stmt->execute([$PhoneNumber]);
                $purchase = $stmt->fetch();

                if ($purchase) {
                    // Update the specific purchase record
                    $updateStmt = $pdo->prepare("UPDATE purchases 
                        SET payment_status = 'completed', 
                            transaction_id = ?, 
                            payment_date = NOW(),
                            mpesa_receipt = ?,
                            merchant_request_id = ?,
                            checkout_request_id = ?
                        WHERE id = ?");
                    $updateStmt->execute([
                        $MpesaReceiptNumber,
                        $MpesaReceiptNumber,
                        $MerchantRequestID,
                        $CheckoutRequestID,
                        $purchase['id']
                    ]);

                    // Store download information in session
                    session_start();
                    $_SESSION['download_book'] = [
                        'book_id' => $purchase['book_id'],
                        'file_path' => $purchase['file_path'],
                        'title' => $purchase['title']
                    ];

                    // Redirect back to view book page with success message
                    $_SESSION['success'] = "Payment successful! You can now download your book.";
                    header("Location: view_book.php?id=" . $purchase['book_id']);
                    exit();

                    // Log successful update
                    file_put_contents("callback_success.log", 
                        date('Y-m-d H:i:s') . " - Purchase ID: {$purchase['id']} updated successfully\n", 
                        FILE_APPEND);
                } else {
                    file_put_contents("callback_error.log", 
                        date('Y-m-d H:i:s') . " - No pending purchase found for phone: $PhoneNumber\n", 
                        FILE_APPEND);
                }
            } catch (Exception $e) {
                file_put_contents("callback_error.log", 
                    date('Y-m-d H:i:s') . " - DB ERROR: " . $e->getMessage() . "\n", 
                    FILE_APPEND);
            }
        } else {
            file_put_contents("callback_error.log", 
                date('Y-m-d H:i:s') . " - Missing MpesaReceiptNumber or PhoneNumber\n", 
                FILE_APPEND);
        }
    } else {
        file_put_contents("callback_error.log", 
            date('Y-m-d H:i:s') . " - STK Push failed or missing metadata. ResultDesc: $ResultDesc\n", 
            FILE_APPEND);
    }
} else {
    file_put_contents("callback_error.log", 
        date('Y-m-d H:i:s') . " - Invalid callback structure received:\n" . print_r($data, true) . "\n", 
        FILE_APPEND);
}

echo json_encode(["ResultCode" => 0, "ResultDesc" => "Callback received successfully"]);
