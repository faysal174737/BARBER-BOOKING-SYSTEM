<?php // Handle read-only assistant requests.
declare(strict_types=1); // Enable strict typing. 
require __DIR__ . '/config.php'; // Load the required file. 
require __DIR__ . '/ai_demo_lib.php'; // Load the required file. 
require __DIR__ . '/schedule.php'; // Load the required file. 
require __DIR__ . '/local_assistant.php'; // Load the required file. 
header('Content-Type: application/json; charset=utf-8'); // Set a response header.  
header('Cache-Control: no-store'); // Set a response header.  
function demo_reply(array $body, int $status = 200): void { // Return the JSON response and end the request.
    http_response_code($status); // Set the HTTP response status.  
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); // Write the response output.  
    exit; // End the request. 
} // Close the preceding block or expression.  
if ($_SERVER['REQUEST_METHOD'] !== 'POST') demo_reply(['error' => 'Use a POST request.'], 405); // Check whether this case applies.  
if (!isset($_SESSION['ai_demo_token']) || !hash_equals($_SESSION['ai_demo_token'], $_SERVER['HTTP_X_DEMO_TOKEN'] ?? '')) { // Check whether this case applies.  
    demo_reply(['error' => 'Your page session has expired. Reload the page and try again.'], 403); // Continue the PHP expression.  
} // Close the preceding block or expression.  
try { // Run the operation with error handling. 
    $raw = file_get_contents('php://input', false, null, 0, 8193); // Set the raw.
    if (strlen($raw) > 8192) demo_reply(['error' => 'This request is too long.'], 413); // Check whether this case applies.  
    $input = json_decode($raw, true, 16, JSON_THROW_ON_ERROR); // Decode the JSON input. 
    if (!is_array($input)) throw new InvalidArgumentException('Invalid request.'); // Check whether this case applies.  
    foreach ($input as $value) if (!is_string($value) || strlen($value) > 2000) throw new InvalidArgumentException('Invalid request values.'); // Process each item.  
    $action = $input['action'] ?? ''; // Set the action. 
    if (!in_array($action, ['chat', 'style', 'staff', 'draft'], true)) throw new InvalidArgumentException('Unknown action.'); // Check whether this case applies.  
    if (in_array($action, ['staff', 'draft'], true) && !isset($_SESSION['staff_id'])) { // Check whether this case applies.  
        demo_reply(['error' => 'Please sign in as staff to use this feature.'], 401); // Continue the PHP expression.  
    } // Close the preceding block or expression.  
    if ($action === 'style') demo_reply(['text' => demo_style($input['texture'] ?? '', $input['length'] ?? '', $input['effort'] ?? '')]); // Check whether this case applies.  
    $pdo = db(); // Set the pdo. 
    $settings = $pdo->query('SELECT * FROM shop_settings WHERE id = 1')->fetch(); // Read the database result. 
    if (!$settings) throw new RuntimeException('Missing settings'); // Check whether this case applies.  
    if ($action === 'chat') { // Check whether this case applies.  
        $message = trim($input['message'] ?? ''); // Set the message. 
        if ($message === '') throw new InvalidArgumentException('Type a question first.'); // Check whether this case applies.  
        $date = trim($input['date'] ?? ''); // Set the date. 
        if ($date !== '') $date = schedule_date($date); // Check whether this case applies.  
        // Release the session while inference runs so availability requests are not blocked.
        session_write_close(); // Continue the PHP expression.  
        set_time_limit(60); // Continue the PHP expression.  
        try { // Run the operation with error handling. 
            $interpretation = assistant_interpret($message, $date); // Set the interpretation. 
        } catch (Throwable $error) { // Handle the exception. 
            error_log('Local assistant failed: ' . $error->getMessage()); // Continue the PHP expression.  
            demo_reply(['error' => 'The local AI assistant is unavailable. Please use the booking form or try again later.'], 503); // Continue the PHP expression.  
        } // Close the preceding block or expression.  
        $schedule = $interpretation['intent'] === 'availability' ? schedule_load($pdo, $interpretation['date']) : null; // Set the schedule. 
        demo_reply(assistant_result($interpretation, $settings, $schedule)); // Continue the PHP expression.  
    } // Close the preceding block or expression.  
    if ($action === 'draft') { // Check whether this case applies.  
        $id = filter_var($input['booking_id'] ?? '', FILTER_VALIDATE_INT); // Set the id. 
        if (!$id || $id < 1) throw new InvalidArgumentException('Choose a booking.'); // Check whether this case applies.  
        $statement = $pdo->prepare("SELECT b.customer_name, b.booking_number, b.booking_date, b.appointment_time, br.name AS barber_name FROM bookings b JOIN barbers br ON br.id = b.barber_id WHERE b.id = ? AND b.status = 'confirmed' AND TIMESTAMP(b.booking_date, b.appointment_time) >= ?"); // Prepare the database statement. 
        $statement->execute([$id, date('Y-m-d H:i:s')]); $booking = $statement->fetch(); // Run the prepared statement. 
        if (!$booking) throw new InvalidArgumentException('That appointment is no longer upcoming and confirmed. Refresh the staff tools.'); // Check whether this case applies.  
        demo_reply(['text' => demo_draft($booking, $settings['shop_name'], $input['language'] ?? '', $input['kind'] ?? '')]); // Continue the PHP expression.  
    } // Close the preceding block or expression.  
    $statement = $pdo->prepare("SELECT booking_date, COUNT(*) AS total FROM bookings WHERE booking_date >= ? AND booking_date < ? AND status <> 'cancelled' GROUP BY booking_date"); // Prepare the database statement. 
    $statement->execute([date('Y-m-d', strtotime('-56 days')), date('Y-m-d')]); // Run the prepared statement. 
    $daily = []; // Set the daily. 
    foreach ($statement->fetchAll() as $row) $daily[$row['booking_date']] = (int) $row['total']; // Process each item.  
    $statement = $pdo->prepare("SELECT id, booking_number, customer_name, booking_date, appointment_time FROM bookings WHERE status = 'confirmed' AND TIMESTAMP(booking_date, appointment_time) >= ? ORDER BY booking_date, appointment_time LIMIT 100"); // Prepare the database statement. 
    $statement->execute([date('Y-m-d H:i:s')]); // Run the prepared statement. 
    $upcoming = $statement->fetchAll(); // Read the database result. 
    demo_reply(['forecast' => demo_forecast($daily, date('Y-m-d')), 'bookings' => $upcoming]); // Continue the PHP expression.  
} catch (JsonException | InvalidArgumentException $error) { // Handle the exception. 
    demo_reply(['error' => $error instanceof JsonException ? 'Invalid JSON request.' : $error->getMessage()], 400); // Continue the PHP expression.  
} catch (Throwable $error) { // Handle the exception. 
    demo_reply(['error' => 'The assistant could not read the shop data. Check that MySQL is running, then try again.'], 503); // Continue the PHP expression.  
} // Close the preceding block or expression.  
