const CACHE_NAME = 'montaapp-v9';
const ASSETS = ['/montaapp/icons/icon-192.png', '/montaapp/icons/icon-512.png'];

self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS)));
  self.skipWaiting();
});
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))))
    .then(() => self.clients.claim())
  );
});
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  if (event.request.method !== 'GET' || url.origin !== location.origin) return;
  // Ícones: cache-first, fallback pra rede
  if (ASSETS.some(a => url.pathname === a || url.pathname.startsWith(a))) {
    event.respondWith(
      caches.match(event.request).then(function(cached) {
        return cached || fetch(event.request);
      })
    );
    return;
  }
  event.respondWith(fetch(event.request).catch(function() { return new Response(null, { status: 503 }); }));
});
