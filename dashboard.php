<?php // Staff dashboard showing booking records and links to protected schedule actions.

declare(strict_types=1); // Enable strict typing. 

require __DIR__ . '/services.php'; // Format the service-price snapshot recorded with each appointment.
require __DIR__ . '/config.php'; // Load the required file. 


// Require staff sign-in.





if (!isset($_SESSION['staff_id'])) { // Check whether this case applies.  

    header('Location: login.php'); // Set a response header.  

    exit; // End the request. 
} // Close the preceding block or expression.  


$pdo = db(); // Set the pdo. 


// Set the default dashboard date.





$today = is_string($_GET['date'] ?? null) ? $_GET['date'] : date('Y-m-d'); // Read the requested today.  
$parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $today); // Set the parsed date. 
if (!$parsedDate || $parsedDate->format('Y-m-d') !== $today) $today = date('Y-m-d'); // Check whether this case applies.  
$notice = $_SESSION['staff_notice'] ?? ''; // Set the notice.  
unset($_SESSION['staff_notice']); // Continue the PHP expression.  


// Load the shop settings.





$settings = $pdo // Set the settings. 
    ->query( // Continue the PHP expression. 
        "SELECT *
         FROM shop_settings
         WHERE id = 1"  
    ) // Close the preceding block or expression.  
    ->fetch(); // Continue the PHP expression. 


// Load bookings for the selected date.





$statement = $pdo->prepare( // Prepare the database statement. 
    "SELECT

        b.id,

        b.booking_number,

        b.customer_name,
        b.service_name,
        b.service_price_pence,

        b.contact_type,

        b.contact_value,

        b.appointment_time,

        b.status,

        br.name AS barber_name

     FROM bookings b

     INNER JOIN barbers br
        ON br.id = b.barber_id

     WHERE b.booking_date = ?

     ORDER BY
        b.appointment_time ASC,
        b.barber_id ASC"  
); // Close the preceding block or expression.  


$statement->execute([ // Run the prepared statement. 
    $today // Continue the PHP expression.  
]); // Close the preceding block or expression.  


$bookings = // Set the bookings. 
    $statement->fetchAll(); // Continue the PHP expression. 


// Statistics





$totalBookings = count($bookings); // Set the total bookings. 


$confirmedBookings = 0; // Set the confirmed bookings. 

$cancelledBookings = 0; // Set the cancelled bookings. 


