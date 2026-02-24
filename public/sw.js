// ============================================================================
// DOARFAZBEM - SERVICE WORKER PWA
// ============================================================================
// Funcionalidades: Cache, Offline, Push Notifications, Background Sync
// ============================================================================

const CACHE_NAME = 'doarfazbem-v1.0.0';
const CACHE_STATIC = 'doarfazbem-static-v1.0.0';
const CACHE_DYNAMIC = 'doarfazbem-dynamic-v1.0.0';

// Arquivos para cache estático (recursos essenciais)
const STATIC_FILES = [
    '/',
    '/login',
    '/register',
    '/campaigns',
    '/assets/css/style.css',
    '/assets/js/app.js',
    '/assets/images/logo.png',
    '/assets/icons/icon-192x192.png',
    '/assets/icons/icon-512x512.png',
    '/manifest.json'
];

// Arquivos que devem ser sempre buscados da rede (dados dinâmicos)
const NETWORK_FIRST = [
    '/api/',
    '/dashboard',
    '/donations/',
    '/campaigns/create',
    '/campaigns/edit',
    '/webhook/',
    '/payment/',
    '/admin/'
];

// ============================================================================
// INSTALL - Instalar Service Worker
// ============================================================================
self.addEventListener('install', function(event) {
    console.log('[SW] Instalando Service Worker DoarFazBem v1.0.0');

    event.waitUntil(
        caches.open(CACHE_STATIC)
            .then(function(cache) {
                console.log('[SW] Fazendo cache dos arquivos estáticos');
                return cache.addAll(STATIC_FILES);
            })
            .catch(function(error) {
                console.error('[SW] Erro ao fazer cache estático:', error);
            })
    );
});

