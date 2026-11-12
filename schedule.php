<?php // Date validation and availability calculations shared by customer and staff handlers.
declare(strict_types=1); // Enable strict typing. 

function schedule_date(string $value): string // Accept only a real, current or future ISO appointment date.
{ // Start the block.  
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value); // Set the date. 
    if (!$date || $date->format('Y-m-d') !== $value || $value < date('Y-m-d')) { // Check whether this case applies.  
        throw new InvalidArgumentException('Choose today or a valid future date.'); // Report an error to the caller. 
    } // Close the preceding block or expression.  
    return $value; // Return the result. 
} // Close the preceding block or expression.  

// Build available slots for each barber.
function schedule_build(array $settings, array $barbers, array $bookings, string $date, DateTimeImmutable $now, array $unavailable = []): array // Enumerate unoccupied barber-time positions using the supplied schedule.
{ // Start the block.  
    $duration = (int) $settings['appointment_minutes']; // Set the duration. 
    if ($duration < 1) { // Check whether this case applies.  
        throw new RuntimeException('The shop schedule requires a positive appointment duration.'); // Report an error to the caller. 
    } // Close the preceding block or expression.  
    $open = new DateTimeImmutable($date . ' ' . $settings['opening_time']); // Set the open. 
    $close = new DateTimeImmutable($date . ' ' . $settings['closing_time']); // Set the close. 
    $occupied = []; $loads = []; // Set the occupied. 
    foreach ($bookings as $booking) { // Process each item.  
        if (($booking['status'] ?? 'confirmed') === 'cancelled') continue; // Check whether this case applies.  
        $occupied[$booking['appointment_time'] . ':' . $booking['barber_id']] = true; // Continue the PHP expression.  
        $loads[$booking['barber_id']] = ($loads[$booking['barber_id']] ?? 0) + 1; // Continue the PHP expression.  
    } // Close the preceding block or expression.  
    $slots = []; $capacity = 0; // Set the slots. 
    for ($time = $open; $time->modify("+{$duration} minutes") <= $close; $time = $time->modify("+{$duration} minutes")) { // Repeat while the condition holds.  
        foreach ($barbers as $barber) { // Process each item.  
            $blocked = false; // Set the blocked. 
            foreach ($unavailable as $period) { // Process each item.  
                if ((int) $period['barber_id'] === (int) $barber['id'] // Check whether this case applies.  
                    && $time->format('H:i:s') < $period['end_time'] // Continue the condition.  
                    && $time->modify("+{$duration} minutes")->format('H:i:s') > $period['start_time']) $blocked = true; // Continue the condition.  
            } // Close the preceding block or expression.  
            if ($blocked) continue; // Check whether this case applies.  
            $capacity++; // Increase the counter.  
            $key = $time->format('H:i:s') . ':' . $barber['id']; // Set the key. 
            if ($time >= $now && !isset($occupied[$key])) { // Check whether this case applies.  
                $slots[] = ['time' => $time->format('H:i:s'), 'barber_id' => (int) $barber['id'], 'barber_name' => $barber['name'], 'barber_load' => $loads[$barber['id']] ?? 0]; // Append an item to the list.  
            } // Close the preceding block or expression.  
        } // Close the preceding block or expression.  
    } // Close the preceding block or expression.  
    return ['date' => $date, 'capacity' => $capacity, 'remaining' => count($slots), 'slots' => $slots, // Return the result. 
        'next' => $slots[0] ?? null, 'settings' => $settings]; // Set this array entry. 
} // Close the preceding block or expression.  

function schedule_load(PDO $pdo, string $date, bool $lock = false): array // Read current settings, active barbers, blocks and bookings.
{ // Start the block.  
    $suffix = $lock ? ' FOR UPDATE' : ''; // Set the suffix. 
    // book.php holds this settings lock until the confirmed booking is committed.
    $settings = $pdo->query('SELECT * FROM shop_settings WHERE id = 1' . $suffix)->fetch(); // Read the database result. 
    if (!$settings) throw new RuntimeException('The shop settings are unavailable.'); // Check whether this case applies.  
    $barbers = $pdo->query('SELECT id, name FROM barbers WHERE active = 1 ORDER BY id' . $suffix)->fetchAll(); // Read the database result. 
    $periods = $pdo->prepare('SELECT barber_id, start_time, end_time FROM barber_unavailability WHERE unavailable_date = ?' . $suffix); // Prepare the database statement. 
    $periods->execute([$date]); // Run the prepared statement. 
    $statement = $pdo->prepare("SELECT appointment_time, barber_id, status FROM bookings WHERE booking_date = ? AND status <> 'cancelled'" . $suffix); // Prepare the database statement. 
    $statement->execute([$date]); // Run the prepared statement. 
    return schedule_build($settings, $barbers, $statement->fetchAll(), $date, new DateTimeImmutable(), $periods->fetchAll()); // Return the result. 
} // Close the preceding block or expression.  

function schedule_public(array $schedule): array // Return the fields used by the public availability endpoint.
{ // Start the block.  
    return ['date' => $schedule['date'], 'capacity' => $schedule['capacity'], 'remaining' => $schedule['remaining'], // Return the result. 
        'next_time' => $schedule['next'] ? substr($schedule['next']['time'], 0, 5) : null, // Set this array entry. 
        'barber' => $schedule['next']['barber_name'] ?? null, // Set this array entry. 
        'slots' => array_map(static fn(array $slot): array => ['time' => $slot['time'], 'barber_id' => $slot['barber_id'], 'barber_name' => $slot['barber_name']], $schedule['slots'])]; // Set this array entry. 
} // Close the preceding block or expression.  

function schedule_pick(array $schedule, string $choice): array // Choose an eligible explicit position or the earliest available position.
{ // Start the block.  
    foreach ($schedule['slots'] as $slot) { // Process each item.  
        if ($choice === $slot['barber_id'] . '|' . $slot['time']) return $slot; // Check whether this case applies.  
    } // Close the preceding block or expression.  
    throw new InvalidArgumentException('That appointment is no longer available. Please choose another slot.'); // Report an error to the caller. 
} // Close the preceding block or expression.  
