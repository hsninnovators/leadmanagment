document.querySelectorAll('[data-copy-target]').forEach(function(btn){
  btn.addEventListener('click', function(){
    const el=document.getElementById(btn.dataset.copyTarget);
    navigator.clipboard.writeText(el.textContent.trim());
    btn.textContent='Copied';
    setTimeout(()=>btn.textContent='Copy',1000);
  });
});
