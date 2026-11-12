(() => { /* Continue the existing browser expression at this point. */
    'use strict'; /* Use the existing strict language rules for this file. */
    const root = document.querySelector('.ai-demo'); /* Locate the existing page element used by this operation. */
    if (!root) return; /* Check this condition before running the associated branch. */
    const $ = id => document.getElementById(id); /* Locate the existing page element used by this operation. */
    const launch = root.querySelector('.ai-launch'); /* Locate the existing page element used by this operation. */
    const panel = $('ai-panel'); /* Set panel for the next step. */
    function close() { if (!launch) return; panel.hidden = true; launch.setAttribute('aria-expanded', 'false'); launch.focus(); } /* Define the close operation. */
    if (launch) { /* Check this condition before running the associated branch. */
        launch.addEventListener('click', () => { panel.hidden = !panel.hidden; launch.setAttribute('aria-expanded', String(!panel.hidden)); if (!panel.hidden) $('ai-message').focus(); }); /* Register the existing click event handler. */
        root.querySelector('.ai-close').addEventListener('click', close); /* Register the existing click event handler. */
        root.addEventListener('keydown', event => { if (event.key === 'Escape' && !panel.hidden) close(); }); /* Register the existing keydown event handler. */
    } /* Finish the current block and return to the surrounding flow. */
    async function request(data, button) { /* Define the request operation. */
        $('ai-error').textContent = ''; /* Read or update plain text without interpreting it as HTML. */
        if (button) button.disabled = true; /* Check this condition before running the associated branch. */
        const controller = new AbortController(); /* Set controller for the next step. */
        const timeout = setTimeout(() => controller.abort(), 60000); /* Schedule the existing timeout for this operation. */
        try { /* Begin an operation whose failure is handled below. */
            const response = await fetch('ai_demo_api.php', {method: 'POST', credentials: 'same-origin', signal: controller.signal, /* Request the existing endpoint asynchronously without reloading the page. */
                headers: {'Content-Type': 'application/json', 'X-Demo-Token': root.dataset.token}, body: JSON.stringify(data)}); /* Encode the request data as JSON for the existing endpoint. */
            const result = await response.json(); /* Set result for the next step. */
            if (!response.ok) throw new Error(result.error || 'The request could not be completed.'); /* Check this condition before running the associated branch. */
            return result; /* Return the computed result to the caller without further work here. */
        } catch (error) { /* Handle a failure from the preceding protected operation. */
            $('ai-error').textContent = error.name === 'AbortError' ? 'The request took too long. Please try again.' /* Read or update plain text without interpreting it as HTML. */
                : error instanceof SyntaxError || error instanceof TypeError ? 'Could not reach the assistant. Check that Apache and MySQL are running.' : error.message; /* Continue the existing browser expression at this point. */
            return null; /* Return the computed result to the caller without further work here. */
        } finally { clearTimeout(timeout); if (button) button.disabled = false; } /* Run cleanup whether the protected operation succeeds or fails. */
    } /* Finish the current block and return to the surrounding flow. */
    function addMessage(text, kind) { /* Define the add message operation. */
        const log = $('ai-conversation'); const item = document.createElement('p'); /* Create the existing interface element before attaching it to the page. */
        item.className = kind; item.textContent = text; log.append(item); /* Read or update plain text without interpreting it as HTML. */
        while (log.children.length > 20) log.firstElementChild.remove(); /* Repeat the following work for the specified records or iterations. */
        log.scrollTop = log.scrollHeight; /* Continue the existing browser expression at this point. */
    } /* Finish the current block and return to the surrounding flow. */
    if (root.dataset.staff !== '1') { /* Check this condition before running the associated branch. */
        let suggestedDate = ''; /* Set suggested date for the next step. */
        let busy = false; /* Set busy for the next step. */
        root.querySelectorAll('[data-question]').forEach(button => button.addEventListener('click', () => { /* Register the existing click event handler. */
            if (busy) return; /* Check this condition before running the associated branch. */
            $('ai-message').value = button.dataset.question; /* Continue the existing browser expression at this point. */
            $('ai-chat-form').requestSubmit(); /* Continue the existing browser expression at this point. */
        })); /* Finish the current block and return to the surrounding flow. */
        $('ai-chat-form').addEventListener('submit', async event => { /* Register the existing submit event handler. */
            event.preventDefault(); const message = $('ai-message').value.trim(); if (!message || busy) return; /* Prevent the browser default action for this already-handled event. */
            busy = true; /* Continue the existing browser expression at this point. */
            const controls = [...$('ai-chat-form').querySelectorAll('input, button'), ...root.querySelectorAll('[data-question]')]; /* Locate the existing page element used by this operation. */
            const date = $('ai-date').value; /* Set date for the next step. */
            controls.forEach(control => control.disabled = true); /* Continue the existing browser expression at this point. */
            $('ai-chat-status').textContent = 'Checking shop information…'; /* Read or update plain text without interpreting it as HTML. */
            $('ai-conversation').setAttribute('aria-busy', 'true'); /* Update the existing element attribute or accessibility state. */
            suggestedDate = ''; $('ai-use-date').hidden = true; /* Continue the existing browser expression at this point. */
            addMessage(message, 'ai-question'); $('ai-message').value = ''; /* Continue the existing browser expression at this point. */
            const result = await request({action: 'chat', message, date}); /* Set result for the next step. */
            busy = false; /* Continue the existing browser expression at this point. */
            controls.forEach(control => control.disabled = false); /* Continue the existing browser expression at this point. */
            $('ai-chat-status').textContent = ''; /* Read or update plain text without interpreting it as HTML. */
            $('ai-conversation').setAttribute('aria-busy', 'false'); /* Update the existing element attribute or accessibility state. */
            if (!panel.hidden) $('ai-message').focus(); /* Check this condition before running the associated branch. */
            if (!result) { $('ai-message').value = message; return; } /* Check this condition before running the associated branch. */
            addMessage(result.text, 'ai-reply'); /* Continue the existing browser expression at this point. */
            if (result.facts) addMessage('Verified shop information: ' + result.facts + ' No booking has been made by this chat.', 'ai-reply'); /* Check this condition before running the associated branch. */
            if (result.date) { suggestedDate = result.date; $('ai-date').value = result.date; $('ai-use-date').hidden = false; } /* Check this condition before running the associated branch. */
        }); /* Finish the current block and return to the surrounding flow. */
        $('ai-use-date').addEventListener('click', () => { /* Register the existing click event handler. */
            const field = document.querySelector('form[action="book.php"] input[name="booking_date"]'); /* Locate the existing page element used by this operation. */
            if (!field || !suggestedDate) return; /* Check this condition before running the associated branch. */
            field.value = suggestedDate; field.dispatchEvent(new Event('change', {bubbles: true})); /* Continue the existing browser expression at this point. */
            close(); field.scrollIntoView({behavior: 'smooth', block: 'center'}); field.focus({preventScroll: true}); /* Continue the existing browser expression at this point. */
        }); /* Finish the current block and return to the surrounding flow. */
        return; /* Continue the existing browser expression at this point. */
    } /* Finish the current block and return to the surrounding flow. */
    function clearDraft() { $('ai-draft').value = ''; $('ai-copy').disabled = true; $('ai-draft-status').textContent = ''; } /* Define the clear draft operation. */
    for (const id of ['ai-booking', 'ai-kind', 'ai-language']) $(id).addEventListener('change', clearDraft); /* Repeat the following work for the specified records or iterations. */
    $('ai-draft').addEventListener('input', () => { $('ai-copy').disabled = !$('ai-draft').value.trim(); }); /* Register the existing input event handler. */
    $('ai-refresh').addEventListener('click', async event => { /* Register the existing click event handler. */
        clearDraft(); $('ai-generate').disabled = true; $('ai-staff-status').textContent = 'Loading…'; /* Read or update plain text without interpreting it as HTML. */
        const result = await request({action: 'staff'}, event.currentTarget); /* Set result for the next step. */
        if (!result) { $('ai-staff-status').textContent = 'Could not load staff tools.'; return; } /* Check this condition before running the associated branch. */
        $('ai-booking').replaceChildren(new Option('Choose an appointment', '')); /* Continue the existing browser expression at this point. */
        for (const booking of result.bookings) $('ai-booking').add(new Option(`${booking.booking_date} ${booking.appointment_time.slice(0, 5)} · #${booking.booking_number} · ${booking.customer_name}`, String(booking.id))); /* Repeat the following work for the specified records or iterations. */
        $('ai-generate').disabled = result.bookings.length === 0; /* Continue the existing browser expression at this point. */
        $('ai-staff-status').textContent = result.bookings.length ? `${result.bookings.length} upcoming confirmed appointments loaded.` : 'No upcoming confirmed appointments. New bookings will appear here after confirmation.'; /* Read or update plain text without interpreting it as HTML. */
        const forecast = result.forecast; /* Set forecast for the next step. */
        $('ai-forecast-status').textContent = (forecast.enough ? `Based on ${forecast.bookings} bookings across ${forecast.history_days} days. ` /* Read or update plain text without interpreting it as HTML. */
            : `Not enough history for an estimate: ${forecast.bookings} bookings across ${forecast.history_days} days. Estimates require at least 20 bookings and 28 days of history. `) + forecast.method; /* Continue the existing browser expression at this point. */
        const table = document.createElement('table'); const caption = document.createElement('caption'); caption.textContent = 'Estimated appointments per day'; table.append(caption); /* Read or update plain text without interpreting it as HTML. */
        const head = document.createElement('thead'); const headings = document.createElement('tr'); /* Create the existing interface element before attaching it to the page. */
        for (const label of ['Date', 'Day', 'Estimate']) { const th = document.createElement('th'); th.scope = 'col'; th.textContent = label; headings.append(th); } /* Repeat the following work for the specified records or iterations. */
        head.append(headings); table.append(head); const body = document.createElement('tbody'); /* Create the existing interface element before attaching it to the page. */
        for (const day of forecast.days) { const row = document.createElement('tr'); for (const value of [day.date, day.day, day.estimate === null ? 'Insufficient data' : String(day.estimate)]) { const td = document.createElement('td'); td.textContent = value; row.append(td); } body.append(row); } /* Repeat the following work for the specified records or iterations. */
        table.append(body); $('ai-forecast').replaceChildren(table); /* Continue the existing browser expression at this point. */
    }); /* Finish the current block and return to the surrounding flow. */
    $('ai-draft-form').addEventListener('submit', async event => { /* Register the existing submit event handler. */
        event.preventDefault(); clearDraft(); /* Prevent the browser default action for this already-handled event. */
        const selectors = ['ai-booking', 'ai-kind', 'ai-language'].map($); /* Set selectors for the next step. */
        const data = {action: 'draft', booking_id: selectors[0].value, kind: selectors[1].value, language: selectors[2].value}; /* Set data for the next step. */
        selectors.forEach(select => select.disabled = true); /* Continue the existing browser expression at this point. */
        const result = await request(data, event.submitter); /* Set result for the next step. */
        selectors.forEach(select => select.disabled = false); /* Continue the existing browser expression at this point. */
        if (result) { $('ai-draft').value = result.text; $('ai-copy').disabled = false; $('ai-draft-status').textContent = 'Template draft ready. Review before using it. Nothing has been sent.'; } /* Check this condition before running the associated branch. */
    }); /* Finish the current block and return to the surrounding flow. */
    $('ai-copy').addEventListener('click', async () => { /* Register the existing click event handler. */
        try { await navigator.clipboard.writeText($('ai-draft').value); $('ai-draft-status').textContent = 'Copied. Nothing has been sent.'; } /* Begin an operation whose failure is handled below. */
        catch { $('ai-draft').focus(); $('ai-draft').select(); $('ai-draft-status').textContent = 'Select and copy the draft with Ctrl+C (or Command+C).'; } /* Handle a failure from the preceding protected operation. */
    }); /* Finish the current block and return to the surrounding flow. */
    $('ai-refresh').click(); /* Continue the existing browser expression at this point. */
})(); /* Continue the existing browser expression at this point. */
