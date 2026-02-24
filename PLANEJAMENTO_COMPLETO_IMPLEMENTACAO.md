# 📋 PLANEJAMENTO COMPLETO - IMPLEMENTAÇÃO DOARFAZBEM

**Data:** 2025-11-05
**Versão:** 2.0.0
**Objetivo:** Implementar PWA, Service Worker, Firebase, Notificações Push, Asaas e Design Moderno

---

## 📊 ANÁLISE DOS PROJETOS DE REFERÊNCIA

### ✅ Projetos Analisados

1. **Cantina PRÓ-VIDA** (`C:\laragon\www\cantina`)
   - ✅ Asaas 100% funcional
   - ✅ PWA completo
   - ✅ Service Worker com cache strategies
   - ✅ Firebase Cloud Messaging
   - ✅ Notificações Push funcionais
   - ✅ Design: Tailwind + Alpine.js

2. **SocialFlowIA** (`C:\laragon\www\socialflowia`)
   - ✅ Asaas 100% funcional
   - ✅ Design moderno: Tailwind + Alpine.js + Tremor
   - ✅ Formulários em etapas (wizard)
   - ✅ Botões com gradientes e efeitos
   - ✅ Checkout otimizado

3. **MediLife** (`C:\laragon\www\medlife`)
   - ✅ Asaas 100% funcional
   - ✅ PWA completo com shortcuts
   - ✅ Service Worker avançado
   - ✅ Firebase + Web Push (VAPID)
   - ✅ Sistema de notificações completo
   - ✅ Design responsivo premium

---

## 🎯 OBJETIVOS DO PROJETO

### Funcionalidades Principais

1. **Asaas (Gateway de Pagamento)**
   - PIX (instantâneo com QR Code)
   - Cartão de Crédito (tokenizado)
   - Boleto Bancário
   - Doações recorrentes (assinaturas)
   - Webhook automático
   - Split payment para criadores

2. **PWA (Progressive Web App)**
   - Instalável (Add to Home Screen)
   - Ícones em múltiplos tamanhos
   - Shortcuts para ações rápidas
   - Funciona offline (parcial)
   - Splash screen personalizada

3. **Service Worker**
   - Cache de recursos estáticos
   - Network-first para dados dinâmicos
   - Background sync
   - Offline fallback

4. **Firebase Cloud Messaging**
   - Notificações push em tempo real
   - Múltiplos tipos de notificação
   - Auto-refresh de páginas
   - Ações em notificações

5. **Sistema de Notificações**
   - Doação recebida → Notifica criador
   - Campanha aprovada → Notifica criador
   - Meta atingida → Notifica criador e doadores
   - Pagamento confirmado → Notifica doador

6. **Design Moderno**
   - Tailwind CSS 3.x
   - Alpine.js para interatividade
   - Tremor para dashboards
   - Botões com gradientes
   - Animações e transições
   - 100% responsivo

7. **Formulários em Etapas**
   - Criar campanha (wizard 4 etapas)
   - Fazer doação (wizard 3 etapas)
   - Registro de usuário (wizard 2 etapas)
   - Barra de progresso visual

---

## 🗃️ ESTRUTURA DO BANCO DE DADOS

### Tabelas Novas a Criar

#### 1. `fcm_tokens` - Tokens Firebase
```sql
CREATE TABLE `fcm_tokens` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(500) NOT NULL,
  `device_type` ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop',
  `user_agent` VARCHAR(500) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_used_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_is_active` (`is_active`),
  UNIQUE KEY `unique_user_token` (`user_id`, `token`(255)),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 2. `push_subscriptions` - Web Push (VAPID)
