// ============================================================================
// DOARFAZBEM - Firebase Cloud Messaging Service Worker
// ============================================================================
// Gerencia notifica��es push em background
// ============================================================================

importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// Configura��o do Firebase - DoarFazBem
// IMPORTANTE: Substituir com suas credenciais reais do Firebase Console
const firebaseConfig = {
  apiKey: "AIzaSyCbM5oEJdVyNF7WsI7VPjJpEFyed9CoS_c",
  authDomain: "doarfazbem-4e506.firebaseapp.com",
  projectId: "doarfazbem-4e506",
  storageBucket: "doarfazbem-4e506.firebasestorage.app",
  messagingSenderId: "147534343556",
  appId: "1:147534343556:web:doarfazbem"
};

// Inicializar Firebase
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// ============================================================================
// BACKGROUND MESSAGES - Notifica��es em background
// ============================================================================
messaging.onBackgroundMessage((payload) => {
  console.log('[FCM-SW] Notifica��o recebida em background:', payload);

  const notificationTitle = payload.notification?.title || payload.data?.title || 'DoarFazBem';

  const notificationOptions = {
    body: payload.notification?.body || payload.data?.body || 'Voc� tem uma nova notifica��o',
    icon: payload.notification?.icon || payload.data?.icon || '/assets/icons/icon-192x192.png',
    badge: '/assets/icons/badge-72x72.png',
    tag: payload.data?.tag || 'doarfazbem-notification',
    requireInteraction: payload.data?.requireInteraction === 'true' || false,
    vibrate: [200, 100, 200],
    data: {
      url: payload.data?.url || '/dashboard',
      type: payload.data?.type || 'default',
      campaign_id: payload.data?.campaign_id || null,
      donation_id: payload.data?.donation_id || null,
      timestamp: Date.now()
    },
    actions: []
  };

  // Adicionar a��es baseadas no tipo de notifica��o
  const notificationType = payload.data?.type || 'default';

  if (notificationType === 'new_donation') {
    notificationOptions.actions = [
      {
        action: 'view',
        title: 'Ver doa��o',
        icon: '/assets/icons/action-view.png'
      },
      {
        action: 'thank',
        title: 'Agradecer',
        icon: '/assets/icons/action-heart.png'
      }
    ];
  } else if (notificationType === 'donation_confirmed') {
    notificationOptions.actions = [
      {
        action: 'view',
        title: 'Ver comprovante',
        icon: '/assets/icons/action-receipt.png'
      }
    ];
  } else if (notificationType === 'campaign_goal_reached') {
    notificationOptions.actions = [
      {
        action: 'celebrate',
        title: 'Celebrar',
        icon: '/assets/icons/action-celebrate.png'
      },
      {
        action: 'share',
        title: 'Compartilhar',
        icon: '/assets/icons/action-share.png'
      }
    ];
  }

  // Mostrar notifica��o
  return self.registration.showNotification(notificationTitle, notificationOptions);
});

// ============================================================================
// NOTIFICATION CLICK - A��o ao clicar na notifica��o
// ============================================================================
self.addEventListener('notificationclick', (event) => {
  console.log('[FCM-SW] Clique na notifica��o:', event.action);

  event.notification.close();

  let urlToOpen = event.notification.data?.url || '/dashboard';

  // Processar a��es espec�ficas
  if (event.action === 'thank') {
    const donationId = event.notification.data?.donation_id;
    if (donationId) {
      urlToOpen = `/donations/${donationId}/thank`;
    }
  } else if (event.action === 'celebrate') {
    const campaignId = event.notification.data?.campaign_id;
    if (campaignId) {
      urlToOpen = `/campaigns/${campaignId}/celebrate`;
    }
  } else if (event.action === 'share') {
    const campaignId = event.notification.data?.campaign_id;
    if (campaignId) {
      // Abrir modal de compartilhamento (Web Share API)
      urlToOpen = `/campaigns/${campaignId}/share`;
    }
  } else if (event.action === 'view') {
    // Usar URL padr�o dos dados
    urlToOpen = event.notification.data?.url || '/dashboard';
  }

  // Abrir/focar na janela do app
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      const fullUrl = new URL(urlToOpen, self.location.origin).href;

      // Tentar focar em uma janela existente
      for (const client of clientList) {
        if (client.url === fullUrl && 'focus' in client) {
          return client.focus();
        }
      }

      // Focar em qualquer janela aberta e navegar
      if (clientList.length > 0) {
        const client = clientList[0];
        if ('focus' in client) {
          client.focus();
          if ('navigate' in client) {
            return client.navigate(fullUrl);
          }
        }
      }

      // Se n�o h� janela aberta, abrir uma nova
      if (clients.openWindow) {
        return clients.openWindow(fullUrl);
      }
    })
  );
});

// ============================================================================
// INSTALA��O E ATIVA��O
// ============================================================================
self.addEventListener('install', (event) => {
  console.log('[FCM-SW] Firebase Messaging Service Worker instalado');
  self.skipWaiting();
});

self.addEventListener('activate', () => {
  console.log('[FCM-SW] Firebase Messaging Service Worker ativado');
  self.clients.claim();
});

console.log('[FCM-SW] Firebase Messaging Service Worker carregado com sucesso!');
