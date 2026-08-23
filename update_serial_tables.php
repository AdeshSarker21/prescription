<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::unprepared("SET FOREIGN_KEY_CHECKS=0");

// 1. Alter patient_queues: add new columns, update status enum
echo "Altering patient_queues table...\n";

$cols = DB::select("SHOW COLUMNS FROM `patient_queues`");
$existingCols = array_column($cols, 'Field');

if (!in_array('formatted_serial', $existingCols)) {
    DB::statement("ALTER TABLE `patient_queues` ADD COLUMN `formatted_serial` VARCHAR(20) NULL AFTER `serial_number`");
    echo "  + formatted_serial\n";
}

if (!in_array('prepared_at', $existingCols)) {
    DB::statement("ALTER TABLE `patient_queues` ADD COLUMN `prepared_at` TIMESTAMP NULL AFTER `called_at`");
    echo "  + prepared_at\n";
}

if (!in_array('skipped_at', $existingCols)) {
    DB::statement("ALTER TABLE `patient_queues` ADD COLUMN `skipped_at` TIMESTAMP NULL AFTER `cancelled_at`");
    echo "  + skipped_at\n";
}

if (!in_array('inside_at', $existingCols)) {
    DB::statement("ALTER TABLE `patient_queues` ADD COLUMN `inside_at` TIMESTAMP NULL AFTER `consultation_started_at`");
    echo "  + inside_at\n";
}

// Update status column to new ENUM values
DB::statement("ALTER TABLE `patient_queues` MODIFY COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'waiting'");
echo "  ~ status column updated to VARCHAR(20)\n";

// Update priority column to allow 'emergency'
DB::statement("ALTER TABLE `patient_queues` MODIFY COLUMN `priority` VARCHAR(20) NOT NULL DEFAULT 'normal'");
echo "  ~ priority column updated to VARCHAR(20)\n";

// Add unique index for serial per doctor+chamber+date (via session)
if (empty(DB::select("SHOW INDEX FROM `patient_queues` WHERE Key_name = 'uniq_serial_per_session'"))) {
    DB::statement("ALTER TABLE `patient_queues` ADD UNIQUE INDEX `uniq_serial_per_session` (`serial_session_id`, `serial_number`)");
    echo "  + uniq_serial_per_session index\n";
}

// 2. Create serial_status_histories table
echo "\nCreating serial_status_histories table...\n";
DB::statement("DROP TABLE IF EXISTS `serial_status_histories`");

$histTable = "CREATE TABLE `serial_status_histories` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `patient_queue_id` BIGINT UNSIGNED NOT NULL,
    `serial_session_id` BIGINT UNSIGNED NOT NULL,
    `doctor_id` BIGINT UNSIGNED NOT NULL,
    `patient_id` BIGINT UNSIGNED NOT NULL,
    `serial_number` INT NOT NULL,
    `formatted_serial` VARCHAR(20) NULL,
    `from_status` VARCHAR(20) NULL,
    `to_status` VARCHAR(20) NOT NULL,
    `priority` VARCHAR(20) NOT NULL DEFAULT 'normal',
    `changed_by` BIGINT UNSIGNED NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    INDEX `idx_ssh_queue` (`patient_queue_id`),
    INDEX `idx_ssh_session` (`serial_session_id`),
    INDEX `idx_ssh_doctor_date` (`doctor_id`, `created_at`),
    INDEX `idx_ssh_serial` (`serial_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
DB::statement($histTable);
echo "  serial_status_histories created\n";

DB::unprepared("SET FOREIGN_KEY_CHECKS=1");

// Verify
echo "\n=== patient_queues columns ===\n";
$cols = DB::select("SHOW COLUMNS FROM `patient_queues`");
foreach ($cols as $c) {
    echo "  {$c->Field} : {$c->Type}\n";
}

echo "\n=== serial_status_histories columns ===\n";
$cols = DB::select("SHOW COLUMNS FROM `serial_status_histories`");
foreach ($cols as $c) {
    echo "  {$c->Field} : {$c->Type}\n";
}

echo "\nDone.\n";
