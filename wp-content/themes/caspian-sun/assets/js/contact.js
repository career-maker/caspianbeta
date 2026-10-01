/**
 * Contact form: client-side validation + AJAX submit (falls back to a normal
 * POST to admin-post.php when JavaScript is unavailable).
 */
(function () {
  var form = document.getElementById('cspContactForm');
  if (!form || !window.CSP_FORM) return;

  var status = form.querySelector('.form-status');
  var btn = form.querySelector('.form-submit');
  var btnLabel = btn ? btn.textContent : '';
  var siteKey = window.CSP_FORM.recaptcha || '';

  function field(name) { return form.elements[name]; }
  function err(name, msg) {
    var el = field(name);
    if (!el) return;
    var out = document.getElementById(el.id + '-err');
    if (out) out.textContent = msg || '';
    el.setAttribute('aria-invalid', msg ? 'true' : 'false');
  }
  function clearErrors() {
    ['name', 'phone', 'email', 'message'].forEach(function (n) { err(n, ''); });
    if (status) { status.textContent = ''; status.className = 'form-status'; }
  }

  function validate() {
    var ok = true, first = null;
    function bad(n, m) { err(n, m); ok = false; if (!first) first = field(n); }
    var name = (field('name').value || '').trim();
    var email = (field('email').value || '').trim();
    var msg = (field('message').value || '').trim();
    var phoneEl = field('phone');
    var phone = phoneEl ? (phoneEl.value || '').trim() : '';

    if (name.length < 2) bad('name', 'Please enter your name (at least 2 characters).');
    if (phone && (!/^[0-9+\-\s().]{6,25}$/.test(phone) || phone.replace(/\D/g, '').length < 6)) bad('phone', 'Please enter a valid phone number.');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) bad('email', 'Please enter a valid email address.');
    if (msg.length < 10) bad('message', 'Please write a message (at least 10 characters).');
    if (first) first.focus();
    return ok;
  }

  function setStatus(text, cls) {
    if (!status) return;
    status.textContent = text;
    status.className = 'form-status ' + cls;
  }

  function send(extra) {
    var data = new FormData(form);
    data.set('action', 'csp_contact');
    if (extra) data.set('g-recaptcha-response', extra);
    return fetch(window.CSP_FORM.ajax, { method: 'POST', body: data, credentials: 'same-origin' })
      .then(function (r) { return r.json(); });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearErrors();
    if (!validate()) return;
    if (btn) { btn.disabled = true; btn.textContent = form.dataset.sending || btnLabel; }

    var run = siteKey && window.grecaptcha
      ? new Promise(function (res) { grecaptcha.ready(function () { grecaptcha.execute(siteKey, { action: 'contact' }).then(res); }); })
      : Promise.resolve('');

    run.then(send).then(function (json) {
      if (json && json.success) {
        form.reset();
        setStatus(form.dataset.success || (json.data && json.data.message) || '', 'is-success');
      } else {
        var d = (json && json.data) || {};
        var errs = d.errors || {};
        Object.keys(errs).forEach(function (k) { if (k === '_form') return; err(k, errs[k]); });
        setStatus(errs._form || form.dataset.error || 'Something went wrong.', 'is-error');
      }
    }).catch(function () {
      setStatus(form.dataset.error || 'Something went wrong.', 'is-error');
    }).then(function () {
      if (btn) { btn.disabled = false; btn.textContent = btnLabel; }
    });
  });

  if (siteKey) {
    var s = document.createElement('script');
    s.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(siteKey);
    s.async = true;
    document.head.appendChild(s);
  }
})();