```sql
CREATE TABLE `push_subscriptions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `endpoint` VARCHAR(500) NOT NULL,
  `p256dh_key` VARCHAR(255) NOT NULL,
  `auth_token` VARCHAR(255) NOT NULL,
  `expiration_time` DATETIME NULL,
  `device_type` ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop',
  `user_agent` VARCHAR(500) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_is_active` (`is_active`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 3. `notifications` - Histórico de Notificações
```sql
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `campaign_id` INT UNSIGNED NULL,
  `donation_id` INT UNSIGNED NULL,
  `type` VARCHAR(50) NOT NULL COMMENT 'donation_received, campaign_approved, goal_reached, etc',
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `icon` VARCHAR(255) NULL,
  `url` VARCHAR(500) NULL,
  `data` JSON NULL COMMENT 'Dados adicionais',
  `channel` ENUM('push', 'email', 'sms', 'whatsapp') DEFAULT 'push',
  `status` ENUM('sent', 'failed', 'read') DEFAULT 'sent',
  `fcm_response` JSON NULL,
  `error_message` TEXT NULL,
  `read_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_type` (`type`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`donation_id`) REFERENCES `donations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 4. `asaas_accounts` - Contas Asaas dos Criadores
```sql
CREATE TABLE `asaas_accounts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `asaas_customer_id` VARCHAR(100) NOT NULL COMMENT 'ID do cliente no Asaas',
  `asaas_wallet_id` VARCHAR(100) NULL COMMENT 'ID da wallet/subconta',
  `cpf_cnpj` VARCHAR(18) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `mobile_phone` VARCHAR(20) NULL,
  `account_status` ENUM('active', 'inactive', 'pending') DEFAULT 'active',
  `api_response` JSON NULL COMMENT 'Resposta completa da API',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user` (`user_id`),
  UNIQUE KEY `unique_customer` (`asaas_customer_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 5. `asaas_transactions` - Transações Asaas
```sql
CREATE TABLE `asaas_transactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `donation_id` INT UNSIGNED NULL,
  `subscription_id` INT UNSIGNED NULL,
  `asaas_payment_id` VARCHAR(100) NOT NULL,
  `asaas_customer_id` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('pix', 'boleto', 'credit_card') NOT NULL,
  `status` ENUM('pending', 'confirmed', 'received', 'overdue', 'refunded', 'cancelled') DEFAULT 'pending',
  `webhook_data` JSON NULL COMMENT 'Dados completos do webhook',
  `processed_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_asaas_payment_id` (`asaas_payment_id`),
  INDEX `idx_donation_id` (`donation_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`donation_id`) REFERENCES `donations`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 6. `saved_cards` - Cartões Salvos (Tokenizados)
