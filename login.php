<?php // Staff sign-in form and password-hash verification.

declare(strict_types=1); // Enable strict typing. 

require __DIR__ . '/config.php'; // Load the required file. 


// Redirect signed-in staff to the dashboard.





if (isset($_SESSION['staff_id'])) { // Check whether this case applies.  

    header('Location: dashboard.php'); // Set a response header.  

    exit; // End the request. 
} // Close the preceding block or expression.  


$error = ''; // Set the error. 


// Process the staff sign-in form.





if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check whether this case applies.  

    $username = trim( // Set the username. 
        (string) ( // Continue the PHP expression.  
            $_POST['username'] ?? '' // Continue the PHP expression.  
        ) // Close the preceding block or expression.  
    ); // Close the preceding block or expression.  


    $password = (string) ( // Set the password. 
        $_POST['password'] ?? '' // Continue the PHP expression.  
    ); // Close the preceding block or expression.  


    if ( // Check whether this case applies.  
        $username === '' // Continue the PHP expression. 
        || // Continue the condition.  
        $password === '' // Continue the PHP expression. 
    ) { // Continue the PHP expression.  

        $error = // Set the error. 
            'Please enter your username and password.'; // Continue the PHP expression.  

    } else { // Handle the alternative case. 

        $pdo = db(); // Set the pdo. 


        $statement = $pdo->prepare( // Prepare the database statement. 
            "SELECT
                id,
                username,
                password_hash
             FROM staff_users
             WHERE username = ?
             LIMIT 1"  
        ); // Close the preceding block or expression.  


        $statement->execute([ // Run the prepared statement. 
            $username // Continue the PHP expression.  
        ]); // Close the preceding block or expression.  


        $staff = $statement->fetch(); // Read the database result. 


        if ( // Check whether this case applies.  
            $staff // Continue the PHP expression.  
            && // Continue the condition.  
            password_verify( // Continue the PHP expression. 
                $password, // Supply the next value.  
                $staff['password_hash'] // Continue the PHP expression.  
            ) // Close the preceding block or expression.  
        ) { // Continue the PHP expression.  

            // Regenerate the session ID after sign-in.





            session_regenerate_id(true); // Replace the session ID. 


            $_SESSION['staff_id'] = // Continue the PHP expression.  
                (int) $staff['id']; // Continue the PHP expression.  


            $_SESSION['staff_username'] = // Continue the PHP expression.  
                $staff['username']; // Continue the PHP expression.  


            header( // Set a response header.  
                'Location: dashboard.php' // Continue the PHP expression.  
            ); // Close the preceding block or expression.  

            exit; // End the request. 

        } else { // Handle the alternative case. 

            $error = // Set the error. 
                'Invalid username or password.'; // Continue the PHP expression.  

        } // Close the preceding block or expression.  

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
        Staff Login
    </title> 


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    > 


    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Oswald:wght@400;500;600&display=swap"
        rel="stylesheet"
    > 


    <link rel="stylesheet" href="login.css"> 

</head> 


<body> 


<div class="login-container"> 


    <div class="login-card"> 


        <div class="brand"> 

            Staff Login 

        </div> 


        <div class="subtitle"> 

            <?= e( // Continue the PHP expression.  
                'Barber Booking System' // Continue the PHP expression.  
            ) // Close the preceding block or expression.    ?>

        </div> 



        <?php if ($error !== ''): // Continue the PHP expression.    ?>

            <div
                class="alert alert-danger"
            > 

                <?= e($error) // Continue the PHP expression.    ?>

            </div> 

        <?php endif; // Continue the PHP expression.    ?>



        <form
            method="POST"
            action="login.php"
        > 


            <div class="mb-3"> 

                <label
                    class="form-label fw-semibold"
                > 

                    Username 

                </label> 


                <input
                    type="text"
                    name="username"
                    class="form-control"
                    autocomplete="username"
                    required
                > 

            </div> 



            <div class="mb-4"> 

                <label
                    class="form-label fw-semibold"
                > 

                    Password 

                </label> 


                <input
                    type="password"
                    name="password"
                    class="form-control"
                    autocomplete="current-password"
                    required
                > 

            </div> 



            <button
                type="submit"
                class="login-button"
            > 

                Sign In 

            </button> 


        </form> 



        <div class="back-link"> 

            <a href="index.php"> 

                ← Back to customer website 

            </a> 

        </div> 


    </div> 

</div> 


</body> 

</html> 