<?php // Validate and confirm customer bookings.

declare(strict_types=1); // Enable strict typing. 

require __DIR__ . '/services.php'; // Validate service choices and prices against the server catalogue.
require __DIR__ . '/config.php'; // Load the required file. 
require __DIR__ . '/schedule.php'; // Load the required file. 
require __DIR__ . '/notifications.php'; // Load the required file. 


// Accept booking submissions by POST.





if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Check whether this case applies.  

    header('Location: index.php'); // Set a response header.  

    exit; // End the request. 
} // Close the preceding block or expression.  


require_csrf(); // Validate the form token.
$bookingToken = $_POST['booking_token'] ?? null; // Read the token used to prevent repeat submissions.
if (!is_string($bookingToken) || !isset($_SESSION['booking_token']) || !hash_equals($_SESSION['booking_token'], $bookingToken)) { // Check whether this case applies.  
    http_response_code(409); // Set the HTTP response status.  
    exit('This booking form has already been used or expired. Check your confirmation, or return to the website to start a new booking.'); // End the request. 
} // Close the preceding block or expression.  

// Read the submitted booking details.





$customerName = trim( // Set the customer name. 
    (string) ($_POST['customer_name'] ?? '') // Continue the PHP expression.  
); // Close the preceding block or expression.  

$contactType = 'email'; // Set the contact type. 

$contactValue = trim( // Set the contact value. 
    (string) ($_POST['contact_value'] ?? '') // Continue the PHP expression.  
); // Close the preceding block or expression.  

$bookingDate = trim( // Set the booking date. 
    (string) ($_POST['booking_date'] ?? '') // Continue the PHP expression.  
); // Close the preceding block or expression.  


// Validate the booking details.





$errors = []; // Set the errors. 
$selectedService = service_find($_POST['service_code'] ?? null); // Never trust a customer-submitted price or service name.
if ($selectedService === null) $errors[] = 'Choose a service from the price list.'; // Refuse missing, malformed or unknown choices before saving.


// Customer name





if ( // Check whether this case applies.  
    $customerName === '' // Continue the PHP expression. 
    || // Continue the condition.  
    mb_strlen($customerName) > 120 // Continue the PHP expression.  
) { // Continue the PHP expression.  

    $errors[] = // Append an item to the list.  
        'Please enter your name.'; // Continue the PHP expression.  

} // Close the preceding block or expression.  


// Contact type





if ( // Check whether this case applies.  
    !in_array( // Continue the PHP expression.  
        $_POST['contact_type'] ?? 'email', // Supply the next value.  
        ['email'], // Supply the next value.  
        true // Continue the PHP expression.  
    ) // Close the preceding block or expression.  
) { // Continue the PHP expression.  

    $errors[] = // Append an item to the list.  
        'Bookings now use email only. Please reload the booking form and enter your email address.'; // Continue the PHP expression.  

} // Close the preceding block or expression.  


// Email validation





if ( // Check whether this case applies.  
    $contactType === 'email' // Continue the PHP expression. 
) { // Continue the PHP expression.  

    if ( // Check whether this case applies.  
        !filter_var( // Continue the PHP expression.  
            $contactValue, // Supply the next value.  
            FILTER_VALIDATE_EMAIL // Continue the PHP expression.  
        ) // Close the preceding block or expression.  
    ) { // Continue the PHP expression.  

        $errors[] = // Append an item to the list.  
            'Please enter a valid email address.'; // Continue the PHP expression.  

    } // Close the preceding block or expression.  

} // Close the preceding block or expression.  


// Date validation





$dateObject = // Set the date object. 
    DateTime::createFromFormat( // Continue the PHP expression.  
        'Y-m-d', // Supply the next value.  
        $bookingDate // Continue the PHP expression.  
    ); // Close the preceding block or expression.  


if ( // Check whether this case applies.  
    !$dateObject // Continue the PHP expression.  
    || // Continue the condition.  
    $dateObject->format('Y-m-d') // Continue the PHP expression.  
        !== $bookingDate // Continue the PHP expression.  
) { // Continue the PHP expression.  

    $errors[] = // Append an item to the list.  
        'Please select a valid appointment date.'; // Continue the PHP expression.  

} // Close the preceding block or expression.  