```sql
CREATE TABLE `saved_cards` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `asaas_card_token` VARCHAR(255) NOT NULL,
  `card_brand` VARCHAR(50) NULL COMMENT 'Visa, Mastercard, etc',
  `card_last_digits` VARCHAR(4) NULL,
  `card_holder_name` VARCHAR(255) NULL,
  `card_expiry_month` VARCHAR(2) NULL,
  `card_expiry_year` VARCHAR(4) NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Tabelas Existentes a Modificar

#### `users` - Adicionar Campos
```sql
ALTER TABLE `users`
ADD COLUMN `cpf_cnpj` VARCHAR(18) NULL AFTER `email`,
ADD COLUMN `phone` VARCHAR(20) NULL AFTER `cpf_cnpj`,
ADD COLUMN `mobile_phone` VARCHAR(20) NULL AFTER `phone`,
ADD COLUMN `cep` VARCHAR(10) NULL AFTER `mobile_phone`,
ADD COLUMN `address` VARCHAR(255) NULL AFTER `cep`,
ADD COLUMN `address_number` VARCHAR(20) NULL AFTER `address`,
ADD COLUMN `complement` VARCHAR(100) NULL AFTER `address_number`,
ADD COLUMN `neighborhood` VARCHAR(100) NULL AFTER `complement`,
ADD COLUMN `city` VARCHAR(100) NULL AFTER `neighborhood`,
ADD COLUMN `state` VARCHAR(2) NULL AFTER `city`,
ADD COLUMN `asaas_customer_id` VARCHAR(100) NULL AFTER `state`,
ADD COLUMN `notification_push` TINYINT(1) DEFAULT 1 AFTER `asaas_customer_id`,
ADD COLUMN `notification_email` TINYINT(1) DEFAULT 1 AFTER `notification_push`,
ADD INDEX `idx_asaas_customer_id` (`asaas_customer_id`);
```

#### `campaigns` - Adicionar Campos Asaas
```sql
ALTER TABLE `campaigns`
ADD COLUMN `asaas_split_percentage` DECIMAL(5,2) DEFAULT 1.00 COMMENT 'Percentual da plataforma (1% padrão)',
ADD COLUMN `creator_receives_percentage` DECIMAL(5,2) DEFAULT 99.00,
ADD COLUMN `asaas_wallet_id` VARCHAR(100) NULL COMMENT 'Wallet ID do criador';
```

#### `donations` - Adicionar Campos Detalhados
```sql
ALTER TABLE `donations`
ADD COLUMN `charged_amount` DECIMAL(10,2) NULL COMMENT 'Valor total cobrado (com taxas)',
ADD COLUMN `platform_fee` DECIMAL(10,2) NULL COMMENT 'Taxa da plataforma',
ADD COLUMN `payment_gateway_fee` DECIMAL(10,2) NULL COMMENT 'Taxa do gateway',
ADD COLUMN `net_amount` DECIMAL(10,2) NULL COMMENT 'Valor líquido para o criador',
ADD COLUMN `donor_pays_fees` TINYINT(1) DEFAULT 0 COMMENT 'Doador pagou as taxas?',
ADD COLUMN `pix_qr_code` TEXT NULL,
ADD COLUMN `pix_copy_paste` TEXT NULL,
ADD COLUMN `boleto_url` VARCHAR(500) NULL,
ADD COLUMN `boleto_barcode` VARCHAR(100) NULL,
ADD COLUMN `card_brand` VARCHAR(50) NULL,
ADD COLUMN `card_last_digits` VARCHAR(4) NULL,
ADD COLUMN `paid_at` DATETIME NULL,
ADD COLUMN `expires_at` DATETIME NULL COMMENT 'Validade do PIX/Boleto',
ADD COLUMN `metadata` JSON NULL;
```

---

## 📁 ESTRUTURA DE ARQUIVOS A CRIAR

```
doarfazbem/
├── app/
│   ├── Config/
│   │   ├── Firebase.php                    # ✅ Criar
│   │   └── Vapid.php                       # ✅ Criar
│   │
│   ├── Controllers/
│   │   ├── WebhookController.php           # ✅ Criar
│   │   ├── PushNotificationController.php  # ✅ Criar
│   │   ├── PWAController.php               # ✅ Criar
│   │   └── API/
│   │       ├── FCMController.php           # ✅ Criar
│   │       └── PushController.php          # ✅ Criar
│   │
│   ├── Libraries/
│   │   ├── AsaasService.php                # ✅ Criar (completo)
│   │   ├── FirebaseService.php             # ✅ Criar
│   │   └── Notifications/
│   │       ├── PushChannel.php             # ✅ Criar
│   │       └── NotificationManager.php     # ✅ Criar
│   │
│   ├── Models/
│   │   ├── FcmTokenModel.php               # ✅ Criar
│   │   ├── PushSubscriptionModel.php       # ✅ Criar
│   │   ├── NotificationModel.php           # ✅ Criar
│   │   ├── AsaasAccountModel.php           # ✅ Criar
│   │   ├── AsaasTransactionModel.php       # ✅ Criar
│   │   └── SavedCardModel.php              # ✅ Criar
│   │
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── app.php                     # ✅ Atualizar (Firebase init)
│   │   │   └── components/
│   │   │       ├── pwa-install-prompt.php  # ✅ Criar
│   │   │       └── notification-bell.php   # ✅ Criar
│   │   │
│   │   ├── auth/
│   │   │   ├── login.php                   # ✅ Redesign
│   │   │   ├── register.php                # ✅ Redesign (wizard)
│   │   │   └── forgot-password.php         # ✅ Redesign
│   │   │
│   │   ├── campaigns/
│   │   │   ├── create-wizard.php           # ✅ Criar (4 etapas)
│   │   │   ├── index.php                   # ✅ Redesign (cards)
│   │   │   └── view.php                    # ✅ Redesign
│   │   │
│   │   ├── donations/
│   │   │   ├── create-wizard.php           # ✅ Criar (3 etapas)
│   │   │   ├── payment-pix.php             # ✅ Criar
│   │   │   ├── payment-boleto.php          # ✅ Criar
│   │   │   ├── payment-card.php            # ✅ Criar
│   │   │   └── success.php                 # ✅ Redesign
│   │   │
│   │   └── dashboard/
│   │       ├── index.php                   # ✅ Redesign (Tremor)
│   │       ├── notifications.php           # ✅ Criar
│   │       └── settings.php                # ✅ Adicionar notif. settings
│   │
│   └── Database/
│       └── Migrations/
│           ├── 2025-11-05-000001_CreateFcmTokensTable.php
│           ├── 2025-11-05-000002_CreatePushSubscriptionsTable.php
│           ├── 2025-11-05-000003_CreateNotificationsTable.php
│           ├── 2025-11-05-000004_CreateAsaasAccountsTable.php
│           ├── 2025-11-05-000005_CreateAsaasTransactionsTable.php
│           ├── 2025-11-05-000006_CreateSavedCardsTable.php
│           ├── 2025-11-05-000007_AlterUsersAddAsaasFields.php
│           ├── 2025-11-05-000008_AlterCampaignsAddAsaasFields.php
│           └── 2025-11-05-000009_AlterDonationsAddAsaasFields.php
│
├── public/
│   ├── manifest.json                       # ✅ Criar
│   ├── sw.js                              # ✅ Criar (Service Worker)
│   ├── firebase-messaging-sw.js           # ✅ Criar
│   │
│   ├── assets/
│   │   ├── icons/                         # ✅ Criar (8 tamanhos)
│   │   │   ├── icon-72x72.png
│   │   │   ├── icon-96x96.png
│   │   │   ├── icon-128x128.png
│   │   │   ├── icon-144x144.png
│   │   │   ├── icon-152x152.png
│   │   │   ├── icon-192x192.png
│   │   │   ├── icon-384x384.png
│   │   │   ├── icon-512x512.png
│   │   │   └── badge-72x72.png
│   │   │
│   │   ├── css/
│   │   │   ├── input.css                  # ✅ Já existe (Tailwind)
│   │   │   └── output.css                 # ✅ Recompilar
│   │   │
│   │   └── js/
│   │       ├── firebase-init.js           # ✅ Criar
│   │       ├── push-notifications.js      # ✅ Criar
│   │       ├── pwa-install.js             # ✅ Criar
│   │       └── alpine-components.js       # ✅ Criar
│   │
│   └── firebase-credentials.json          # ✅ Criar (gitignore)
│
├── .env                                    # ✅ Atualizar (vars Firebase/Asaas)
├── tailwind.config.js                     # ✅ Atualizar (cores/gradientes)
└── package.json                           # ✅ Adicionar Tremor
```

---

## 🚀 FASES DE IMPLEMENTAÇÃO

### **FASE 1: PREPARAÇÃO DO AMBIENTE** (2 horas)

#### 1.1. Atualizar Dependências
```bash
# Composer
composer require google/auth
composer require minishlink/web-push

