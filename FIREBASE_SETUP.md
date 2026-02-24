# 🔥 Configuração do Firebase - DoarFazBem

## Índice
- [1. Criar Projeto no Firebase Console](#1-criar-projeto-no-firebase-console)
- [2. Ativar Firebase Cloud Messaging](#2-ativar-firebase-cloud-messaging)
- [3. Obter Credenciais do Projeto](#3-obter-credenciais-do-projeto)
- [4. Gerar Service Account (Backend)](#4-gerar-service-account-backend)
- [5. Gerar VAPID Keys (Web Push)](#5-gerar-vapid-keys-web-push)
- [6. Configurar Arquivos do Projeto](#6-configurar-arquivos-do-projeto)
- [7. Testar Notificações](#7-testar-notificações)
- [8. Troubleshooting](#8-troubleshooting)

---

## 1. Criar Projeto no Firebase Console

### Passo 1.1: Acessar Firebase Console
1. Acesse: https://console.firebase.google.com/
2. Faça login com sua conta Google
3. Clique em **"Adicionar projeto"** ou **"Create a project"**

### Passo 1.2: Configurar Projeto
1. **Nome do projeto**: `DoarFazBem` (ou nome desejado)
2. **Project ID**: `doarfazbem` (será usado nas URLs)
3. **Analytics**: Ative o Google Analytics (recomendado)
4. Clique em **"Criar projeto"**

Aguarde alguns segundos até o projeto ser criado.

---

## 2. Ativar Firebase Cloud Messaging

### Passo 2.1: Acessar Configurações
1. No painel do Firebase Console, clique no ícone de **engrenagem** ⚙️ ao lado de "Project Overview"
2. Selecione **"Project settings"** (Configurações do projeto)
3. Vá para a aba **"Cloud Messaging"**

### Passo 2.2: Ativar Firebase Cloud Messaging API
1. Clique no botão **"Enable Cloud Messaging API"**
2. Será redirecionado para o Google Cloud Console
3. Clique em **"ENABLE"** para ativar a API
4. Retorne ao Firebase Console

---

## 3. Obter Credenciais do Projeto

### Passo 3.1: Adicionar App Web
1. No Firebase Console, vá para **Project settings** > **General**
2. Role até a seção **"Your apps"** (Seus aplicativos)
3. Clique no ícone **`</>`** (Web)
4. Preencha:
   - **App nickname**: `DoarFazBem Web`
   - **Firebase Hosting**: Marque se for usar (opcional)
5. Clique em **"Register app"**

### Passo 3.2: Copiar Configuração Web
Você verá um código JavaScript como este:

```javascript
const firebaseConfig = {
  apiKey: "AIzaSyAbIQ5M_WtCQmKuaSHyTRnRUGEp8PJ8BgU",
  authDomain: "doarfazbem.firebaseapp.com",
  projectId: "doarfazbem",
  storageBucket: "doarfazbem.firebasestorage.app",
  messagingSenderId: "868670655033",
  appId: "1:868670655033:web:6d5da1e89b94c1becc5be8"
};
```

**IMPORTANTE**: Guarde essas credenciais, você precisará delas nos próximos passos.

---

## 4. Gerar Service Account (Backend)

O Service Account é necessário para o **backend PHP** enviar notificações via API.

### Passo 4.1: Gerar Chave Privada
1. No Firebase Console, vá para **Project settings** > **Service accounts**
2. Clique em **"Generate new private key"**
3. Confirme clicando em **"Generate key"**
4. Um arquivo JSON será baixado automaticamente

### Passo 4.2: Renomear e Colocar no Projeto
1. Renomeie o arquivo baixado para: `firebase-credentials.json`
2. Coloque o arquivo em: `C:\laragon\www\doarfazbem\app\Config\firebase-credentials.json`

⚠️ **SEGURANÇA**: Este arquivo contém credenciais privadas. Adicione ao `.gitignore`:

```
# Firebase
app/Config/firebase-credentials.json
```

### Exemplo de estrutura do arquivo:
```json
{
  "type": "service_account",
  "project_id": "doarfazbem",
  "private_key_id": "abc123...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
  "client_email": "firebase-adminsdk-xxxxx@doarfazbem.iam.gserviceaccount.com",
  "client_id": "123456789",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "..."
}
```

---

## 5. Gerar VAPID Keys (Web Push)

VAPID Keys são necessárias para **Web Push Notifications** no navegador.

### Passo 5.1: Gerar Par de Chaves
1. No Firebase Console, vá para **Project settings** > **Cloud Messaging**
2. Role até a seção **"Web Push certificates"**
3. Clique em **"Generate key pair"**
4. Uma chave pública será gerada automaticamente

### Passo 5.2: Copiar Chave VAPID
Você verá algo como:

```
Key pair generated
Public key: BNT8jZ6fQv4z...
```

**IMPORTANTE**: Copie a **Public key** (chave pública). Você precisará dela no próximo passo.

---

## 6. Configurar Arquivos do Projeto

Agora vamos configurar os arquivos do projeto com as credenciais obtidas.

### Arquivo 1: `public/firebase-messaging-sw.js`

Abra o arquivo `C:\laragon\www\doarfazbem\public\firebase-messaging-sw.js` e substitua:

```javascript
const firebaseConfig = {
  apiKey: "YOUR_API_KEY",                    // ← Cole aqui o apiKey do passo 3.2
  authDomain: "doarfazbem.firebaseapp.com",  // ← Cole aqui o authDomain
  projectId: "doarfazbem",                   // ← Cole aqui o projectId
  storageBucket: "doarfazbem.firebasestorage.app", // ← Cole aqui o storageBucket
  messagingSenderId: "YOUR_SENDER_ID",       // ← Cole aqui o messagingSenderId
  appId: "YOUR_APP_ID"                       // ← Cole aqui o appId
};
```

**Exemplo preenchido:**
```javascript
const firebaseConfig = {
  apiKey: "AIzaSyAbIQ5M_WtCQmKuaSHyTRnRUGEp8PJ8BgU",
  authDomain: "doarfazbem.firebaseapp.com",
  projectId: "doarfazbem",
  storageBucket: "doarfazbem.firebasestorage.app",
  messagingSenderId: "868670655033",
  appId: "1:868670655033:web:6d5da1e89b94c1becc5be8"
};
```

---

### Arquivo 2: `public/assets/js/firebase-init.js`

Abra o arquivo `C:\laragon\www\doarfazbem\public\assets\js\firebase-init.js` e faça as mesmas substituições:

```javascript
// Configuração do Firebase
const firebaseConfig = {
  apiKey: "AIzaSyAbIQ5M_WtCQmKuaSHyTRnRUGEp8PJ8BgU",  // ← Cole suas credenciais aqui
  authDomain: "doarfazbem.firebaseapp.com",
  projectId: "doarfazbem",
  storageBucket: "doarfazbem.firebasestorage.app",
  messagingSenderId: "868670655033",
  appId: "1:868670655033:web:6d5da1e89b94c1becc5be8"
};

// VAPID Key
const VAPID_KEY = "BNT8jZ6fQv4z...";  // ← Cole a chave VAPID do passo 5.2
```

---

### Arquivo 3: `.env` (Opcional - para referência)

Você pode adicionar as credenciais no `.env` para facilitar:

```env
# Firebase Cloud Messaging
FIREBASE_PROJECT_ID=doarfazbem
FIREBASE_API_KEY=AIzaSyAbIQ5M_WtCQmKuaSHyTRnRUGEp8PJ8BgU
FIREBASE_AUTH_DOMAIN=doarfazbem.firebaseapp.com
FIREBASE_MESSAGING_SENDER_ID=868670655033
FIREBASE_APP_ID=1:868670655033:web:6d5da1e89b94c1becc5be8
FIREBASE_VAPID_KEY=BNT8jZ6fQv4z...
```

---

## 7. Testar Notificações

### Teste 1: Verificar Instalação do Service Worker

1. Abra o projeto no navegador: `http://doarfazbem.test`
2. Abra o **DevTools** (F12)
3. Vá para a aba **Console**
4. Verifique se aparecem as mensagens:
   ```
   [PWA] Service Worker registrado com sucesso
   [FCM] Firebase Messaging SW registrado
   [Firebase] DoarFazBem Firebase inicializado com sucesso!
   ```

### Teste 2: Solicitar Permissão de Notificação

1. Na página inicial, procure o botão **"Ativar notificações"** (se tiver)
2. OU abra o console e execute:
   ```javascript
   await DoarFazBemFirebase.requestNotificationPermission()
   ```
3. Clique em **"Permitir"** quando o navegador solicitar permissão

### Teste 3: Obter Token FCM

No console do navegador, execute:

```javascript
const token = await DoarFazBemFirebase.getFCMToken();
console.log('Token FCM:', token);
```

Você deve ver um token longo como:
```
Token FCM: dJZ8fQv4zNT8...
```

### Teste 4: Enviar Notificação via PHP

Crie um script de teste em `C:\laragon\www\doarfazbem\test-firebase.php`:

```php
<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/Libraries/FirebaseService.php';

use App\Libraries\FirebaseService;

// Substitua pelo ID do usuário de teste
$userId = 1;

$title = '🎉 Teste de Notificação';
$body = 'Sua configuração do Firebase está funcionando perfeitamente!';
$data = [
    'type' => 'test',
    'url' => '/dashboard',
    'icon' => '/assets/icons/icon-192x192.png'
];

$result = FirebaseService::sendToUser($userId, $title, $body, $data);

if ($result) {
    echo "✅ Notificação enviada com sucesso!\n";
} else {
    echo "❌ Erro ao enviar notificação.\n";
}
```

Execute no terminal:
```bash
php test-firebase.php
```

Se tudo estiver correto, você receberá a notificação no navegador!

---

## 8. Troubleshooting

### Erro: "Firebase credentials file not found"

**Causa**: O arquivo `firebase-credentials.json` não está no local correto.

**Solução**:
1. Verifique se o arquivo está em: `app/Config/firebase-credentials.json`
2. Certifique-se de que renomeou corretamente o arquivo
3. Verifique as permissões do arquivo (deve ser legível)

---

### Erro: "Failed to get OAuth access token"

**Causa**: A chave privada no `firebase-credentials.json` está incorreta ou o formato do JSON está quebrado.

**Solução**:
1. Gere uma nova chave privada no Firebase Console (Passo 4.1)
2. Substitua o arquivo `firebase-credentials.json`
3. Verifique se o JSON está válido (use um validador online)

---

### Erro: "No FCM tokens found for user"

**Causa**: O usuário não permitiu notificações ou o token não foi salvo no banco.

**Solução**:
1. Verifique se a tabela `fcm_tokens` existe no banco de dados
2. Execute as migrations: `php spark migrate`
3. Solicite permissão de notificação novamente no navegador
4. Verifique se o endpoint `/api/fcm/save-token` está funcionando

---

### Notificações não aparecem no navegador

**Possíveis causas**:

1. **Permissão negada**: Verifique nas configurações do navegador se o site tem permissão para notificações
   - Chrome: `chrome://settings/content/notifications`
   - Firefox: Configurações > Privacidade e Segurança > Permissões > Notificações

2. **Service Worker não registrado**: Verifique no DevTools > Application > Service Workers

3. **VAPID Key incorreta**: Verifique se a VAPID Key em `firebase-init.js` está correta

4. **Navegador em modo privado**: Notificações não funcionam em modo anônimo/privado

---

### Erro: "This browser doesn't support push notifications"

**Causa**: O navegador não suporta Web Push ou está desatualizado.

**Solução**:
- Use um navegador moderno: Chrome 50+, Firefox 44+, Edge 17+, Safari 16+
- Atualize o navegador para a versão mais recente
- Notificações **não funcionam** no Safari iOS (somente macOS Safari 16+)

---

### Testar com Firebase Console

Você pode enviar notificações de teste diretamente do Firebase Console:

1. Vá para Firebase Console > **Cloud Messaging**
2. Clique em **"Send your first message"**
3. Preencha:
   - **Notification title**: "Teste"
   - **Notification text**: "Olá do Firebase"
4. Em **Target**, selecione **"User segment"** > **"All users"**
5. Clique em **"Test on device"**
6. Cole o **FCM token** obtido no Teste 3
7. Clique em **"Test"**

Se você receber a notificação, o Firebase está configurado corretamente! 🎉

---

## Resumo dos Arquivos Configurados

Após seguir este guia, você terá configurado:

- ✅ `app/Config/firebase-credentials.json` - Credenciais do Service Account (backend)
- ✅ `public/firebase-messaging-sw.js` - Service Worker do Firebase (frontend)
- ✅ `public/assets/js/firebase-init.js` - Inicialização do Firebase (frontend)
- ✅ `.env` - Variáveis de ambiente (opcional)

---

## Próximos Passos

1. **Criar endpoint de API**: Implemente o endpoint `/api/fcm/save-token` para salvar tokens no banco
2. **Integrar com webhooks**: Envie notificações automáticas quando doações forem recebidas
3. **Personalizar notificações**: Customize ícones, sons e ações das notificações
4. **Testar em produção**: Configure domínio HTTPS real (necessário para PWA/Push)
5. **Monitoramento**: Acompanhe entregas e erros no Firebase Console > Cloud Messaging

---

## Referências

- [Firebase Documentation](https://firebase.google.com/docs)
- [Firebase Cloud Messaging (FCM)](https://firebase.google.com/docs/cloud-messaging)
- [Web Push Protocol (VAPID)](https://developers.google.com/web/fundamentals/push-notifications)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [Notification API](https://developer.mozilla.org/en-US/docs/Web/API/Notifications_API)

---

**Desenvolvido para DoarFazBem** 💚
