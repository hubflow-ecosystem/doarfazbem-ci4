# 💚 DOAR FAZ BEM - Plataforma de Crowdfunding Social

> A plataforma de crowdfunding mais justa do Brasil, com foco em campanhas sociais e médicas gratuitas.

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://www.php.net/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-EF4223?logo=codeigniter)](https://codeigniter.com/)
[![TailwindCSS](https://img.shields.io/badge/Tailwind-3.x-06B6D4?logo=tailwindcss)](https://tailwindcss.com/)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?logo=alpinedotjs)](https://alpinejs.dev/)
[![Tremor](https://img.shields.io/badge/Tremor-Latest-6366F1)](https://tremor.so/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Status Atual](#status-atual)
- [Diferenciais Competitivos](#diferenciais-competitivos)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Pré-requisitos](#pré-requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Banco de Dados](#banco-de-dados)
- [Funcionalidades](#funcionalidades)
- [Integração Asaas](#integração-asaas---sistema-de-pagamentos)
- [Frontend Stack](#frontend-stack)
- [Roadmap de Desenvolvimento](#roadmap-de-desenvolvimento)
- [Documentação](#documentação)
- [Contribuindo](#contribuindo)
- [Suporte](#suporte)
- [Licença](#licença)

---

## 🎯 Sobre o Projeto

**DoarFazBem.com.br** é uma plataforma de crowdfunding social desenvolvida para democratizar o acesso a doações solidárias no Brasil. Com foco em **transparência total** e **taxas justas**, permitimos que pessoas e instituições criem campanhas de arrecadação de forma simples e eficiente.

### Nossa Missão

Tornar o ato de doar mais acessível, transparente e eficaz, conectando quem precisa de ajuda com quem pode ajudar, sem intermediários abusivos.

### Público-Alvo

- **Primário**: Pessoas com necessidades médicas urgentes
- **Secundário**: Projetos sociais e ONGs
- **Terciário**: Projetos criativos e empresariais
- **Apoiadores**: Pessoas físicas e jurídicas com perfil solidário

---

## 📊 Status Atual

### ✅ MVP Implementado (~100% Completo)

**Versão Atual**: 1.0.0-beta

**Data de Implementação**: Janeiro 2025

#### Funcionalidades Implementadas

##### Autenticação e Usuários
- ✅ Sistema de registro com validação
- ✅ Login/Logout
- ✅ Recuperação de senha
- ✅ Verificação de email (desabilitada temporariamente para testes locais)
- ✅ Perfil de usuário
- ✅ Google reCAPTCHA v3 (desabilitado para ambiente local)

##### Campanhas
- ✅ Criar campanhas (médicas, sociais, criativas, emergenciais)
- ✅ Upload de imagens
- ✅ Tipos de campanha: Flexível, Tudo ou Nada, Recorrente
- ✅ Listagem de campanhas com filtros
- ✅ Página individual de campanha
- ✅ Status: Rascunho, Ativa, Pausada, Concluída
- ✅ Sistema de categorias

##### Doações
- ✅ Doações únicas (PIX, Cartão, Boleto)
- ✅ Doações recorrentes (assinaturas mensais)
- ✅ Cálculo dinâmico de taxas
- ✅ Opção "Doador paga taxas"
- ✅ Doações anônimas
- ✅ Mensagens para o criador

##### Integração Asaas (Payment Gateway)
- ✅ API Asaas totalmente integrada
- ✅ Split Payment automático
- ✅ Criação de subcontas para criadores
- ✅ Tratamento de CPF duplicado
- ✅ Webhooks para notificações
- ✅ Validação de saques
- ✅ Ambiente Sandbox configurado

##### Dashboard
- ✅ Dashboard do criador de campanhas
- ✅ Dashboard do doador
- ✅ Minhas campanhas
- ✅ Minhas doações
- ✅ Estatísticas em tempo real
- ✅ Histórico de transações

##### Sistema de Email
- ✅ Envio de emails transacionais
- ✅ Templates responsivos
- ✅ Notificações de doação
- ✅ Confirmação de cadastro
- ✅ Recuperação de senha

##### Configurações e Segurança
- ✅ Variáveis de ambiente (.env)
- ✅ Proteção CSRF
- ✅ Sanitização de inputs
- ✅ Password hashing (bcrypt)
- ✅ Logs de auditoria
- ✅ SSL/HTTPS ready

#### Correções Recentes (07/10/2025)

##### Correções do Dashboard
- ✅ **Nomes de classes dos models** - Corrigido imports (Campaign → CampaignModel, etc)
- ✅ **Coluna de status** - Corrigido `payment_status` → `status`
- ✅ **Métodos dos models** - Corrigido `getByUser()` → `getUserDonations()`
- ✅ **Chave da sessão** - Corrigido `session->get('user_id')` → `session->get('id')` em todos os controllers
- ✅ **Campo de verificação** - Corrigido `email_verified_at` → `email_verified` na view de perfil

##### Sistema de Subcontas Asaas
- ✅ Detecção e vinculação de CPF duplicado
- ✅ Reutilização de subconta em múltiplas campanhas
- ✅ Busca automática de contas existentes

##### Usuários de Teste Criados
- ✅ 1 super admin + 5 usuários regulares
- ✅ Todos com email verificado
- ✅ Dados completos (CPF, telefone, endereço)

**Documentação**: Ver [CORRECOES_DASHBOARD.md](CORRECOES_DASHBOARD.md)

---

## ✨ Diferenciais Competitivos

| Característica | DoarFazBem | Concorrentes |
|----------------|------------|--------------|
| **Campanhas Médicas/Sociais** | ✅ **0% de taxa** | 5-13% de taxa |
| **Outras Campanhas** | ✅ **1% de taxa** | 5-13% de taxa |
| **Doador paga taxas** | ✅ **Opcional** | Não disponível |
| **Sistema "Tudo ou Tudo"** | ✅ **Inovador** | Limitado |
| **Transparência Total** | ✅ **100%** | Parcial |
| **Integração WhatsApp** | 🚧 **Em desenvolvimento** | Via terceiros |
| **Sem Mensalidade** | ✅ **Gratuito** | Alguns cobram |
| **Split Payment** | ✅ **Automático** | Manual |

---

## 🛠️ Tecnologias Utilizadas

### Backend
- **PHP 8.2+** - Linguagem de programação moderna
- **CodeIgniter 4.6.3** - Framework MVC leve e rápido
- **MySQL 8.0** - Banco de dados relacional
- **Composer** - Gerenciador de dependências PHP

### Frontend Stack Moderno

#### Framework CSS
- **Tailwind CSS 3.x** - Framework CSS utility-first
  - Design system customizado
  - Modo dark/light (planejado)
  - Componentes reutilizáveis
  - Configuração personalizada para DoarFazBem

#### JavaScript Reativo
- **Alpine.js 3.x** - Framework JavaScript reativo e leve (15kb)
  - Reatividade declarativa
  - Componentes interativos
  - Alternativa leve ao Vue/React
  - Perfeito para aplicações server-side
  - Sintaxe familiar (Vue-like)

#### UI Components Library
- **Tremor** - Biblioteca de componentes React/Vue para dashboards
  - Gráficos e charts modernos
  - Componentes de analytics
  - Cards estatísticos
  - Tabelas avançadas
  - Design system profissional
  - Totalmente compatível com Tailwind

#### Outras Bibliotecas Frontend
- **Chart.js** - Gráficos e visualizações
- **Font Awesome 6** - Ícones
- **Google Fonts** - Tipografia (Inter, Poppins)

### Integrações

#### Payment Gateway
- **API Asaas** - Gateway de pagamento brasileiro
  - PIX instantâneo
  - Cartão de crédito (à vista e parcelado)
  - Boleto bancário
  - Doações recorrentes (assinaturas)
  - Split Payment (divisão automática)
  - Criação de subcontas
  - Webhooks para notificações

#### Google Services
- **Google Analytics 4** (G-9SWBDMBQL6)
- **Google Tag Manager** (GT-P8452X3)
- **Google Maps Geocoding API**
- **Google reCAPTCHA v3** (proteção anti-spam)

#### Comunicação (Planejado)
- **WhatsApp Business API** - Notificações automáticas
- **SendGrid/Mailgun** - Emails transacionais

#### CMS (Planejado)
- **WordPress REST API** - Integração com blog

### Infraestrutura

#### Servidor
- **Hetzner Cloud VPS**
  - Ubuntu 22.04 LTS
  - CloudPanel (gerenciamento)
  - SSL: Let's Encrypt
  - CDN: Cloudflare

#### Ambiente Local
- **Laragon** (Windows)
  - Apache/Nginx
  - PHP 8.3.16
  - MySQL 8.0
  - Node.js 18+

---

## 📋 Pré-requisitos

### Ambiente de Desenvolvimento (Local)

#### Software Necessário
- **Laragon** (ou XAMPP/WAMP/MAMP)
  - PHP 8.2 ou superior
  - MySQL 8.0 ou superior
  - Apache ou Nginx
- **Composer** (gerenciador de dependências PHP)
- **Node.js 18+** e **npm** (para Tailwind CSS)
- **Git** (versionamento)

#### Extensões PHP Necessárias

```bash
# Verifique se estão habilitadas no php.ini:
extension=intl
extension=mbstring
extension=json
extension=mysqlnd
extension=curl
extension=fileinfo
extension=openssl
extension=pdo_mysql
```

#### Verificar Requisitos

```bash
# Verificar versão do PHP
php -v  # Deve ser 8.2+

# Verificar extensões instaladas
php -m

# Verificar Composer
composer --version

# Verificar Node.js
node -v  # Deve ser 18+
npm -v
```

### Ferramentas Recomendadas

- **Visual Studio Code** (editor de código)
  - Extensões recomendadas:
    - PHP Intelephense
    - Tailwind CSS IntelliSense
    - Alpine.js IntelliSense
    - GitLens
- **Thunder Client** ou **Postman** (testar APIs)
- **HeidiSQL** ou **MySQL Workbench** (gerenciar banco)

---

## 🚀 Instalação

### Passo a Passo Completo

#### 1. Clone o Repositório

```bash
git clone https://github.com/seu-usuario/doarfazbem.git
cd doarfazbem
```

#### 2. Instale as Dependências PHP

```bash
composer install
```

#### 3. Instale as Dependências Node.js

```bash
npm install
```

Isso instalará:
- Tailwind CSS
- Alpine.js
- PostCSS
- Autoprefixer
- Outras dependências do frontend

#### 4. Configure o Arquivo de Ambiente

```bash
# Windows
copy env .env

# Linux/Mac
cp env .env
```

Edite o arquivo `.env` com suas credenciais (veja seção [Configuração](#configuração))

#### 5. Gere a Chave de Criptografia

```bash
php spark key:generate
```

#### 6. Crie o Banco de Dados

```sql
-- No MySQL (HeidiSQL, phpMyAdmin ou linha de comando)
CREATE DATABASE doarfazbem CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 7. Execute as Migrations

```bash
php spark migrate
```

Isso criará todas as tabelas necessárias:
- users
- campaigns
- donations
- subscriptions
- asaas_accounts
- email_verifications
- password_resets
- campaign_updates
- analytics_events
- advertisements

#### 8. (Opcional) Execute os Seeders

```bash
php spark db:seed DatabaseSeeder
```

Isso populará o banco com dados de exemplo.

#### 9. Compile o Tailwind CSS

**Para desenvolvimento (com watch):**
```bash
npm run watch
```

**Para produção (minificado):**
```bash
npm run build
```

#### 10. Configure o Virtual Host

**Laragon (Recomendado):**
1. Coloque o projeto em `C:\laragon\www\doarfazbem`
2. Laragon criará automaticamente: `http://doarfazbem.test`

**Ou use o servidor embutido do CodeIgniter:**
```bash
php spark serve
```
Acesse: `http://localhost:8080`

#### 11. Limpe o Cache (se necessário)

```bash
php spark cache:clear
```

---

## ⚙️ Configuração

### Arquivo `.env`

Edite o arquivo `.env` com suas configurações:

```bash
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = development  # production quando for deploy

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'http://doarfazbem.test/'
app.indexPage = ''
app.defaultLocale = 'pt-BR'
app.supportedLocales = ['pt-BR']

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = doarfazbem
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------
# Gerada automaticamente com: php spark key:generate
encryption.key = base64:SUA_CHAVE_AQUI

#--------------------------------------------------------------------
# EMAIL (Configure seu provedor SMTP)
#--------------------------------------------------------------------
email.protocol = smtp
email.SMTPHost = smtp.sendgrid.net
email.SMTPPort = 587
email.SMTPUser = apikey
email.SMTPPass = SG.xxxxxx
email.fromEmail = noreply@doarfazbem.com.br
email.fromName = DoarFazBem
email.mailType = html

#--------------------------------------------------------------------
# ASAAS API (Gateway de Pagamento)
#--------------------------------------------------------------------
# Ambiente: sandbox (testes) ou production (produção)
ASAAS_ENVIRONMENT = sandbox
ASAAS_API_URL_SANDBOX = https://api-sandbox.asaas.com/
ASAAS_API_URL_PRODUCTION = https://api.asaas.com/

# Credenciais Sandbox (Testes)
ASAAS_API_KEY_SANDBOX = $aact_hmlg_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6Ojk0OWRlOTkwLWJhNmYtNDA5Yy1iNWM4LWYxNzQyODAyOGEyZTo6JGFhY2hfYzE3YzNhNTItYTg1ZS00NmQzLWEwYjAtYjNlZTk0MWRjYzNj
ASAAS_WALLET_ID_SANDBOX = 8e3acaa3-5040-436c-83fc-cff9b8c1b326

# Credenciais Produção (preencher quando obter)
ASAAS_API_KEY_PRODUCTION =
ASAAS_WALLET_ID_PRODUCTION =

# Webhook para notificações do Asaas
ASAAS_WEBHOOK_URL = http://doarfazbem.test/webhook/asaas
ASAAS_WEBHOOK_EMAIL = solucoesninja.com.br@gmail.com

#--------------------------------------------------------------------
# GOOGLE SERVICES
#--------------------------------------------------------------------
# reCAPTCHA v3 (desabilitado para testes locais)
GOOGLE_RECAPTCHA_SITE_KEY =
GOOGLE_RECAPTCHA_SECRET_KEY =
GOOGLE_RECAPTCHA_SCORE_THRESHOLD = 0.0

# Analytics
GA_MEASUREMENT_ID = G-9SWBDMBQL6
GTM_ID = GT-P8452X3

# Maps API
GOOGLE_MAPS_API_KEY =

#--------------------------------------------------------------------
# WHATSAPP BUSINESS API (Planejado)
#--------------------------------------------------------------------
WHATSAPP_PHONE_ID =
WHATSAPP_ACCESS_TOKEN =

#--------------------------------------------------------------------
# SOCIAL MEDIA
#--------------------------------------------------------------------
FACEBOOK_APP_ID =
INSTAGRAM_ACCESS_TOKEN =
```

### Configuração do Tailwind CSS

O arquivo `tailwind.config.js` já está configurado com:

```javascript
module.exports = {
  content: [
    "./app/Views/**/*.php",
    "./public/assets/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0fdf4',
          100: '#dcfce7',
          500: '#22c55e',
          600: '#16a34a',
          700: '#15803d',
        },
        secondary: {
          500: '#06b6d4',
          600: '#0891b2',
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Poppins', 'sans-serif'],
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ]
}
```

### Scripts NPM Disponíveis

```json
{
  "scripts": {
    "dev": "npx tailwindcss -i ./public/assets/css/input.css -o ./public/assets/css/output.css --watch",
    "build": "npx tailwindcss -i ./public/assets/css/input.css -o ./public/assets/css/output.css --minify",
    "watch": "npm run dev"
  }
}
```

**Uso:**
```bash
# Desenvolvimento (com watch)
npm run dev

# Produção (minificado)
npm run build
```

---

## 📁 Estrutura do Projeto

```
doarfazbem/
├── app/
│   ├── Config/              # Configurações do CodeIgniter
│   │   ├── App.php
│   │   ├── Database.php
│   │   ├── Routes.php       # Rotas da aplicação
│   │   ├── Asaas.php        # Config API Asaas
│   │   └── Google.php       # Config Google Services
│   ├── Controllers/         # Controllers MVC
│   │   ├── Home.php         # Homepage
│   │   ├── Campaign.php     # CRUD campanhas
│   │   ├── Donation.php     # Sistema de doações
│   │   ├── User.php         # Autenticação e perfil
│   │   ├── Dashboard.php    # Dashboards
│   │   ├── Webhook.php      # Webhooks Asaas
│   │   └── Admin.php        # Painel admin
│   ├── Models/              # Models do banco de dados
│   │   ├── UserModel.php
│   │   ├── CampaignModel.php
│   │   ├── DonationModel.php
│   │   ├── Subscription.php
│   │   ├── AsaasAccount.php
│   │   └── ...
│   ├── Views/               # Views (HTML + Alpine.js)
│   │   ├── layout/
│   │   │   ├── app.php      # Layout base
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── home/
│   │   │   └── index.php    # Homepage
│   │   ├── campaigns/
│   │   │   ├── index.php    # Listagem
│   │   │   ├── create.php   # Criar campanha
│   │   │   └── view.php     # Ver campanha
│   │   ├── donations/
│   │   │   └── create.php   # Página de doação
│   │   ├── dashboard/
│   │   │   ├── index.php    # Dashboard principal
│   │   │   ├── my_campaigns.php
│   │   │   └── my_donations.php
│   │   ├── user/
│   │   │   ├── login.php
│   │   │   ├── register.php
│   │   │   └── profile.php
│   │   ├── admin/
│   │   │   └── dashboard.php
│   │   └── emails/          # Templates de email
│   ├── Libraries/           # Bibliotecas customizadas
│   │   ├── AsaasLibrary.php # API Asaas
│   │   └── WhatsAppLibrary.php (planejado)
│   ├── Helpers/             # Helper functions
│   │   ├── auth_helper.php
│   │   └── campaign_helper.php
│   └── Database/
│       └── Migrations/      # Migrations do banco
├── public/                  # Pasta pública (root do servidor)
│   ├── index.php            # Entry point
│   ├── .htaccess            # Rewrite rules
│   ├── assets/
│   │   ├── css/
│   │   │   ├── input.css    # Input do Tailwind
│   │   │   └── output.css   # Output compilado
│   │   ├── js/
│   │   │   ├── app.js       # JavaScript principal
│   │   │   └── alpine-components.js
│   │   └── images/
│   └── uploads/             # Uploads de usuários
│       ├── campaigns/       # Imagens de campanhas
│       └── profiles/        # Avatars de usuários
├── writable/                # Cache, logs, sessions
│   ├── cache/
│   ├── logs/
│   └── session/
├── vendor/                  # Dependências Composer
├── node_modules/            # Dependências NPM
├── .env                     # Variáveis de ambiente (NÃO commitar)
├── .gitignore
├── composer.json            # Dependências PHP
├── composer.lock
├── package.json             # Dependências Node.js
├── package-lock.json
├── tailwind.config.js       # Config do Tailwind
├── postcss.config.js        # Config do PostCSS
├── README.md                # Este arquivo
├── CORRECOES_DASHBOARD.md   # Log de correções
├── CREDENCIAIS_ACESSO.md    # Credenciais de teste
└── LICENSE
```

---

## 🗄️ Banco de Dados

### Principais Tabelas

#### Tabela: `users`
Armazena os usuários da plataforma (criadores e doadores).

```sql
- id (PK)
- name
- email (unique)
- google_id (para login Google)
- phone
- cpf
- password_hash
- asaas_customer_id
- asaas_wallet_id
- email_verified (boolean)
- role (enum: user, admin)
- avatar
- created_at, updated_at
```

#### Tabela: `campaigns`
Armazena as campanhas de arrecadação.

```sql
- id (PK)
- user_id (FK → users)
- title
- slug (unique)
- description (text)
- goal_amount (decimal)
- raised_amount (decimal, atualizado via webhook)
- category (enum: medical, social, creative, emergency, etc)
- campaign_type (enum: flexible, all_or_nothing, recurring)
- image
- video_url
- end_date
- status (enum: draft, active, paused, completed, cancelled)
- created_at, updated_at
```

#### Tabela: `donations`
Armazena as doações realizadas.

```sql
- id (PK)
- campaign_id (FK → campaigns)
- user_id (FK → users, nullable para doações anônimas)
- donor_name
- donor_email
- donor_cpf
- amount (valor da doação)
- charged_amount (valor cobrado do doador)
- platform_fee (taxa da plataforma)
- payment_gateway_fee (taxa do gateway)
- net_amount (valor líquido para o criador)
- donor_pays_fees (boolean)
- payment_method (enum: credit_card, boleto, pix)
- asaas_payment_id
- status (enum: pending, confirmed, received, refunded)
- is_anonymous (boolean)
- message (text)
- pix_qr_code, pix_copy_paste, boleto_url
- paid_at
- created_at, updated_at
```

#### Tabela: `subscriptions`
Armazena as doações recorrentes (assinaturas).

```sql
- id (PK)
- campaign_id (FK → campaigns)
- user_id (FK → users, nullable)
- donor_name, donor_email, donor_cpf
- amount
- payment_method
- cycle (enum: monthly, quarterly, semiannual, yearly)
- status (enum: active, cancelled, suspended, expired)
- asaas_subscription_id
- asaas_customer_id
- next_due_date
- started_at, cancelled_at
- created_at, updated_at
```

#### Tabela: `asaas_accounts`
Armazena as subcontas criadas no Asaas para os criadores de campanhas.

```sql
- id (PK)
- user_id (FK → users)
- asaas_account_id (ID da subconta no Asaas)
- asaas_wallet_id
- account_status (enum: active, inactive, pending)
- cpf_cnpj
- phone, mobile_phone
- api_response (JSON completo da API)
- created_at, updated_at
```

#### Outras Tabelas
- **email_verifications** - Tokens de verificação de email
- **password_resets** - Tokens de recuperação de senha
- **campaign_updates** - Atualizações postadas pelos criadores
- **analytics_events** - Eventos de analytics
- **advertisements** - Anúncios publicitários (futuro)

### Diagrama ER Simplificado

```
┌─────────┐         ┌───────────┐         ┌───────────┐
│  users  │────1:N──│ campaigns │────1:N──│ donations │
└─────────┘         └───────────┘         └───────────┘
     │                     │
     │                     │
     │              ┌──────────────┐
     └────1:1───────│asaas_accounts│
                    └──────────────┘
```

### Executar Migrations

```bash
# Executar todas as migrations
php spark migrate

# Ver status das migrations
php spark migrate:status

# Rollback da última migration
php spark migrate:rollback

# Resetar banco de dados (cuidado!)
php spark migrate:refresh

# Criar nova migration
php spark make:migration NomeDaMigration
```

---

## ✅ Funcionalidades

### 🟢 Já Implementadas (MVP Completo)

#### Autenticação e Usuários
- ✅ Registro de usuários com validação
- ✅ Login/Logout com sessão segura
- ✅ Recuperação de senha por email
- ✅ Verificação de email (pode ser desabilitada para testes)
- ✅ Perfil de usuário editável
- ✅ Upload de avatar
- ✅ Google reCAPTCHA v3 (desabilitável)

#### Campanhas
- ✅ Criar campanhas (5 categorias)
- ✅ Upload de imagem de capa
- ✅ 3 tipos de campanha:
  - Flexível (criador recebe mesmo sem atingir meta)
  - Tudo ou Nada (só recebe se atingir meta)
  - Recorrente (doações mensais)
- ✅ Listagem de campanhas com filtros
- ✅ Busca por categoria e status
- ✅ Página individual de campanha
- ✅ Contador de progresso
- ✅ Status: Rascunho, Ativa, Pausada, Concluída

#### Doações
- ✅ Doações únicas via PIX/Cartão/Boleto
- ✅ Doações recorrentes (assinaturas)
- ✅ Cálculo automático de taxas
- ✅ Opção "Doador paga taxas"
- ✅ Doações anônimas
- ✅ Mensagem para o criador
- ✅ Geração de QR Code PIX
- ✅ Boleto para download

#### Integração Asaas
- ✅ Split Payment automático
- ✅ Criação de subcontas
- ✅ Tratamento de CPF duplicado
- ✅ Reutilização de subconta
- ✅ Webhooks configurados
- ✅ Validação de saques

#### Dashboard
- ✅ Dashboard do criador
  - Minhas campanhas
  - Total arrecadado
  - Estatísticas
  - Lista de doações
- ✅ Dashboard do doador
  - Minhas doações
  - Assinaturas ativas
  - Histórico
- ✅ Gráficos e métricas

#### Sistema de Email
- ✅ Envio de emails transacionais
- ✅ Templates HTML responsivos
- ✅ Notificações:
  - Confirmação de cadastro
  - Recuperação de senha
  - Doação recebida
  - Campanha aprovada

#### Admin Panel
- ✅ Painel administrativo básico
- ✅ Listagem de campanhas
- ✅ Moderação (aprovar/reprovar)

### 🔶 Em Desenvolvimento

- 🚧 Sistema "Tudo ou Tudo" completo
- 🚧 Integração WhatsApp Business API
- 🚧 Sistema de badges para doadores
- 🚧 Relatórios PDF exportáveis
- 🚧 SEO otimizado
- 🚧 Migração completa para Alpine.js + Tremor

### 📅 Planejadas (Roadmap)

- 📋 App mobile (React Native)
- 📋 Sistema de afiliados
- 📋 Marketplace de serviços solidários
- 📋 Integração redes sociais (share)
- 📋 Certificados de doação (blockchain)
- 📋 Sistema de comentários nas campanhas
- 📋 Live streaming para campanhas
- 📋 Modo escuro (dark mode)

---

## 💳 Integração Asaas - Sistema de Pagamentos

### Visão Geral

O **DoarFazBem** utiliza o **Asaas** como gateway de pagamento oficial, oferecendo:

- 💰 **PIX** - Pagamento instantâneo
- 💳 **Cartão de crédito** - À vista e parcelado (até 12x)
- 📄 **Boleto bancário** - Vencimento configurável
- 🔄 **Doações recorrentes** - Assinaturas mensais, trimestrais, semestrais ou anuais

### Arquitetura de Split Payment

O sistema implementa **split payment automático** via subcontas Asaas:

```
┌─────────────┐
│   Doador    │
│  paga R$100 │
└──────┬──────┘
       │
       ▼
┌────────────────────────┐
│   Gateway Asaas        │
│   Split Automático     │
└───┬────────────────┬───┘
    │                │
    ▼                ▼
┌─────────┐    ┌──────────┐
│ Criador │    │Plataforma│
│ R$99,00 │    │  R$1,00  │
└─────────┘    └──────────┘
 (99%)           (1%)
```

### Fluxo de Criação de Subconta

```
┌──────────────────────────────────────┐
│ Usuário cria campanha                │
└────────────┬─────────────────────────┘
             │
             ▼
    ┌────────────────────┐
    │ Tem subconta no    │   SIM
    │ banco local?       ├──────────────┐
    └────────┬───────────┘              │
             │ NÃO                       │
             ▼                           │
    ┌────────────────────┐              │
    │ Tenta criar no     │              │
    │ Asaas via API      │              │
    └────────┬───────────┘              │
             │                           │
             ▼                           │
    ┌────────────────────┐              │
    │ CPF já existe?     │              │
    └────┬───────────┬───┘              │
         │ SIM       │ NÃO              │
         │           │                  │
         ▼           ▼                  │
  ┌──────────┐ ┌──────────┐            │
  │  Busca   │ │  Cria    │            │
  │ existente│ │   nova   │            │
  └─────┬────┘ └────┬─────┘            │
        │           │                  │
        └─────┬─────┘                  │
              │                        │
              ▼                        │
    ┌──────────────────────┐          │
    │ Salva no banco local │          │
    └────────┬─────────────┘          │
             │                        │
             └────────────┬───────────┘
                          │
                          ▼
           ┌──────────────────────────┐
           │ Campanha pronta para     │
           │ receber doações          │
           └──────────────────────────┘
```

**Benefícios:**
- ✅ **Primeira campanha**: Cria subconta automaticamente
- ✅ **Campanhas seguintes**: Reutiliza subconta existente
- ✅ **CPF já cadastrado no Asaas**: Busca e vincula conta existente

### Fluxo de Doação

```
1. Doador acessa campanha
          ↓
2. Escolhe valor e método de pagamento
          ↓
3. Sistema cria cobrança no Asaas
   (com split configurado)
          ↓
4. Doador realiza pagamento
          ↓
5. Asaas processa pagamento
          ↓
6. Webhook notifica plataforma
          ↓
7. Status atualizado no banco
   (pending → confirmed → received)
          ↓
8. Se aprovado: split executado automaticamente
   - Criador recebe sua parte
   - Plataforma recebe taxa
```

### Tipos de Campanha e Taxas

| Tipo | Taxa Plataforma | Taxa Gateway | Responsável |
|------|-----------------|--------------|-------------|
| **Médica** | 0% | PIX: R$ 0,95<br>Boleto: R$ 3,49<br>Cartão: 4,99% + parcelas | Criador |
| **Social** | 0% | PIX: R$ 0,95<br>Boleto: R$ 3,49<br>Cartão: 4,99% + parcelas | Criador |
| **Criativa** | 1% | PIX: R$ 0,95<br>Boleto: R$ 3,49<br>Cartão: 4,99% + parcelas | Criador |
| **Emergencial** | 1% | PIX: R$ 0,95<br>Boleto: R$ 3,49<br>Cartão: 4,99% + parcelas | Criador |
| **Outras** | 1% | PIX: R$ 0,95<br>Boleto: R$ 3,49<br>Cartão: 4,99% + parcelas | Criador |

**Opção "Doador Paga Taxas":**
- Se habilitada, o doador paga as taxas do gateway
- Criador recebe 100% do valor doado (menos taxa da plataforma, se houver)

### Webhooks Configurados

O sistema escuta eventos do Asaas via webhook:

| Evento | Descrição | Ação |
|--------|-----------|------|
| `PAYMENT_RECEIVED` | Pagamento confirmado | Atualiza status para "received", credita valores |
| `PAYMENT_CONFIRMED` | Pagamento aguardando compensação | Atualiza status para "confirmed" |
| `PAYMENT_OVERDUE` | Boleto vencido | Notifica criador e doador |
| `PAYMENT_DELETED` | Pagamento cancelado | Atualiza status para "cancelled" |
| `PAYMENT_REFUNDED` | Pagamento estornado | Atualiza status para "refunded" |

**URL do Webhook**: `https://doarfazbem.com.br/webhook/asaas`

### Credenciais

#### Ambiente Sandbox (Testes)
```
API Key: $aact_hmlg_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6Ojk0OWRlOTkwLWJhNmYtNDA5Yy1iNWM4LWYxNzQyODAyOGEyZTo6JGFhY2hfYzE3YzNhNTItYTg1ZS00NmQzLWEwYjAtYjNlZTk0MWRjYzNj
Wallet ID: 8e3acaa3-5040-436c-83fc-cff9b8c1b326
URL: https://api-sandbox.asaas.com/
Email: solucoesninja.com.br@gmail.com
```

#### Ambiente Produção
- Será configurado após aprovação em sandbox

### Segurança

- ✅ Todas as chamadas usam HTTPS
- ✅ API Key em variável de ambiente
- ✅ Webhook valida origem
- ✅ Logs de todas as transações
- ✅ PCI Compliance via Asaas (não armazenamos dados de cartão)
- ✅ Rate limiting nas APIs

### Documentação Oficial

- [Documentação Asaas](https://docs.asaas.com)
- [API Reference](https://docs.asaas.com/reference)
- [Split Payment Guide](https://docs.asaas.com/docs/split-de-pagamento)

---

## 🎨 Frontend Stack

### Tailwind CSS

**Framework CSS utility-first** que permite criar designs customizados rapidamente.

#### Vantagens
- ✅ Produtividade 10x maior
- ✅ Design consistente
- ✅ Sem conflito de classes
- ✅ Tree-shaking (remove CSS não usado)
- ✅ Mobile-first por padrão

#### Uso no Projeto

```html
<!-- Exemplo: Card de Campanha -->
<div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
  <img src="..." class="w-full h-48 object-cover">
  <div class="p-6">
    <h3 class="text-xl font-bold text-gray-900 mb-2">
      Título da Campanha
    </h3>
    <p class="text-gray-600 mb-4">
      Descrição breve...
    </p>
    <div class="flex items-center justify-between">
      <span class="text-primary-600 font-semibold">
        R$ 10.000,00
      </span>
      <span class="text-sm text-gray-500">
        50% atingido
      </span>
    </div>
  </div>
</div>
```

#### Tema Customizado

```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e', // Verde principal
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
        },
        secondary: {
          500: '#06b6d4', // Azul ciano
          600: '#0891b2',
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Poppins', 'sans-serif'],
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in',
        'slide-up': 'slideUp 0.3s ease-out',
      }
    }
  }
}
```

### Alpine.js

**Framework JavaScript reativo e leve** (15kb) - perfeito para aplicações server-side.

#### Por que Alpine.js?
- ✅ **Leve**: Apenas 15kb (vs. 40kb do Vue, 130kb do React)
- ✅ **Simples**: Sintaxe familiar (Vue-like)
- ✅ **Reativo**: Data binding automático
- ✅ **Perfeito para SSR**: Funciona nativamente com PHP/CodeIgniter
- ✅ **Sem build step**: Pode ser usado diretamente via CDN

#### Sintaxe Básica

```html
<!-- Contador simples -->
<div x-data="{ count: 0 }">
  <button @click="count++">Incrementar</button>
  <span x-text="count"></span>
</div>

<!-- Modal -->
<div x-data="{ open: false }">
  <button @click="open = true">Abrir Modal</button>

  <div x-show="open" @click.away="open = false">
    <div class="modal">
      <h2>Modal Title</h2>
      <button @click="open = false">Fechar</button>
    </div>
  </div>
</div>

<!-- Formulário com validação -->
<form x-data="{
  amount: 0,
  method: 'pix',
  get total() {
    return this.amount + (this.method === 'credit_card' ? this.amount * 0.0499 : 0.95)
  }
}">
  <input x-model.number="amount" type="number">
  <select x-model="method">
    <option value="pix">PIX</option>
    <option value="credit_card">Cartão</option>
  </select>
  <p>Total: R$ <span x-text="total.toFixed(2)"></span></p>
</form>
```

#### Diretivas Principais

| Diretiva | Descrição | Exemplo |
|----------|-----------|---------|
| `x-data` | Define estado do componente | `x-data="{ open: false }"` |
| `x-show` | Toggle visibilidade (CSS) | `x-show="open"` |
| `x-if` | Conditional rendering (DOM) | `x-if="isLoggedIn"` |
| `x-for` | Loop sobre arrays | `x-for="item in items"` |
| `x-on` ou `@` | Event listeners | `@click="toggle()"` |
| `x-bind` ou `:` | Bind attributes | `:class="{ 'active': isActive }"` |
| `x-model` | Two-way binding | `x-model="name"` |
| `x-text` | Set textContent | `x-text="message"` |
| `x-html` | Set innerHTML | `x-html="htmlContent"` |
| `x-transition` | Animações CSS | `x-transition` |

#### Uso no DoarFazBem

**Exemplo 1: Formulário de Doação**

```html
<div x-data="{
  amount: 50,
  method: 'pix',
  payerPaysGateway: false,

  get gatewayFee() {
    if (this.method === 'pix') return 0.95;
    if (this.method === 'boleto') return 3.49;
    return this.amount * 0.0499; // Cartão 4.99%
  },

  get platformFee() {
    return this.amount * 0.01; // 1% para campanhas não-médicas
  },

  get totalAmount() {
    return this.payerPaysGateway
      ? this.amount + this.gatewayFee
      : this.amount;
  }
}">
  <!-- Valor -->
  <input x-model.number="amount" type="number" min="5">

  <!-- Método de Pagamento -->
  <select x-model="method">
    <option value="pix">PIX</option>
    <option value="credit_card">Cartão</option>
    <option value="boleto">Boleto</option>
  </select>

  <!-- Checkbox "Pagar taxas" -->
  <label>
    <input type="checkbox" x-model="payerPaysGateway">
    Pagar taxas do gateway
  </label>

  <!-- Resumo -->
  <div class="summary">
    <p>Valor da doação: R$ <span x-text="amount.toFixed(2)"></span></p>
    <p x-show="payerPaysGateway">
      Taxa do gateway: R$ <span x-text="gatewayFee.toFixed(2)"></span>
    </p>
    <p class="font-bold">
      Total a pagar: R$ <span x-text="totalAmount.toFixed(2)"></span>
    </p>
  </div>

  <!-- Botão -->
  <button
    @click="submitDonation()"
    :disabled="amount < 5"
    :class="{ 'opacity-50 cursor-not-allowed': amount < 5 }">
    Doar Agora
  </button>
</div>
```

**Exemplo 2: Listagem de Campanhas com Filtros**

```html
<div x-data="{
  campaigns: <?= json_encode($campaigns) ?>,
  category: 'all',
  search: '',

  get filteredCampaigns() {
    return this.campaigns
      .filter(c => this.category === 'all' || c.category === this.category)
      .filter(c => c.title.toLowerCase().includes(this.search.toLowerCase()))
  }
}">
  <!-- Filtros -->
  <div class="filters">
    <input x-model="search" placeholder="Buscar campanhas...">

    <select x-model="category">
      <option value="all">Todas</option>
      <option value="medical">Médicas</option>
      <option value="social">Sociais</option>
      <option value="creative">Criativas</option>
    </select>
  </div>

  <!-- Contador -->
  <p x-text="`${filteredCampaigns.length} campanha(s) encontrada(s)`"></p>

  <!-- Grid de Campanhas -->
  <div class="grid grid-cols-3 gap-6">
    <template x-for="campaign in filteredCampaigns" :key="campaign.id">
      <div class="campaign-card">
        <img :src="campaign.image" :alt="campaign.title">
        <h3 x-text="campaign.title"></h3>
        <p x-text="campaign.description"></p>
        <a :href="`/campaign/${campaign.slug}`">Ver mais</a>
      </div>
    </template>
  </div>

  <!-- Mensagem vazia -->
  <div x-show="filteredCampaigns.length === 0">
    <p>Nenhuma campanha encontrada</p>
  </div>
</div>
```

### Tremor

**Biblioteca de componentes para dashboards** construída sobre React/Vue e Tailwind.

#### Componentes Disponíveis

**Charts:**
- AreaChart
- BarChart
- LineChart
- DonutChart
- PieChart

**Cards:**
- Card
- Metric (KPI cards)
- BadgeDelta
- ProgressBar

**Tabelas:**
- Table
- TableHead
- TableBody
- TableRow

**Outros:**
- Tabs
- Badge
- Button
- Select
- DateRangePicker

#### Exemplo: Dashboard com Tremor

```html
<!-- Card de Métrica -->
<div class="card">
  <h3>Total Arrecadado</h3>
  <div class="metric">
    <span class="text-4xl font-bold">R$ 125.450,00</span>
    <span class="badge-delta increase">+12%</span>
  </div>
</div>

<!-- Gráfico de Área -->
<div class="card">
  <h3>Doações por Mês</h3>
  <div id="donations-chart"></div>
</div>

<!-- Tabela de Doações Recentes -->
<div class="card">
  <h3>Doações Recentes</h3>
  <table class="tremor-table">
    <thead>
      <tr>
        <th>Doador</th>
        <th>Campanha</th>
        <th>Valor</th>
        <th>Data</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recent_donations as $donation): ?>
      <tr>
        <td><?= esc($donation['donor_name']) ?></td>
        <td><?= esc($donation['campaign_title']) ?></td>
        <td>R$ <?= number_format($donation['amount'], 2, ',', '.') ?></td>
        <td><?= date('d/m/Y', strtotime($donation['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
```

### Chart.js

**Biblioteca JavaScript para gráficos** - simples e poderosa.

```javascript
// Gráfico de doações por mês
const ctx = document.getElementById('donationsChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
    datasets: [{
      label: 'Doações',
      data: [1200, 1900, 3000, 5000, 2300, 4500],
      borderColor: '#22c55e',
      backgroundColor: 'rgba(34, 197, 94, 0.1)',
      tension: 0.4
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: false
      }
    }
  }
});
```

---

## 🗓️ Roadmap de Desenvolvimento

### Fase 1: Setup + Autenticação ✅ CONCLUÍDA
**Duração**: 2 semanas | **Status**: ✅ 100%

- [x] Setup do ambiente (Laragon, Composer, NPM)
- [x] Configuração CodeIgniter 4
- [x] Setup Tailwind CSS
- [x] Migrations do banco de dados
- [x] Sistema de autenticação completo
- [x] Recuperação de senha
- [x] Verificação de email

### Fase 2: Sistema de Campanhas ✅ CONCLUÍDA
**Duração**: 2 semanas | **Status**: ✅ 100%

- [x] CRUD de campanhas
- [x] Upload de imagens
- [x] 3 tipos de campanha (Flexível, Tudo ou Nada, Recorrente)
- [x] Listagem com filtros
- [x] Página individual de campanha
- [x] Sistema de categorias

### Fase 3: Integração Asaas ✅ CONCLUÍDA
**Duração**: 2 semanas | **Status**: ✅ 100%

- [x] API Asaas conectada
- [x] Split Payment configurado
- [x] Criação de subcontas
- [x] Tratamento de CPF duplicado
- [x] Webhooks funcionais
- [x] Validação de saques

### Fase 4: Interface de Doação ✅ CONCLUÍDA
**Duração**: 2 semanas | **Status**: ✅ 100%

- [x] Formulário de doação
- [x] Cálculo de taxas
- [x] Opção "Doador paga taxas"
- [x] PIX, Cartão, Boleto
- [x] Doações anônimas
- [x] Mensagens para criador

### Fase 5: Dashboard e Relatórios ✅ CONCLUÍDA
**Duração**: 1 semana | **Status**: ✅ 100%

- [x] Dashboard do criador
- [x] Dashboard do doador
- [x] Minhas campanhas
- [x] Minhas doações
- [x] Estatísticas básicas

### Fase 6: Migração para Alpine.js + Tremor 🚧 EM ANDAMENTO
**Duração**: 2 semanas | **Status**: 🚧 30%

- [ ] Substituir jQuery por Alpine.js
- [ ] Implementar componentes Tremor
- [ ] Refatorar dashboards
- [ ] Adicionar gráficos interativos
- [ ] Componentes reativos

### Fase 7: Sistema "Tudo ou Tudo" Completo 📋 PLANEJADA
**Duração**: 1 semana | **Status**: ⏳ Pendente

- [ ] Lógica de redistribuição
- [ ] Integração com Central do Dízimo
- [ ] Sistema de votação de campanhas médicas
- [ ] Notificações automáticas

### Fase 8: WhatsApp Business API 📋 PLANEJADA
**Duração**: 1 semana | **Status**: ⏳ Pendente

- [ ] Configurar conta WhatsApp Business
- [ ] Integrar API
- [ ] Templates de mensagens
- [ ] Notificações automáticas
- [ ] Chatbot básico

### Fase 9: SEO e Performance 📋 PLANEJADA
**Duração**: 1 semana | **Status**: ⏳ Pendente

- [ ] Meta tags otimizadas
- [ ] Schema.org markup
- [ ] Sitemap XML
- [ ] Cache estratégico
- [ ] Lazy loading de imagens
- [ ] CDN configurado

### Fase 10: Testes e Segurança 📋 PLANEJADA
**Duração**: 2 semanas | **Status**: ⏳ Pendente

- [ ] Testes unitários (PHPUnit)
- [ ] Testes de integração
- [ ] Auditoria de segurança
- [ ] Testes de carga
- [ ] Penetration testing

### Fase 11: Lançamento 📋 PLANEJADA
**Duração**: 1 semana | **Status**: ⏳ Pendente

- [ ] Deploy em produção
- [ ] Configurar monitoramento
- [ ] Setup backups automáticos
- [ ] Documentação final
- [ ] Marketing de lançamento

**Tempo Total**: ~18 semanas (~4,5 meses)
**Status Geral**: 60% concluído

---

## 📚 Documentação

### Documentação do Projeto

**📂 Toda documentação técnica está na pasta [`docs/`](docs/)**

- [Estrutura do Projeto](STRUCTURE.md) - Organização de pastas e arquivos
- [Especificações Completas](docs/DoarFazBem_Especificacoes_Completas.md) - Requisitos e regras de negócio
- [Wireframe Visual](docs/wireframe.html) - Estrutura visual do projeto
- [Correções do Dashboard](docs/CORRECOES_DASHBOARD.md) - Log de correções recentes
- [Credenciais de Acesso](docs/CREDENCIAIS_ACESSO.md) - Usuários de teste
- [Taxas Asaas](docs/TAXAS_ASAAS_OFICIAL_2025.md) - Tabela de taxas oficial
- [Segurança](docs/SECURITY.md) - Políticas de segurança

### Documentação Externa

**CodeIgniter 4:**
- [User Guide](https://codeigniter.com/user_guide/)
- [API Reference](https://codeigniter.com/api/)

**Frontend:**
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev/start-here)
- [Tremor Documentation](https://tremor.so/docs)
- [Chart.js Docs](https://www.chartjs.org/docs/)

**APIs:**
- [Asaas API Docs](https://docs.asaas.com)
- [WhatsApp Business API](https://developers.facebook.com/docs/whatsapp)
- [Google APIs](https://developers.google.com/)

---

## 🤝 Contribuindo

Contribuições são **muito bem-vindas**! Este projeto tem um impacto social real.

### Como Contribuir

1. **Fork** o projeto
2. Crie uma **branch** para sua feature (`git checkout -b feature/MinhaFeature`)
3. **Commit** suas mudanças (`git commit -m 'feat: Minha nova feature'`)
4. **Push** para a branch (`git push origin feature/MinhaFeature`)
5. Abra um **Pull Request**

### Padrões de Commit (Conventional Commits)

```
feat: Adiciona nova funcionalidade
fix: Corrige um bug
docs: Atualiza documentação
style: Formatação, ponto e vírgula, etc
refactor: Refatoração de código
test: Adiciona ou atualiza testes
chore: Tarefas de build, configs, etc
perf: Melhoria de performance
```

**Exemplos:**
```bash
git commit -m "feat: Adiciona doações recorrentes"
git commit -m "fix: Corrige cálculo de taxas no formulário"
git commit -m "docs: Atualiza README com instruções de instalação"
```

### Código de Conduta

Este projeto segue o [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md).

---

## 💰 Modelo de Negócio

### Estrutura de Taxas

#### Campanhas Médicas e Sociais
- ✅ **0% de taxa da plataforma** (GRATUITO)
- Doador pode optar por pagar taxas do gateway
- 100% transparência

#### Outras Campanhas (Criativas, Emergenciais, etc)
- **1% de taxa da plataforma** (a menor do mercado)
- Doador pode pagar taxas adicionais
- Criador recebe até 99% do valor

### Sistema "Tudo ou Tudo" (Inovador)

Se a meta NÃO for atingida no prazo:
- **1%** → Plataforma (manutenção)
- **49%** → Central Geral do Dízimo Pró-Vida
- **50%** → Campanha médica escolhida pelo doador

### Receitas da Plataforma

1. **Taxa de 1%** em campanhas não-médicas/sociais
2. **Publicidade segmentada** (banners e anúncios nativos)
3. **Taxa extra voluntária** dos doadores
4. **Parcerias com empresas** (RSC - Responsabilidade Social Corporativa)
5. **Marketplace de serviços** solidários (futuro)

---

## 🐛 Suporte

### Encontrou um Bug?

Abra uma [issue](https://github.com/seu-usuario/doarfazbem/issues) descrevendo:
- **O que aconteceu**
- **O que deveria acontecer**
- **Passos para reproduzir**
- **Screenshots** (se aplicável)
- **Ambiente** (navegador, OS, PHP version)

### Precisa de Ajuda?

- 📧 **Email**: suporte@doarfazbem.com.br
- 💬 **Discord**: [Link do servidor](https://discord.gg/xxxxx)
- 📱 **WhatsApp**: [Link do grupo](https://chat.whatsapp.com/xxxxx)
- 📚 **Wiki**: [github.com/doarfazbem/wiki](https://github.com/doarfazbem/wiki)

---

## 🔐 Segurança

### Reportar Vulnerabilidades

Se você encontrou uma vulnerabilidade de segurança, **NÃO abra uma issue pública**.

Envie um email para: **security@doarfazbem.com.br**

Responderemos em até 48 horas.

### Práticas de Segurança Implementadas

- ✅ **HTTPS obrigatório** em produção
- ✅ **Senhas hasheadas** (bcrypt com salt)
- ✅ **Proteção CSRF** (tokens em todos os formulários)
- ✅ **Sanitização de inputs** (prevenção XSS)
- ✅ **Prepared statements** (prevenção SQL Injection)
- ✅ **Rate limiting** nas APIs
- ✅ **Logs de auditoria** (todas as ações importantes)
- ✅ **Sessões seguras** (cookies httpOnly)
- ✅ **Headers de segurança** (X-Frame-Options, CSP, etc)
- ✅ **Validação de uploads** (tipo, tamanho, conteúdo)
- ✅ **reCAPTCHA v3** (proteção contra bots)

---

## 📊 Status do Projeto

### Métricas Atuais

- **Versão**: 1.0.0-beta
- **Status**: 🚧 Em desenvolvimento (~60% completo)
- **Cobertura de Testes**: 0% (planejado para Fase 10)
- **Performance**: N/A (auditoria planejada)
- **Contribuidores**: 1 (aberto para mais!)

### Próximos Marcos

- [x] **v0.1.0** - MVP funcional ✅ CONCLUÍDO
- [ ] **v0.5.0** - Sistema completo (todas as fases)
- [ ] **v1.0.0** - Lançamento oficial
- [ ] **v1.5.0** - App mobile
- [ ] **v2.0.0** - Marketplace de serviços

### Estatísticas de Desenvolvimento

- **Linhas de código**: ~15.000+
- **Arquivos PHP**: 50+
- **Views**: 30+
- **Migrations**: 15+
- **Models**: 10+
- **Commits**: 100+

---

## 📜 Licença

Este projeto está licenciado sob a **MIT License** - veja o arquivo [LICENSE](LICENSE) para detalhes.

```
Copyright (c) 2025 DoarFazBem

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software...
```

---

## 🙏 Agradecimentos

- **CodeIgniter Community** - Framework incrível e documentação excelente
- **Tailwind Labs** - Por democratizar o design moderno
- **Alpine.js Team** - Framework JavaScript perfeito para SSR
- **Tremor** - Componentes profissionais para dashboards
- **Asaas** - Gateway brasileiro confiável e com ótimo suporte
- **Hetzner** - Infraestrutura de qualidade
- **Todos os contribuidores** - Vocês são incríveis! 💚

---

## 📞 Contato

**DoarFazBem** - Tornando o ato de doar mais humano 💚

- 🌐 **Website**: [www.doarfazbem.com.br](https://www.doarfazbem.com.br)
- 📧 **Email**: contato@doarfazbem.com.br
- 📧 **Suporte**: suporte@doarfazbem.com.br
- 📧 **Segurança**: security@doarfazbem.com.br
- 📱 **WhatsApp**: +55 11 99999-9999
- 💼 **LinkedIn**: [linkedin.com/company/doarfazbem](https://linkedin.com/company/doarfazbem)
- 🐦 **Twitter**: [@doarfazbem](https://twitter.com/doarfazbem)
- 📷 **Instagram**: [@doarfazbem.oficial](https://instagram.com/doarfazbem.oficial)
- 👥 **Facebook**: [/doarfazbem](https://facebook.com/doarfazbem)

---

<div align="center">

### ⭐ Star este projeto se ele te ajudou!

**Feito com 💚 para ajudar quem precisa**

[⬆ Voltar ao topo](#-doar-faz-bem---plataforma-de-crowdfunding-social)

</div>