// Reject past booking dates.





if ( // Check whether this case applies.  
    $bookingDate < date('Y-m-d') // Continue the PHP expression.  
) { // Continue the PHP expression.  

    $errors[] = // Append an item to the list.  
        'You cannot book a date in the past.'; // Continue the PHP expression.  

} // Close the preceding block or expression.  


// Display validation errors





if (!empty($errors)) { // Check whether this case applies.  

    ?>

    <!DOCTYPE html>

    <html lang="en"> 

    <head> 

        <meta charset="UTF-8"> 

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        > 

        <title>
            Booking Error
        </title> 

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
        > 

    <link rel="stylesheet" href="booking-message.css"> 
</head> 

    <body class="bg-light"> 

    <div class="container py-5"> 

        <div
            class="alert alert-danger mx-auto booking-message"
            
        > 

            <h3> 
                Booking Error 
            </h3> 

            <ul class="mb-3"> 

                <?php foreach ( // Continue the PHP expression.
                    $errors as $error // Continue the PHP expression.  
                ): // Continue the PHP expression.    ?>

                    <li> 
                        <?= e($error) // Continue the PHP expression.    ?>
                    </li> 

                <?php endforeach; // Continue the PHP expression.    ?>

            </ul> 

            <a
                href="index.php"
                class="btn btn-dark"
            > 

                Back to Booking 

            </a> 

        </div> 

    </div> 

    </body> 

    </html> 

    <?php

    exit; // End the request. 
} // Close the preceding block or expression.  


// Connect to database





$pdo = db(); // Set the pdo. 


