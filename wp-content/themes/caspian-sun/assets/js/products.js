
(function(){
  var input=document.querySelector('.search-box input');
  var cards=document.querySelectorAll('.product-card[data-name]');
  var blocks=document.querySelectorAll('.cat-block[id]');
  var tabs=document.querySelectorAll('.cat-tab');
  var none=document.querySelector('.no-results');
  var tabBox=document.querySelector('.cat-tabs'),countEl=document.getElementById('resultCount');
  var filter='all';
  function apply(){
    var q=input?input.value.trim().toLowerCase():'',shown=0;
    cards.forEach(function(c){var m=!q||c.dataset.name.indexOf(q)>-1;c.style.display=m?'':'none';});
    blocks.forEach(function(b){
      var inCat=filter==='all'||b.id===filter,vis=false;
      if(b.id==='other'){vis=inCat&&!q;}
      else{b.querySelectorAll('.product-card').forEach(function(c){if(c.style.display!=='none')vis=true;});vis=vis&&inCat;}
      b.style.display=vis?'':'none';
      if(vis&&b.id!=='other')shown++;else if(vis)shown++;
    });
    none.style.display=shown?'none':'block';
    if(countEl){
      var n=0;cards.forEach(function(c){var b=c.closest('.cat-block');if(c.style.display!=='none'&&b&&b.style.display!=='none')n++;});
      countEl.textContent=n+' '+(n===1?countEl.dataset.one:countEl.dataset.many);
    }
  }
  /* tab row on small screens: horizontally scrollable with edge fades, active tab kept centred */
  function fades(){
    if(!tabBox)return;
    var max=tabBox.scrollWidth-tabBox.clientWidth;
    tabBox.classList.toggle('can-left',max>2&&tabBox.scrollLeft>2);
    tabBox.classList.toggle('can-right',max>2&&tabBox.scrollLeft<max-2);
  }
  function centreActive(smooth){
    if(!tabBox||tabBox.scrollWidth<=tabBox.clientWidth+2)return;
    var t=tabBox.querySelector('.cat-tab.active');if(!t)return;
    var left=t.offsetLeft-(tabBox.clientWidth-t.offsetWidth)/2;
    tabBox.scrollTo({left:Math.max(0,left),behavior:smooth?'smooth':'auto'});
  }
  if(tabBox){
    tabBox.addEventListener('scroll',fades,{passive:true});
    window.addEventListener('resize',function(){fades();centreActive(false);});
  }
  function setFilter(f){
    filter=f;
    tabs.forEach(function(t){var on=t.dataset.filter===f;t.classList.toggle('active',on);t.setAttribute('aria-pressed',on);});
    apply();
    centreActive(true);
  }
  tabs.forEach(function(t){t.addEventListener('click',function(){setFilter(t.dataset.filter);});});
  if(input)input.addEventListener('input',apply);
  var h=location.hash.replace('#','');
  var valid=[].slice.call(tabs).map(function(t){return t.dataset.filter;});
  if(h&&h!=='all'&&valid.indexOf(h)>-1)setFilter(h);else apply();
  fades();
})();
