<?php // Shared session setup, shop time zone, database connection, staff checks and HTML escaping.

declare(strict_types=1); // Enable strict typing. 

if (PHP_SAPI === 'cli') ini_set('session.save_path', sys_get_temp_dir()); // Check whether this case applies.  
session_start(); // Open the session.  

date_default_timezone_set('Europe/London'); // Interpret shop-local dates and times using Europe/London.

function require_staff(): void // Reject schedule administration without a signed-in staff session.
{ // Start the block.  
    if (!isset($_SESSION['staff_id'])) { // Check whether this case applies.  
        header('Location: login.php'); // Set a response header.  
        exit; // End the request. 
    } // Close the preceding block or expression.  
} // Close the preceding block or expression.  

function csrf_token(): string // Create or reuse the current session request token.
{ // Start the block.  
    return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32)); // Return the result. 
} // Close the preceding block or expression.  

function require_csrf(): void // Compare the submitted request token before allowing a write.
{ // Start the block.  
    $token = $_POST['csrf_token'] ?? null; // Read the submitted token.  
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) { // Check whether this case applies.  
        http_response_code(403); // Set the HTTP response status.  
        exit('Your session has expired. Reload the page and try again.'); // End the request. 
    } // Close the preceding block or expression.  
} // Close the preceding block or expression.  


function db(): PDO // Reuse or open the configured PDO database connection.
{ // Start the block.  
    static $pdo = null; // Continue the PHP expression.  

    if ($pdo instanceof PDO) { // Check whether this case applies.  
        return $pdo; // Return the result. 
    } // Close the preceding block or expression.  

    $host = getenv('BARBER_DB_HOST') ?: '127.0.0.1'; // Set the host. 
    $port = getenv('BARBER_DB_PORT') ?: '3306'; // Set the port. 
    $database = 'barber_booking'; // Set the database. 
    $username = getenv('BARBER_DB_USER') ?: 'root'; // Set the username. 
    $password = getenv('BARBER_DB_PASSWORD') ?: ''; // Set the password. 

    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4"; // Set the dsn. 

    $pdo = new PDO( // Set the pdo. 
        $dsn, // Supply the next value.  
        $username, // Supply the next value.  
        $password, // Supply the next value.  
        [ // Continue the PHP expression.  
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Supply the next value.  
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Supply the next value.  
            PDO::ATTR_EMULATE_PREPARES => false // Continue the PHP expression.  
        ] // Close the preceding block or expression.  
    ); // Close the preceding block or expression.  

    return $pdo; // Return the result. 
} // Close the preceding block or expression.  


function e(string $value): string // Escape dynamic text before inserting it into HTML.
{ // Start the block.  
    return htmlspecialchars( // Return the result. 
        $value, // Supply the next value.  
        ENT_QUOTES, // Supply the next value.  
        'UTF-8' // Continue the PHP expression.  
    ); // Close the preceding block or expression.  
} // Close the preceding block or expression.  