# NPM
npm install @tremor/react recharts
npm run build
```

#### 1.2. Configurar Firebase
- Criar projeto no Firebase Console
- Baixar `firebase-credentials.json`
- Gerar VAPID keys
- Configurar `.env`

#### 1.3. Configurar Asaas
- Verificar credenciais sandbox/produção
- Configurar webhook URL
- Testar conexão API

#### 1.4. Criar Migrations
- Executar todas as 9 migrations
- Verificar foreign keys
- Popular dados de teste

**Resultado Esperado:** ✅ Ambiente configurado e banco de dados atualizado

---

### **FASE 2: INTEGRAÇÃO ASAAS** (6 horas)

#### 2.1. AsaasService.php (Library)
Copiar de `cantina/app/Libraries/AsaasService.php` e adaptar:
- Métodos de cliente (create, update, get)
- Métodos de pagamento (PIX, Boleto, Cartão)
- Métodos de cobrança recorrente
- Split payment para criadores
- Tokenização de cartão

#### 2.2. WebhookController.php
Copiar de `medlife/app/Controllers/WebhookController.php`:
- Validação de token
- Processamento de eventos
- Atualização de status de doações
- Envio de notificações

#### 2.3. Adaptar DonationController
- Integrar AsaasService
- Criar cliente no Asaas
- Gerar PIX/Boleto/Cartão
- Salvar dados da transação

#### 2.4. Views de Pagamento
- `payment-pix.php` (QR Code + copia-cola)
- `payment-boleto.php` (Download PDF)
- `payment-card.php` (Formulário tokenizado)

**Resultado Esperado:** ✅ Pagamentos funcionando 100%

---

### **FASE 3: PWA (Progressive Web App)** (3 horas)

#### 3.1. manifest.json
Copiar de `medlife/public/manifest.json`:
- Nome e descrição
- Cores (theme/background)
- Ícones (8 tamanhos)
- Shortcuts
- Screenshots

#### 3.2. Gerar Ícones PWA
Usar ferramenta online (pwa-asset-generator):
```bash
npx pwa-asset-generator logo.png public/assets/icons
```

#### 3.3. PWAController.php
- Servir manifest dinamicamente
- Registrar service worker
- Status PWA

#### 3.4. Adicionar no Layout
```html
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#22c55e">
<link rel="apple-touch-icon" href="/assets/icons/icon-192x192.png">
```

**Resultado Esperado:** ✅ App instalável no mobile/desktop

---

### **FASE 4: SERVICE WORKER** (4 horas)

#### 4.1. sw.js (Service Worker Principal)
Copiar de `medlife/public/sw.js`:
- Cache de recursos estáticos
- Network-first para APIs
- Fallback offline
- Push notification handler
- Background sync

#### 4.2. Estratégias de Cache
```javascript
// Cache-first (imagens, CSS, JS)
STATIC_CACHE = [
  '/',
  '/assets/css/output.css',
  '/assets/js/app.js',
  '/assets/icons/icon-192x192.png'
]

