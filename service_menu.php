<?php // Render the service menus.
$scissors = '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M8 8l13 13M8 16L21 3m-9 9 2 2"/>'; // Set the scissors.
$beard = '<path d="M4 8v5c0 4 4 7 8 9 4-2 8-5 8-9V8l-3 5-5-3-5 3zM8 6c1-2 3-3 4-3s3 1 4 3"/>'; // Set the beard.
$serviceIcons = ['haircut' => $scissors, 'beard' => $beard, 'hair_beard' => '<path d="m12 2 8 5v10l-8 5-8-5V7zM8 10h8m-8 4h8m-4-8v12"/>']; // Set the service icons.
// Illustrate the cut shape using local vector paths. Combined services share their haircut silhouette.
$crop = 'M10 18v-4l3-3h14l3 3v4z'; // Set the crop.
$blend = 'M10 19v-5l4-4h12l4 4v5l-4-3H14z'; // Set the blend.
$classic = 'M10 20v-6l5-5 11 1 4 5v5l-5-6-7 3z'; // Set the classic.
$scissorCut = 'M10 20v-6l4-5 4 2 4-4 5 3 3 5v5l-5-5-5 2-5-2z'; // Set the scissor cut.
$fade = 'M10 18v-4l5-5h10l5 5v4l-5-3H15z'; // Set the fade.
$restyle = 'M9 22v-8l6-6 10-1 6 6v9l-5-7-7 3-5-2z'; // Set the restyle.
$cutIllustrations = [ // Set the cut illustrations.
    'crew_one_grade' => $crop, 'crew_blended' => $blend, 'haircut' => $classic, // Set this array entry.
    'scissor_haircut' => $scissorCut, 'skin_fade' => $fade, 'restyle' => $restyle, // Set this array entry.
    'clipper_beard' => $crop, 'haircut_beard' => $classic, 'scissor_beard' => $scissorCut, 'fade_beard' => $fade, // Set this array entry.
]; // Close the preceding block or expression.
$serviceDescriptions = ['haircut' => 'Classic cuts, fades and a fresh new style.', 'beard' => 'A tidy trim or a sharper shape.', 'hair_beard' => 'Choose a complete haircut and beard service.']; // Short descriptions explain the category choices.
?>
<div class="row g-4"> 
<?php foreach (service_categories() as $category => $categoryLabel): // Render each existing service category once.?>
    <div class="col-md-4">
        <button class="service-card service-card-button" type="button" data-service-category="<?= e($category) ?>" aria-haspopup="dialog" aria-controls="service-menu-<?= e($category) // Continue the PHP expression.  ?>">
            <span class="service-card-picture" aria-hidden="true">
                <svg viewBox="0 0 160 120" fill="none" focusable="false">
                    <circle cx="96" cy="57" r="49" fill="#f1e5c9"/>
                    <path d="M49 119c4-20 20-28 35-30h27c17 4 30 14 35 30" fill="#d7bc81"/>
                    <path d="M76 45c0-20 11-29 27-29 17 0 27 12 27 29v15c0 20-13 34-27 34S76 80 76 60z" fill="#fcf6e6" stroke="#795b2e" stroke-width="2"/>
                    <?php if ($category === 'haircut'): // Continue the PHP expression.  ?>
                        <path d="M76 57V40c0-16 12-24 27-24 14 0 25 8 27 24v12l-10-13-12 6-15-3z" fill="#59401f"/>
                        <path d="M83 48l12-8 13 3 13-6M83 37l12-9 19 1" stroke="#bc9a5c" stroke-width="2" stroke-linecap="round"/>
                    <?php elseif ($category === 'beard'): // Continue the PHP expression.  ?>
                        <path d="M76 55l12 14 15-8 15 8 12-14v10c0 18-12 29-27 32-15-3-27-14-27-32z" fill="#59401f"/>
                        <path d="M89 77l14 8 14-8" stroke="#bc9a5c" stroke-width="2" stroke-linecap="round"/>
                    <?php else: // Continue the PHP expression.  ?>
                        <path d="M76 51V39c0-15 12-25 27-25s27 10 27 25v12l-11-12-15 5-14-5zM76 59l12 12 15-7 15 7 12-12v7c0 17-12 29-27 31-15-2-27-14-27-31z" fill="#59401f"/>
                        <path d="M87 32l14-8 16 3m-26 51 12 7 12-7" stroke="#bc9a5c" stroke-width="2" stroke-linecap="round"/>
                    <?php endif; // Continue the PHP expression.  ?>
                    <path d="M91 54h4m16 0h4" stroke="#795b2e" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="service-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false"><?= $serviceIcons[$category] // Continue the PHP expression.  ?></svg></span>
            <span class="service-title"><?= e($categoryLabel) // Continue the PHP expression.  ?></span>
            <span class="service-description"><?= e($serviceDescriptions[$category]) // Continue the PHP expression.  ?></span>
            <span class="service-card-action">View services &amp; prices <span aria-hidden="true">→</span></span>
        </button>
    </div>
