(function(){
'use strict';
const cfg=window.SWARNIM_PWA||{};
const swUrl=cfg.swUrl||'sw.js';
const INSTALL_KEY='swarnim-install-state-v2';
let deferred=null;
const isStandalone=()=>window.matchMedia('(display-mode: standalone)').matches||navigator.standalone===true;
const state=()=>{try{return localStorage.getItem(INSTALL_KEY)||''}catch(e){return ''}};
const setState=v=>{try{localStorage.setItem(INSTALL_KEY,v)}catch(e){}};
function ready(){
  if('serviceWorker' in navigator) navigator.serviceWorker.register(swUrl,{scope:cfg.scope||'./'}).catch(()=>{});
  if(isStandalone()) return;
  const install=document.createElement('button');
  install.type='button'; install.className='sw-install-btn'; install.setAttribute('aria-label','Install Swarnim Groups');
  install.innerHTML='<span aria-hidden="true">＋</span><span>Install App</span>'; install.hidden=true; document.body.appendChild(install);
  install.addEventListener('click',async()=>{
    if(!deferred){setState('dismissed');install.hidden=true;return;}
    deferred.prompt();
    try{const choice=await deferred.userChoice;setState(choice.outcome==='accepted'?'installed':'dismissed')}catch(e){setState('dismissed')}
    install.hidden=true; deferred=null;
  });
  window.addEventListener('beforeinstallprompt',e=>{
    if(isStandalone()||state()==='installed'||state()==='dismissed') return;
    e.preventDefault(); deferred=e;
    // Show a single non-blocking install button; never auto-open a popup.
    install.hidden=false;
  },{once:true});
  window.addEventListener('appinstalled',()=>{setState('installed');install.hidden=true;deferred=null});
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready,{once:true});else ready();
})();
