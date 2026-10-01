/**
 * Contact form: strict live validation (mirrors inc/validation.php) + AJAX submit
 * (falls back to a normal POST to admin-post.php when JavaScript is unavailable).
 *
 * Behaviour:
 *  - while typing, an invalid value shows its error immediately;
 *  - the error disappears as soon as the value becomes valid or the field is emptied;
 *  - on blur the value is trimmed and an empty required field is reported;
 *  - the packing list follows the selected product.
 */
(function () {
  var form = document.getElementById('cspContactForm');
  if (!form || !window.CSP_FORM) return;

  var status = form.querySelector('.form-status');
  var btn = form.querySelector('.form-submit');
  var btnLabel = btn ? btn.textContent : '';
  var siteKey = window.CSP_FORM.recaptcha || '';
  var catalog = {};
  try { catalog = JSON.parse(form.getAttribute('data-products') || '{}') || {}; } catch (e) { catalog = {}; }

  /* ------------------------------------------------------------ validators */

  function trim(s) { return String(s).replace(/^[\s\u00A0\u200B]+|[\s\u00A0\u200B]+$/g, ''); }
  function oneLine(s) { return trim(s).replace(/\s+/g, ' '); }
  function count(re, s) { var m = s.match(re); return m ? m.length : 0; }

  var INJECTION = new RegExp(
    '<\\s*\\/?\\s*[a-z!?]' +
    '|javascript\\s*:|vbscript\\s*:|data\\s*:\\s*text\\/html' +
    '|\\bon(?:error|load|click|mouse\\w*|focus|blur|key\\w*|change|submit|input|abort|toggle|animation\\w*|pointer\\w*)\\s*=' +
    '|\\{\\{|\\}\\}|\\{%|%\\}|\\$\\{' +
    '|\\bunion\\s+(?:all\\s+)?select\\b|\\b(?:drop|truncate)\\s+(?:table|database)\\b|\\bdelete\\s+from\\b|\\binsert\\s+into\\b' +
    '|;\\s*--|--\\s*$|\\/\\*|\\*\\/' +
    '|[\'"]\\s*or\\s+[\'"]?\\w+[\'"]?\\s*=\\s*[\'"]?\\w+', 'i');
  var EMAIL = /^[A-Za-z0-9](?:[A-Za-z0-9._%+\-]{0,62}[A-Za-z0-9])?@(?:[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])?\.)+[A-Za-z]{2,24}$/;
  var NAME = /^[\p{L}\p{M}][\p{L}\p{M} '’.\-]*$/u;

  function text(label, min, max, multiline) {
    return function (raw) {
      var v = trim(String(raw).replace(/\r\n?/g, '\n'));
      if (v === '') return 'Please enter your ' + label + '.';
      if (!multiline) v = v.replace(/\s+/g, ' ');
      if (/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/.test(v)) return 'Your ' + label + ' contains invalid characters.';
      if (v.length < min) return 'Your ' + label + ' must be at least ' + min + ' characters.';
      if (v.length > max) return 'Your ' + label + ' is too long (' + max + ' characters maximum).';
      if (INJECTION.test(v)) return 'Your ' + label + ' contains code or characters that are not allowed.';
      if (count(/[\p{L}\p{N}]/gu, v) < 2) return 'Please enter a meaningful ' + label + ' (letters or numbers, not only symbols).';
      return '';
    };
  }

  var rules = {
    name: function (raw) {
      var v = oneLine(raw);
      if (v === '') return 'Please enter your name.';
      if (v.length < 2) return 'Name must be at least 2 characters.';
      if (v.length > 100) return 'Name is too long (100 characters maximum).';
      if (!NAME.test(v) || count(/\p{L}/gu, v) < 2) return 'Name can only contain letters, spaces, apostrophes, hyphens and full stops.';
      return '';
    },
    phone: function (raw) {
      var v = oneLine(raw);
      if (v === '') return 'Please enter your phone number.';
      if (!/^\+?[0-9() .\-]+$/.test(v)) return 'Phone number can only contain digits, spaces, ( ) - and a single leading +.';
      var d = v.replace(/\D/g, '');
      if (d.length < 7 || d.length > 15) return 'Phone number must have between 7 and 15 digits.';
      if (/^(\d)\1*$/.test(d)) return 'Please enter a valid phone number.';
      return '';
    },
    email: function (raw) {
      var v = trim(raw);
      if (v === '') return 'Please enter your email address.';
      if (v.length > 254) return 'Email address is too long.';
      if (!EMAIL.test(v) || v.indexOf('..') > -1) return 'Please enter a valid email address (e.g. name@example.com).';
      return '';
    },
    subject: text('subject', 2, 150, false),
    message: text('message', 2, 3000, true),
    product: function (raw) {
      return raw === '' || Object.prototype.hasOwnProperty.call(catalog, raw) ? '' : 'Please choose a product from the list.';
    },
    size: function (raw) {
      if (raw === '') return '';
      var p = form.elements.product.value;
      return p && catalog[p] && catalog[p].indexOf(raw) > -1 ? '' : 'Please choose a packing option from the list.';
    }
  };

  /** Cleaned value written back into the field (trim, collapse spaces). */
  var cleaners = {
    name: oneLine, phone: oneLine, email: trim, subject: oneLine,
    message: function (v) { return trim(String(v).replace(/\r\n?/g, '\n')); }
  };

  /* --------------------------------------------------------------- helpers */

  function field(name) { return form.elements[name]; }

  function showError(name, msg) {
    var el = field(name);
    if (!el) return;
    var out = document.getElementById(el.id + '-err');
    if (out && out.textContent !== (msg || '')) out.textContent = msg || '';
    if (msg) el.setAttribute('aria-invalid', 'true'); else el.setAttribute('aria-invalid', 'false');
  }
  function clearState(name) {
    var el = field(name);
    if (!el) return;
    var out = document.getElementById(el.id + '-err');
    if (out) out.textContent = '';
    el.removeAttribute('aria-invalid');
  }
  function setStatus(text, cls) {
    if (!status) return;
    status.textContent = text;
    status.className = 'form-status' + (cls ? ' ' + cls : '');
  }

  /** Validate one field. final=true also reports empty required fields. Returns true when valid. */
  function check(name, final) {
    var el = field(name);
    if (!el || !rules[name]) return true;
    var raw = el.value;
    if (raw === '' && !final) { clearState(name); return false; }
    var optional = name === 'product' || name === 'size';
    if (optional && raw === '') { clearState(name); return true; }
    var msg = rules[name](raw);
    showError(name, msg);
    return !msg;
  }

  function normalise(name) {
    var el = field(name);
    if (el && cleaners[name]) {
      var c = cleaners[name](el.value);
      if (c !== el.value) el.value = c;
    }
  }

  function fillSizes(selected) {
    var sel = field('size');
    var p = field('product').value;
    var list = (p && catalog[p]) ? catalog[p] : [];
    sel.innerHTML = '';
    var first = document.createElement('option');
    first.value = ''; first.textContent = 'Select packing';
    sel.appendChild(first);
    list.forEach(function (s) {
      var o = document.createElement('option');
      o.value = s; o.textContent = s;
      if (s === selected) o.selected = true;
      sel.appendChild(o);
    });
    sel.disabled = list.length === 0;
    clearState('size');
  }

  /* ------------------------------------------------------- live validation */

  ['name', 'phone', 'email', 'subject', 'message'].forEach(function (n) {
    var el = field(n);
    if (!el) return;
    el.addEventListener('input', function () {
      if (status && status.textContent) setStatus('', '');
      check(n, false);
    });
    el.addEventListener('blur', function () {
      normalise(n);
      check(n, true);
    });
  });

  field('product').addEventListener('change', function () {
    fillSizes('');
    check('product', true);
    if (status && status.textContent) setStatus('', '');
  });
  field('size').addEventListener('change', function () { check('size', true); });

  /* ---------------------------------------------------------------- submit */

  function validateAll() {
    var first = null, ok = true;
    ['name', 'phone', 'email', 'subject', 'message', 'product', 'size'].forEach(function (n) {
      normalise(n);
      if (!check(n, true)) { ok = false; if (!first) first = field(n); }
    });
    if (first) first.focus();
    return ok;
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
    setStatus('', '');
    if (!validateAll()) return;
    if (btn) { btn.disabled = true; btn.textContent = form.dataset.sending || btnLabel; }

    var run = siteKey && window.grecaptcha
      ? new Promise(function (res) { grecaptcha.ready(function () { grecaptcha.execute(siteKey, { action: 'contact' }).then(res); }); })
      : Promise.resolve('');

    run.then(send).then(function (json) {
      if (json && json.success) {
        form.reset();
        field('product').value = '';
        fillSizes('');
        ['name', 'phone', 'email', 'subject', 'message', 'product', 'size'].forEach(clearState);
        setStatus(form.dataset.success || (json.data && json.data.message) || '', 'is-success');
      } else {
        var d = (json && json.data) || {};
        var errs = d.errors || {};
        var first = null;
        Object.keys(errs).forEach(function (k) {
          if (k === '_form') return;
          showError(k, errs[k]);
          if (!first && field(k)) first = field(k);
        });
        if (first) first.focus();
        setStatus(errs._form || (first ? '' : (form.dataset.error || 'Something went wrong.')), errs._form || !first ? 'is-error' : '');
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
