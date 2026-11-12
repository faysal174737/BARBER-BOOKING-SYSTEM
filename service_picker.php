<?php // Shared service field; $selectedServiceCode optionally restores an existing valid choice.
$selectedServiceCode = isset($selectedServiceCode) && is_string($selectedServiceCode) ? $selectedServiceCode : ''; // Default to no unconfirmed service.
?>
<div class="mb-4">
    <label class="form-label fw-semibold" for="service_code">Your service</label>
    <select id="service_code" name="service_code" class="form-select" aria-describedby="service-summary" required>
        <option value="">Choose your service</option>
        <?php foreach (service_categories() as $category => $categoryLabel): // Group the catalogue consistently with the popup menus.?>
        <optgroup label="<?= e($categoryLabel) // Continue the PHP expression.  ?>">
            <?php foreach (service_catalogue() as $serviceCode => $formService): // Render only this category's trusted choices.?>
                <?php if ($formService['category'] !== $category) continue; // Skip options in another category.?>
                <option value="<?= e($serviceCode) ?>" data-service-name="<?= e($formService['name']) ?>" data-service-price="<?= e(service_price_label($formService['price_pence'])) ?>" <?= $selectedServiceCode === $serviceCode ? 'selected' : '' ?>><?= e($formService['name'] . ' — ' . service_price_label($formService['price_pence'])) // Continue the PHP expression.  ?></option>
            <?php endforeach; // Finish this category.?>
        </optgroup>
        <?php endforeach; // Finish all service choices.?>
    </select>
    <p id="service-summary" class="booking-help service-selection-summary" aria-live="polite">Choose a service above or from the service menus. Pay at the shop after your appointment.</p>
</div>
