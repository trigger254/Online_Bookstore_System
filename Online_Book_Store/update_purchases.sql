-- Add new columns to purchases table
ALTER TABLE purchases
ADD COLUMN IF NOT EXISTS merchant_request_id VARCHAR(50) NULL AFTER mpesa_receipt,
ADD COLUMN IF NOT EXISTS checkout_request_id VARCHAR(50) NULL AFTER merchant_request_id;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_purchases_phone_number ON purchases(phone_number);
CREATE INDEX IF NOT EXISTS idx_purchases_payment_status ON purchases(payment_status); 