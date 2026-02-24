# 📬 Sistema de Notificações - DoarFazBem

Sistema completo de notificações por email e push para doadores quando criadores postam atualizações nas campanhas.

---

## ✅ O QUE FOI IMPLEMENTADO

### 1. **Banco de Dados**
- ✅ Tabela `notification_preferences` - Preferências dos doadores
- ✅ Tabela `notification_queue` - Fila de notificações pendentes

### 2. **Models**
- ✅ `NotificationPreference` - Gerencia preferências
- ✅ `NotificationQueue` - Gerencia fila de envio

### 3. **Services**
- ✅ `EmailNotificationService` - Envia emails com template HTML
- ✅ `PushNotificationService` - Envia push notifications via Firebase

### 4. **Controllers**
- ✅ `NotificationController` - Gerencia preferências e unsubscribe
- ✅ Modificado `Donation` - Salva preferências ao doar
- ✅ Modificado `CampaignInteractionController` - Enfileira notificações

### 5. **Command**
- ✅ `notifications:send` - Processa fila de notificações (cron job)

### 6. **Routes**
- ✅ `/dashboard/notifications` - Gerenciar preferências
- ✅ `/notifications/unsubscribe/{token}` - Cancelar inscrição
- ✅ `/notifications/save-push-token` - API para Firebase

---

## 🔧 O QUE VOCÊ PRECISA CONFIGURAR

### 1. **Firebase Cloud Messaging** 🔥

