<?php // Public home page and booking form; booking rules remain in schedule.php and book.php.

declare(strict_types=1); // Enable strict typing. 

require __DIR__ . '/services.php'; // Load the shared catalogue before rendering prices.
require __DIR__ . '/config.php'; // Load the required file. 

$pdo = db(); // Set the pdo. 

$settings = $pdo // Set the settings. 
    ->query( // Continue the PHP expression. 
        "SELECT *
         FROM shop_settings
         WHERE id = 1"  
    ) // Close the preceding block or expression.  
    ->fetch(); // Continue the PHP expression. 


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
        <?= e($settings['shop_name']) // Continue the PHP expression.    ?>
    </title> 


    

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    > 


    

    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Oswald:wght@400;500;600&display=swap"
        rel="stylesheet"
    > 


    <link rel="stylesheet" href="index.css"> 

<link rel="stylesheet" href="booking-polish.css">
<link rel="stylesheet" href="service-menu.css"> 
</head> 


<body> 




 

<nav class="navbar navbar-expand-lg"> 

    <div class="container"> 


        <a
            class="navbar-brand"
            href="#"
        > 

            <?= e( // Continue the PHP expression.  
                $settings['shop_name'] // Continue the PHP expression.  
            ) // Close the preceding block or expression.    ?>

        </a> 


        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNav"
        > 

            <span class="navbar-toggler-icon"></span> 

        </button> 


        <div
            class="collapse navbar-collapse"
            id="mainNav"
        > 

            <ul
                class="navbar-nav ms-auto"
            > 

                <li class="nav-item"> 

                    <a
                        class="nav-link"
                        href="#home"
                    > 

                        Home 

                    </a> 

                </li> 


                <li class="nav-item"> 

                    <a
                        class="nav-link"
                        href="#services"
                    > 

                        Services 

                    </a> 

                </li> 



                <li class="nav-item"> 

                    <a
                        class="nav-link"
                        href="#booking"
                    > 

                        Book 

                    </a> 

                </li> 


                <li class="nav-item"> 

                    <a
                        class="nav-link"
                        href="#contact"
                    > 

                        Contact 

                    </a> 

                </li> 


                <li class="nav-item"> 

                    <a
                        class="nav-link"
                        href="dashboard.php#staff-assistant"
                    > 

                        Staff Assistant 

                    </a> 

                </li> 

            </ul> 

        </div> 

    </div> 

</nav> 





 

<section
    class="hero"
    id="home"
> 

    <div class="container"> 

        <div class="hero-content"> 


            <div class="hero-small"> 

                Local Barber Shop 

            </div> 


            <h1> 

                Sharp Cuts.<br> 

                Better Style. 

            </h1> 


            <p> 

                Professional barbering with simple 
                online appointment booking. Choose 
                your date, get your booking number, 
                and let our system take care of the 
                scheduling. 

            </p> 


            <div class="mt-4"> 


                <a
                    href="#booking"
                    class="btn btn-gold"
                > 

                    Book Appointment 

                </a> 


                <a
                    href="#services"
                    class="btn btn-outline-light-custom"
                > 

                    Our Services 

                </a> 



            </div> 

        </div> 

    </div> 

</section> 





 

<section class="info-bar" aria-label="Opening hours">
    <div class="container text-center">
        <div class="info-number"><?= e(substr($settings['opening_time'], 0, 5) . ' – ' . substr($settings['closing_time'], 0, 5)) // Continue the PHP expression.  ?></div>
        <div class="info-label">Opening Hours</div>
    </div>
</section> 





 

<section
    class="section"
    id="services"
> 

    <div class="container"> 


        <div class="text-center mb-5"> 

            <div class="hero-small"> 

                What We Do 

            </div> 


            <h2 class="section-title"> 

                Our Services 

            </h2> 


            <p class="section-subtitle">Choose a service and price, then book your appointment. Pay at the shop.</p> 

        </div> 



        <?php require __DIR__ . '/service_menu.php'; // Render the three category cards and numbered price menus.?>
    </div>
</section> 





 

<section
    class="section bg-white"
