<?php // Handle completion through the shared booking handler.
declare(strict_types=1); // Enable strict typing. 
require __DIR__ . '/config.php'; // Load the required file. 
require __DIR__ . '/booking_status.php'; // Load the required file. 
booking_status_request('completed'); // Continue the PHP expression.  
