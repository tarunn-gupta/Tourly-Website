/* Sends Tourly forms to PHP/MySQL. Runs in capture phase to replace the original demo-only submit handlers. */
(() => {
  const typeById = { bookingForm: location.pathname.includes('/booking/location.html') ? 'location_booking' : 'booking', contactForm: 'contact', inquiryForm: location.pathname.includes('/destination/') ? 'destination_inquiry' : 'inquiry' };
  document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !typeById[form.id]) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    const submit = form.querySelector('[type="submit"]');
    if (submit) submit.disabled = true;
    const details = Object.fromEntries(new FormData(form).entries());
    // Preserve multiple checkbox values such as optional services.
    for (const field of form.querySelectorAll('input[type="checkbox"][name]')) {
      if (field.checked) {
        if (!Array.isArray(details[field.name])) details[field.name] = details[field.name] ? [details[field.name]] : [];
        if (!details[field.name].includes(field.value)) details[field.name].push(field.value);
      }
    }
    // location.html receives traveller identity from booking page query parameters.
    if (form.id === 'bookingForm' && location.pathname.includes('/booking/location.html')) {
      const params = new URLSearchParams(location.search);
      details.name = details.name || params.get('name') || '';
      details.email = details.email || params.get('email') || '';
      details.travelDate = details.travelDate || params.get('date') || '';
    }
    try {
      // Under XAMPP, use the same-origin API. Under VS Code Live Server,
      // send the request to the PHP API hosted by XAMPP instead of the static server.
      const isLiveServer = /^(localhost|127\.0\.0\.1)$/.test(location.hostname) && /^55\d\d$/.test(location.port);
      const apiUrl = isLiveServer
        ? 'http://localhost/Mini_project_2Year/api/save.php'
        : new URL('../api/save.php', location.href).href;
      const response = await fetch(apiUrl, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({form_type:typeById[form.id], details}) });
      const responseText = await response.text();
      let result;
      try { result = JSON.parse(responseText); }
      catch (_) { throw new Error('The PHP server returned an empty or invalid response. Check that Apache is running and open the project through the correct server.'); }
      if (!response.ok || !result.success) throw new Error(result.message || 'Save failed');
      const success = document.getElementById('successMessage') || document.getElementById('successMsg') || document.getElementById('successModal');
      if (success) { success.style.display = success.id === 'successModal' ? 'flex' : 'block'; success.classList.add('show'); }
      else alert('Your details have been saved successfully.');
      if (form.id !== 'bookingForm' || !location.pathname.includes('/booking/booking.html')) form.reset();
    } catch (error) {
      console.error(error);
      alert('Unable to save your details. Make sure Apache and MySQL are running and database.sql has been imported. ' + error.message);
    } finally { if (submit) submit.disabled = false; }
  }, true);
})();