#### Passo 1: Criar/Configurar Projeto Firebase
1. Acesse [Firebase Console](https://console.firebase.google.com/)
2. Crie novo projeto ou use existente
3. No projeto, vá em **Project Settings** (engrenagem)

#### Passo 2: Ativar Cloud Messaging
1. Na aba **Cloud Messaging**
2. Se necessário, ative a API do Firebase Cloud Messaging
3. Copie as seguintes informações:

**Anote estes valores:**
```
Project ID: seu-projeto-firebase
Sender ID: 123456789012
Server Key: AAAA...xxx (chave secreta)
```

#### Passo 3: Gerar Chave VAPID (Web Push)
1. Ainda em **Cloud Messaging**
2. Role até **Web Push certificates**
3. Clique em **Generate key pair**
4. Copie a **chave pública VAPID**

#### Passo 4: Adicionar ao .env
Adicione estas linhas ao arquivo `.env`:

```env
#--------------------------------------------------------------------
# FIREBASE - Push Notifications
#--------------------------------------------------------------------
FIREBASE_PROJECT_ID = seu-projeto-firebase
FIREBASE_API_KEY = AIzaSy... (da configuração web)
FIREBASE_MESSAGING_SENDER_ID = 123456789012
FIREBASE_SERVER_KEY = AAAAxxxxxxx... (Server key)
FIREBASE_VAPID_KEY = BPxxxxxxx... (Chave pública VAPID)
```

#### Passo 5: Adicionar Domínio Autorizado
1. Em **Project Settings > General**
2. Em **Your apps**, encontre o app Web
3. Em **Authorized domains**, adicione:
   - `doarfazbem.ai`
   - `localhost` (para testes)

---

### 2. **Configurar Cron Job** ⏰

O sistema precisa de um cron job para processar a fila de notificações.

#### Opção A: Cron (Linux/Mac/WSL)

```bash
# Editar crontab
crontab -e

# Adicionar esta linha (executar a cada 5 minutos):
*/5 * * * * cd /c/laragon/www/doarfazbem && php spark notifications:send >> /c/laragon/www/doarfazbem/writable/logs/cron.log 2>&1
```

#### Opção B: Task Scheduler (Windows)

1. Abra **Task Scheduler** (Agendador de Tarefas)
2. Clique em **Create Basic Task**
3. Nome: `DoarFazBem Notificações`
4. Gatilho: **Daily**
5. Recorrência: Marque **Repeat task every: 5 minutes**
6. Ação: **Start a program**
7. Program/script: `C:\laragon\bin\php\php-8.x\php.exe`
8. Add arguments: `C:\laragon\www\doarfazbem\spark notifications:send`
9. Start in: `C:\laragon\www\doarfazbem`

#### Opção C: Testar Manualmente (Desenvolvimento)

```bash
# Rodar comando manualmente para testar
php spark notifications:send
```

---

## 📝 COMO FUNCIONA

### Fluxo Completo:

1. **Doador faz doação**
   - Marca checkboxes "Receber notificações" no checkout
   - Preferências são salvas em `notification_preferences`

2. **Criador posta atualização**
   - Atualização é salva em `campaign_updates`
   - Sistema busca todos os doadores inscritos
   - Adiciona notificações à `notification_queue`

3. **Cron Job processa fila** (a cada 5 minutos)
   - Command `notifications:send` roda
   - Busca notificações pendentes
   - Envia emails via SMTP (já configurado)
   - Envia push via Firebase (se configurado)
   - Marca como enviado ou falha

4. **Doador recebe notificação**
   - **Email:** HTML formatado com link para campanha
   - **Push:** Notificação no navegador (se permitiu)
   - Pode cancelar inscrição via link no email

---

## 🧪 TESTES

### 1. Testar Envio de Email

Já está configurado! O SMTP funciona:
- Servidor: `smtp.stackmail.com`
- Email: `contato@doarfazbem.com.br`

Para testar manualmente:
```bash
# Acesse no navegador
http://doarfazbem.ai/test-email
```

### 2. Testar Sistema Completo

1. Faça uma doação em qualquer campanha
2. Marque os checkboxes de notificação
3. Como criador, poste uma atualização na campanha
4. Rode manualmente: `php spark notifications:send`
5. Verifique o email do doador

---

## 📊 MONITORAMENTO

### Verificar Fila de Notificações

```sql
-- Ver notificações pendentes
SELECT * FROM notification_queue WHERE status = 'pending';

-- Ver notificações falhadas
SELECT * FROM notification_queue WHERE status = 'failed';

-- Contar por status
SELECT status, COUNT(*) as total
FROM notification_queue
GROUP BY status;
```

### Logs

Verifique os logs em:
- `writable/logs/log-YYYY-MM-DD.log` - Logs gerais
- `writable/logs/cron.log` - Logs do cron job

---

##  🎯 PRÓXIMOS PASSOS

### Views Necessárias (Não foram criadas - opcional)

Se quiser interface visual:

1. **dashboard/notifications.php** - Página para gerenciar preferências
2. **notifications/unsubscribe.php** - Página de unsubscribe
3. **notifications/unsubscribe_success.php** - Confirmação

Mas o sistema funciona sem elas! Os checkboxes no checkout já salvam as preferências.

### Firebase JavaScript (Opcional)

Se quiser implementar push notifications no frontend, crie:

`public/assets/js/firebase-notifications.js`:
```javascript
// Inicializar Firebase
const firebaseConfig = {
  apiKey: "SUA_API_KEY",
  projectId: "SEU_PROJECT_ID",
  messagingSenderId: "SEU_SENDER_ID",
  appId: "SEU_APP_ID"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Solicitar permissão
messaging.requestPermission()
  .then(() => messaging.getToken({ vapidKey: 'SUA_VAPID_KEY' }))
  .then(token => {
    // Enviar token para servidor
    fetch('/notifications/save-push-token', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token })
    });
  });
```

---

## ❓ FAQ

**P: As notificações são enviadas imediatamente?**
R: Não, vão para uma fila e são processadas pelo cron job (a cada 5 minutos).

**P: Quantas tentativas de envio?**
R: 3 tentativas. Depois disso, marca como "failed".

**P: Como remover notificações antigas?**
R: O comando `notifications:send` já limpa automaticamente notificações com mais de 30 dias.

**P: E se o Firebase não estiver configurado?**
R: Emails funcionam normalmente. Push notifications apenas não serão enviadas.

**P: Doadores anônimos recebem notificações?**
R: Sim! O sistema usa o email fornecido na doação.

---

## 🎉 PRONTO!

Agora você tem um sistema completo de notificações.

**Para ativar:**
1. Configure Firebase (adicione credenciais ao .env)
2. Configure o Cron Job
3. Teste fazendo uma doação e postando atualização

**Status Atual:**
- ✅ Email: **FUNCIONANDO** (SMTP já configurado)
- ⏳ Push: **AGUARDANDO** (precisa configurar Firebase)
- ✅ Backend: **COMPLETO**
- ⏳ Cron: **PRECISA CONFIGURAR**