// Network-first (dados dinâmicos)
NETWORK_FIRST = [
  '/api/',
  '/dashboard',
  '/campaigns',
  '/donations'
]
```

#### 4.3. Offline Fallback
Criar página offline.html

**Resultado Esperado:** ✅ App funciona offline (parcial)

---

### **FASE 5: FIREBASE CLOUD MESSAGING** (5 horas)

#### 5.1. FirebaseService.php
Copiar de `cantina/app/Libraries/FirebaseService.php`:
- Autenticação OAuth 2.0
- Geração de JWT
- Envio para token
- Envio para usuário
- Envio para múltiplos
- Histórico de notificações

#### 5.2. firebase-messaging-sw.js
Copiar de `medlife/public/firebase-messaging-sw.js`:
- Configuração Firebase
- Background messages
- Notification click
- Auto-refresh

#### 5.3. firebase-init.js (Frontend)
```javascript
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';

// Inicializar
// Solicitar permissão
// Obter token
// Listener de mensagens foreground
```

#### 5.4. FCMController (API)
- `POST /api/fcm/save-token`
- `POST /api/fcm/deactivate-token`
- `POST /api/fcm/test`

**Resultado Esperado:** ✅ Notificações push funcionando

---

### **FASE 6: SISTEMA DE NOTIFICAÇÕES** (4 horas)

#### 6.1. NotificationManager.php
Centralizar envio de notificações:
```php
NotificationManager::send($userId, [
    'type' => 'donation_received',
    'title' => 'Nova Doação!',
    'body' => 'Você recebeu R$ 100,00',
    'url' => '/dashboard/donations',
    'data' => ['donation_id' => 123]
]);
```

#### 6.2. Integrar nos Controllers
- **DonationController** → Doação recebida
- **CampaignController** → Campanha aprovada, meta atingida
- **WebhookController** → Pagamento confirmado

#### 6.3. PushNotificationController
- Listar notificações
- Marcar como lida
- Deletar notificação
- Contador não lidas

#### 6.4. View: notifications.php
- Lista de notificações
- Badge com contador
- Ícone de sino (Alpine.js dropdown)

**Resultado Esperado:** ✅ Notificações em tempo real

---

### **FASE 7: DESIGN MODERNO** (8 horas)

#### 7.1. Atualizar tailwind.config.js
```javascript
theme: {
  extend: {
    colors: {
      primary: {
        50: '#f0fdf4',
        500: '#22c55e',
        600: '#16a34a',
        700: '#15803d'
      }
    },
    backgroundImage: {
      'gradient-primary': 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)',
      'gradient-hero': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
    }
  }
}
```

#### 7.2. Redesign: Login/Register
Copiar de `socialflowia/app/Views/auth/`:
- Gradientes de fundo
- Cards com sombra
- Ícones nos inputs
- Botões com hover effects
- OAuth (Google)

#### 7.3. Redesign: Homepage
- Hero section com gradiente
- Cards de campanhas modernos
- Animações smooth
- CTA buttons destacados

#### 7.4. Componentes Alpine.js
- Modais
- Dropdowns
- Tabs
- Toast notifications
- Loading states

#### 7.5. Botões com Gradientes
```html
<button class="bg-gradient-to-r from-primary-500 to-primary-600
               text-white font-bold py-3 px-6 rounded-xl
               shadow-lg hover:shadow-xl
               transform hover:-translate-y-0.5
               transition-all duration-300">
  Doar Agora
