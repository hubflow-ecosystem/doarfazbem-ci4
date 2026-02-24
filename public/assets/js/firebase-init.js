// ============================================================================
// DOARFAZBEM - Firebase Initialization
// ============================================================================
// Inicializa Firebase Cloud Messaging e gerencia tokens FCM
// ============================================================================

import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
import { getMessaging, getToken, onMessage } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js';

// Configuração do Firebase - DoarFazBem
const firebaseConfig = {
  apiKey: "AIzaSyCbM5oEJdVyNF7WsI7VPjJpEFyed9CoS_c",
  authDomain: "doarfazbem-4e506.firebaseapp.com",
  projectId: "doarfazbem-4e506",
  storageBucket: "doarfazbem-4e506.firebasestorage.app",
  messagingSenderId: "147534343556",
  appId: "1:147534343556:web:doarfazbem"
};

// VAPID Key para Web Push
const VAPID_KEY = "BJ29g4NLGrC6IbaCINCvOpRMAPF1X5B8uh3WAu54sul5UDmTNKyCR9tb4UsxQhyGaP7RxH6fMJKa2_HSuOw-a-Q";

// Inicializar Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

// ============================================================================
// REGISTRAR SERVICE WORKER
// ============================================================================
async function registerServiceWorker() {
  if ('serviceWorker' in navigator) {
    try {
      // Registrar Service Worker principal
      const registration = await navigator.serviceWorker.register('/sw.js');
      console.log('[Firebase] Service Worker principal registrado:', registration.scope);

      // Registrar Firebase Messaging Service Worker
      const messagingRegistration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
      console.log('[Firebase] Messaging Service Worker registrado:', messagingRegistration.scope);

      return messagingRegistration;
    } catch (error) {
      console.error('[Firebase] Erro ao registrar Service Workers:', error);
      return null;
    }
  } else {
    console.warn('[Firebase] Service Workers não são suportados neste navegador');
    return null;
  }
}

// ============================================================================
// SOLICITAR PERMISSÃO DE NOTIFICAÇÃO
// ============================================================================
async function requestNotificationPermission() {
  try {
    const permission = await Notification.requestPermission();

    if (permission === 'granted') {
      console.log('[Firebase] Permissão de notificação concedida');
      return true;
    } else if (permission === 'denied') {
      console.warn('[Firebase] Permissão de notificação negada');
      return false;
    } else {
      console.log('[Firebase] Permissão de notificação pendente');
      return false;
    }
  } catch (error) {
    console.error('[Firebase] Erro ao solicitar permissão:', error);
    return false;
  }
}

// ============================================================================
// OBTER TOKEN FCM
// ============================================================================
async function getFCMToken() {
  try {
    // Verificar se já tem permissão
    if (Notification.permission !== 'granted') {
      const hasPermission = await requestNotificationPermission();
      if (!hasPermission) {
        console.warn('[Firebase] Sem permissão para notificações');
        return null;
      }
    }

    // Registrar Service Worker
    const registration = await registerServiceWorker();
    if (!registration) {
      console.error('[Firebase] Service Worker não registrado');
      return null;
    }

    // Obter token FCM
    const currentToken = await getToken(messaging, {
      vapidKey: VAPID_KEY,
      serviceWorkerRegistration: registration
    });

    if (currentToken) {
      console.log('[Firebase] Token FCM obtido:', currentToken.substring(0, 20) + '...');
      return currentToken;
    } else {
      console.warn('[Firebase] Nenhum token FCM disponível. Solicite permissão primeiro.');
      return null;
    }
  } catch (error) {
    console.error('[Firebase] Erro ao obter token FCM:', error);

    if (error.code === 'messaging/permission-blocked') {
      console.error('[Firebase] Permissão de notificação bloqueada pelo usuário');
      showNotificationBlockedMessage();
    } else if (error.code === 'messaging/unsupported-browser') {
      console.error('[Firebase] Navegador não suporta notificações');
    }

    return null;
  }
}

// ============================================================================
// SALVAR TOKEN NO SERVIDOR
// ============================================================================
async function saveFCMTokenToServer(token) {
  try {
    const response = await fetch('/api/fcm/save-token', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        token: token,
        device_type: getDeviceType()
      })
    });

    const data = await response.json();

    if (data.success) {
      console.log('[Firebase] Token salvo no servidor com sucesso');
      localStorage.setItem('fcm_token', token);
      localStorage.setItem('fcm_token_saved_at', new Date().toISOString());
      return true;
    } else {
      console.error('[Firebase] Erro ao salvar token no servidor:', data.error);
      return false;
    }
  } catch (error) {
    console.error('[Firebase] Erro na requisição para salvar token:', error);
    return false;
  }
}

// ============================================================================
// DETECTAR TIPO DE DISPOSITIVO
// ============================================================================
function getDeviceType() {
  const ua = navigator.userAgent;

  if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
    return 'tablet';
  }
  if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(ua)) {
    return 'mobile';
  }
  return 'desktop';
}