> 

    <div class="container"> 


        <div class="text-center mb-5"> 

            <div class="hero-small"> 

                Simple Booking 

            </div> 


            <h2 class="section-title"> 

                How It Works 

            </h2> 

        </div> 



        <div class="row g-4"> 


            <div class="col-md-3"> 

                <div class="step"> 

                    <div class="step-number"> 

                        1 

                    </div> 


                    <h5> 

                        Choose a Date 

                    </h5> 


                    <p class="text-muted"> 

                        Select the day you want 
                        your appointment. 

                    </p> 

                </div> 

            </div> 



            <div class="col-md-3"> 

                <div class="step"> 

                    <div class="step-number"> 

                        2 

                    </div> 


                    <h5> 

                        Enter Your Details 

                    </h5> 


                    <p class="text-muted"> 

                        No account is needed. 
                        Add your email address. 

                    </p> 

                </div> 

            </div> 



            <div class="col-md-3"> 

                <div class="step"> 

                    <div class="step-number"> 

                        3 

                    </div> 


                    <h5> 

                        Get Your Number 

                    </h5> 


                    <p class="text-muted"> 

                        The system automatically 
                        assigns your booking number. 

                    </p> 

                </div> 

            </div> 



            <div class="col-md-3"> 

                <div class="step"> 

                    <div class="step-number"> 

                        4 

                    </div> 


                    <h5> 

                        Visit the Shop 

                    </h5> 


                    <p class="text-muted"> 

                        Arrive at your assigned 
                        appointment time. 

                    </p> 

                </div> 

            </div> 


        </div> 

    </div> 

</section> 





 


<section
    class="section booking-section"
    id="booking"
> 

    <div class="container"> 


        <div class="row align-items-center g-5"> 


            <div class="col-lg-5"> 


                <div class="hero-small"> 

                    Online Booking 

                </div> 


                <h2 class="section-title"> 

                    Reserve Your Chair 

                </h2> 


                <p> 

                    No account. No password. 
                    Simply provide your name 
                    and contact details, and select 
                    your date. 

                </p> 


                <p> 

                    Choose an available time and barber, 
                    or let our scheduling system select 
                    the earliest appointment for you. 

                </p> 


                <p> 

                    <strong> 

                        Open daily: 

                    </strong> 

                    <?= e( // Continue the PHP expression.  
                        substr( // Continue the PHP expression.  
                            $settings[ // Continue the PHP expression.  
                                'opening_time' // Continue the PHP expression.  
                            ], // Close the preceding block or expression.  
                            0, // Supply the next value.  
                            5 // Continue the PHP expression.  
                        ) // Close the preceding block or expression.  
                    ) // Close the preceding block or expression.    ?>

                    - 

                    <?= e( // Continue the PHP expression.  
                        substr( // Continue the PHP expression.  
                            $settings[ // Continue the PHP expression.  
                                'closing_time' // Continue the PHP expression.  
                            ], // Close the preceding block or expression.  
                            0, // Supply the next value.  
                            5 // Continue the PHP expression.  
                        ) // Close the preceding block or expression.  
                    ) // Close the preceding block or expression.    ?>

                </p> 


            </div> 



            <div class="col-lg-7"> 


                <div class="booking-card"><div class="booking-eyebrow"><span aria-hidden="true">✦</span> YOUR NEXT GREAT LOOK</div> 


                    <h3 class="booking-title"> 

                        Book Now 

                    </h3> 


                    <p class="text-muted mb-4"> 

                        Your booking number and 
                        appointment details will be 
                        shown after confirmation. 

                    </p> 


                    <form
                        action="book.php"
                        method="POST"
                    > 


                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) // Continue the PHP expression.    ?>"> 
                        <input type="hidden" name="booking_token" value="<?= e($_SESSION['booking_token'] ??= bin2hex(random_bytes(32))) // Continue the PHP expression.    ?>"> 
