<?php // Render the assistant on customer and staff pages.
// Include after config.php. Staff pages enable $aiDemoStaff.
$_SESSION['ai_demo_token'] ??= bin2hex(random_bytes(32)); // Continue the PHP expression.  
$aiDemoStaff = !empty($aiDemoStaff) && isset($_SESSION['staff_id']); // Set the ai demo staff.  
?>
<link rel="stylesheet" href="ai_demo.css"> 
<aside class="ai-demo<?= $aiDemoStaff ? ' ai-demo-staff' : ''   ?>" <?= $aiDemoStaff ? 'id="staff-assistant"' : ''   ?> data-token="<?= e($_SESSION['ai_demo_token'])   ?>" data-staff="<?= $aiDemoStaff ? '1' : '0' // Continue the PHP expression.    ?>" aria-label="Shop assistant"> 
    <?php if (!$aiDemoStaff): // Continue the PHP expression.    ?>
    <button class="ai-launch" type="button" aria-expanded="false" aria-controls="ai-panel">✦ <?= $aiDemoStaff ? 'Staff assistant' : 'Booking assistant' // Continue the PHP expression.    ?></button> 
    <?php endif; // Continue the PHP expression.    ?>
    <section class="ai-panel" id="ai-panel" <?= $aiDemoStaff ? '' : 'hidden' // Continue the PHP expression.    ?> aria-labelledby="ai-title"> 
        <div class="ai-heading"><h2 id="ai-title"><?= $aiDemoStaff ? 'Staff assistant' : 'Your visit, made easier'   ?></h2><?php if (!$aiDemoStaff):   ?><button type="button" class="ai-close" aria-label="Close assistant">×</button><?php endif; // Continue the PHP expression.    ?></div> 
        <p class="ai-note"><?= $aiDemoStaff ? 'Message drafts from your bookings · Demand estimates from your booking history' : 'Your AI booking companion · Hours, availability & your next visit' // Continue the PHP expression.    ?></p> 
        <?php if (!$aiDemoStaff): // Continue the PHP expression.    ?>
        <div id="ai-conversation" class="ai-conversation" role="log" aria-live="polite" aria-label="Conversation"><p class="ai-reply">Hi! Ready for a fresh look? I can help you check our hours or find your next appointment. Pick a question below to get started.</p></div> 
        <p class="ai-note">Ask about a date or time without names or email addresses. AI can misunderstand your question; check the interpreted date below. Confirm your visit in the booking form.</p> 
        <div class="ai-prompts" aria-label="Suggested questions"><button type="button" data-question="Any appointments today?">Today’s availability</button><button type="button" data-question="Any appointments tomorrow?">Tomorrow’s availability</button><button type="button" data-question="What are your opening hours?">Opening hours</button></div> 
        <p id="ai-chat-status" role="status" class="ai-note"></p> 
        <form id="ai-chat-form"> 
            <label for="ai-date">Date to check (optional)</label><input id="ai-date" type="date" min="<?= date('Y-m-d') // Continue the PHP expression.    ?>"> 
            <label for="ai-message">Your question</label><div class="ai-input-row"><input id="ai-message" maxlength="500" placeholder="Can I book tomorrow?" required><button type="submit" class="ai-gold">Ask</button></div> 
        </form> 
        <button id="ai-use-date" class="ai-gold ai-wide" type="button" hidden>Continue to booking form</button> 
        <?php else: // Continue the PHP expression.    ?>
        <button id="ai-refresh" type="button" class="ai-gold ai-wide">Load / refresh staff tools</button> 
        <p id="ai-staff-status" role="status"></p> 
        <div class="ai-staff-columns"> 
        <section class="ai-staff-tool" aria-labelledby="ai-messages-heading"><h3 id="ai-messages-heading">Customer message drafts</h3><p>Choose an upcoming confirmed booking. Drafts are editable and are never sent automatically.</p> 
            <form id="ai-draft-form"><label for="ai-booking">Booking (next 100 appointments)</label><select id="ai-booking" required><option value="">Load staff tools first</option></select> 
            <label for="ai-kind">Message type</label><select id="ai-kind"><option value="reminder">Appointment reminder</option><option value="confirmation">Booking confirmation</option></select> 
            <label for="ai-language">Language</label><select id="ai-language"><option value="en">English</option><option value="da">Danish</option></select><button type="submit" class="ai-gold ai-wide" id="ai-generate" disabled>Create draft</button></form> 
            <label for="ai-draft">Review and edit your draft</label><textarea id="ai-draft" rows="6" placeholder="Your message draft will appear here."></textarea><button id="ai-copy" type="button" class="ai-gold ai-wide" disabled>Copy draft</button><p id="ai-draft-status" role="status"></p> 
        </section> 
        <section class="ai-staff-tool" aria-labelledby="ai-demand-heading"><h3 id="ai-demand-heading">Demand outlook · next 7 days</h3><p id="ai-forecast-status">Load staff tools to see available booking history.</p><div id="ai-forecast"></div><p class="ai-note">Baseline estimates only. No trained prediction model.</p></section> 
        </div> 
        <?php endif; // Continue the PHP expression.    ?>
        <p id="ai-error" class="ai-error" role="alert"></p> 
    </section> 
</aside> 
<script src="ai_demo.js" defer></script> 
