/**
 * Product page: thumbnail gallery, hover zoom origin, packing selection that
 * is passed on to the enquiry form.
 */
(function () {
  var main = document.getElementById('mainImg');
  var thumbs = document.querySelectorAll('.thumb');
  if (main && thumbs.length) {
    thumbs.forEach(function (t) {
      t.addEventListener('click', function () {
        if (t.classList.contains('active')) return;
        thumbs.forEach(function (x) { x.classList.remove('active'); x.setAttribute('aria-selected', 'false'); });
        t.classList.add('active');
        t.setAttribute('aria-selected', 'true');
        main.classList.add('swap');
        setTimeout(function () {
          main.removeAttribute('srcset');
          main.removeAttribute('sizes');
          main.src = t.getAttribute('data-src');
          var ss = t.getAttribute('data-srcset');
          if (ss) { main.srcset = ss; main.sizes = '(max-width: 900px) 100vw, 50vw'; }
          main.classList.remove('swap');
        }, 220);
      });
    });
    var box = main.parentNode;
    box.addEventListener('mousemove', function (ev) {
      var r = box.getBoundingClientRect();
      main.style.transformOrigin = ((ev.clientX - r.left) / r.width * 100) + '% ' + ((ev.clientY - r.top) / r.height * 100) + '%';
    });
    box.addEventListener('mouseleave', function () { main.style.transformOrigin = '50% 50%'; });
  }

  var sizes = document.querySelectorAll('.size');
  var hint = document.getElementById('sizesHint');
  var enquire = document.getElementById('enquireBtn');
  var bar = document.getElementById('enqBar'), barBtn = document.getElementById('enqBarBtn');

  /* mobile sticky bar: visible while the main "Contact us" button is not on screen */
  if (bar) {
    var show = function (on) { bar.classList.toggle('is-on', on); };
    if (enquire && 'IntersectionObserver' in window) {
      new IntersectionObserver(function (en) { show(!en[0].isIntersecting); }, { threshold: 0.2 }).observe(enquire);
    } else { show(true); }
  }
  function setLink(h) {
    if (enquire) enquire.setAttribute('href', h);
    if (barBtn) barBtn.setAttribute('href', h);
  }

  if (!sizes.length || !enquire) return;
  var base = enquire.getAttribute('href');
  var idle = hint ? hint.getAttribute('data-idle') : '';
  var selectedLabel = hint ? hint.getAttribute('data-selected') : '';
  function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
  sizes.forEach(function (s) {
    s.addEventListener('click', function () {
      var on = s.classList.contains('active');
      sizes.forEach(function (x) { x.classList.remove('active'); x.setAttribute('aria-checked', 'false'); });
      if (on) {
        if (hint) hint.textContent = idle;
        setLink(base);
        return;
      }
      s.classList.add('active');
      s.setAttribute('aria-checked', 'true');
      var v = s.getAttribute('data-size');
      if (hint) hint.innerHTML = esc(selectedLabel) + ' <strong>' + esc(v) + '</strong>';
      setLink(base + (base.indexOf('?') > -1 ? '&' : '?') + 'enquiry_size=' + encodeURIComponent(v));
    });
  });
})();
