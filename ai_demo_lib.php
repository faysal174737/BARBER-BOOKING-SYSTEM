<?php // Optional demonstration utilities for style suggestions, drafts and historical averages.
declare(strict_types=1); // Enable strict typing. 

// Run demo helpers without network calls or database writes.
function demo_date(string $value): string // Validate the demonstration date without writing booking records.
{ // Start the block.  
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value); // Set the date. 
    if (!$date || $date->format('Y-m-d') !== $value || $value < date('Y-m-d')) { // Check whether this case applies.  
        throw new InvalidArgumentException('Choose today or a valid future date.'); // Report an error to the caller. 
    } // Close the preceding block or expression.  
    return $value; // Return the result. 
} // Close the preceding block or expression.  

function demo_slot(array $settings, array $barbers, int $lastNumber, string $date): array // Calculate the legacy demo slot for two barbers.
{ // Start the block.  
    $minutes = (int) $settings['appointment_minutes']; // Set the minutes. 
    if (count($barbers) < 2 || $minutes < 1) { // Check whether this case applies.  
        return ['available' => false, 'reason' => 'The shop schedule is not ready. Please contact staff.']; // Return the result. 
    } // Close the preceding block or expression.  
    $open = new DateTimeImmutable($date . ' ' . $settings['opening_time']); // Set the open. 
    $close = new DateTimeImmutable($date . ' ' . $settings['closing_time']); // Set the close. 
    $capacity = max(0, (int) floor(($close->getTimestamp() - $open->getTimestamp()) / ($minutes * 60))) * 2; // Set the capacity. 
    if ($lastNumber >= $capacity) { // Check whether this case applies.  
        return ['available' => false, 'reason' => 'This date is fully booked. Please choose another day.']; // Return the result. 
    } // Close the preceding block or expression.  
    // The legacy demo calculates the next slot from the last booking number.
    $time = $open->modify('+' . (intdiv($lastNumber, 2) * $minutes) . ' minutes'); // Set the time. 
    if ($time < new DateTimeImmutable()) { // Check whether this case applies.  
        return ['available' => false, 'reason' => 'The next slot in the current booking schedule has already passed. Choose a future date or contact staff.']; // Return the result. 
    } // Close the preceding block or expression.  
    return ['available' => true, 'date' => $date, 'time' => $time->format('H:i'), // Return the result. 
        'barber' => $barbers[$lastNumber % 2]['name'], 'remaining' => $capacity - $lastNumber]; // Set this array entry. 
} // Close the preceding block or expression.  

function demo_style(string $texture, string $length, string $effort): string // Suggest a hairstyle based on the selected options.
{ // Start the block.  
    if (!in_array($texture, ['straight', 'wavy', 'curly', 'coily'], true) // Check whether this case applies.  
        || !in_array($length, ['short', 'medium', 'long'], true) // Continue the condition.  
        || !in_array($effort, ['low', 'styled'], true)) { // Continue the condition.  
        throw new InvalidArgumentException('Choose a hair texture, length, and styling preference.'); // Report an error to the caller. 
    } // Close the preceding block or expression.  
    $styles = [ // Set the styles. 
        'straight' => ['short' => 'a textured crop', 'medium' => 'a side-part cut', 'long' => 'long layers'], // Set this array entry. 
        'wavy' => ['short' => 'a short textured taper', 'medium' => 'a layered scissor cut', 'long' => 'a longer layered cut'], // Set this array entry. 
        'curly' => ['short' => 'a short curly taper', 'medium' => 'a rounded curly cut', 'long' => 'long curly layers'], // Set this array entry. 
        'coily' => ['short' => 'a low tapered cut', 'medium' => 'a shaped natural cut', 'long' => 'a shaped longer natural cut'], // Set this array entry. 
    ]; // Close the preceding block or expression.  
    $care = $effort === 'low' ? 'Ask for a shape that follows your natural texture and needs little daily styling.' // Set the care. 
        : 'Ask your barber to demonstrate a styling routine and suggest a suitable product.'; // Supply the conditional value.  
    return 'You could try ' . $styles[$texture][$length] . '. ' . $care // Return the result. 
        . ' Discuss your hair density, growth pattern, and preferred upkeep with your barber before deciding.'; // Continue the PHP expression.  
} // Close the preceding block or expression.  

