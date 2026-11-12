(() => { /* Continue the existing browser expression at this point. */
    'use strict'; /* Use the existing strict language rules for this file. */
    const box = document.getElementById('schedule-counter'); /* Locate the existing page element used by this operation. */
    if (!box) return; /* Check this condition before running the associated branch. */
    const form = document.querySelector('form[action="book.php"]'); /* Locate the existing page element used by this operation. */
    const field = form?.querySelector('[name="booking_date"]'); /* Locate the existing page element used by this operation. */
    const submit = form?.querySelector('button[type="submit"]'); /* Locate the existing page element used by this operation. */
    const choices = document.getElementById('slot-choice'); /* Locate the existing page element used by this operation. */
    let controller; /* Continue the existing browser expression at this point. */
    let submitting = false; /* Set submitting for the next step. */
    const submitLabel = submit?.textContent; /* Read or update plain text without interpreting it as HTML. */
    async function refresh() { /* Define the refresh operation. */
        if (submitting) return; /* Check this condition before running the associated branch. */
        controller?.abort(); /* Continue the existing browser expression at this point. */
        controller = null; /* Continue the existing browser expression at this point. */
        const date = field ? field.value : box.dataset.date; /* Set date for the next step. */
        if (!date) { /* Check this condition before running the associated branch. */
            if (choices) choices.replaceChildren(new Option('Automatically choose the earliest appointment', '')); /* Check this condition before running the associated branch. */
            box.textContent = 'Choose a date to see available appointments.'; /* Read or update plain text without interpreting it as HTML. */
            box.removeAttribute('aria-busy'); /* Update the existing element attribute or accessibility state. */
            if (submit) submit.disabled = false; /* Check this condition before running the associated branch. */
            return; /* Continue the existing browser expression at this point. */
        } /* Finish the current block and return to the surrounding flow. */
        const requestController = new AbortController(); controller = requestController; /* Set request controller for the next step. */
        const timeout = setTimeout(() => requestController.abort(), 10000); /* Schedule the existing timeout for this operation. */
        if (submit) submit.disabled = true; /* Check this condition before running the associated branch. */
        box.setAttribute('aria-busy', 'true'); /* Update the existing element attribute or accessibility state. */
        try { /* Begin an operation whose failure is handled below. */
            const response = await fetch(`availability.php?date=${encodeURIComponent(date)}`, {cache: 'no-store', signal: requestController.signal}); /* Request the existing endpoint asynchronously without reloading the page. */
            const data = await response.json(); /* Set data for the next step. */
            if (!response.ok) throw new Error(data.error || 'Could not check availability.'); /* Check this condition before running the associated branch. */
            if (controller !== requestController) return; /* Check this condition before running the associated branch. */
            if (choices) { /* Check this condition before running the associated branch. */
                const previous = choices.value; /* Set previous for the next step. */
                choices.replaceChildren(new Option('Automatically choose the earliest appointment', '')); /* Continue the existing browser expression at this point. */
                for (const slot of data.slots) choices.add(new Option(`${slot.time.slice(0, 5)} · ${slot.barber_name}`, `${slot.barber_id}|${slot.time}`)); /* Repeat the following work for the specified records or iterations. */
                if ([...choices.options].some(option => option.value === previous)) choices.value = previous; /* Check this condition before running the associated branch. */
            } /* Finish the current block and return to the surrounding flow. */
            const headline = document.createElement('strong'); /* Create the existing interface element before attaching it to the page. */
            const displayDate = new Intl.DateTimeFormat('en-GB', { /* Set display date for the next step. */
                day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Europe/London' /* Continue the existing browser expression at this point. */
            }).format(new Date(`${data.date}T12:00:00Z`)); /* Continue the existing browser expression at this point. */
            headline.textContent = data.remaining ? `Appointments available for ${displayDate}` : `No appointments available for ${displayDate}`; // Show availability without publishing capacity.
            const detail = document.createElement('div'); detail.className = 'small mt-1'; /* Create the existing interface element before attaching it to the page. */
            detail.textContent = data.remaining ? 'Choose an available time and barber below.' : 'Please choose another date.'; // Keep aggregate staffing and capacity out of the customer display.
            const note = document.createElement('div'); note.className = 'small text-muted mt-1'; /* Create the existing interface element before attaching it to the page. */
            note.textContent = 'Choose a time and barber, or let the assistant select the earliest appointment. Availability is rechecked before saving.'; /* Read or update plain text without interpreting it as HTML. */
            box.replaceChildren(headline, detail, note); /* Continue the existing browser expression at this point. */
            if (submit) submit.disabled = data.remaining === 0; /* Check this condition before running the associated branch. */
        } catch (error) { /* Handle a failure from the preceding protected operation. */
            if (controller !== requestController) return; /* Check this condition before running the associated branch. */
            box.textContent = error.name === 'AbortError' ? 'Availability check timed out. Please select the date again.' : error.message; /* Read or update plain text without interpreting it as HTML. */
            // Server-side scheduling still validates bookings if this optional preview fails.
            if (submit) submit.disabled = false; /* Check this condition before running the associated branch. */
        } finally { /* Run cleanup whether the protected operation succeeds or fails. */
            clearTimeout(timeout); /* Cancel the timeout after this operation finishes. */
            if (controller === requestController) box.removeAttribute('aria-busy'); /* Check this condition before running the associated branch. */
        } /* Finish the current block and return to the surrounding flow. */
    } /* Finish the current block and return to the surrounding flow. */
    field?.addEventListener('change', () => { if (choices) choices.value = ''; refresh(); }); /* Register the existing change event handler. */
    form?.addEventListener('submit', event => { /* Register the existing submit event handler. */
        if (submitting) { event.preventDefault(); return; } /* Check this condition before running the associated branch. */
        submitting = true; /* Continue the existing browser expression at this point. */
        controller?.abort(); controller = null; /* Continue the existing browser expression at this point. */
        if (submit) { submit.disabled = true; submit.textContent = 'AI is selecting your appointment…'; } /* Check this condition before running the associated branch. */
    }); /* Finish the current block and return to the surrounding flow. */
    window.addEventListener('pageshow', () => { /* Register the existing pageshow event handler. */
        submitting = false; /* Continue the existing browser expression at this point. */
        if (submit) submit.textContent = submitLabel; /* Check this condition before running the associated branch. */
        refresh(); /* Continue the existing browser expression at this point. */
    }); /* Finish the current block and return to the surrounding flow. */
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); }); /* Register the existing visibilitychange event handler. */
    setInterval(() => { if (!document.hidden) refresh(); }, 30000); /* Repeat the existing background refresh at its configured interval. */
    refresh(); /* Continue the existing browser expression at this point. */
})(); /* Continue the existing browser expression at this point. */