</button>
```

**Resultado Esperado:** ✅ Interface moderna e atraente

---

### **FASE 8: FORMULÁRIOS EM ETAPAS** (6 horas)

#### 8.1. Criar Campanha (Wizard 4 Etapas)
```
Etapa 1: Informações Básicas
  - Título, descrição
  - Categoria, tipo

Etapa 2: Meta e Prazo
  - Valor da meta
  - Data de término
  - Tipo de campanha

Etapa 3: Mídia
  - Upload de imagem
  - URL de vídeo (opcional)

Etapa 4: Revisão
  - Preview da campanha
  - Confirmar e publicar
```

#### 8.2. Fazer Doação (Wizard 3 Etapas)
```
Etapa 1: Valor e Opções
  - Valor da doação
  - Doação anônima?
  - Doador paga taxas?
  - Mensagem (opcional)

Etapa 2: Dados Pessoais
  - Nome, email, CPF
  - Telefone
  - (se não logado)

Etapa 3: Pagamento
  - Escolher método (PIX/Boleto/Cartão)
  - Preencher dados
  - Confirmar
```

#### 8.3. Componente Wizard Base
```javascript
function wizard() {
  return {
    currentStep: 1,
    totalSteps: 4,
    form: {},
    nextStep() { /* validar e avançar */ },
    prevStep() { /* voltar */ },
    get progress() { return (this.currentStep / this.totalSteps) * 100; }
  }
}
```

**Resultado Esperado:** ✅ UX otimizada para conversão

---

### **FASE 9: DASHBOARDS COM TREMOR** (4 horas)

#### 9.1. Instalar Tremor
```bash
npm install @tremor/react recharts
```

#### 9.2. Componentes Tremor
- Metric Cards (total arrecadado, doações, campanhas)
- Chart (doações por mês)
- Table (últimas doações)
- Badge (status)
- ProgressBar (progresso de campanhas)

#### 9.3. Redesign Dashboard
Copiar estrutura de `socialflowia`:
- Grid responsivo
- Cards com estatísticas
- Gráficos interativos
- Tabela de atividades recentes

**Resultado Esperado:** ✅ Dashboard profissional

---

### **FASE 10: CONFIGURAÇÃO DE DOMÍNIOS** (2 horas)

#### 10.1. Localhost (HTTPS)
Configurar `doarfazbem.ai`:
- Criar virtual host no Laragon
- Certificado SSL local
- Atualizar `.env`

#### 10.2. Ngrok (Testes Externos)
```bash
ngrok http 80 --domain=doarfazbem.ngrok.app
```
- Atualizar webhook Asaas
- Configurar Firebase authorized domains
- Testar notificações push

#### 10.3. Produção
Preparar para `doarfazbem.com.br`:
- Template `.env.production`
- Documentação de deploy
- Checklist de segurança

**Resultado Esperado:** ✅ Funcionando nos 3 ambientes

---

### **FASE 11: TESTES AUTOMATIZADOS** (6 horas)

#### 11.1. Criar Agentes de Teste

**Agente 1: Teste de Pagamento PIX**
```php
class PagamentoPixTest extends TestCase
{
    public function testCriarPagamentoPix() { /* ... */ }
    public function testWebhookConfirmarPagamento() { /* ... */ }
    public function testNotificarCriador() { /* ... */ }
}
```

**Agente 2: Teste de Notificações**
```php
class NotificacoesTest extends TestCase
{
    public function testEnviarNotificacaoPush() { /* ... */ }
    public function testSalvarToken() { /* ... */ }
    public function testMarcarComoLida() { /* ... */ }
}
```

**Agente 3: Teste PWA**
```javascript
describe('PWA', () => {
  it('deve registrar service worker', async () => { /* ... */ });
  it('deve funcionar offline', async () => { /* ... */ });
  it('deve cachear recursos', async () => { /* ... */ });
});
```

**Agente 4: Teste de Formulários**
```javascript
describe('Wizard Doação', () => {
  it('deve validar etapa 1', () => { /* ... */ });
  it('deve avançar para etapa 2', () => { /* ... */ });
  it('deve calcular taxas corretamente', () => { /* ... */ });
});
```

#### 11.2. Executar Testes
```bash
# PHP (CodeIgniter)
php spark test