// ============================================================================
// ACTIVATE - Ativar Service Worker
// ============================================================================
self.addEventListener('activate', function(event) {
    console.log('[SW] Ativando Service Worker DoarFazBem');

    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    // Remover caches antigos
                    if (cacheName !== CACHE_STATIC &&
                        cacheName !== CACHE_DYNAMIC &&
                        cacheName !== CACHE_NAME) {
                        console.log('[SW] Removendo cache antigo:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );

    return self.clients.claim();
});

// ============================================================================
// FETCH - Interceptar requisições
// ============================================================================
self.addEventListener('fetch', function(event) {
    const url = new URL(event.request.url);

    // Ignorar requisições não HTTP
    if (!event.request.url.startsWith('http')) {
        return;
    }

    // Estratégia Network First para dados dinâmicos
    if (NETWORK_FIRST.some(path => url.pathname.startsWith(path))) {
        event.respondWith(networkFirst(event.request));
    }
    // Estratégia Cache First para arquivos estáticos
    else if (event.request.method === 'GET') {
        event.respondWith(cacheFirst(event.request));
    }
});

// ============================================================================
// PUSH NOTIFICATIONS - Notificações Push
// ============================================================================
self.addEventListener('push', function(event) {
    console.log('[SW] Push notification recebida');

    let notificationData = {
        title: 'DoarFazBem',
        body: 'Nova notificação',
        icon: '/assets/icons/icon-192x192.png',
        badge: '/assets/icons/badge-72x72.png',
        tag: 'doarfazbem-notification',
        requireInteraction: false,
        actions: [],
        data: {
            url: '/dashboard',
            timestamp: Date.now()
        }
    };

    // Se veio dados na push notification
    if (event.data) {
        try {
            const pushData = event.data.json();

            // Adaptar estrutura de notificação
            if (pushData.notification) {
                notificationData.title = pushData.notification.title || notificationData.title;
                notificationData.body = pushData.notification.body || notificationData.body;
                notificationData.icon = pushData.notification.icon || notificationData.icon;
            }

            if (pushData.data) {
                notificationData.data = pushData.data;
            }

            // Adicionar ações baseadas no tipo de notificação
            if (pushData.data && pushData.data.type === 'new_donation') {
                notificationData.actions = [
                    {
                        action: 'view',
                        title: 'Ver doação',
                        icon: '/assets/icons/action-view.png'
                    },
                    {
                        action: 'thank',
                        title: 'Agradecer doador',
                        icon: '/assets/icons/action-heart.png'
                    }
                ];
            } else if (pushData.data && pushData.data.type === 'donation_confirmed') {
                notificationData.actions = [
                    {
                        action: 'view',
                        title: 'Ver comprovante',
                        icon: '/assets/icons/action-receipt.png'
                    }
                ];
            }

        } catch (e) {
            console.error('[SW] Erro ao parsear push data:', e);
            notificationData.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(notificationData.title, notificationData)
    );
});

// ============================================================================
// NOTIFICATION CLICK - Clique na notificação
// ============================================================================
self.addEventListener('notificationclick', function(event) {
    console.log('[SW] Clique na notificação:', event.action);

    event.notification.close();

    let url = '/dashboard';

    if (event.action === 'view') {
        // Redirecionar para URL especificada nos dados
        url = event.notification.data?.url || '/dashboard';

    } else if (event.action === 'thank') {
        // Redirecionar para página de agradecimento
        const donationId = event.notification.data?.donation_id;
        if (donationId) {
            url = `/donations/${donationId}/thank`;
        }

    } else {
        // Clique na notificação principal
        url = event.notification.data?.url || '/dashboard';
    }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            // Se já tem uma janela aberta, focar nela
            for (let client of clientList) {
                if ('focus' in client) {
                    return client.focus().then(() => {
                        if (client.url !== url && 'navigate' in client) {
                            return client.navigate(url);
                        }
                    });
                }
            }
            // Senão, abrir nova janela
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

// ============================================================================
// BACKGROUND SYNC - Sincronização em background
// ============================================================================
self.addEventListener('sync', function(event) {
    console.log('[SW] Background sync:', event.tag);

    if (event.tag === 'sync-donations') {
        event.waitUntil(syncDonations());
    } else if (event.tag === 'sync-campaigns') {
        event.waitUntil(syncCampaigns());
    }
});

// ============================================================================
// ESTRATÉGIAS DE CACHE
// ============================================================================

/**
 * Cache First - Para arquivos estáticos
 */
function cacheFirst(request) {
    return caches.match(request)
        .then(function(response) {
            if (response) {
                return response;
            }

            // Se não está no cache, buscar da rede
            return fetch(request)
                .then(function(response) {
                    // Se é uma resposta válida, adicionar ao cache dinâmico
                    if (response && response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_DYNAMIC)
                            .then(function(cache) {
                                cache.put(request, responseClone);
                            });
                    }
                    return response;
                })
                .catch(function(error) {
                    console.log('[SW] Erro ao buscar:', request.url, error);
                    // Se offline, retornar página offline para páginas HTML
                    if (request.headers.get('accept') && request.headers.get('accept').includes('text/html')) {
                        return caches.match('/offline.html') || caches.match('/');
                    }
                });
        });
}

/**
 * Network First - Para dados dinâmicos
 */
function networkFirst(request) {
    return fetch(request)
        .then(function(response) {
            // Se sucesso, atualizar cache
            if (response && response.status === 200 && request.method === 'GET') {
                const responseClone = response.clone();
                caches.open(CACHE_DYNAMIC)
                    .then(function(cache) {
                        cache.put(request, responseClone);
                    });
            }
            return response;
        })
        .catch(function(error) {
            console.log('[SW] Network error, tentando cache:', request.url);
            // Se offline, tentar buscar do cache
            return caches.match(request)
                .then(function(response) {
                    if (response) {
                        console.log('[SW] Offline - usando cache:', request.url);
                        return response;
                    }
                    // Se não tem cache, retornar resposta de erro
                    return new Response(
                        JSON.stringify({ error: 'Você está offline. Tente novamente mais tarde.' }),
                        {
                            status: 503,
                            statusText: 'Service Unavailable',
                            headers: new Headers({
                                'Content-Type': 'application/json'
                            })
                        }
                    );
                });
        });
}

// ============================================================================
// FUNÇÕES DE SINCRONIZAÇÃO
// ============================================================================

/**
 * Sincronizar doações offline
 */
function syncDonations() {
    return new Promise(function(resolve, reject) {
        console.log('[SW] Sincronizando doações...');

        // TODO: Implementar sincronização com IndexedDB
        // Buscar doações pendentes no IndexedDB local
        // Enviar para servidor quando voltar online

        resolve();
    });
}

/**
 * Sincronizar campanhas offline
 */
function syncCampaigns() {
    return new Promise(function(resolve, reject) {
        console.log('[SW] Sincronizando campanhas...');

        // TODO: Implementar sincronização com IndexedDB

        resolve();
    });
}

// ============================================================================
// MENSAGENS DO CLIENTE
// ============================================================================
self.addEventListener('message', function(event) {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    console.log('[SW] Mensagem recebida:', event.data);
});

console.log('[SW] Service Worker DoarFazBem carregado com sucesso!');
