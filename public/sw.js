/* GEZR – Service Worker
 *
 * - Les pages HTML (authentifiées, avec jeton CSRF) ne sont JAMAIS mises en cache :
 *   réseau d'abord, et page hors-ligne si le réseau est indisponible.
 * - Les ressources statiques (icônes, CSS/JS/polices des CDN) : stale-while-revalidate.
 * - Les requêtes non-GET (formulaires, AJAX POST) ne sont jamais interceptées.
 *
 * Pour forcer la mise à jour du cache chez les utilisateurs, incrémenter VERSION.
 */
const VERSION = 'v1';
const STATIC_CACHE = 'gezr-static-' + VERSION;
const PRECACHE = [
    '/offline.html',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png',
];

const CDN_HOSTS = ['cdn.jsdelivr.net'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => k.startsWith('gezr-') && k !== STATIC_CACHE).map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

function isStaticAsset(url) {
    if (CDN_HOSTS.includes(url.hostname)) return true;
    if (url.origin !== self.location.origin) return false;
    return url.pathname.startsWith('/icons/')
        || /\.(?:css|js|woff2?|ttf|png|jpg|jpeg|svg|ico|webp)$/i.test(url.pathname);
}

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // Pages : réseau d'abord, fallback hors-ligne
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // Ressources statiques : stale-while-revalidate
    if (isStaticAsset(url)) {
        event.respondWith(
            caches.open(STATIC_CACHE).then((cache) =>
                cache.match(req).then((cached) => {
                    const network = fetch(req)
                        .then((res) => {
                            if (res && (res.ok || res.type === 'opaque')) cache.put(req, res.clone());
                            return res;
                        })
                        .catch(() => cached);
                    return cached || network;
                })
            )
        );
    }
    // Le reste (AJAX GET, etc.) passe directement par le réseau.
});
