<?php // Handle staff cancellations and completions.
declare(strict_types=1); // Enable strict typing. 

function booking_change_status(PDO $pdo, int $id, string $status): bool // Update the status of a confirmed booking.
{ // Start the block.  
    if ($id < 1 || !in_array($status, ['cancelled', 'completed'], true)) { // Check whether this case applies.  
        throw new InvalidArgumentException('Invalid booking action.'); // Report an error to the caller. 
    } // Close the preceding block or expression.  
    $pdo->beginTransaction(); // Start the database transaction. 
    try { // Run the operation with error handling. 
        // Acquire locks in the same order as booking confirmation.
        $pdo->query('SELECT id FROM shop_settings WHERE id = 1 FOR UPDATE')->fetch(); // Run the database statement. 
        $statement = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ? AND status = 'confirmed'"); // Prepare the database statement. 
        $statement->execute([$status, $id]); // Run the prepared statement. 
        $changed = $statement->rowCount() === 1; // Set the changed. 
        $pdo->commit(); // Commit the database changes. 
        return $changed; // Return the result. 
    } catch (Throwable $error) { // Handle the exception. 
        if ($pdo->inTransaction()) $pdo->rollBack(); // Check whether this case applies.  
        throw $error; // Report an error to the caller. 
    } // Close the preceding block or expression.  
} // Close the preceding block or expression.  

function booking_status_request(string $status): void // Validate the staff request and redirect to the dashboard.
{ // Start the block.  
    require_staff(); // Continue the PHP expression. 
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Check whether this case applies.  
        http_response_code(405); // Set the HTTP response status.  
        header('Allow: POST'); // Set a response header.  
        exit('Use the appointment buttons on the staff dashboard.'); // End the request. 
    } // Close the preceding block or expression.  
    require_csrf(); // Validate the form token.
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT); // Read the submitted id.  
    $date = is_string($_POST['date'] ?? null) ? $_POST['date'] : date('Y-m-d'); // Read the submitted date.  
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date); // Set the parsed. 
    if (!$parsed || $parsed->format('Y-m-d') !== $date) $date = date('Y-m-d'); // Check whether this case applies.  
    try { // Run the operation with error handling. 
        $changed = booking_change_status(db(), (int) $id, $status); // Set the changed. 
        $_SESSION['staff_notice'] = $changed ? 'Appointment ' . $status . '.' : 'This appointment has already changed. Please check its status.'; // Continue the PHP expression.  
    } catch (Throwable $error) { // Handle the exception. 
        error_log('Booking status update failed: ' . $error->getMessage()); // Continue the PHP expression.  
        $_SESSION['staff_notice'] = 'The appointment could not be updated. Please try again.'; // Continue the PHP expression.  
    } // Close the preceding block or expression.  
    header('Location: dashboard.php?date=' . urlencode($date), true, 303); // Set a response header.  
    exit; // End the request. 
} // Close the preceding block or expression.  