// ============================================================================
// INICIALIZAR NOTIFICAÇÕES
// ============================================================================
async function initializeNotifications() {
  // Verificar se o navegador suporta notificações
  if (!('Notification' in window)) {
    console.warn('[Firebase] Este navegador não suporta notificações');
    return;
  }

  // Verificar se já tem token salvo e se ainda é válido (7 dias)
  const savedToken = localStorage.getItem('fcm_token');
  const savedAt = localStorage.getItem('fcm_token_saved_at');

  if (savedToken && savedAt) {
    const daysSinceSaved = (new Date() - new Date(savedAt)) / (1000 * 60 * 60 * 24);

    if (daysSinceSaved < 7) {
      console.log('[Firebase] Token FCM ainda válido no localStorage');
      setupForegroundMessageHandler();
      return;
    }
  }

  // Obter novo token
  const token = await getFCMToken();

  if (token) {
    // Salvar no servidor
    await saveFCMTokenToServer(token);

    // Configurar handler de mensagens em foreground
    setupForegroundMessageHandler();
  }
}

// ============================================================================
// HANDLER DE MENSAGENS EM FOREGROUND
// ============================================================================
function setupForegroundMessageHandler() {
  onMessage(messaging, (payload) => {
    console.log('[Firebase] Mensagem recebida em foreground:', payload);

    const notificationTitle = payload.notification?.title || payload.data?.title || 'DoarFazBem';
    const notificationOptions = {
      body: payload.notification?.body || payload.data?.body || '',
      icon: payload.notification?.icon || payload.data?.icon || '/assets/icons/icon-192x192.png',
      badge: '/assets/icons/badge-72x72.png',
      tag: payload.data?.tag || 'doarfazbem-notification',
      data: payload.data || {}
    };

    // Mostrar notificação usando Notification API
    if (Notification.permission === 'granted') {
      const notification = new Notification(notificationTitle, notificationOptions);

      notification.onclick = function(event) {
        event.preventDefault();
        const url = payload.data?.url || '/dashboard';
        window.open(url, '_blank');
        notification.close();
      };

      // Fechar automaticamente após 10 segundos
      setTimeout(() => notification.close(), 10000);
    }

    // Mostrar também como toast/banner na página (opcional)
    showInAppNotification(payload);
  });
}

// ============================================================================
// MOSTRAR NOTIFICAÇÃO IN-APP (Toast)
// ============================================================================
function showInAppNotification(payload) {
  const title = payload.notification?.title || payload.data?.title || 'Nova notificação';
  const body = payload.notification?.body || payload.data?.body || '';
  const icon = payload.notification?.icon || payload.data?.icon || '/assets/icons/icon-192x192.png';
  const url = payload.data?.url || '/dashboard';

  // Criar elemento de notificação
  const notificationEl = document.createElement('div');
  notificationEl.className = 'fixed top-4 right-4 z-50 max-w-sm bg-white rounded-lg shadow-lg p-4 flex items-start space-x-3 animate-slide-in';
  notificationEl.innerHTML = `
    <img src="${icon}" alt="Notification" class="w-10 h-10 rounded-full">
    <div class="flex-1">
      <h4 class="font-semibold text-gray-900">${title}</h4>
      <p class="text-sm text-gray-600 mt-1">${body}</p>
    </div>
    <button class="text-gray-400 hover:text-gray-600" onclick="this.parentElement.remove()">
      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
      </svg>
    </button>
  `;

  // Adicionar evento de clique
  notificationEl.addEventListener('click', (e) => {
    if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'svg' && e.target.tagName !== 'path') {
      window.location.href = url;
    }
  });

  // Adicionar ao body
  document.body.appendChild(notificationEl);

  // Remover automaticamente após 8 segundos
  setTimeout(() => {
    notificationEl.classList.add('animate-slide-out');
    setTimeout(() => notificationEl.remove(), 300);
  }, 8000);
}

// ============================================================================
// MOSTRAR MENSAGEM DE BLOQUEIO
// ============================================================================
function showNotificationBlockedMessage() {
  const message = `
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-yellow-700">
            Notificações bloqueadas. Para receber alertas de doações, permita notificações nas configurações do navegador.
          </p>
        </div>
      </div>
    </div>
  `;

  // Tentar inserir no topo da página se houver um container
  const container = document.querySelector('.container, main, #app');
  if (container) {
    const messageEl = document.createElement('div');
    messageEl.innerHTML = message;
    container.insertBefore(messageEl.firstElementChild, container.firstChild);
  }
}

// ============================================================================
// BOTÃO PARA SOLICITAR PERMISSÃO MANUALMENTE
// ============================================================================
function setupNotificationButton() {
  const button = document.getElementById('enable-notifications-btn');

  if (button) {
    button.addEventListener('click', async () => {
      button.disabled = true;
      button.textContent = 'Solicitando permissão...';

      const token = await getFCMToken();

      if (token) {
        await saveFCMTokenToServer(token);
        setupForegroundMessageHandler();

        button.textContent = 'Notificações ativadas!';
        button.classList.add('bg-green-500');

        setTimeout(() => {
          button.style.display = 'none';
        }, 2000);
      } else {
        button.disabled = false;
        button.textContent = 'Ativar notificações';
        alert('Não foi possível ativar notificações. Verifique as permissões do navegador.');
      }
    });
  }
}

// ============================================================================
// INICIALIZAÇÃO AUTOMÁTICA
// ============================================================================
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    initializeNotifications();
    setupNotificationButton();
  });
} else {
  initializeNotifications();
  setupNotificationButton();
}

// Exportar funções para uso global
window.DoarFazBemFirebase = {
  initializeNotifications,
  getFCMToken,
  requestNotificationPermission,
  saveFCMTokenToServer
};

console.log('[Firebase] DoarFazBem Firebase inicializado com sucesso!');
