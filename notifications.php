<?php // Email submission after a booking commits; distinguish submission status from inbox receipt.
declare(strict_types=1); // Enable strict typing. 

function notification_ready(string $channel): bool // Check whether the supported email channel has credentials.
{ // Start the block.  
    return $channel === 'email' && (bool) getenv('BARBER_GMAIL_APP_PASSWORD'); // Return the result. 
} // Close the preceding block or expression.  

// Send after commit so provider failures leave the booking intact.
function notification_send(string $channel, string $recipient, string $message): string // Submit an email and report its outcome without undoing the booking.
{ // Start the block.  
    if ($channel !== 'email') return 'unsupported'; // Check whether this case applies.  
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $recipient)) return 'failed'; // Check whether this case applies.  
    if (!notification_ready($channel)) return 'not_configured'; // Check whether this case applies.  
    if (!function_exists('curl_init')) return 'failed'; // Check whether this case applies.  
    $stream = null; // Set the stream. 
    $curl = null; // Set the curl. 
    try { // Run the operation with error handling. 
        $sender = 'fabd01012001@gmail.com'; // Set the sender. 
        $body = "From: {$sender}\r\nTo: {$recipient}\r\nSubject: Your barber appointment confirmation\r\nDate: " . date(DATE_RFC2822) // Set the body. 
            . "\r\nMessage-ID: <" . bin2hex(random_bytes(16)) . "@gmail.com>\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" // Continue the PHP expression.  
            . chunk_split(base64_encode($message), 76, "\r\n"); // Continue the PHP expression.  
        $stream = fopen('php://temp', 'r+'); // Set the stream.
        fwrite($stream, $body); rewind($stream); // Continue the PHP expression.  
        $curl = curl_init('smtp://smtp.gmail.com:587'); // Set the curl.
        curl_setopt_array($curl, [CURLOPT_USERNAME => $sender, // Supply the next value. 
            CURLOPT_PASSWORD => str_replace(' ', '', getenv('BARBER_GMAIL_APP_PASSWORD')), // Supply the next value.  
            CURLOPT_USE_SSL => CURLUSESSL_ALL, CURLOPT_MAIL_FROM => $sender, // Supply the next value.  
            CURLOPT_MAIL_RCPT => [$recipient], CURLOPT_UPLOAD => true, CURLOPT_INFILE => $stream, // Supply the next value.  
            CURLOPT_INFILESIZE => strlen($body)]); // Continue the PHP expression.  
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 5, // Supply the next value. 
            CURLOPT_TIMEOUT => 20, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2]); // Continue the PHP expression.  
        $result = curl_exec($curl); // Set the result. 
        $code = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE); // Set the code. 
        if ($result === false || $code < 200 || $code >= 300) return 'failed'; // Check whether this case applies.  
        return 'accepted'; // Return the result. 
    } catch (Throwable $error) { // Handle the exception. 
        return 'failed'; // Return the result. 
    } finally { // Run cleanup whether the protected operation succeeds or fails.
        if ($curl !== null) curl_close($curl); // Check whether this case applies.  
        if (is_resource($stream)) fclose($stream); // Check whether this case applies.  
    } // Close the preceding block or expression.  
} // Close the preceding block or expression.  

function notification_status_text(string $status): string // Turn the recorded notification state into customer-facing wording.
{ // Start the block.  
    if ($status === 'accepted') return 'Your appointment email was accepted by the email provider. Delivery may take a moment; check your inbox and spam folder.'; // Check whether this case applies.  
    if ($status === 'unsupported') return 'Your appointment is confirmed. This older booking has no email contact, so no email was sent. Please save these details.'; // Check whether this case applies.  
    if ($status === 'not_configured') return 'Your appointment is confirmed. Email delivery is not set up yet; please save these appointment details.'; // Check whether this case applies.  
    return 'Your appointment is confirmed, but we could not verify email submission. Please save these details and do not book again.'; // Return the result. 
} // Close the preceding block or expression.  
