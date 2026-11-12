(() => { // Keep service-menu state local to this page.
    'use strict'; // Use strict browser-language rules.
    const picker = document.getElementById('service_code'); // Find the required service field in the appointment form.
    const summary = document.getElementById('service-summary'); // Find the text that confirms the selected service and price.
    if (!picker) return; // Leave pages without a service picker unchanged.
    const menus = Array.from(document.querySelectorAll('dialog.service-menu')); // Collect only the service popups.
    let opener = null; // Remember which category button opened a popup.
    const updateSummary = () => { // Reflect direct dropdown changes as well as popup choices.
        const option = picker.selectedOptions[0]; // Read the currently selected catalogue option.
        summary.textContent = option?.value // Show a price only after a genuine catalogue choice.
            ? `${option.dataset.serviceName} · ${option.dataset.servicePrice}. Pay at the shop after your appointment.`
            : 'Choose a service above or from the service menus. Pay at the shop after your appointment.';
    }; // Finish the selection-summary update.
    document.querySelectorAll('[data-service-category]').forEach(button => { // Connect each of the three category cards to its own dialog.
        button.addEventListener('click', () => { // Open the menu only after a customer activates its card.
            const menu = document.getElementById(button.getAttribute('aria-controls')); // Use the accessible control relationship.
            if (!menu) return; // Ignore an unavailable dialog safely.
            opener = button; // Save the element to restore focus when the menu closes.
            menu.showModal(); // Use the browser's modal focus handling and Escape behaviour.
        }); // Finish opening this category.
    }); // Finish connecting the cards.
    menus.forEach(menu => { // Register close and selection behaviour for each dialog.
        menu.querySelector('[data-close-service-menu]').addEventListener('click', () => menu.close()); // Let the explicit close button dismiss the popup.
        menu.addEventListener('click', event => { // Allow a click on the backdrop to dismiss the menu.
            if (event.target !== menu) return; // Keep clicks inside the service content active.
            const bounds = menu.getBoundingClientRect(); // Locate the visible dialog rather than treating its padding as the backdrop.
            if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) menu.close(); // Close only a backdrop click.
        }); // Finish backdrop handling.
        menu.addEventListener('close', () => { // Restore keyboard focus after ordinary dismissal.
            if (!menu.dataset.serviceSelected && opener) opener.focus(); // A service choice instead moves focus to the booking form.
            delete menu.dataset.serviceSelected; // Reset the flag before this dialog opens again.
        }); // Finish focus restoration.
        menu.querySelectorAll('[data-select-service]').forEach(button => { // Connect each numbered option to the shared booking field.
            button.addEventListener('click', () => { // A selected service is confirmed again in the booking form.
                picker.value = button.dataset.selectService; // Select the matching trusted option already rendered by PHP.
                picker.dispatchEvent(new Event('change', {bubbles: true})); // Update the visible service and price summary.
                menu.dataset.serviceSelected = 'true'; // Preserve focus on the form when the close event fires.
                menu.close(); // Close the popup before moving to the appointment form.
                document.getElementById('booking')?.scrollIntoView({behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'}); // Respect motion preferences when scrolling.
                picker.focus({preventScroll: true}); // Put keyboard users at the newly selected service.
            }); // Finish this option's click handler.
        }); // Finish all options in this popup.
    }); // Finish the popup handlers.
    picker.addEventListener('change', updateSummary); // Keep the direct dropdown route available without opening a popup.
    updateSummary(); // Initialise the summary for the current form choice.
})(); // Finish the isolated service-menu controller.