<?php endforeach; // Finish the service cards.?>
</div>
<p class="service-price-note">Example GBP prices. Pay at the shop after your appointment.</p>
<?php foreach (service_categories() as $category => $categoryLabel): // Keep each popup linked to its own category card.?>
<dialog class="service-menu" id="service-menu-<?= e($category) ?>" aria-labelledby="service-menu-title-<?= e($category) // Continue the PHP expression.  ?>">
    <div class="service-menu-heading">
        <div><p class="service-menu-eyebrow">CHOOSE YOUR SERVICE</p><h2 id="service-menu-title-<?= e($category) ?>"><?= e($categoryLabel) // Continue the PHP expression.  ?></h2></div>
        <button class="service-menu-close" type="button" data-close-service-menu aria-label="Close <?= e($categoryLabel) // Continue the PHP expression.  ?> services">×</button>
    </div>
    <p class="service-menu-note">Choose a service to add it to your appointment. Payment is at the shop.</p>
    <ol class="service-menu-list">
    <?php $serviceNumber = 0; // Number this category's services in their displayed order.?>
    <?php foreach (service_catalogue() as $serviceCode => $menuService): // Use the same server catalogue as the booking form.?>
        <?php if ($menuService['category'] !== $category) continue; // Show only services belonging to this popup.?>
        <li><button class="service-option" type="button" data-select-service="<?= e($serviceCode) // Continue the PHP expression.  ?>">
            <span class="service-option-number" aria-hidden="true"><?= e(str_pad((string) ++$serviceNumber, 2, '0', STR_PAD_LEFT)) // Continue the PHP expression.  ?></span>
            <span class="service-option-art" aria-hidden="true">
                <svg viewBox="0 0 40 40" focusable="false">
                    <path d="M11 17v6c0 6 4 11 9 11s9-5 9-11v-6" fill="none" stroke="currentColor" stroke-width="1.4"/>
                    <?php if (isset($cutIllustrations[$serviceCode])): // Continue the PHP expression.  ?>
                        <path d="<?= e($cutIllustrations[$serviceCode]) // Continue the PHP expression.  ?>" fill="currentColor"/>
                    <?php endif; // Continue the PHP expression.  ?>
                    <?php if ($category !== 'haircut'): // Continue the PHP expression.  ?>
                        <path d="<?= $serviceCode === 'beard_clippers' ? 'M11 23l5 4 4-2 4 2 5-4c-1 7-5 10-9 11-5-1-8-5-9-11z' : 'M11 22l5 5 4-2 4 2 5-5c0 7-4 11-9 13-5-2-9-6-9-13z' // Continue the PHP expression.  ?>" fill="currentColor"/>
                    <?php endif; // Continue the PHP expression.  ?>
                    <path d="M16 21h1m6 0h1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="service-option-name"><?= e($menuService['name']) // Continue the PHP expression.  ?></span>
            <strong class="service-option-price"><?= e(service_price_label($menuService['price_pence'])) // Continue the PHP expression.  ?></strong>
            <span class="service-option-action">Select <span aria-hidden="true">→</span></span>
        </button></li>
    <?php endforeach; // Finish the ordered service options.?>
    </ol>
    <p class="service-menu-footer">Example prices · No online payment</p>
</dialog>
<?php endforeach; // Finish the three accessible dialogs.?>
