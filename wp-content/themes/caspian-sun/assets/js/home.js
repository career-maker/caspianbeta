
(function(){
  /* counters */
  var fmt=function(n){return n.toLocaleString('en-US');};
  var counters=document.querySelectorAll('[data-count]');
  function run(el){
    var target=+el.dataset.count,suf=el.dataset.suffix||'',start=null,dur=1800;
    function step(t){
      if(!start)start=t;
      var p=Math.min((t-start)/dur,1),e=1-Math.pow(1-p,3);
      el.textContent=fmt(Math.round(target*e))+suf;
      if(p<1)requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  if('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion:reduce)').matches){
    var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){run(e.target);io.unobserve(e.target);}});},{threshold:.6});
    counters.forEach(function(c){c.textContent='0'+(c.dataset.suffix||'');io.observe(c);});
  }

  /* clients: highlight each logo in turn (1s apart); real hover takes over and pauses the cycle */
  (function(){
    var box=document.querySelector('.logos');if(!box)return;
    var cells=[].slice.call(box.querySelectorAll('.logo-cell')).filter(function(c){return c.children.length;});
    if(!cells.length||(window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches))return;
    var i=-1,timer=null,hovering=false,visible=false;
    function clear(){cells.forEach(function(c){c.classList.remove('is-active');});}
    function tick(){
      if(hovering||document.hidden)return;
      clear();i=(i+1)%cells.length;cells[i].classList.add('is-active');
    }
    function start(){if(!timer){tick();timer=setInterval(tick,1000);}}
    function stop(){clearInterval(timer);timer=null;clear();}
    box.addEventListener('mouseenter',function(){hovering=true;clear();});
    box.addEventListener('mouseleave',function(){hovering=false;});
    if('IntersectionObserver' in window){
      new IntersectionObserver(function(en){en.forEach(function(x){if(x.isIntersecting)start();else stop();});},{threshold:.3}).observe(box);
    }else start();
  })();

  /* blogs: in each side column hovering the text-only article shows its image and hides the other one; restores on leave */
  document.querySelectorAll('.acol').forEach(function(col){
    var arts=[].slice.call(col.querySelectorAll('.art'));
    var initial=arts.map(function(a){return a.classList.contains('show-img');});
    function restore(){arts.forEach(function(a,i){a.classList.toggle('show-img',initial[i]);});}
    arts.forEach(function(a){
      a.addEventListener('mouseenter',function(){arts.forEach(function(o){o.classList.toggle('show-img',o===a);});});
      a.addEventListener('focusin',function(){arts.forEach(function(o){o.classList.toggle('show-img',o===a);});});
    });
    col.addEventListener('mouseleave',restore);
    col.addEventListener('focusout',function(e){if(!col.contains(e.relatedTarget))restore();});
  });

  /* testimonial slider */
  var track=document.getElementById('track'),dots=[].slice.call(document.querySelectorAll('#dots i'));
  var cards=track.querySelectorAll('.tcard');
  function step(){var c=cards[0];return c.getBoundingClientRect().width+parseFloat(getComputedStyle(track).columnGap||16);}
  document.getElementById('next').addEventListener('click',function(){track.scrollBy({left:step(),behavior:'smooth'});});
  document.getElementById('prev').addEventListener('click',function(){track.scrollBy({left:-step(),behavior:'smooth'});});
  function sync(){
    var i=Math.round(track.scrollLeft/step());i=Math.max(0,Math.min(i,dots.length-1));
    dots.forEach(function(d,k){d.classList.toggle('on',k===i);});
  }
  track.addEventListener('scroll',function(){window.requestAnimationFrame(sync);},{passive:true});
  dots.forEach(function(d,k){d.addEventListener('click',function(){track.scrollTo({left:k*step(),behavior:'smooth'});});});
  /* drag-to-scroll with the mouse (touch already scrolls natively) */
  (function(){
    var down=false,moved=false,startX=0,startLeft=0;
    track.addEventListener('mousedown',function(e){
      if(e.button!==0)return;
      down=true;moved=false;startX=e.pageX;startLeft=track.scrollLeft;
      track.classList.add('dragging');
    });
    window.addEventListener('mousemove',function(e){
      if(!down)return;
      var dx=e.pageX-startX;
      if(Math.abs(dx)>5)moved=true;
      if(moved){e.preventDefault();track.scrollLeft=startLeft-dx;}
    });
    window.addEventListener('mouseup',function(){
      if(!down)return;
      down=false;track.classList.remove('dragging');
      if(moved){var s=step();track.scrollTo({left:Math.round(track.scrollLeft/s)*s,behavior:'smooth'});}
    });
    /* a drag must not open the video card */
    track.addEventListener('click',function(e){if(moved){e.stopPropagation();e.preventDefault();moved=false;}},true);
    track.addEventListener('dragstart',function(e){e.preventDefault();});
  })();

  /* auto-scroll: advance every few seconds, loop back at the end; pause while hovering/focused, touching, lightbox open or tab hidden */
  var autoTimer=null,paused=false,body=document.querySelector('.testi-body'),lbEl=document.getElementById('lightbox');
  var reduceMotion=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  function advance(){
    if(paused||document.hidden||lbEl.classList.contains('open'))return;
    var atEnd=track.scrollLeft+track.clientWidth>=track.scrollWidth-4;
    if(atEnd)track.scrollTo({left:0,behavior:'smooth'});else track.scrollBy({left:step(),behavior:'smooth'});
  }
  function startAuto(){if(!reduceMotion&&!autoTimer)autoTimer=setInterval(advance,3500);}
  function setPaused(v){return function(){paused=v;};}
  ['mouseenter','focusin','touchstart'].forEach(function(e){body.addEventListener(e,setPaused(true),{passive:true});});
  ['mouseleave','focusout'].forEach(function(e){body.addEventListener(e,setPaused(false));});
  body.addEventListener('touchend',function(){setTimeout(function(){paused=false;},4000);},{passive:true});
  startAuto();

  /* video lightbox */
  var lb=document.getElementById('lightbox'),lv=document.getElementById('lbVideo');
  function openLb(src){lv.src=src;lb.classList.add('open');lb.setAttribute('aria-hidden','false');lv.play&&lv.play().catch(function(){});}
  function closeLb(){lb.classList.remove('open');lb.setAttribute('aria-hidden','true');lv.pause();lv.removeAttribute('src');lv.load();}
  document.querySelectorAll('[data-video]').forEach(function(el){
    el.addEventListener('click',function(){openLb(el.dataset.video);});
    el.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();openLb(el.dataset.video);}});
  });
  document.getElementById('lbClose').addEventListener('click',closeLb);
  lb.addEventListener('click',function(e){if(e.target===lb)closeLb();});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&lb.classList.contains('open'))closeLb();});
})();
