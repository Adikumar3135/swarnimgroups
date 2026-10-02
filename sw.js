const CACHE='swarnim-public-v4';
const OFFLINE='./offline.html';
const PUBLIC_DOCS=new Set(['/','/index.php','/product.php','/login.php','/register.php','/maintenance.php','/offline.html']);
const isPrivate=p=>p.includes('/admin/')||p.includes('/employee/')||p.includes('/api.php')||p.includes('/profile')||p.includes('/checkout')||p.includes('/orders')||p.includes('/my-orders')||p.includes('/invoice')||p.includes('/contact-api')||p.includes('/login-api')||p.includes('/register-api');

self.addEventListener('install',e=>{
  e.waitUntil(caches.open(CACHE).then(c=>c.addAll([
    './','./index.php','./product.php','./offline.html','./manifest.webmanifest',
    './css/style.css','./css/pwa.css','./js/script.js','./js/pwa.js',
    './data/favicon.png','./data/Swarnim Logo.png'
  ])).then(()=>self.skipWaiting()));
});
self.addEventListener('activate',e=>{
  e.waitUntil(caches.keys().then(keys=>Promise.all(keys.filter(k=>k!==CACHE).map(k=>caches.delete(k)))).then(()=>self.clients.claim()));
});
self.addEventListener('fetch',e=>{
  if(e.request.method!=='GET')return;
  const u=new URL(e.request.url);
  if(u.origin!==location.origin||isPrivate(u.pathname))return;
  if(e.request.destination==='document'&&!PUBLIC_DOCS.has(u.pathname))return;
  e.respondWith(fetch(e.request).then(r=>{
    if(r.ok&&(e.request.destination!=='document'||PUBLIC_DOCS.has(u.pathname))){
      const copy=r.clone();caches.open(CACHE).then(c=>c.put(e.request,copy)).catch(()=>{});
    }
    return r;
  }).catch(()=>caches.match(e.request).then(r=>{
    if(r)return r;
    if(e.request.destination==='document')return caches.match(OFFLINE);
    return new Response('',{status:503,statusText:'Offline'});
  })));
});
self.addEventListener('notificationclick',e=>{
  e.notification.close();
  const target=e.notification.data?.url||'./employee/index.php';
  e.waitUntil(clients.matchAll({type:'window',includeUncontrolled:true}).then(list=>{
    for(const client of list){if(client.url.includes(target)||client.url.endsWith(target)){return client.focus();}}
    return clients.openWindow(target);
  }));
});
