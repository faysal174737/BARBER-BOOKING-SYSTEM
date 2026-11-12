<?php // Initial staff-account setup utility; retains the existing setup behaviour.
declare(strict_types=1); // Enable strict typing. 
// Initial account creation is available only from the local command line.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; } // Check whether this case applies.  
require __DIR__ . '/config.php'; // Load the required file. 
$username = getenv('BARBER_SETUP_USER') ?: ''; // Set the username. 
$password = getenv('BARBER_SETUP_PASSWORD') ?: ''; // Set the password. 
if ($username === '' || strlen($username) > 50 || strlen($password) < 12) { // Check whether this case applies.  
    fwrite(STDERR, "Set BARBER_SETUP_USER and BARBER_SETUP_PASSWORD (at least 12 characters), then run this script. Existing accounts are not changed.\n"); // Continue the PHP expression.  
    exit(1); // End the request. 
} // Close the preceding block or expression.  
$pdo = db(); // Set the pdo. 
$check = $pdo->prepare('SELECT id FROM staff_users WHERE username = ?'); // Prepare the database statement. 
$check->execute([$username]); // Run the prepared statement. 
if ($check->fetch()) { fwrite(STDERR, "Account already exists; no change made.\n"); exit(1); } // Check whether this case applies.  
$pdo->prepare('INSERT INTO staff_users(username,password_hash) VALUES(?,?)')->execute([$username,password_hash($password,PASSWORD_DEFAULT)]); // Create the password hash used by the existing account operation.
echo "Staff account created.\n"; // Write the response output.  