function demo_draft(array $booking, string $shop, string $language, string $kind): string // Draft a confirmation or reminder.
{ // Start the block.  
    if (!in_array($language, ['en', 'da'], true) || !in_array($kind, ['reminder', 'confirmation'], true)) { // Check whether this case applies.  
        throw new InvalidArgumentException('Choose a supported language and message type.'); // Report an error to the caller. 
    } // Close the preceding block or expression.  
    $number = str_pad((string) $booking['booking_number'], 3, '0', STR_PAD_LEFT); // Set the number. 
    $time = substr($booking['appointment_time'], 0, 5); // Set the time. 
    if ($language === 'da') { // Check whether this case applies.  
        $intro = $kind === 'reminder' ? 'Her er en påmindelse om din aftale' : 'Din aftale er bekræftet'; // Set the intro. 
        return "Hej {$booking['customer_name']}, {$intro} hos {$shop} den {$booking['booking_date']} kl. {$time} med {$booking['barber_name']}. Bookingnummer: #{$number}. Kontakt salonen, hvis du har brug for at ændre din aftale. Vi glæder os til at se dig!"; // Return the result. 
    } // Close the preceding block or expression.  
    $intro = $kind === 'reminder' ? 'this is a reminder of your appointment' : 'your appointment is confirmed'; // Set the intro. 
    return "Hi {$booking['customer_name']}, {$intro} at {$shop} on {$booking['booking_date']} at {$time} with {$booking['barber_name']}. Booking number: #{$number}. Please contact the shop if you need to change your appointment. See you soon!"; // Return the result. 
} // Close the preceding block or expression.  

function demo_forecast(array $daily, string $today): array // Calculate booking averages by weekday.
{ // Start the block.  
    $end = new DateTimeImmutable($today); // Set the end. 
    $start = $end->modify('-56 days'); // Set the start. 
    // The first recorded day is the earliest evidence that the system was in use.
    if ($daily && min(array_keys($daily)) > $start->format('Y-m-d')) { // Check whether this case applies.  
        $start = new DateTimeImmutable(min(array_keys($daily))); // Set the start. 
    } // Close the preceding block or expression.  
    $totals = array_fill(0, 7, 0); $samples = array_fill(0, 7, 0); // Set the totals. 
    for ($day = $start; $day < $end; $day = $day->modify('+1 day')) { // Repeat while the condition holds.  
        $weekday = (int) $day->format('w'); // Set the weekday. 
        $samples[$weekday]++; // Continue the PHP expression.  
        $totals[$weekday] += (int) ($daily[$day->format('Y-m-d')] ?? 0); // Continue the PHP expression.  
    } // Close the preceding block or expression.  
    $enough = array_sum($samples) >= 28 && array_sum($totals) >= 20; // Set the enough. 
    $days = []; // Set the days. 
    for ($i = 0; $i < 7; $i++) { // Repeat while the condition holds.  
        $day = $end->modify("+{$i} days"); $w = (int) $day->format('w'); // Set the day. 
        $days[] = ['date' => $day->format('Y-m-d'), 'day' => $day->format('D'), // Append an item to the list.  
            'estimate' => $enough && $samples[$w] ? round($totals[$w] / $samples[$w], 1) : null]; // Set this array entry. 
    } // Close the preceding block or expression.  
    return ['enough' => $enough, 'bookings' => array_sum($totals), 'history_days' => $daily ? array_sum($samples) : 0, // Return the result. 
        'days' => $days, 'method' => 'Simple weekday averages from up to 56 completed calendar days, including days with no bookings. Cancelled bookings are excluded. This is a baseline estimate, not a trained AI prediction. Holidays and changes in opening hours are not modeled.']; // Set this array entry. 
} // Close the preceding block or expression.  
