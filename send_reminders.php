<?php // Command-line worker that records and attempts appointment reminder delivery.
declare(strict_types=1); // Enable strict typing. 
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // Check whether this case applies.  
require __DIR__ . '/config.php'; // Load the required file. 
require __DIR__ . '/notifications.php'; // Load the required file. 
session_write_close(); // Continue the PHP expression.  
$pdo = db(); // Set the pdo. 
$dryRun = in_array('--dry-run', $argv, true); // Set the dry run. 
$statement = $pdo->prepare("SELECT id FROM bookings WHERE status = 'confirmed' AND contact_type = 'email' AND TIMESTAMP(booking_date, appointment_time) > ? AND TIMESTAMP(booking_date, appointment_time) <= ? ORDER BY booking_date, appointment_time"); // Find confirmed appointments in the reminder window.
$statement->execute([date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('+24 hours'))]); // Run the prepared statement. 
$ids = $statement->fetchAll(PDO::FETCH_COLUMN); // Read the database result. 
$attempts = 0; // Set the attempts. 
foreach ($ids as $id) { // Check each appointment again before sending.
    $pdo->beginTransaction(); // Start the database transaction. 
    try { // Run the operation with error handling. 
        // Hold the booking lock while sending the reminder.
        $pdo->query('SELECT id FROM shop_settings WHERE id = 1 FOR UPDATE')->fetch(); // Run the database statement. 
        $read = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND status = 'confirmed' AND TIMESTAMP(booking_date, appointment_time) > ? AND TIMESTAMP(booking_date, appointment_time) <= ? FOR UPDATE"); // Prepare the database statement. 
        $read->execute([$id, date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('+24 hours'))]); // Run the prepared statement. 
        $booking = $read->fetch(); // Read the database result. 
        if (!$booking) { $pdo->rollBack(); continue; } // Check whether this case applies.  
        $at = $booking['booking_date'] . ' ' . $booking['appointment_time']; // Set the at. 
        $read = $pdo->prepare('SELECT status FROM booking_reminders WHERE booking_id = ? AND appointment_at = ?'); // Prepare the database statement. 
        $read->execute([$id, $at]); // Run the prepared statement. 
        if ($read->fetch() || !notification_ready($booking['contact_type'])) { $pdo->rollBack(); continue; } // Check whether this case applies.  
        if ($dryRun) { $attempts++; $pdo->rollBack(); continue; } // Count eligible reminders without sending them.
        // Save the send claim before contacting the provider.
        $pdo->prepare("INSERT INTO booking_reminders (booking_id, appointment_at, status, attempted_at) VALUES (?, ?, 'attempting', ?)")->execute([$id, $at, date('Y-m-d H:i:s')]); // Continue the PHP expression. 
        $pdo->commit(); // Commit the database changes. 
        $pdo->beginTransaction(); // Start the database transaction. 
        $pdo->query('SELECT id FROM shop_settings WHERE id = 1 FOR UPDATE')->fetch(); // Run the database statement. 
        $read = $pdo->prepare("SELECT status, booking_date, appointment_time FROM bookings WHERE id = ? FOR UPDATE"); // Prepare the database statement. 
        $read->execute([$id]); $current = $read->fetch(); // Run the prepared statement. 
        $status = 'skipped'; // Set the status. 
        if ($current['status'] === 'confirmed' && $current['booking_date'] . ' ' . $current['appointment_time'] === $at && $at > date('Y-m-d H:i:s')) { // Check whether this case applies.  
            $status = notification_send($booking['contact_type'], $booking['contact_value'], 'Reminder: your barber appointment #' . $booking['booking_number'] . ' is on ' . $booking['booking_date'] . ' at ' . substr($booking['appointment_time'], 0, 5) . ' (' . date_default_timezone_get() . ').'); // Set the status. 
        } // Close the preceding block or expression.  
        $pdo->prepare('UPDATE booking_reminders SET status = ? WHERE booking_id = ? AND appointment_at = ?')->execute([$status, $id, $at]); // Continue the PHP expression. 
        $pdo->commit(); $attempts++; // Commit the database changes. 
    } catch (Throwable $error) { // Handle the exception. 
        if ($pdo->inTransaction()) $pdo->rollBack(); // Check whether this case applies.  
        fwrite(STDERR, "Reminder worker stopped; check database/provider configuration.\n"); exit(1); // Continue the PHP expression. 
    } // Close the preceding block or expression.  
} // Close the preceding block or expression.  
echo ($dryRun ? 'Eligible configured reminders: ' : 'Reminder attempts: ') . $attempts . "\n"; // Write the response output.  
