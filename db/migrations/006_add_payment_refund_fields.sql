-- Add refund_amount column to payments table if it doesn't exist

-- Check and add refund_amount column
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'payments'
    AND COLUMN_NAME = 'refund_amount'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE payments ADD COLUMN refund_amount DECIMAL(10,2) DEFAULT 0.00 AFTER amount',
    'SELECT "Column refund_amount already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add notes column
SET @notes_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'payments'
    AND COLUMN_NAME = 'notes'
);

SET @sql2 = IF(@notes_exists = 0,
    'ALTER TABLE payments ADD COLUMN notes TEXT NULL AFTER transaction_id',
    'SELECT "Column notes already exists" as message'
);

PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
