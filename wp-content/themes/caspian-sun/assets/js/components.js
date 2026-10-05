document.addEventListener("DOMContentLoaded", function () {
  initHeaderScroll();
  initHamburger();
  initFooterAccordion();
  initReveal();
});

function initHeaderScroll() {
  var header = document.querySelector('header');
  if(!header) return;

  // Transparent at the very top (over hero/banner); glass + sticky behaviour
  // starts as soon as the user scrolls, not only after the hero ends.
  var lastY = window.scrollY;
  function onScroll() {
    var y = window.scrollY;
    if (y > 10) {
      header.classList.add('scrolled');
      if (y > lastY + 4 && y > 100) { header.classList.add('hide'); }
      else if (y < lastY - 4) { header.classList.remove('hide'); }
    } else {
      header.classList.remove('scrolled');
      header.classList.remove('hide');
    }
    lastY = y;
  }
  window.addEventListener('scroll', onScroll, {passive:true});
  onScroll();
}

function initHamburger() {
  var btn = document.getElementById('hamburgerBtn');
  var drawer = document.getElementById('mobileDrawer');
  var backdrop = document.getElementById('drawerBackdrop');
  var closeBtn = document.getElementById('drawerClose');
  if(!btn || !drawer || !backdrop) return;
  
  var links = drawer.querySelectorAll('a');
  function open() {
    drawer.classList.add('open');
    backdrop.classList.add('open');
    document.body.classList.add('drawer-open');
    btn.setAttribute('aria-expanded', 'true');
  }
  function close() {
    drawer.classList.remove('open');
    backdrop.classList.remove('open');
    document.body.classList.remove('drawer-open');
    btn.setAttribute('aria-expanded', 'false');
  }
  btn.addEventListener('click', open);
  closeBtn.addEventListener('click', close);
  backdrop.addEventListener('click', close);
  links.forEach(function (a) { a.addEventListener('click', close); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
}

function initFooterAccordion() {
  document.querySelectorAll('.footer-accordion h3').forEach(function (h3) {
    h3.addEventListener('click', function () {
      h3.closest('.footer-accordion').classList.toggle('open');
    });
  });
}

function initReveal() {
  // Pages without hand-placed .reveal markup: tag their main blocks automatically (staggered).
  ['.form-section', '.info-box', '.map-section', 'footer .footer-section'].forEach(function (sel) {
    document.querySelectorAll(sel).forEach(function (el, i) {
      if (el.classList.contains('reveal')) return;
      el.classList.add('reveal');
      if (i) el.style.transitionDelay = Math.min(i, 6) * 80 + 'ms';
    });
  });
  const reveals = document.querySelectorAll('.reveal');
  if (reveals.length === 0) return;
  if (!('IntersectionObserver' in window)) { reveals.forEach(r => r.classList.add('in-view')); return; }
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in-view');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
  reveals.forEach(r => observer.observe(r));
}

// PRELOADER: logo expands and reveals the page once everything has loaded
(function(){
  var pl=document.getElementById('preloader');
  if(!pl)return;
  var root=document.documentElement,start=Date.now(),MIN=0,done=false;
  root.classList.add('pl-lock');
  function finish(){
    if(done)return;done=true;
    var wait=Math.max(0,MIN-(Date.now()-start));
    setTimeout(function(){
      pl.classList.add('pl-done');try{sessionStorage.setItem('plSeen','1');}catch(e){}
      root.classList.remove('pl-lock');
      setTimeout(function(){if(pl.parentNode)pl.parentNode.removeChild(pl);},1400);
    },wait);
  }
  // Reveal as soon as the page is interactive and the hero image (if any) is ready,
  // instead of waiting for every image on the page to finish loading.
  function ready(){
    var hp=document.querySelector('.hero-poster');
    if(hp&&!hp.complete){hp.addEventListener('load',finish);hp.addEventListener('error',finish);}
    else finish();
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();
  setTimeout(finish,7000);
})();

// Hero video: load after the page is ready, pick a size for the screen
(function(){
  var v=document.querySelector('video.hero-video[data-src]');
  if(!v)return;
  function go(){
    if(window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches)return;
    var small=window.innerWidth<=768;
    v.src=small?v.dataset.srcSm:v.dataset.src;
    v.addEventListener('playing',function(){v.classList.add('is-playing');},{once:true});
    v.load();
    var p=v.play();if(p&&p.catch)p.catch(function(){});
  }
  // Start on first interaction (keeps the poster as the fast LCP), or after 10s
  var started=false;
  function once(){if(started)return;started=true;['pointermove','pointerdown','scroll','keydown','touchstart'].forEach(function(e){window.removeEventListener(e,once);});go();}
  ['pointermove','pointerdown','scroll','keydown','touchstart'].forEach(function(e){window.addEventListener(e,once,{passive:true});});
  setTimeout(once,10000);
})();

// Lazy CSS background images for below-the-fold sections
(function(){
  var els=document.querySelectorAll('.cats,.cta-bg');
  if(!els.length)return;
  if(!('IntersectionObserver' in window)){els.forEach(function(e){e.classList.add('bg-on');});return;}
  var io=new IntersectionObserver(function(en){en.forEach(function(x){if(x.isIntersecting){x.target.classList.add('bg-on');io.unobserve(x.target);}});},{rootMargin:'600px 0px'});
  els.forEach(function(e){io.observe(e);});
})();

// Warm lazy images: once the page has loaded, fetch the remaining lazy images in the background
// so they are ready before the visitor scrolls to them (does not compete with the hero/LCP).
(function(){
  function warm(){
    var run=function(){document.querySelectorAll('img[loading="lazy"]').forEach(function(i){i.loading='eager';});};
    if('requestIdleCallback' in window)requestIdleCallback(run,{timeout:2500});else setTimeout(run,800);
  }
  if(document.readyState==='complete')setTimeout(warm,300);else window.addEventListener('load',function(){setTimeout(warm,300);});
})();

// Product cards: on touch devices the hover overlay is skipped and a tap anywhere on the card opens its detail page.
(function(){
  var cards=document.querySelectorAll('.pcard, .product-card');
  if(!cards.length)return;
  cards.forEach(function(card){
    card.addEventListener('click',function(e){
      if(e.target.closest('a'))return;
      var link=card.querySelector('a[href]');
      if(link)window.location.href=link.getAttribute('href');
    });
  });
})();