try { // Run the operation with error handling. 

    // Start transaction





    // Allocate appointments using the booking rules.
    set_time_limit(90); // Continue the PHP expression.  
    $slotChoice = is_string($_POST['slot_choice'] ?? null) ? $_POST['slot_choice'] : ''; // Read the submitted slot choice.  
    $initialSchedule = schedule_load($pdo, $bookingDate); // Set the initial schedule. 
    $proposal = $slotChoice === '' ? $initialSchedule['next'] : schedule_pick($initialSchedule, $slotChoice); // Set the proposal. 
    if (!$proposal) throw new RuntimeException('No bookable slots remain for this date. Please choose another day.'); // Check whether this case applies.  
    $pdo->beginTransaction(); // Save the booking inside a database transaction.
    // Recheck availability while holding the booking lock.
    $schedule = schedule_load($pdo, $bookingDate, true); // Set the schedule. 
    if (!$schedule['next']) { // Check whether this case applies.  
        throw new RuntimeException('No bookable slots remain for this date. Please choose another day.'); // Report an error to the caller. 
    } // Close the preceding block or expression.  
    $assignedSlot = $slotChoice === '' ? $schedule['next'] : schedule_pick($schedule, $slotChoice); // Set the assigned slot. 
    $selectedBarber = ['id' => $assignedSlot['barber_id'], 'name' => $assignedSlot['barber_name']]; // Set the selected barber. 
    $appointmentTime = $assignedSlot['time']; // Set the appointment time. 
    $maximumDailyBookings = $schedule['capacity']; // Set the maximum daily bookings. 
    $remainingSlots = $schedule['remaining'] - 1; // Set the remaining slots. 
    $numberStatement = $pdo->prepare('SELECT COALESCE(MAX(booking_number), 0) + 1 FROM bookings WHERE booking_date = ?'); // Prepare the database statement. 
    $numberStatement->execute([$bookingDate]); // Run the prepared statement. 
    $bookingNumber = (int) $numberStatement->fetchColumn(); // Read the database result. 
    $insert = // Set the insert. 
        $pdo->prepare( // Continue the PHP expression. 
            "INSERT INTO bookings (
                booking_date, booking_number, customer_name, contact_type, contact_value,
                service_code, service_name, service_price_pence, barber_id, appointment_time, status
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')"  
        ); // Close the preceding block or expression.  


    $insert->execute([ // Store the service details with the booking.
        $bookingDate, // Supply the next value. 
        $bookingNumber, // Supply the next value. 
        $customerName, // Supply the next value. 
        $contactType, // Supply the next value. 
        $contactValue, // Supply the next value. 
        $selectedService['code'], // Supply the next value. 
        $selectedService['name'], // Keep the service name as it was at booking.
        $selectedService['price_pence'], // Store the agreed price in pence.
        $selectedBarber['id'], // Supply the next value. 
        $appointmentTime, // Supply the next value. 
    ]); // Close the preceding block or expression.  


    // Get booking ID





    $bookingId = // Set the booking id. 
        (int) // Continue the PHP expression.  
        $pdo->lastInsertId(); // Continue the PHP expression.  


    // Commit transaction





    $pdo->commit(); // Commit before attempting to send the confirmation.
    unset($_SESSION['booking_token']); // Continue the PHP expression.  

} // Close the preceding block or expression.  
catch (PDOException $exception) { // Handle the exception. 

    // Roll back the transaction.





    if ( // Check whether this case applies.  
        $pdo->inTransaction() // Continue the PHP expression.  
    ) { // Continue the PHP expression.  

        $pdo->rollBack(); // Discard uncommitted changes. 

    } // Close the preceding block or expression.  


    // Handle duplicate slot





    if ( // Check whether this case applies.  
        isset( // Continue the PHP expression.  
            $exception->errorInfo[1] // Continue the PHP expression.  
        ) // Close the preceding block or expression.  
        && // Continue the condition.  
        (int) // Continue the PHP expression.  
        $exception->errorInfo[1] // Continue the PHP expression.  
        === 1062 // Continue the PHP expression.  
    ) { // Continue the PHP expression.  

        $errorMessage = // Set the error message. 
            'Another customer has just taken this appointment slot. Please try again.'; // Continue the PHP expression.  

    } else { // Handle the alternative case. 

        $errorMessage = // Set the error message. 
            'A database error occurred while creating your booking.'; // Continue the PHP expression.  

    } // Close the preceding block or expression.  


    ?>

    <!DOCTYPE html>

    <html lang="en"> 

    <head> 

        <meta charset="UTF-8"> 

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        > 

        <title>
            Booking Error
        </title> 

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
        > 

    <link rel="stylesheet" href="booking-message.css"> 
</head> 

    <body class="bg-light"> 

    <div class="container py-5"> 

        <div
            class="alert alert-danger mx-auto booking-message"
            
        > 

            <h3> 
                Booking Failed 
            </h3> 

            <p> 
                <?= e($errorMessage) // Continue the PHP expression.    ?>
            </p> 

            <a
                href="index.php"
                class="btn btn-dark"
            > 

                Try Again 

            </a> 

        </div> 

    </div> 

    </body> 

    </html> 

    <?php

    exit; // End the request. 

} // Close the preceding block or expression.  
catch (Throwable $exception) { // Handle the exception. 

    // Roll back the transaction.





    if ( // Check whether this case applies.  
        $pdo->inTransaction() // Continue the PHP expression.  
    ) { // Continue the PHP expression.  

        $pdo->rollBack(); // Discard uncommitted changes. 

    } // Close the preceding block or expression.  


    ?>

    <!DOCTYPE html>

    <html lang="en"> 

    <head> 

        <meta charset="UTF-8"> 

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        > 

        <title>
            Booking Error
        </title> 

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
        > 

    <link rel="stylesheet" href="booking-message.css"> 
</head> 

    <body class="bg-light"> 

    <div class="container py-5"> 

        <div
            class="alert alert-danger mx-auto booking-message"
            
        > 

            <h3> 
                Booking Failed 
            </h3> 

            <p> 
                <?= e( // Continue the PHP expression.  
                    $exception->getMessage() // Continue the PHP expression.  
                ) // Close the preceding block or expression.    ?>
            </p> 

            <a
                href="index.php"
                class="btn btn-dark"
            > 

                Try Again 

            </a> 

        </div> 

    </div> 

    </body> 

    </html> 

    <?php

    exit; // End the request. 

} // Close the preceding block or expression.  


// Format booking number





$formattedNumber = // Set the formatted number. 
    str_pad( // Continue the PHP expression.  
        (string) $bookingNumber, // Supply the next value.  
        3, // Supply the next value.  
        '0', // Supply the next value.  
        STR_PAD_LEFT // Continue the PHP expression.  
    ); // Close the preceding block or expression.  