<?php require __DIR__ . '/service_picker.php'; // Require a service before confirming the appointment.?>
                        <p class="small text-muted">Your booking is confirmed here. Please save your appointment details if an email does not arrive.</p> 
                        

                        <div class="mb-3"> 

                            <label for="customer_name"
                                class="form-label fw-semibold"
                            > 

                                Your Name 

                            </label> 


                            <input
                                type="text"
                                name="customer_name" id="customer_name" autocomplete="name" placeholder="Your full name"
                                class="form-control"
                                maxlength="120"
                                required
                            > 

                        </div> 



                        
                        <div class="mb-4"> 
                            <label class="form-label fw-semibold" for="contact_value">Email address</label> 
                            <input type="email" name="contact_value" id="contact_value" class="form-control" placeholder="you@example.com" autocomplete="email" maxlength="190" aria-describedby="email-help" required> 
                            <p id="email-help" class="booking-help">Your appointment details and reminders, in one place.</p> 
                        </div> 
                        

                        <div class="mb-4"> 

                            <label for="booking_date"
                                class="form-label fw-semibold"
                            > 

                                Appointment Date 

                            </label> 


                            <input
                                type="date"
                                name="booking_date" id="booking_date"
                                class="form-control"
                                min="<?= date('Y-m-d') // Continue the PHP expression.    ?>"
                                required
                            > 

                        </div> 



                        <label for="slot-choice" class="form-label">Appointment time and barber</label> 
                        <select id="slot-choice" name="slot_choice" class="form-select mb-3"><option value="">Automatically choose the earliest appointment</option></select> 
                        <div id="schedule-counter" class="border border-warning p-3 mb-3" role="status" aria-live="polite">Choose a date to see available appointments.</div> 

                        <button
                            type="submit"
                            class="btn btn-gold w-100"
                        > 

                            Confirm Appointment 

                        </button> 


                    </form> 

                </div> 

            </div> 

        </div> 

    </div> 

</section> 





 

<section
    class="section"
    id="contact"
> 

    <div class="container"> 


        <div class="row text-center"> 


            <div class="col-md-4 mb-4"> 

                <h4> 

                    Location 

                </h4> 

                <p class="text-muted"> 

                    <?= e( // Continue the PHP expression.  
                        $settings['city'] // Continue the PHP expression.  
                    ) // Close the preceding block or expression.    ?>

                </p> 

            </div> 


            <div class="col-md-4 mb-4"> 

                <h4> 

                    Opening Hours 

                </h4> 

                <p class="text-muted"> 

                    <?= e( // Continue the PHP expression.  
                        substr( // Continue the PHP expression.  
                            $settings[ // Continue the PHP expression.  
                                'opening_time' // Continue the PHP expression.  
                            ], // Close the preceding block or expression.  
                            0, // Supply the next value.  
                            5 // Continue the PHP expression.  
                        ) // Close the preceding block or expression.  
                    ) // Close the preceding block or expression.    ?>

                    - 

                    <?= e( // Continue the PHP expression.  
                        substr( // Continue the PHP expression.  
                            $settings[ // Continue the PHP expression.  
                                'closing_time' // Continue the PHP expression.  
                            ], // Close the preceding block or expression.  
                            0, // Supply the next value.  
                            5 // Continue the PHP expression.  
                        ) // Close the preceding block or expression.  
                    ) // Close the preceding block or expression.    ?>

                </p> 

            </div> 


            <div class="col-md-4 mb-4"> 

                <h4> 

                    Appointments 

                </h4> 

                <p class="text-muted"> 

                    Online booking available 24/7. 

                </p> 

            </div> 


        </div> 

    </div> 

</section> 





 

<footer> 

    <div class="container"> 


        <div class="row"> 


            <div class="col-md-6"> 

                <h4> 

                    <?= e( // Continue the PHP expression.  
                        $settings[ // Continue the PHP expression.  
                            'shop_name' // Continue the PHP expression.  
                        ] // Close the preceding block or expression.  
                    ) // Close the preceding block or expression.    ?>

                </h4> 


                <p> 

                    Professional barbering with 
                    simple online appointment booking. 

                </p> 

            </div> 


            <div class="col-md-6 text-md-end"> 

                <p> 

                    &copy; 
                    <?= date('Y') // Continue the PHP expression.    ?>

                    <?= e( // Continue the PHP expression.  
                        $settings[ // Continue the PHP expression.  
                            'shop_name' // Continue the PHP expression.  
                        ] // Close the preceding block or expression.  
                    ) // Close the preceding block or expression.    ?>

                </p> 


                <a
                    href="login.php"
                    class="text-secondary"
                > 

                    Staff Login 

                </a> 

                <br><a href="dashboard.php#staff-assistant" class="staff-assistant-home">Staff assistant · Message drafts &amp; demand outlook</a> 

            </div> 


        </div> 

    </div> 

</footer> 





<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script> 






<?php require __DIR__ . '/ai_demo_widget.php'; // Load the required file.    ?>
<script src="service-menu.js" defer></script>
<script src="availability.js" defer></script> 
</body> 

</html> 
