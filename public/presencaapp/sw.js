const CACHE_NAME = 'presencaapp-v1';
const ASSETS = [];

self.addEventListener('install', event => {
  self.skipWaiting();
});
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))))
      .then(() => self.clients.claim())
  );
});
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  if (event.request.method !== 'GET' || url.origin !== location.origin) return;
  // Network-first: app sempre pega dados frescos
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});