foreach ($bookings as $booking) { // Process each item.  

    if ( // Check whether this case applies.  
        $booking['status'] // Continue the PHP expression.  
        === 'confirmed' // Continue the PHP expression.  
    ) { // Continue the PHP expression.  

        $confirmedBookings++; // Increase the counter.  

    } // Close the preceding block or expression.  


    if ( // Check whether this case applies.  
        $booking['status'] // Continue the PHP expression.  
        === 'cancelled' // Continue the PHP expression.  
    ) { // Continue the PHP expression.  

        $cancelledBookings++; // Increase the counter.  

    } // Close the preceding block or expression.  

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
        Staff Dashboard
    </title> 


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    > 


    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Oswald:wght@400;500;600&display=swap"
        rel="stylesheet"
    > 


    <link rel="stylesheet" href="dashboard.css"> 

</head> 


<body> 




 

<nav class="navbar navbar-dark"> 

    <div class="container"> 


        <a
            class="navbar-brand"
            href="dashboard.php"
        > 

            Staff Dashboard 

        </a> 
        <a href="manage_schedule.php" class="btn btn-outline-light btn-sm">Manage schedule</a> 


        <div
            class="d-flex align-items-center gap-3"
        > 

            <span
                class="text-white-50 d-none d-md-block"
            > 

                Welcome, 

                <?= e( // Continue the PHP expression.  
                    $_SESSION[ // Continue the PHP expression.  
                        'staff_username' // Continue the PHP expression.  
                    ] // Close the preceding block or expression.  
                ) // Close the preceding block or expression.    ?>

            </span> 


            <a
                href="logout.php"
                class="btn btn-outline-light btn-sm"
            > 

                Logout 

            </a> 

        </div> 

    </div> 

</nav> 





 

<section class="dashboard-header"> 

    <div class="container"> 


        <div class="row align-items-center"> 


            <div class="col-md-8"> 


                <div class="text-white-50 mb-2"> 

                    <?= e( // Continue the PHP expression.  
                        $today // Continue the PHP expression.  
                    ) // Close the preceding block or expression.    ?>

                </div> 


                <h1 class="dashboard-title"> 

                    Appointments 

                </h1> 


                <p class="mb-0 text-white-50"> 

                    Manage customer bookings for the selected date 
                    and appointment schedule. 

                </p> 

            </div> 


            <div class="col-md-4 text-md-end mt-3 mt-md-0"> 


                <a
                    href="index.php"
                    class="btn btn-outline-light"
                > 

                    View Website 

                </a> 

            </div> 


        </div> 

    </div> 

</section> 





 

<main class="container py-5"> 
    <?php if ($notice):   ?><p class="alert alert-info" role="status"><?= e($notice)   ?></p><?php endif; // Continue the PHP expression.    ?>
    <form method="get" class="d-flex gap-2 align-items-center mb-4"> 
        <label for="schedule-date">Appointment date</label> 
        <input id="schedule-date" type="date" name="date" value="<?= e($today) // Continue the PHP expression.    ?>" required class="form-control w-auto"> 
        <button class="btn btn-dark" type="submit">View appointments</button> 
    </form> 

    <a class="btn btn-dark mb-4" href="#staff-assistant">Staff assistant · Message drafts &amp; demand outlook</a> 


    <div class="row g-4 mb-5"> 


        <div class="col-md-4"> 


            <div class="stat-card"> 


                <div class="stat-number"> 

                    <?= $totalBookings // Continue the PHP expression.    ?>

                </div> 


                <div class="stat-label"> 

                    Bookings for selected date 

                </div> 


            </div> 

        </div> 



        <div class="col-md-4"> 


            <div class="stat-card"> 


                <div class="stat-number text-success"> 

                    <?= $confirmedBookings // Continue the PHP expression.    ?>

                </div> 


                <div class="stat-label"> 

                    Confirmed 

                </div> 


            </div> 

        </div> 



        <div class="col-md-4"> 


            <div class="stat-card"> 


                <div class="stat-number text-danger"> 

                    <?= $cancelledBookings // Continue the PHP expression.    ?>

                </div> 


                <div class="stat-label"> 

                    Cancelled 

                </div> 


            </div> 

        </div> 


    </div> 



    <?php $aiDemoStaff = true; require __DIR__ . '/ai_demo_widget.php'; // Continue the PHP expression.    ?>

    

 


    <div class="booking-card"> 


        <div class="card-body p-0"> 


            <?php if ( // Continue the PHP expression.
                empty($bookings) // Continue the PHP expression.  
            ): // Continue the PHP expression.    ?>


                <div class="empty-state"> 


                    <h3> 

                        No Bookings for This Date 

                    </h3> 


                    <p> 

                        There are currently no 
                        appointments scheduled 
                        for this date. 

                    </p> 


                </div> 


            <?php else: // Continue the PHP expression.    ?>


                <div class="table-responsive"> 


                    <table
                        class="table table-hover align-middle mb-0"
                    > 


                        <thead
                            class="table-dark"
                        > 

                            <tr> 

                                <th class="ps-4"> 

                                    Booking 

                                </th> 

                                <th> 

                                    Customer 

                                </th> 

                                <th>Service</th> 

                                <th> 

                                    Contact 

                                </th> 

                                <th> 

                                    Time 

                                </th> 

                                <th> 

                                    Barber 

                                </th> 

                                <th> 

                                    Status 

                                </th> 

                                <th class="pe-4"> 

                                    Action 

                                </th> 

                            </tr> 

                        </thead> 


                        <tbody> 


                        <?php foreach ( // Continue the PHP expression.
                            $bookings // Continue the PHP expression.  
                            as $booking // Continue the PHP expression.  
                        ): // Continue the PHP expression.    ?>


                            <tr> 


                                <td class="ps-4"> 


                                    <span
                                        class="booking-number"
                                    > 

                                        # 

                                        <?= e( // Continue the PHP expression.  
                                            str_pad( // Continue the PHP expression.  
                                                (string) // Continue the PHP expression.  
                                                $booking[ // Continue the PHP expression.  
                                                    'booking_number' // Continue the PHP expression.  
                                                ], // Close the preceding block or expression.  
                                                3, // Supply the next value.  
                                                '0', // Supply the next value.  
                                                STR_PAD_LEFT // Continue the PHP expression.  
                                            ) // Close the preceding block or expression.  
                                        ) // Close the preceding block or expression.    ?>

                                    </span> 


                                </td> 


                                <td> 


                                    <?= e( // Continue the PHP expression.  
                                        $booking[ // Continue the PHP expression.  
                                            'customer_name' // Continue the PHP expression.  
                                        ] // Close the preceding block or expression.  
                                    ) // Close the preceding block or expression.    ?>


                                </td>
                                <td>
                                    <?= e($booking['service_name'] ?? 'Not recorded') // Continue the PHP expression.  ?>
                                    <?php if ($booking['service_price_pence'] !== null): // Historical bookings retain unknown service prices.?>
                                        <small class="d-block text-muted"><?= e(service_price_label((int) $booking['service_price_pence'])) // Continue the PHP expression.  ?> · Pay at shop</small>
                                    <?php endif; // Continue the PHP expression.  ?>
                                </td>
 


                                <td> 


                                    <small> 

                                        <?= e( // Continue the PHP expression.  
                                            $booking[ // Continue the PHP expression.  
                                                'contact_value' // Continue the PHP expression.  
                                            ] // Close the preceding block or expression.  
                                        ) // Close the preceding block or expression.    ?>

                                    </small> 


                                </td> 


                                <td> 


                                    <strong> 

                                        <?= e( // Continue the PHP expression.  
                                            substr( // Continue the PHP expression.  
                                                $booking[ // Continue the PHP expression.  
                                                    'appointment_time' // Continue the PHP expression.  
                                                ], // Close the preceding block or expression.  
                                                0, // Supply the next value.  
                                                5 // Continue the PHP expression.  
                                            ) // Close the preceding block or expression.  
                                        ) // Close the preceding block or expression.    ?>


                                    </strong> 


                                </td> 


                                <td> 


                                    <?= e( // Continue the PHP expression.  
                                        $booking[ // Continue the PHP expression.  
                                            'barber_name' // Continue the PHP expression.  
                                        ] // Close the preceding block or expression.  
                                    ) // Close the preceding block or expression.    ?>


                                </td> 


                                <td> 


                                    <?php

                                    $status = // Set the status. 
                                        $booking[ // Continue the PHP expression.  
                                            'status' // Continue the PHP expression.  
                                        ]; // Close the preceding block or expression.  

                                    $badgeClass = // Set the badge class. 
                                        'badge-confirmed'; // Continue the PHP expression.  

                                    if ( // Check whether this case applies.  
                                        $status // Continue the PHP expression.  
                                        === 'cancelled' // Continue the PHP expression.  
                                    ) { // Continue the PHP expression.  

                                        $badgeClass = // Set the badge class. 
                                            'badge-cancelled'; // Continue the PHP expression.  

                                    } // Close the preceding block or expression.  

                                    if ( // Check whether this case applies.  
                                        $status // Continue the PHP expression.  
                                        === 'completed' // Continue the PHP expression.  
                                    ) { // Continue the PHP expression.  

                                        $badgeClass = // Set the badge class. 
                                            'badge-completed'; // Continue the PHP expression.  

                                    } // Close the preceding block or expression.  

                                    ?>


                                    <span
                                        class="badge
                                        <?= e( // Continue the PHP expression.  
                                            $badgeClass // Continue the PHP expression.  
                                        ) // Close the preceding block or expression.    ?>"
                                    > 

                                        <?= e( // Continue the PHP expression.  
                                            ucfirst( // Continue the PHP expression.  
                                                $status // Continue the PHP expression.  
                                            ) // Close the preceding block or expression.  
                                        ) // Close the preceding block or expression.    ?>

                                    </span> 


                                </td> 


                                <td class="pe-4"> 


                                    <?php if ( // Continue the PHP expression.
                                        $status // Continue the PHP expression.  
                                        === 'confirmed' // Continue the PHP expression.  
                                    ): // Continue the PHP expression.    ?>


                                        <?php foreach (['completed' => 'Complete', 'cancelled' => 'Cancel'] as $targetStatus => $label): // Continue the PHP expression.    ?>
                                        <form method="post" action="<?= $targetStatus === 'completed' ? 'complete.php' : 'cancel.php'   ?>" class="d-inline" onsubmit="return confirm('<?= e($label) // Continue the PHP expression.    ?> this appointment?');"> 
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) // Continue the PHP expression.    ?>"> 
                                            <input type="hidden" name="id" value="<?= (int) $booking['id'] // Continue the PHP expression.    ?>"> 
                                            <input type="hidden" name="date" value="<?= e($today) // Continue the PHP expression.    ?>"> 
                                            <button type="submit" class="btn btn-sm <?= $targetStatus === 'completed' ? 'btn-outline-success' : 'btn-outline-danger'   ?>"><?= e($label) // Continue the PHP expression.    ?></button> 
                                        </form> 
                                        <?php endforeach; // Continue the PHP expression.    ?>


                                    <?php else: // Continue the PHP expression.    ?>


                                        <span
                                            class="text-muted"
                                        > 

                                            — 

                                        </span> 


                                    <?php endif; // Continue the PHP expression.    ?>


                                </td> 


                            </tr> 


                        <?php endforeach; // Continue the PHP expression.    ?>


                        </tbody> 


                    </table> 


                </div> 


            <?php endif; // Continue the PHP expression.    ?>


        </div> 


    </div> 


</main> 


</body> 

</html> 
