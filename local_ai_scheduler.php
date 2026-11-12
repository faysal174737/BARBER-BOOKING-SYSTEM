<?php // Local model configuration and existing staff helper text generation.
declare(strict_types=1); // Enable strict typing. 

const LOCAL_SCHEDULING_MODEL = 'qwen2.5:0.5b'; // Continue the PHP expression. 

// Ask the local model to choose from the supplied slots.
function local_ai_choose(array $schedule): array // Define local ai choose. 
{ // Start the block.  
    if (!$schedule['slots']) throw new RuntimeException('No bookable slots remain for this date.'); // Check whether this case applies.  
    if (!function_exists('curl_init')) throw new RuntimeException('PHP cURL is required for the local AI scheduler.'); // Check whether this case applies.  
    $candidates = []; // Set the candidates. 
    // Earliest time is a hard scheduling requirement, not a model suggestion.
    $earliestTime = $schedule['next']['time']; // Set the earliest time. 
    foreach ($schedule['slots'] as $index => $slot) { // Process each item.  
        if ($slot['time'] !== $earliestTime) continue; // Check whether this case applies.  
        $candidates[] = ['slot_index' => $index, 'time' => substr($slot['time'], 0, 5), 'barber_id' => $slot['barber_id'], // Append an item to the list.  
            'existing_bookings' => $slot['barber_load'] ?? 0]; // Set this array entry. 
    } // Close the preceding block or expression.  
    $payload = [ // Set the payload. 
        'model' => LOCAL_SCHEDULING_MODEL, // Set this array entry. 
        'stream' => false, // Set this array entry. 
        'keep_alive' => '10m', // Set this array entry. 
        'options' => ['temperature' => 0, 'num_predict' => 48, 'num_ctx' => 2048], // Set this array entry. 
        'format' => ['type' => 'object', 'properties' => ['slot_index' => ['type' => 'integer', 'enum' => array_column($candidates, 'slot_index')]], // Set this array entry. 
            'required' => ['slot_index'], 'additionalProperties' => false], // Set this array entry. 
        'messages' => [ // Set this array entry. 
            ['role' => 'system', 'content' => 'Select one barber appointment from the supplied candidates, which all have the earliest available time. Prefer fewer existing_bookings to balance barber workload. If tied, prefer lower barber_id. Return only JSON with slot_index.'], // Supply the next value.  
            ['role' => 'user', 'content' => json_encode(['date' => $schedule['date'], 'candidates' => $candidates], JSON_THROW_ON_ERROR)], // Supply the next value. 
        ], // Close the preceding block or expression.  
    ]; // Close the preceding block or expression.  
    // Send inference requests to the local model.
    $curl = curl_init('http://127.0.0.1:11434/api/chat'); // Set the curl.
    curl_setopt_array($curl, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, // Supply the next value. 
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR), // Supply the next value. 
        CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_TIMEOUT => 60, CURLOPT_PROXY => '']); // Continue the PHP expression.  
    $body = curl_exec($curl); $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE); curl_close($curl); // Set the body. 
    if ($body === false || $status !== 200) { // Check whether this case applies.  
        throw new RuntimeException('The local AI scheduler is not ready. Start Ollama and ensure qwen2.5:0.5b is installed, then try again. No booking has been created.'); // Report an error to the caller. 
    } // Close the preceding block or expression.  
    try { // Run the operation with error handling. 
        $response = json_decode($body, true, 32, JSON_THROW_ON_ERROR); // Decode the JSON input. 
        $choice = json_decode($response['message']['content'] ?? '', true, 8, JSON_THROW_ON_ERROR); // Decode the JSON input. 
    } catch (Throwable $error) { // Handle the exception. 
        throw new RuntimeException('The local AI returned an invalid scheduling response. Please try again. No booking has been created.'); // Report an error to the caller. 
    } // Close the preceding block or expression.  
    $index = $choice['slot_index'] ?? null; // Set the index. 
    if (!is_int($index) || !in_array($index, array_column($candidates, 'slot_index'), true)) { // Check whether this case applies.  
        throw new RuntimeException('The local AI did not select a valid slot. Please try again. No booking has been created.'); // Report an error to the caller. 
    } // Close the preceding block or expression.  
    return $schedule['slots'][$index]; // Return the result. 
} // Close the preceding block or expression.  

// Check the proposed slot again while holding the booking lock.
function local_ai_validate(array $proposal, array $current): array // Define local ai validate. 
{ // Start the block.  
    foreach ($current['slots'] as $slot) { // Process each item.  
        if ($slot['barber_id'] === $proposal['barber_id'] && $slot['time'] === $proposal['time']) return $slot; // Check whether this case applies.  
    } // Close the preceding block or expression.  
    throw new RuntimeException('The AI-selected appointment is no longer available. Please try again so the AI can select another slot. No booking has been created.'); // Report an error to the caller. 
} // Close the preceding block or expression.  
