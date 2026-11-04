const APP_NAME = 'Aplicativo Delivery';
const APP_VER = '1.0';
const CACHE_NAME = `${APP_NAME}-${APP_VER}`;

// Somente arquivos LOCAIS usados na página
const REQUIRED_FILES = [
  'index.php',  
  'app_assets/css/style.css',
  'app_assets/css/bootstrap.css',
  'manifest.php'
];

// Instala o Service Worker e adiciona arquivos ao cache
self.addEventListener('install', (event) => {
  console.log('✅ Service Worker instalado');

  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('📦 Cacheando arquivos:', REQUIRED_FILES);
        return cache.addAll(REQUIRED_FILES);
      })
      .then(() => self.skipWaiting())
      .catch(err => console.error('❌ Erro ao adicionar ao cache:', err))
  );
});

// Ativação do SW
self.addEventListener('activate', (event) => {
  console.log('✅ Service Worker ativado');
  event.waitUntil(self.clients.claim());
});

// Intercepta as requisições
self.addEventListener('fetch', (event) => {
  console.log('🔄 Interceptando:', event.request.url);

  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});
