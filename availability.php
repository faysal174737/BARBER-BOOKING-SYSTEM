<?php // Return available appointments for the selected date.
declare(strict_types=1); // Enable strict typing. 
require __DIR__ . '/config.php'; // Load the required file. 
require __DIR__ . '/schedule.php'; // Load the required file. 
session_write_close(); // Continue the PHP expression.  
header('Content-Type: application/json; charset=utf-8'); // Set a response header.  
header('Cache-Control: no-store'); // Set a response header.  
try { // Run the operation with error handling. 
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') { // Check whether this case applies.  
        http_response_code(405); // Set the HTTP response status.  
        header('Allow: GET'); // Set a response header.  
        echo json_encode(['error' => 'Use a GET request.']); // Write the response output.  
        exit; // End the request. 
    } // Close the preceding block or expression.  
    $value = $_GET['date'] ?? ''; // Read the requested value.  
    if (!is_string($value)) throw new InvalidArgumentException('Choose a valid date.'); // Check whether this case applies.  
    $date = schedule_date($value); // Set the date. 
    echo json_encode(schedule_public(schedule_load(db(), $date)), JSON_THROW_ON_ERROR); // Write the response output.  
} catch (InvalidArgumentException $error) { // Handle the exception. 
    http_response_code(400); // Set the HTTP response status.  
    echo json_encode(['error' => $error->getMessage()]); // Write the response output.  
} catch (Throwable $error) { // Handle the exception. 
    http_response_code(503); // Set the HTTP response status.  
    echo json_encode(['error' => 'Availability is temporarily unavailable. Please try again.']); // Write the response output.  
} // Close the preceding block or expression.  