# JavaScript (Jest/Cypress)
npm run test
npm run test:e2e
```

**Resultado Esperado:** ✅ 100% dos testes passando

---

## 📊 CRONOGRAMA

| Fase | Duração | Responsável | Status |
|------|---------|-------------|--------|
| 1. Preparação do Ambiente | 2h | Dev | ⏳ Pendente |
| 2. Integração Asaas | 6h | Dev | ⏳ Pendente |
| 3. PWA | 3h | Dev | ⏳ Pendente |
| 4. Service Worker | 4h | Dev | ⏳ Pendente |
| 5. Firebase | 5h | Dev | ⏳ Pendente |
| 6. Notificações | 4h | Dev | ⏳ Pendente |
| 7. Design Moderno | 8h | Dev | ⏳ Pendente |
| 8. Formulários Wizard | 6h | Dev | ⏳ Pendente |
| 9. Dashboards Tremor | 4h | Dev | ⏳ Pendente |
| 10. Configuração Domínios | 2h | Dev | ⏳ Pendente |
| 11. Testes Automatizados | 6h | Dev/QA | ⏳ Pendente |
| **TOTAL** | **50 horas** | | **~2 semanas** |

---

## ✅ CHECKLIST FINAL

### Funcionalidades
- [ ] Asaas: PIX funcionando
- [ ] Asaas: Boleto funcionando
- [ ] Asaas: Cartão funcionando
- [ ] Asaas: Webhook processando
- [ ] Asaas: Split payment ativo
- [ ] PWA: Instalável
- [ ] PWA: Funciona offline
- [ ] Service Worker: Cache funcionando
- [ ] Firebase: Notificações push
- [ ] Notificações: Em tempo real
- [ ] Notificações: Histórico
- [ ] Design: Login/Register moderno
- [ ] Design: Homepage redesenhada
- [ ] Design: Dashboard Tremor
- [ ] Wizard: Criar campanha
- [ ] Wizard: Fazer doação
- [ ] Responsivo: Mobile
- [ ] Responsivo: Tablet
- [ ] Responsivo: Desktop

### Segurança
- [ ] CSRF protection
- [ ] XSS sanitization
- [ ] SQL injection prevention
- [ ] HTTPS obrigatório
- [ ] Webhook validation
- [ ] Rate limiting

### Performance
- [ ] Lazy loading de imagens
- [ ] Minificação CSS/JS
- [ ] Cache de assets
- [ ] Database indexing
- [ ] Query optimization

### Testes
- [ ] Testes unitários (backend)
- [ ] Testes de integração
- [ ] Testes E2E (frontend)
- [ ] Testes de carga
- [ ] Testes cross-browser

### Documentação
- [ ] README.md atualizado
- [ ] Documentação API
- [ ] Guia de deploy
- [ ] Variáveis .env documentadas
- [ ] Changelog

---

## 🎯 MÉTRICAS DE SUCESSO

### KPIs Técnicos
- **Performance:** Lighthouse Score > 90
- **PWA:** Instalável e funcional
- **Uptime:** > 99.5%
- **Tempo de resposta API:** < 200ms
- **Notificações:** Taxa de entrega > 95%

### KPIs de Negócio
- **Taxa de conversão doações:** > 10%
- **Doações via PIX:** > 60%
- **Usuários retornando:** > 40%
- **NPS:** > 50

---

## 📞 CONTATOS E RECURSOS

### Documentação Oficial
- [Asaas API](https://docs.asaas.com)
- [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging)
- [Service Worker](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Alpine.js](https://alpinejs.dev)
- [Tremor](https://www.tremor.so/docs)

### Ferramentas Úteis
- [PWA Asset Generator](https://github.com/elegantapp/pwa-asset-generator)
- [VAPID Key Generator](https://vapidkeys.com/)
- [Ngrok](https://ngrok.com/)
- [Postman](https://www.postman.com/)

---

**Última Atualização:** 2025-11-05
**Versão do Documento:** 1.0
**Status:** 📋 Planejamento Completo - Pronto para Implementação