$notificationStatus = notification_send($contactType, $contactValue, // Set the notification status. 
    'Your appointment is confirmed. Booking #' . $formattedNumber . '. Date: ' . $bookingDate // Continue the PHP expression.  
    . '. Time: ' . substr($appointmentTime, 0, 5) . ' (' . date_default_timezone_get() . '). Barber: ' . $selectedBarber['name'] . '. Service: ' . $selectedService['name'] . ' (' . service_price_label($selectedService['price_pence']) . '). Pay at the shop after your appointment. Please keep your booking number.'); // Continue the PHP expression.  


// Format appointment time





$formattedTime = // Set the formatted time. 
    date( // Continue the PHP expression.  
        'g:i A', // Supply the next value.  
        strtotime($appointmentTime) // Continue the PHP expression.  
    ); // Close the preceding block or expression.  


// Format booking date





$formattedDate = // Set the formatted date. 
    date( // Continue the PHP expression.  
        'l, d F Y', // Supply the next value.  
        strtotime($bookingDate) // Continue the PHP expression.  
    ); // Close the preceding block or expression.  

?>

<!DOCTYPE html>

<html lang="en"> 

<head> 

    <meta charset="UTF-8"> 

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    > 

    <title>
        Booking Confirmed
    </title> 


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    > 


    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Oswald:wght@400;500;600&display=swap"
        rel="stylesheet"
    > 


    <link rel="stylesheet" href="booking-form.css"> 

</head> 


<body> 


<div class="confirmation-card"> 


    <div class="success"> 

        ✓ Booking Confirmed 

    </div> 


    <div class="booking-number"> 

        #<?= e( // Continue the PHP expression.  
            $formattedNumber // Continue the PHP expression.  
        ) // Close the preceding block or expression.    ?>

    </div> 


    <h1 class="confirmation-title"> 

        Your Appointment Is Booked 

    </h1> 


    <div class="detail"> 


        <div class="detail-label"> 

            Customer 

        </div> 


        <div class="detail-value"> 

            <?= e( // Continue the PHP expression.  
                $customerName // Continue the PHP expression.  
            ) // Close the preceding block or expression.    ?>

        </div> 


    </div> 


    <div class="detail"> 


        <div class="detail-label"> 

            Date 

        </div> 


        <div class="detail-value"> 

            <?= e( // Continue the PHP expression.  
                $formattedDate // Continue the PHP expression.  
            ) // Close the preceding block or expression.    ?>

        </div> 


    </div> 


    <div class="detail"> 


        <div class="detail-label"> 

            Appointment Time 

        </div> 


        <div class="detail-value"> 

            <?= e( // Continue the PHP expression.  
                $formattedTime // Continue the PHP expression.  
            ) // Close the preceding block or expression.    ?>

        </div> 


    </div> 


    <div class="detail"> 


        <div class="detail-label"> 

            Barber 

        </div> 


        <div class="detail-value"> 

            <?= e( // Continue the PHP expression.  
                $selectedBarber['name'] // Continue the PHP expression.  
            ) // Close the preceding block or expression.    ?>

        </div> 


    </div> 


    <div class="detail">
        <div class="detail-label">Service</div>
        <div class="detail-value"><?= e($selectedService['name']) // Continue the PHP expression.  ?></div>
    </div>
    <div class="detail">
        <div class="detail-label">Service price</div>
        <div class="detail-value"><?= e(service_price_label($selectedService['price_pence'])) // Continue the PHP expression.  ?></div>
    </div>
    <p class="small text-muted">Example GBP price. Pay at the shop after your appointment. No online payment has been taken.</p>

    <div class="important"> 
        <p role="status"><?= e(notification_status_text($notificationStatus)) // Continue the PHP expression.    ?></p> 


        <strong> 

            Your daily serial number is 

            #<?= e( // Continue the PHP expression.  
                $formattedNumber // Continue the PHP expression.  
            ) // Close the preceding block or expression.    ?>

        </strong> 


        <br> 


        Please keep this number for your 
        appointment. 

    </div> 


    <a
        href="index.php"
        class="btn btn-dark"
    > 

        Back to Website 

    </a> 


 </div> 
<script src="availability.js" defer></script> 
</body> 

</html> 
