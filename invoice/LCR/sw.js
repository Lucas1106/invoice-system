// sw.js – cache estático resiliente + network-first p/ JSON
const CACHE_STATIC  = 'invoice-static-v3';
const CACHE_DYNAMIC = 'invoice-dynamic-v3';

const STATIC_ASSETS = [
  './',                     // raiz (resolve p/ invoice.html ou index.php)
  './logo.png',
  './manifest.webmanifest',
  './icon-192.png',
  './icon-512.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    const cache = await caches.open(CACHE_STATIC);
    await Promise.allSettled(
      STATIC_ASSETS.map(u => cache.add(u).catch(() => null))
    );
  })());
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys
      .filter(k => k !== CACHE_STATIC && k !== CACHE_DYNAMIC)
      .map(k => caches.delete(k)));
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method === 'POST') return;

  const url = new URL(req.url);
  const isDynJson = /\/(settings\.json|counter\.json)$/.test(url.pathname);

  if (isDynJson) {
    event.respondWith((async () => {
      try {
        const fresh = await fetch(req, { cache: 'no-store' });
        const c = await caches.open(CACHE_DYNAMIC);
        c.put(req, fresh.clone());
        return fresh;
      } catch (e) {
        const cached = await caches.match(req);
        if (cached) return cached;
        throw e;
      }
    })());
    return;
  }

  event.respondWith((async () => {
    const cached = await caches.match(req);
    if (cached) return cached;
    const fresh = await fetch(req);
    const c = await caches.open(CACHE_STATIC);
    c.put(req, fresh.clone());
    return fresh;
  })());
});
