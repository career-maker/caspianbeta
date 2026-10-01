
(function(){
  var input=document.querySelector('.search-box input');
  var cards=document.querySelectorAll('.product-card[data-name]');
  var blocks=document.querySelectorAll('.cat-block[id]');
  var tabs=document.querySelectorAll('.cat-tab');
  var none=document.querySelector('.no-results');
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
  }
  function setFilter(f){
    filter=f;
    tabs.forEach(function(t){var on=t.dataset.filter===f;t.classList.toggle('active',on);t.setAttribute('aria-pressed',on);});
    apply();
  }
  tabs.forEach(function(t){t.addEventListener('click',function(){setFilter(t.dataset.filter);});});
  if(input)input.addEventListener('input',apply);
  var h=location.hash.replace('#','');
  var valid=[].slice.call(tabs).map(function(t){return t.dataset.filter;});
  if(h&&h!=='all'&&valid.indexOf(h)>-1)setFilter(h);
})();
