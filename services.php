<?php // Shared example service catalogue and server-side price lookup.
declare(strict_types=1); // Reject unintended type coercion in catalogue helpers.

function service_categories(): array // Keep category labels consistent in the cards, popups and form.
{ // Start the block.
    return ['haircut' => 'Haircut', 'beard' => 'Beard Trim', 'hair_beard' => 'Hair & Beard']; // These are the three existing service groups.
} // Close the preceding block or expression.

function service_catalogue(): array // Return the single trusted source of service names and GBP prices.
{ // Start the block.
    // Example haircut/beard prices: https://www.prokut.co.uk/ — checked 30 October 2026.
    // Example combined-service prices: https://truemanbarbers.co.uk/services — checked 30 October 2026.
    // Amounts are integer pence; these examples are not prices supplied by a participating shop.
    return [ // Return the result.
        'crew_one_grade' => ['category' => 'haircut', 'name' => 'Crew Cut — One Number', 'price_pence' => 1400], // Published PRO KUT example: £14.
        'crew_blended' => ['category' => 'haircut', 'name' => 'Crew Cut — Different Numbers', 'price_pence' => 1600], // Published PRO KUT example: £16.
        'haircut' => ['category' => 'haircut', 'name' => 'Haircut', 'price_pence' => 2100], // Published PRO KUT example: £21.
        'scissor_haircut' => ['category' => 'haircut', 'name' => 'Scissor Haircut', 'price_pence' => 2200], // Published PRO KUT example: £22.
        'skin_fade' => ['category' => 'haircut', 'name' => 'Skinfade', 'price_pence' => 2200], // Published PRO KUT example: £22.
        'restyle' => ['category' => 'haircut', 'name' => 'Restyle', 'price_pence' => 2400], // Published PRO KUT example: £24.
        'beard_clippers' => ['category' => 'beard', 'name' => 'Clippers Only Beard Trim', 'price_pence' => 1000], // Published PRO KUT example: £10.
        'beard_blade' => ['category' => 'beard', 'name' => 'Clippers + Blade Beard Trim', 'price_pence' => 1500], // Published PRO KUT example: £15.
        'clipper_beard' => ['category' => 'hair_beard', 'name' => 'All over cut & beard trim', 'price_pence' => 2800], // Published True Man example: £28.
        'haircut_beard' => ['category' => 'hair_beard', 'name' => 'Haircut, wash & beard trim', 'price_pence' => 3400], // Published True Man example: £34.
        'scissor_beard' => ['category' => 'hair_beard', 'name' => 'Scissor cut & beard trim, hot towel', 'price_pence' => 3600], // Published True Man example: £36.
        'fade_beard' => ['category' => 'hair_beard', 'name' => 'Skin fade & beard trim, hot towel', 'price_pence' => 3600], // Published True Man example: £36.
    ]; // Each code is stable and each booking stores a name/price snapshot.
} // Close the preceding block or expression.

function service_find(mixed $code): ?array // Accept a known catalogue code rather than a price submitted by the browser.
{ // Start the block.
    if (!is_string($code)) return null; // Reject arrays and other malformed request values.
    $catalogue = service_catalogue(); // Read the trusted catalogue on the server.
    return isset($catalogue[$code]) ? ['code' => $code] + $catalogue[$code] : null; // Unknown or empty codes have no valid service.
} // Close the preceding block or expression.

function service_price_label(int $pence): string // Format integer pence for customer and staff displays.
{ // Start the block.
    return '£' . number_format($pence / 100, 2, '.', ','); // GBP is the only catalogue currency; no payment is processed.
} // Close the preceding block or expression.
