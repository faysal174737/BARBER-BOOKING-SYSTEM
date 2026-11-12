<?php // Protected staff actions for unavailable periods and appointment rescheduling.
declare(strict_types=1); // Enable strict typing. 
require __DIR__ . '/config.php'; // Load the required file. 
require __DIR__ . '/schedule.php'; // Load the required file. 
require __DIR__ . '/notifications.php'; // Load the required file. 
require_staff(); // Continue the PHP expression. 
$pdo = db(); // Set the pdo. 
$notice = $_SESSION['schedule_notice'] ?? ''; // Set the notice.  
unset($_SESSION['schedule_notice']); // Continue the PHP expression.  
$date = date('Y-m-d'); // Set the date. 
try { $date = schedule_date((string) ($_REQUEST['date'] ?? $date)); } catch (InvalidArgumentException $error) { $notice = $error->getMessage(); } // Run the operation with error handling. 
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Handle staff schedule updates.
    require_csrf(); // Validate the form token.
    try { // Run the operation with error handling. 
        $date = schedule_date((string) ($_POST['date'] ?? '')); // Read the submitted date.  
        $pdo->beginTransaction(); // Start the database transaction. 
        $pdo->query('SELECT id FROM shop_settings WHERE id = 1 FOR UPDATE')->fetch(); // Run the database statement. 
        $action = $_POST['action'] ?? ''; // Read the submitted action.  
        if ($action === 'block') { // Check the requested block against confirmed appointments.
            $barber = filter_var($_POST['barber'] ?? '', FILTER_VALIDATE_INT); // Read the submitted barber.  
            $start = (string) ($_POST['start'] ?? ''); $end = (string) ($_POST['end'] ?? ''); // Read the submitted start.  
            if (!$barber || !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $start) || !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $end) || $start >= $end) throw new InvalidArgumentException('Choose a barber and a valid start/end time.'); // Check whether this case applies.  
            $statement = $pdo->prepare("SELECT COUNT(*) FROM bookings b JOIN shop_settings s ON s.id = 1 WHERE b.barber_id = ? AND b.booking_date = ? AND b.status = 'confirmed' AND b.appointment_time < ? AND ADDTIME(b.appointment_time, SEC_TO_TIME(s.appointment_minutes * 60)) > ?"); // Prepare the database statement. 
            $statement->execute([$barber, $date, $end, $start]); // Run the prepared statement. 
            if ($statement->fetchColumn()) throw new InvalidArgumentException('This period overlaps a confirmed booking. Reschedule or cancel it first.'); // Check whether this case applies.  
            $pdo->prepare('INSERT INTO barber_unavailability (barber_id, unavailable_date, start_time, end_time) VALUES (?, ?, ?, ?)')->execute([$barber, $date, $start, $end]); // Continue the PHP expression. 
            $notice = 'Unavailable period saved.'; // Set the notice. 
        } elseif ($action === 'unblock') { // Check the next condition.  
            $pdo->prepare('DELETE FROM barber_unavailability WHERE id = ? AND unavailable_date = ?')->execute([(int) ($_POST['period'] ?? 0), $date]); // Continue the PHP expression. 
            $notice = 'Unavailable period removed.'; // Set the notice. 
        } elseif ($action === 'reschedule') { // Check the next condition.  
            $statement = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND status = 'confirmed' FOR UPDATE"); // Prepare the database statement. 
            $statement->execute([(int) ($_POST['booking'] ?? 0)]); $booking = $statement->fetch(); // Run the prepared statement. 
            if (!$booking || $booking['booking_date'] . ' ' . $booking['appointment_time'] < date('Y-m-d H:i:s')) throw new InvalidArgumentException('Choose an upcoming confirmed booking.'); // Check whether this case applies.  
            $slot = schedule_pick(schedule_load($pdo, $date, true), (string) ($_POST['slot'] ?? '')); // Read the submitted slot.  
            $number = (int) $booking['booking_number']; // Set the number. 
            if ($date !== $booking['booking_date']) { // Assign a new daily booking number when the date changes.
                $statement = $pdo->prepare('SELECT COALESCE(MAX(booking_number), 0) + 1 FROM bookings WHERE booking_date = ?'); // Prepare the database statement. 
                $statement->execute([$date]); $number = (int) $statement->fetchColumn(); // Run the prepared statement. 
            } // Close the preceding block or expression.  
            $pdo->prepare('UPDATE bookings SET booking_date = ?, booking_number = ?, appointment_time = ?, barber_id = ? WHERE id = ?')->execute([$date, $number, $slot['time'], $slot['barber_id'], $booking['id']]); // Continue the PHP expression. 
            $notice = 'Appointment rescheduled.'; // Set the notice. 
        } else throw new InvalidArgumentException('Choose a valid action.'); // Handle the alternative case. 
        $pdo->commit(); // Commit the database changes. 
        if ($action === 'reschedule') { // Check whether this case applies.  
            $status = notification_send($booking['contact_type'], $booking['contact_value'], 'Your appointment has been rescheduled. Booking #' . $number . ': ' . $date . ' at ' . substr($slot['time'], 0, 5) . ' (' . date_default_timezone_get() . '), with ' . $slot['barber_name'] . '.'); // Set the status. 
            $notice .= ' ' . notification_status_text($status); // Continue the PHP expression.  
        } // Close the preceding block or expression.  
    } catch (Throwable $error) { // Handle the exception. 
        if ($pdo->inTransaction()) $pdo->rollBack(); // Check whether this case applies.  
        $notice = $error instanceof InvalidArgumentException ? $error->getMessage() : 'The schedule could not be updated. Please try again.'; // Set the notice. 
    } // Close the preceding block or expression.  
    $_SESSION['schedule_notice'] = $notice; // Continue the PHP expression.  
    header('Location: manage_schedule.php?date=' . urlencode($date), true, 303); exit; // Set a response header.  
} // Close the preceding block or expression.  
$schedule = schedule_load($pdo, $date); // Set the schedule. 
$barbers = $pdo->query('SELECT id, name FROM barbers WHERE active = 1 ORDER BY id')->fetchAll(); // Load active barbers for the staff form.
$statement = $pdo->prepare('SELECT u.*, b.name FROM barber_unavailability u JOIN barbers b ON b.id = u.barber_id WHERE unavailable_date = ? ORDER BY start_time'); // Prepare the database statement. 
$statement->execute([$date]); $periods = $statement->fetchAll(); // Run the prepared statement. 
$statement = $pdo->prepare("SELECT id, customer_name, booking_date, appointment_time FROM bookings WHERE status = 'confirmed' AND TIMESTAMP(booking_date, appointment_time) >= ? ORDER BY booking_date, appointment_time LIMIT 200"); // Prepare the database statement. 
$statement->execute([date('Y-m-d H:i:s')]); $bookings = $statement->fetchAll(); // Run the prepared statement. 
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Manage schedule</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="manage_schedule.css"></head><body>
<nav class="p-3"><a class="text-white" href="dashboard.php">← Staff dashboard</a></nav><main class="container py-4"><h1>Manage schedule</h1> 
<?php if ($notice):   ?><p class="alert alert-info" role="status"><?= e($notice)   ?></p><?php endif; // Check whether this case applies.    ?>
<p>Shop timezone: <?= e(date_default_timezone_get()) // Continue the PHP expression.    ?>. Email reminders require the Gmail setup and reminder worker.</p> 
<form method="get" class="mb-4"><label for="date">Day to manage / new appointment date</label><input id="date" name="date" type="date" min="<?= date('Y-m-d')   ?>" value="<?= e($date) // Continue the PHP expression.    ?>" required><button class="btn btn-dark" type="submit">Show availability</button></form> 
<div class="row g-4"><section class="col-md-6"><div class="card p-4"><h2>Barber availability</h2><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token())   ?>"><input type="hidden" name="date" value="<?= e($date) // Continue the PHP expression.    ?>"><input type="hidden" name="action" value="block"> 
<label for="barber">Barber</label><select id="barber" name="barber" class="form-select" required><?php foreach ($barbers as $barber):   ?><option value="<?= (int) $barber['id']   ?>"><?= e($barber['name'])   ?></option><?php endforeach; // Continue the PHP expression.    ?></select> 
<label for="start">Unavailable from</label><input id="start" name="start" type="time" class="form-control" required><label for="end">Until</label><input id="end" name="end" type="time" class="form-control" required><button class="btn btn-dark mt-3">Save unavailable period</button></form> 
<?php foreach ($periods as $period):   ?><form method="post" class="border-top mt-3 pt-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token())   ?>"><input type="hidden" name="date" value="<?= e($date)   ?>"><input type="hidden" name="action" value="unblock"><input type="hidden" name="period" value="<?= (int) $period['id']   ?>"><p><?= e($period['name'] . ': ' . substr($period['start_time'], 0, 5) . '–' . substr($period['end_time'], 0, 5))   ?></p><button class="btn btn-outline-dark">Restore availability</button></form><?php endforeach; // Process each item.    ?></div></section> 
<section class="col-md-6"><div class="card p-4"><h2>Reschedule appointment</h2><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token())   ?>"><input type="hidden" name="date" value="<?= e($date) // Continue the PHP expression.    ?>"><input type="hidden" name="action" value="reschedule"> 
<label for="booking">Upcoming booking (first 200)</label><select id="booking" name="booking" class="form-select" required><option value="">Choose booking</option><?php foreach ($bookings as $booking):   ?><option value="<?= (int) $booking['id']   ?>"><?= e($booking['customer_name'] . ' · ' . $booking['booking_date'] . ' ' . substr($booking['appointment_time'], 0, 5))   ?></option><?php endforeach; // Continue the PHP expression.    ?></select> 
<label for="slot">New time and barber on <?= e($date)   ?></label><select id="slot" name="slot" class="form-select" required><option value="">Choose available slot</option><?php foreach ($schedule['slots'] as $slot):   ?><option value="<?= e($slot['barber_id'] . '|' . $slot['time'])   ?>"><?= e(substr($slot['time'], 0, 5) . ' · ' . $slot['barber_name'])   ?></option><?php endforeach; // Continue the PHP expression.    ?></select><button class="btn btn-dark mt-3">Reschedule and notify customer</button></form></div></section></div></main></body></html> 
