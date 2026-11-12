<?php // End the staff session and return to sign-in.

declare(strict_types=1); // Enable strict typing. 

require __DIR__ . '/config.php'; // Load the required file. 


// Start session
if (session_status() !== PHP_SESSION_ACTIVE) { // Check whether this case applies.  
    session_start(); // Open the session.  
} // Close the preceding block or expression.  


// Clear all session variables
$_SESSION = []; // Set the  session.  


// Delete session cookie
if (ini_get('session.use_cookies')) { // Check whether this case applies.  

    $params = session_get_cookie_params(); // Set the params. 

    setcookie( // Continue the PHP expression.  
        session_name(), // Supply the next value.  
        '', // Supply the next value.  
        time() - 42000, // Supply the next value.  
        $params['path'], // Supply the next value.  
        $params['domain'], // Supply the next value.  
        $params['secure'], // Supply the next value.  
        $params['httponly'] // Continue the PHP expression.  
    ); // Close the preceding block or expression.  
} // Close the preceding block or expression.  


// Destroy session
session_destroy(); // Continue the PHP expression.  


// Return to staff login
header('Location: login.php'); // Set a response header.  

exit; // End the request. 