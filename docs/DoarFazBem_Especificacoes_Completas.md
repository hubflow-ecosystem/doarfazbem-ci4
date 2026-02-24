# 🚀 PLATAFORMA DOARFAZBEM.COM.BR
## Especificações Técnicas e Estratégicas Completas

---

## 📋 **ÍNDICE**
1. [Visão Geral do Projeto](#visão-geral)
2. [Modelo de Negócios](#modelo-de-negócios)
3. [Arquitetura Técnica](#arquitetura-técnica)
4. [Design e UX/UI](#design-e-ux-ui)
5. [Funcionalidades Detalhadas](#funcionalidades-detalhadas)
6. [Integração Gateway de Pagamento](#integração-gateway)
7. [Integração WhatsApp](#integração-whatsapp)
8. [Sistema de Publicidade](#sistema-publicidade)
9. [Estratégias de Marketing](#estratégias-marketing)
10. [Cronograma de Desenvolvimento](#cronograma)
11. [Checklist de Implementação](#checklist)

---

## 🎯 **1. VISÃO GERAL DO PROJETO** {#visão-geral}

### **Objetivo Principal**
Criar a **plataforma de crowdfunding mais justa do Brasil**, com foco em campanhas sociais e médicas gratuitas, diferenciando-se pela transparência e baixíssimas taxas.

### **Diferenciais Competitivos**
- ✅ Campanhas médicas/sociais: **100% gratuitas**
- ✅ Outras campanhas: **apenas 1% de taxa**
- ✅ Doador pode optar por pagar taxas do gateway
- ✅ Sistema "Tudo ou Tudo" inovador
- ✅ Transparência total nas taxas
- ✅ Integração WhatsApp nativa

### **Público-Alvo**
- **Primário**: Pessoas com necessidades médicas urgentes
- **Secundário**: Projetos sociais e ONGs
- **Terciário**: Projetos criativos e empresariais
- **Apoiadores**: Pessoas físicas e jurídicas com perfil solidário

---

## 💰 **2. MODELO DE NEGÓCIOS** {#modelo-de-negócios}

### **Estrutura de Cobrança**

#### **2.1 Campanhas Médicas e Sociais**
```
Taxa da Plataforma: 0% (GRATUITO)

Opções para o Doador:
┌─ Opção A: Doar R$ 100
│  └─ Criador recebe: ~R$ 94 (após taxas gateway)
│  └─ Plataforma recebe: R$ 0
│
└─ Opção B: Doar R$ 100 + Taxas (R$ 7) + 1% Plataforma
   └─ Criador recebe: R$ 100 (INTEGRAL)
   └─ Plataforma recebe: R$ 1
   └─ Gateway recebe: R$ 7
```

#### **2.2 Outras Campanhas (Projetos, Negócios, etc.)**
```
Taxa da Plataforma: 1% (OBRIGATÓRIA)

Opções para o Doador:
┌─ Opção A: Doar R$ 100
│  └─ Criador recebe: ~R$ 93 (após taxas)
│  └─ Plataforma recebe: R$ 1
│
└─ Opção B: Doar R$ 100 + Taxas (R$ 7) + 1% Extra
   └─ Criador recebe: R$ 99 (após 1% obrigatório)
   └─ Plataforma recebe: R$ 2 (1% obrigatório + 1% extra)
   └─ Gateway recebe: R$ 7
```

#### **2.3 Sistema "Tudo ou Tudo"**
```
Se META NÃO for atingida no prazo:
├─ Plataforma recebe: 1% do total arrecadado
├─ Central Geral do Dízimo Pró-Vida: 49%
└─ Campanha médica escolhida pelo doador: 50%
```

### **Fontes de Receita**
1. **Taxa condicional** (1% quando aplicável)
2. **Espaços publicitários** personalizados
3. **Taxa extra voluntária** dos doadores
4. **Parcerias** com empresas patrocinadoras

---

## 🏗️ **3. ARQUITETURA TÉCNICA** {#arquitetura-técnica}

### **3.1 Stack Tecnológica**
```
Backend: PHP 8.1+ com CodeIgniter 4
Frontend: HTML5 + TailwindCSS + Alpine.js
Banco de Dados: MySQL 8.0
Servidor: Hertzner Cloud VPS
Painel: CloudPanel
SSL: Let's Encrypt (gratuito)
CDN: CloudFlare (gratuito)
```

### **3.2 Estrutura de Pastas**
```
/doarfazbem/
├── app/
│   ├── Controllers/
│   │   ├── Home.php
│   │   ├── Campaign.php
│   │   ├── Payment.php
│   │   ├── User.php
│   │   └── Admin.php
│   ├── Models/
│   │   ├── CampaignModel.php
│   │   ├── DonationModel.php
│   │   ├── UserModel.php
│   │   └── PaymentModel.php
│   ├── Views/
│   │   ├── layout/
│   │   ├── campaigns/
│   │   ├── user/
│   │   └── admin/
│   └── Libraries/
│       ├── AsaasAPI.php
│       └── WhatsAppAPI.php
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── uploads/
└── writable/
```

### **3.3 Banco de Dados - Estrutura Principal**

#### **Tabela: campaigns**
```sql
CREATE TABLE campaigns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('medica', 'social', 'criativa', 'negocio', 'educacao') NOT NULL,
    goal_amount DECIMAL(10,2) NOT NULL,
    current_amount DECIMAL(10,2) DEFAULT 0,
    deadline DATE NOT NULL,
    status ENUM('active', 'completed', 'expired', 'paused') DEFAULT 'active',
    type ENUM('flexible', 'tudo_ou_tudo') DEFAULT 'flexible',
    featured_image VARCHAR(255),
    asaas_wallet_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### **Tabela: donations**
```sql
CREATE TABLE donations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campaign_id INT NOT NULL,
    donor_name VARCHAR(255),
    donor_email VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    platform_fee DECIMAL(10,2) DEFAULT 0,
    gateway_fee DECIMAL(10,2) NOT NULL,
    donor_paid_fees BOOLEAN DEFAULT FALSE,
    payment_id VARCHAR(100),
    payment_status ENUM('pending', 'confirmed', 'failed') DEFAULT 'pending',
    anonymous BOOLEAN DEFAULT FALSE,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### **Tabela: users**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    cpf VARCHAR(14),
    password_hash VARCHAR(255),
    asaas_customer_id VARCHAR(50),
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🎨 **4. DESIGN E UX/UI** {#design-e-ux-ui}

### **4.1 Psicologia das Cores**

#### **Paleta Principal**
```css
/* Cores Principais */
:root {
    /* Verde Esperança - Cor principal */
    --primary: #10B981;      /* Transmite esperança, saúde, crescimento */
    --primary-light: #34D399; /* Verde mais claro para hovers */
    --primary-dark: #047857;  /* Verde escuro para textos importantes */
    
    /* Azul Confiança - Cor secundária */
    --secondary: #3B82F6;     /* Confiança, segurança, profissionalismo */
    --secondary-light: #60A5FA;
    --secondary-dark: #1E40AF;
    
    /* Laranja Urgência - Para campanhas médicas */
    --urgent: #F97316;        /* Urgência sem ser alarmante */
    --urgent-light: #FB923C;
    
    /* Neutros Modernos */
    --gray-50: #F9FAFB;
    --gray-100: #F3F4F6;
    --gray-200: #E5E7EB;
    --gray-600: #4B5563;
    --gray-800: #1F2937;
    --gray-900: #111827;
    
    /* Sistema */
    --success: #10B981;       /* Sucesso */
    --warning: #F59E0B;       /* Atenção */
    --error: #EF4444;         /* Erro */
}
```

#### **Aplicação das Cores**
```
🔵 Azul (#3B82F6): 
   - Botões secundários
   - Links
   - Elementos de navegação
   - Ícones informativos

🟢 Verde (#10B981):
   - Botões primários (DOAR)
   - Progresso das metas
   - Sucessos e confirmações
   - CTA principais

🟠 Laranja (#F97316):
   - Campanhas URGENTES
   - Deadline próximo
   - Alertas importantes
   - Contador regressivo

⚫ Cinza:
   - Textos
   - Backgrounds
   - Bordas
   - Elementos neutros
```

### **4.2 Tipografia**
```css
/* Fontes do Sistema */
font-family: 'Inter', -apple-system, BlinkMacSystemFont, system-ui, sans-serif;

/* Hierarquia */
.heading-1 { font-size: 2.5rem; font-weight: 800; } /* Títulos principais */
.heading-2 { font-size: 2rem; font-weight: 700; }   /* Títulos seções */
.heading-3 { font-size: 1.5rem; font-weight: 600; } /* Subtítulos */
.body-large { font-size: 1.125rem; line-height: 1.6; } /* Texto importante */
.body { font-size: 1rem; line-height: 1.6; }        /* Texto padrão */
.body-small { font-size: 0.875rem; }                /* Textos menores */
```

### **4.3 Layout e Componentes**

#### **Header Principal**
```html
<header class="bg-white shadow-sm border-b border-gray-200">
    <nav class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
        <div class="flex items-center">
            <img src="/logo.svg" class="h-8 w-auto">
            <span class="ml-2 text-xl font-bold text-gray-900">DoarFazBem</span>
        </div>
        
        <div class="hidden md:flex space-x-8">
            <a href="#" class="text-gray-600 hover:text-primary">Como Funciona</a>
            <a href="#" class="text-gray-600 hover:text-primary">Campanhas</a>
            <a href="#" class="text-gray-600 hover:text-primary">Para ONGs</a>
        </div>
        
        <div class="flex items-center space-x-4">
            <button class="btn-secondary">Entrar</button>
            <button class="btn-primary">Criar Campanha</button>
        </div>
    </nav>
</header>
```

#### **Hero Section**
```html
<section class="bg-gradient-to-br from-primary to-primary-dark text-white py-20">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">
            A única plataforma 100% gratuita para campanhas sociais
        </h1>
        <p class="text-xl opacity-90 mb-8 max-w-3xl mx-auto">
            Campanhas médicas e sociais sem taxas. Outras campanhas com apenas 1%. 
            Transparente, segura e focada em resultados.
        </p>
        <div class="flex justify-center space-x-4">
            <button class="btn-white-large">Criar Campanha Grátis</button>
            <button class="btn-outline-white">Ver Campanhas</button>
        </div>
    </div>
</section>
```

#### **Card de Campanha**
```html
<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
    <!-- Imagem com badge de categoria -->
    <div class="relative">
        <img src="campanha.jpg" class="w-full h-48 object-cover">
        <span class="absolute top-3 left-3 px-2 py-1 bg-urgent text-white text-sm rounded-full">
            Médica - Urgente
        </span>
    </div>
    
    <!-- Conteúdo -->
    <div class="p-6">
        <h3 class="font-semibold text-lg text-gray-900 mb-2">Título da Campanha</h3>
        <p class="text-gray-600 text-sm mb-4 line-clamp-2">Descrição breve...</p>
        
        <!-- Progresso -->
        <div class="mb-4">
            <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>R$ 15.420 arrecadados</span>
                <span>72% da meta</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-primary h-2 rounded-full" style="width: 72%"></div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500">23 doadores</span>
            <button class="btn-primary-small">Doar Agora</button>
        </div>
    </div>
</div>
```

### **4.4 Classes CSS Utilitárias**
```css
/* Botões */
.btn-primary {
    @apply bg-primary text-white px-6 py-3 rounded-lg font-semibold 
           hover:bg-primary-dark transition-colors;
}

.btn-secondary {
    @apply bg-secondary text-white px-6 py-3 rounded-lg font-semibold 
           hover:bg-secondary-dark transition-colors;
}

.btn-outline {
    @apply border-2 border-primary text-primary px-6 py-3 rounded-lg 
           font-semibold hover:bg-primary hover:text-white transition-colors;
}

/* Cards */
.card {
    @apply bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow;
}

/* Badges */
.badge-medical {
    @apply bg-urgent text-white px-3 py-1 rounded-full text-sm font-medium;
}

.badge-social {
    @apply bg-primary text-white px-3 py-1 rounded-full text-sm font-medium;
}
```

---

## ⚙️ **5. FUNCIONALIDADES DETALHADAS** {#funcionalidades-detalhadas}

### **5.1 Sistema de Usuários**

#### **Cadastro de Usuários**
```php
// Controller: User.php
public function register() {
    $rules = [
        'name' => 'required|min_length[2]|max_length[255]',
        'email' => 'required|valid_email|is_unique[users.email]',
        'phone' => 'required|regex_match[/^(\+55|55|0)?[1-9]{2}9?[0-9]{8}$/]',
        'cpf' => 'required|exact_length[14]|regex_match[/^\d{3}\.\d{3}\.\d{3}-\d{2}$/]',
        'password' => 'required|min_length[8]',
        'terms' => 'required'
    ];
    
    if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
        $userData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => preg_replace('/\D/', '', $this->request->getPost('phone')),
            'cpf' => $this->request->getPost('cpf'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT)
        ];
        
        // Criar customer no Asaas
        $asaasCustomer = $this->createAsaasCustomer($userData);
        $userData['asaas_customer_id'] = $asaasCustomer['id'];
        
        $this->userModel->insert($userData);
        
        // Enviar email de verificação
        $this->sendVerificationEmail($userData['email']);
        
        return redirect()->to('/login')->with('success', 'Conta criada! Verifique seu email.');
    }
    
    return view('auth/register');
}
```

#### **Sistema de Verificação de Email**
```php
public function verifyEmail($token) {
    $verification = $this->verificationModel->where('token', $token)
                                           ->where('expires_at >', date('Y-m-d H:i:s'))
                                           ->first();
    
    if (!$verification) {
        return redirect()->to('/login')->with('error', 'Token inválido ou expirado.');
    }
    
    $this->userModel->update($verification['user_id'], ['email_verified' => true]);
    $this->verificationModel->delete($verification['id']);
    
    return redirect()->to('/dashboard')->with('success', 'Email verificado com sucesso!');
}
```

### **5.2 Sistema de Campanhas**

#### **Criação de Campanhas**
```php
// Controller: Campaign.php
public function create() {
    if (!$this->isLoggedIn()) {
        return redirect()->to('/login');
    }
    
    $rules = [
        'title' => 'required|min_length[5]|max_length[255]',
        'description' => 'required|min_length[50]',
        'category' => 'required|in_list[medica,social,criativa,negocio,educacao]',
        'goal_amount' => 'required|decimal|greater_than[100]',
        'deadline' => 'required|valid_date[Y-m-d]',
        'type' => 'required|in_list[flexible,tudo_ou_tudo]',
        'featured_image' => 'uploaded[featured_image]|max_size[featured_image,2048]|is_image[featured_image]'
    ];
    
    if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
        // Upload da imagem
        $image = $this->request->getFile('featured_image');
        $imageName = $image->getRandomName();
        $image->move('uploads/campaigns/', $imageName);
        
        $campaignData = [
            'user_id' => session('user_id'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'goal_amount' => $this->request->getPost('goal_amount'),
            'deadline' => $this->request->getPost('deadline'),
            'type' => $this->request->getPost('type'),
            'featured_image' => $imageName,
            'status' => 'active'
        ];
        
        // Criar subconta no Asaas se necessário
        $user = $this->userModel->find(session('user_id'));
        if (empty($user['asaas_wallet_id'])) {
            $wallet = $this->createAsaasWallet($user);
            $this->userModel->update($user['id'], ['asaas_wallet_id' => $wallet['id']]);
            $campaignData['asaas_wallet_id'] = $wallet['id'];
        } else {
            $campaignData['asaas_wallet_id'] = $user['asaas_wallet_id'];
        }
        
        $campaignId = $this->campaignModel->insert($campaignData);
        
        return redirect()->to("/campanha/{$campaignId}")->with('success', 'Campanha criada com sucesso!');
    }
    
    return view('campaigns/create');
}
```

#### **Sistema de Categorização Inteligente**
```php
public function suggestCategory($description) {
    $keywords = [
        'medica' => ['cirurgia', 'tratamento', 'hospital', 'doença', 'medicina', 'saúde', 'câncer', 'internação'],
        'social' => ['ong', 'comunidade', 'caridade', 'ajuda', 'solidariedade', 'assistência', 'vulnerabilidade'],
        'educacao' => ['escola', 'universidade', 'curso', 'estudos', 'formatura', 'educação', 'aprendizado'],
        'criativa' => ['filme', 'livro', 'arte', 'música', 'projeto', 'criação', 'cultura'],
        'negocio' => ['empresa', 'startup', 'negócio', 'empreendimento', 'investimento', 'produto']
    ];
    
    $scores = [];
    foreach ($keywords as $category => $terms) {
        $scores[$category] = 0;
        foreach ($terms as $term) {
            if (stripos($description, $term) !== false) {
                $scores[$category]++;
            }
        }
    }
    
    return array_search(max($scores), $scores);
}
```

### **5.3 Sistema de Doações**

#### **Interface de Doação**
```html
<!-- Formulário de Doação -->
<div class="donation-form bg-white rounded-lg shadow-lg p-6">
    <h3 class="text-xl font-bold mb-4">Faça sua doação</h3>
    
    <!-- Valores sugeridos -->
    <div class="grid grid-cols-3 gap-3 mb-4">
        <button class="amount-btn" data-amount="50">R$ 50</button>
        <button class="amount-btn" data-amount="100">R$ 100</button>
        <button class="amount-btn" data-amount="200">R$ 200</button>
    </div>
    
    <!-- Valor customizado -->
    <div class="mb-4">
        <label class="block text-sm font-medium mb-2">Valor personalizado</label>
        <div class="relative">
            <span class="absolute left-3 top-3 text-gray-500">R$</span>
            <input type="number" id="custom-amount" class="w-full pl-8 pr-4 py-3 border rounded-lg"
                   placeholder="0,00" min="5" step="0.01">
        </div>
    </div>
    
    <!-- Opções de pagamento das taxas (APENAS para campanhas médicas/sociais) -->
    <div class="fee-options mb-4" style="display: none;" id="fee-options-medical">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-yellow-800 mb-3">
                <strong>Esta é uma campanha médica/social - sem taxas para você!</strong>
            </p>
            
            <div class="space-y-3">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="radio" name="fee_option" value="standard" class="mt-1" checked>
                    <div>
                        <div class="font-medium">Doação padrão</div>
                        <div class="text-sm text-gray-600">
                            Você doa <span class="donation-amount">R$ 100</span>, 
                            criador recebe aproximadamente <span class="creator-receives">R$ 94,10</span>
                        </div>
                    </div>
                </label>
                
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="radio" name="fee_option" value="with_fees">
                    <div>
                        <div class="font-medium text-primary">Doação integral (Recomendado) ⭐</div>
                        <div class="text-sm text-gray-600">
                            Você paga <span class="donation-amount">R$ 100</span> + 
                            <span class="gateway-fee">R$ 5,90</span> de taxas + 
                            <span class="platform-tip">R$ 1,00</span> para manter a plataforma.<br>
                            <strong class="text-primary">Criador recebe R$ 100 integrais!</strong>
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Opções para outras campanhas -->
    <div class="fee-options mb-4" style="display: none;" id="fee-options-other">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-blue-800 mb-3">
                <strong>Taxa da plataforma: 1% (criador já concordou)</strong>
            </p>
            
            <div class="space-y-3">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="radio" name="fee_option" value="standard" checked>
                    <div>
                        <div class="font-medium">Doação padrão</div>
                        <div class="text-sm text-gray-600">
                            Criador recebe <span class="creator-receives-other">R$ 93,10</span> 
                            (após taxas de gateway e plataforma)
                        </div>
                    </div>
                </label>
                
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="radio" name="fee_option" value="help_platform">
                    <div>
                        <div class="font-medium text-secondary">Ajudar a plataforma</div>
                        <div class="text-sm text-gray-600">
                            Doar mais 1% extra (<span class="extra-fee">R$ 1,00</span>) para 
                            ajudar a manter campanhas médicas gratuitas
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>
    
    <!-- Dados do doador -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <input type="text" name="donor_name" placeholder="Seu nome" class="form-input">
        <input type="email" name="donor_email" placeholder="Seu email" class="form-input">
    </div>
    
    <!-- Opções extras -->
    <div class="mb-4">
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="anonymous" class="rounded">
            <span class="text-sm">Doação anônima</span>
        </label>
    </div>
    
    <!-- Mensagem -->
    <div class="mb-6">
        <textarea name="message" placeholder="Deixe uma mensagem de apoio (opcional)"
                  class="w-full p-3 border rounded-lg resize-none h-20"></textarea>
    </div>
    
    <!-- Resumo final -->
    <div class="bg-gray-50 rounded-lg p-4 mb-4" id="donation-summary">
        <div class="flex justify-between text-sm mb-1">
            <span>Valor da doação:</span>
            <span class="summary-donation">R$ 100,00</span>
        </div>
        <div class="flex justify-between text-sm mb-1" id="summary-fees" style="display: none;">
            <span>Taxas do gateway:</span>
            <span class="summary-gateway-fee">R$ 5,90</span>
        </div>
        <div class="flex justify-between text-sm mb-1" id="summary-platform" style="display: none;">
            <span>Ajuda à plataforma:</span>
            <span class="summary-platform-fee">R$ 1,00</span>
        </div>
        <hr class="my-2">
        <div class="flex justify-between font-bold">
            <span>Total a pagar:</span>
            <span class="summary-total">R$ 100,00</span>
        </div>
    </div>
    
    <!-- Botão de doação -->
    <button type="submit" class="w-full btn-primary py-4 text-lg">
        Doar Agora
    </button>
    
    <!-- Segurança -->
    <div class="flex items-center justify-center mt-4 text-sm text-gray-500">
        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
        </svg>
        Pagamento 100% seguro via Asaas
    </div>
</div>
```

#### **Processamento de Doações - Backend**
```php
// Controller: Payment.php
public function processDonation() {
    $rules = [
        'campaign_id' => 'required|integer',
        'amount' => 'required|decimal|greater_than[5]',
        'donor_name' => 'required|min_length[2]',
        'donor_email' => 'required|valid_email',
        'fee_option' => 'required|in_list[standard,with_fees,help_platform]'
    ];
    
    if (!$this->validate($rules)) {
        return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
    }
    
    $campaignId = $this->request->getPost('campaign_id');
    $amount = (float) $this->request->getPost('amount');
    $feeOption = $this->request->getPost('fee_option');
    
    $campaign = $this->campaignModel->find($campaignId);
    if (!$campaign) {
        return $this->response->setJSON(['success' => false, 'message' => 'Campanha não encontrada']);
    }
    
    // Calcular taxas
    $fees = $this->calculateFees($amount, $campaign['category'], $feeOption);
    
    // Preparar split de pagamento
    $split = $this->prepareSplit($campaign, $amount, $fees);
    
    // Criar cobrança no Asaas
    $payment = $this->createAsaasPayment($campaign, $amount, $fees, $split);
    
    if ($payment['success']) {
        // Salvar doação no banco
        $donationData = [
            'campaign_id' => $campaignId,
            'donor_name' => $this->request->getPost('donor_name'),
            'donor_email' => $this->request->getPost('donor_email'),
            'amount' => $amount,
            'platform_fee' => $fees['platform'],
            'gateway_fee' => $fees['gateway'],
            'donor_paid_fees' => in_array($feeOption, ['with_fees', 'help_platform']),
            'payment_id' => $payment['data']['id'],
            'payment_status' => 'pending',
            'anonymous' => (bool) $this->request->getPost('anonymous'),
            'message' => $this->request->getPost('message', '')
        ];
        
        $this->donationModel->insert($donationData);
        
        // Resposta com link de pagamento
        return $this->response->setJSON([
            'success' => true,
            'payment_url' => $payment['data']['invoiceUrl'],
            'payment_id' => $payment['data']['id']
        ]);
    }
    
    return $this->response->setJSON(['success' => false, 'message' => 'Erro ao processar pagamento']);
}

private function calculateFees($amount, $category, $feeOption) {
    $gatewayFeePercent = 3.49; // Taxa do Asaas
    $gatewayFeeFixed = 0.49;   // Taxa fixa do Asaas
    
    $gatewayFee = ($amount * $gatewayFeePercent / 100) + $gatewayFeeFixed;
    $platformFee = 0;
    $extraTip = 0;
    
    // Calcular taxa da plataforma baseada na categoria e opção
    if (in_array($category, ['medica', 'social'])) {
        // Campanhas médicas/sociais
        if ($feeOption === 'with_fees') {
            $platformFee = $amount * 0.01; // 1% se doador pagar taxas
        }
    } else {
        // Outras campanhas
        $platformFee = $amount * 0.01; // 1% sempre
        if ($feeOption === 'help_platform') {
            $extraTip = $amount * 0.01; // 1% extra se quiser ajudar
        }
    }
    
    return [
        'gateway' => round($gatewayFee, 2),
        'platform' => round($platformFee, 2),
        'extra_tip' => round($extraTip, 2),
        'total_fees' => round($gatewayFee + $platformFee + $extraTip, 2)
    ];
}

private function prepareSplit($campaign, $amount, $fees) {
    $splits = [];
    
    // Split para o criador da campanha
    $creatorAmount = $amount - $fees['platform'] - $fees['extra_tip'];
    
    if ($creatorAmount > 0) {
        $splits[] = [
            'walletId' => $campaign['asaas_wallet_id'],
            'fixedValue' => $creatorAmount
        ];
    }
    
    // Split para a plataforma (se houver taxa)
    $platformTotal = $fees['platform'] + $fees['extra_tip'];
    if ($platformTotal > 0) {
        $splits[] = [
            'walletId' => env('ASAAS_PLATFORM_WALLET_ID'),
            'fixedValue' => $platformTotal
        ];
    }
    
    return $splits;
}
```

### **5.4 Sistema "Tudo ou Tudo"**

#### **Processamento de Campanhas Expiradas**
```php
// Command: ProcessExpiredCampaigns.php (para rodar via CRON)
public function run(array $params) {
    $expiredCampaigns = $this->campaignModel->where('deadline <', date('Y-m-d'))
                                           ->where('status', 'active')
                                           ->where('type', 'tudo_ou_tudo')
                                           ->findAll();
    
    foreach ($expiredCampaigns as $campaign) {
        if ($campaign['current_amount'] < $campaign['goal_amount']) {
            $this->processFailedCampaign($campaign);
        } else {
            $this->processSuccessfulCampaign($campaign);
        }
    }
}

private function processFailedCampaign($campaign) {
    // Buscar todas as doações da campanha
    $donations = $this->donationModel->where('campaign_id', $campaign['id'])
                                    ->where('payment_status', 'confirmed')
                                    ->findAll();
    
    $totalAmount = array_sum(array_column($donations, 'amount'));
    
    if ($totalAmount > 0) {
        // Calcular redistribuição (conforme especificado)
        $platformAmount = $totalAmount * 0.01;  // 1% para plataforma
        $proVidaAmount = $totalAmount * 0.49;   // 49% para Central do Dízimo
        $medicalAmount = $totalAmount * 0.50;   // 50% para campanhas médicas
        
        // Transferir para Central Geral do Dízimo Pró-Vida
        $this->transferToProVida($proVidaAmount, $campaign['id']);
        
        // Permitir doadores escolherem campanha médica de destino
        $this->createRedistributionChoices($donations, $medicalAmount);
        
        // Registrar taxa da plataforma
        $this->recordPlatformFee($platformAmount, $campaign['id'], 'failed_campaign');
    }
    
    // Atualizar status da campanha
    $this->campaignModel->update($campaign['id'], ['status' => 'failed_redistributed']);
    
    // Notificar envolvidos
    $this->notifyFailedCampaign($campaign, $donations);
}

private function createRedistributionChoices($donations, $totalMedicalAmount) {
    // Buscar campanhas médicas ativas
    $medicalCampaigns = $this->campaignModel->where('category', 'medica')
                                           ->where('status', 'active')
                                           ->orderBy('deadline', 'ASC')
                                           ->limit(10)
                                           ->findAll();
    
    foreach ($donations as $donation) {
        // Calcular parte proporcional de cada doador
        $donorPortion = ($donation['amount'] / array_sum(array_column($donations, 'amount'))) * $totalMedicalAmount;
        
        // Criar escolha de redistribuição
        $this->redistributionModel->insert([
            'original_donation_id' => $donation['id'],
            'donor_email' => $donation['donor_email'],
            'amount_to_redistribute' => $donorPortion,
            'available_campaigns' => json_encode($medicalCampaigns),
            'status' => 'pending_choice',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
        ]);
        
        // Enviar email para doador escolher destino
        $this->emailService->sendRedistributionChoice($donation['donor_email'], $donorPortion, $medicalCampaigns);
    }
}
```

---

## 💳 **6. INTEGRAÇÃO GATEWAY DE PAGAMENTO** {#integração-gateway}

### **6.1 Configuração Asaas API**

#### **Classe AsaasAPI**
```php
// app/Libraries/AsaasAPI.php
<?php

namespace App\Libraries;

class AsaasAPI {
    private $apiKey;
    private $baseUrl;
    private $webhookUrl;
    
    public function __construct() {
        $this->apiKey = env('ASAAS_API_KEY');
        $this->baseUrl = env('ASAAS_SANDBOX') ? 'https://sandbox.asaas.com/api/v3' : 'https://api.asaas.com/v3';
        $this->webhookUrl = base_url('webhook/asaas');
    }
    
    public function createCustomer($data) {
        return $this->request('POST', '/customers', [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'cpfCnpj' => preg_replace('/\D/', '', $data['cpf']),
            'postalCode' => $data['postal_code'] ?? null,
            'address' => $data['address'] ?? null,
            'addressNumber' => $data['address_number'] ?? null,
            'complement' => $data['complement'] ?? null,
            'province' => $data['province'] ?? null,
            'city' => $data['city'] ?? null
        ]);
    }
    
    public function createPayment($customerId, $amount, $description, $split = null) {
        $paymentData = [
            'customer' => $customerId,
            'billingType' => 'PIX', // Preferência por PIX (mais barato)
            'value' => $amount,
            'description' => $description,
            'dueDate' => date('Y-m-d', strtotime('+1 day')),
            'externalReference' => 'DOACAO_' . uniqid(),
            
            // Configurar métodos de pagamento
            'creditCard' => [
                'acceptInstallments' => true,
                'maxInstallmentCount' => 12
            ],
            
            // Configurar webhook
            'callback' => [
                'successUrl' => base_url('pagamento/sucesso'),
                'autoRedirect' => false
            ]
        ];
        
        // Adicionar split se fornecido
        if ($split && !empty($split)) {
            $paymentData['split'] = $split;
        }
        
        return $this->request('POST', '/payments', $paymentData);
    }
    
    public function createWallet($data) {
        return $this->request('POST', '/wallets', [
            'name' => $data['name'],
            'email' => $data['email'],
            'cpfCnpj' => preg_replace('/\D/', '', $data['cpf']),
            'companyType' => 'INDIVIDUAL', // Pessoa física
            'phone' => $data['phone']
        ]);
    }
    
    public function getPayment($paymentId) {
        return $this->request('GET', "/payments/{$paymentId}");
    }
    
    public function refundPayment($paymentId, $value = null) {
        $data = [];
        if ($value !== null) {
            $data['value'] = $value;
        }
        
        return $this->request('POST', "/payments/{$paymentId}/refund", $data);
    }
    
    public function transfer($walletId, $value, $description) {
        return $this->request('POST', '/transfers', [
            'walletId' => $walletId,
            'value' => $value,
            'description' => $description
        ]);
    }
    
    private function request($method, $endpoint, $data = null) {
        $curl = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'access_token: ' . $this->apiKey
        ];
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            log_message('error', "Asaas API Error: {$error}");
            return ['success' => false, 'error' => $error];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $decodedResponse];
        } else {
            log_message('error', "Asaas API Error {$httpCode}: " . $response);
            return ['success' => false, 'error' => $decodedResponse['errors'] ?? 'Erro desconhecido', 'http_code' => $httpCode];
        }
    }
}
```

#### **Webhook Handler**
```php
// Controller: Webhook.php
<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Webhook extends ResourceController {
    protected $donationModel;
    protected $campaignModel;
    
    public function __construct() {
        $this->donationModel = model('DonationModel');
        $this->campaignModel = model('CampaignModel');
    }
    
    public function asaas() {
        // Verificar se é uma requisição POST
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405);
        }
        
        // Obter payload
        $payload = json_decode($this->request->getBody(), true);
        
        if (!$payload || !isset($payload['event'])) {
            log_message('error', 'Webhook Asaas: Payload inválido');
            return $this->response->setStatusCode(400);
        }
        
        try {
            switch ($payload['event']) {
                case 'PAYMENT_CONFIRMED':
                    $this->handlePaymentConfirmed($payload['payment']);
                    break;
                    
                case 'PAYMENT_RECEIVED':
                    $this->handlePaymentReceived($payload['payment']);
                    break;
                    
                case 'PAYMENT_OVERDUE':
                    $this->handlePaymentOverdue($payload['payment']);
                    break;
                    
                case 'PAYMENT_DELETED':
                    $this->handlePaymentDeleted($payload['payment']);
                    break;
                    
                default:
                    log_message('info', "Webhook Asaas: Evento não tratado - {$payload['event']}");
            }
            
            return $this->response->setStatusCode(200)->setJSON(['status' => 'success']);
            
        } catch (\Exception $e) {
            log_message('error', "Webhook Asaas Error: " . $e->getMessage());
            return $this->response->setStatusCode(500);
        }
    }
    
    private function handlePaymentConfirmed($payment) {
        $donation = $this->donationModel->where('payment_id', $payment['id'])->first();
        
        if (!$donation) {
            log_message('error', "Doação não encontrada para payment_id: {$payment['id']}");
            return;
        }
        
        // Atualizar status da doação
        $this->donationModel->update($donation['id'], ['payment_status' => 'confirmed']);
        
        // Atualizar valor arrecadado da campanha
        $campaign = $this->campaignModel->find($donation['campaign_id']);
        $newAmount = $campaign['current_amount'] + $donation['amount'];
        $this->campaignModel->update($campaign['id'], ['current_amount' => $newAmount]);
        
        // Verificar se atingiu a meta
        if ($newAmount >= $campaign['goal_amount']) {
            $this->campaignModel->update($campaign['id'], ['status' => 'completed']);
            $this->sendGoalReachedNotifications($campaign, $donation);
        }
        
        // Enviar email de agradecimento
        $this->sendDonationConfirmation($donation, $campaign);
        
        // Notificar criador da campanha
        $this->notifyCampaignOwner($campaign, $donation);
        
        log_message('info', "Pagamento confirmado: {$payment['id']} - R$ {$donation['amount']}");
    }
    
    private function handlePaymentReceived($payment) {
        // Similar ao confirmed, mas para quando o pagamento é efetivamente recebido
        $this->handlePaymentConfirmed($payment);
    }
    
    private function handlePaymentOverdue($payment) {
        $donation = $this->donationModel->where('payment_id', $payment['id'])->first();
        
        if ($donation) {
            $this->donationModel->update($donation['id'], ['payment_status' => 'overdue']);
            
            // Enviar lembrete para o doador
            $this->sendPaymentReminder($donation);
        }
    }
    
    private function handlePaymentDeleted($payment) {
        $donation = $this->donationModel->where('payment_id', $payment['id'])->first();
        
        if ($donation) {
            $this->donationModel->update($donation['id'], ['payment_status' => 'cancelled']);
        }
    }
}
```

### **6.2 Frontend JavaScript para Integração**
```javascript
// public/assets/js/donation.js
class DonationHandler {
    constructor() {
        this.initializeEventListeners();
        this.updateCalculations();
    }
    
    initializeEventListeners() {
        // Botões de valor sugerido
        document.querySelectorAll('.amount-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const amount = parseFloat(btn.dataset.amount);
                this.setAmount(amount);
            });
        });
        
        // Input de valor customizado
        const customAmountInput = document.getElementById('custom-amount');
        customAmountInput.addEventListener('input', (e) => {
            const amount = parseFloat(e.target.value) || 0;
            this.setAmount(amount);
        });
        
        // Opções de taxa
        document.querySelectorAll('input[name="fee_option"]').forEach(radio => {
            radio.addEventListener('change', () => {
                this.updateCalculations();
            });
        });
        
        // Formulário de doação
        const donationForm = document.getElementById('donation-form');
        donationForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.processDonation();
        });
    }
    
    setAmount(amount) {
        document.getElementById('custom-amount').value = amount.toFixed(2);
        document.querySelectorAll('.amount-btn').forEach(btn => {
            btn.classList.toggle('selected', parseFloat(btn.dataset.amount) === amount);
        });
        this.updateCalculations();
    }
    
    updateCalculations() {
        const amount = parseFloat(document.getElementById('custom-amount').value) || 0;
        const campaignCategory = document.body.dataset.campaignCategory;
        const feeOption = document.querySelector('input[name="fee_option"]:checked')?.value || 'standard';
        
        if (amount <= 0) return;
        
        // Calcular taxas
        const fees = this.calculateFees(amount, campaignCategory, feeOption);
        
        // Atualizar interface
        this.updateFeeDisplay(campaignCategory);
        this.updateSummary(amount, fees, feeOption);
        this.updateAmountDisplays(amount, fees);
    }
    
    calculateFees(amount, category, feeOption) {
        const GATEWAY_PERCENT = 3.49;
        const GATEWAY_FIXED = 0.49;
        
        const gatewayFee = (amount * GATEWAY_PERCENT / 100) + GATEWAY_FIXED;
        let platformFee = 0;
        let extraTip = 0;
        
        if (['medica', 'social'].includes(category)) {
            // Campanhas médicas/sociais
            if (feeOption === 'with_fees') {
                platformFee = amount * 0.01; // 1%
            }
        } else {
            // Outras campanhas
            platformFee = amount * 0.01; // 1% sempre
            if (feeOption === 'help_platform') {
                extraTip = amount * 0.01; // 1% extra
            }
        }
        
        return {
            gateway: Math.round(gatewayFee * 100) / 100,
            platform: Math.round(platformFee * 100) / 100,
            extraTip: Math.round(extraTip * 100) / 100,
            total: Math.round((gatewayFee + platformFee + extraTip) * 100) / 100
        };
    }
    
    updateFeeDisplay(category) {
        const medicalOptions = document.getElementById('fee-options-medical');
        const otherOptions = document.getElementById('fee-options-other');
        
        if (['medica', 'social'].includes(category)) {
            medicalOptions.style.display = 'block';
            otherOptions.style.display = 'none';
        } else {
            medicalOptions.style.display = 'none';
            otherOptions.style.display = 'block';
        }
    }
    
    updateSummary(amount, fees, feeOption) {
        // Atualizar resumo de pagamento
        document.querySelector('.summary-donation').textContent = `R$ ${amount.toFixed(2)}`;
        
        const summaryFees = document.getElementById('summary-fees');
        const summaryPlatform = document.getElementById('summary-platform');
        
        if (['with_fees', 'help_platform'].includes(feeOption)) {
            summaryFees.style.display = 'flex';
            document.querySelector('.summary-gateway-fee').textContent = `R$ ${fees.gateway.toFixed(2)}`;
        } else {
            summaryFees.style.display = 'none';
        }
        
        if (fees.platform + fees.extraTip > 0) {
            summaryPlatform.style.display = 'flex';
            document.querySelector('.summary-platform-fee').textContent = `R$ ${(fees.platform + fees.extraTip).toFixed(2)}`;
        } else {
            summaryPlatform.style.display = 'none';
        }
        
        // Total
        let total = amount;
        if (['with_fees', 'help_platform'].includes(feeOption)) {
            total += fees.gateway + fees.platform + fees.extraTip;
        }
        
        document.querySelector('.summary-total').textContent = `R$ ${total.toFixed(2)}`;
    }
    
    updateAmountDisplays(amount, fees) {
        // Atualizar valores exibidos nas opções
        document.querySelectorAll('.donation-amount').forEach(el => {
            el.textContent = `R$ ${amount.toFixed(2)}`;
        });
        
        document.querySelectorAll('.gateway-fee').forEach(el => {
            el.textContent = `R$ ${fees.gateway.toFixed(2)}`;
        });
        
        document.querySelectorAll('.creator-receives').forEach(el => {
            const creatorReceives = amount - fees.gateway;
            el.textContent = `R$ ${creatorReceives.toFixed(2)}`;
        });
        
        document.querySelectorAll('.creator-receives-other').forEach(el => {
            const creatorReceives = amount - fees.gateway - fees.platform;
            el.textContent = `R$ ${creatorReceives.toFixed(2)}`;
        });
    }
    
    async processDonation() {
        const formData = new FormData(document.getElementById('donation-form'));
        const submitButton = document.querySelector('button[type="submit"]');
        
        // Desabilitar botão durante processamento
        submitButton.disabled = true;
        submitButton.textContent = 'Processando...';
        
        try {
            const response = await fetch('/donation/process', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Redirecionar para página de pagamento
                window.location.href = result.payment_url;
            } else {
                this.showError(result.message || 'Erro ao processar doação');
            }
            
        } catch (error) {
            console.error('Erro:', error);
            this.showError('Erro de conexão. Tente novamente.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Doar Agora';
        }
    }
    
    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4';
        errorDiv.textContent = message;
        
        const form = document.getElementById('donation-form');
        form.insertBefore(errorDiv, form.firstChild);
        
        // Remover após 5 segundos
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
}

// Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    new DonationHandler();
});
```

---

## 📱 **7. INTEGRAÇÃO WHATSAPP** {#integração-whatsapp}

### **7.1 Configuração WhatsApp Business API**

#### **Biblioteca WhatsApp**
```php
// app/Libraries/WhatsAppAPI.php
<?php

namespace App\Libraries;

class WhatsAppAPI {
    private $accessToken;
    private $phoneNumberId;
    private $baseUrl;
    
    public function __construct() {
        $this->accessToken = env('WHATSAPP_ACCESS_TOKEN');
        $this->phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID');
        $this->baseUrl = 'https://graph.facebook.com/v18.0';
    }
    
    public function sendMessage($to, $message) {
        return $this->request('POST', "/{$this->phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ]);
    }
    
    public function sendTemplate($to, $templateName, $parameters = []) {
        return $this->request('POST', "/{$this->phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => 'pt_BR'
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => $parameters
                    ]
                ]
            ]
        ]);
    }
    
    public function sendCampaignUpdate($to, $campaign, $newDonation) {
        $message = "🎉 *Boa notícia!*\n\n";
        $message .= "Sua campanha \"*{$campaign['title']}*\" recebeu uma nova doação!\n\n";
        $message .= "💰 Valor: R$ " . number_format($newDonation['amount'], 2, ',', '.') . "\n";
        $message .= "📊 Total arrecadado: R$ " . number_format($campaign['current_amount'], 2, ',', '.') . "\n";
        
        $percentage = ($campaign['current_amount'] / $campaign['goal_amount']) * 100;
        $message .= "🎯 Progresso: " . number_format($percentage, 1) . "% da meta\n\n";
        
        if (!$newDonation['anonymous']) {
            $message .= "👤 Doador: {$newDonation['donor_name']}\n";
        }
        
        if (!empty($newDonation['message'])) {
            $message .= "💬 Mensagem: \"{$newDonation['message']}\"\n\n";
        }
        
        $message .= "Ver campanha: " . base_url("campanha/{$campaign['id']}");
        
        return $this->sendMessage($to, $message);
    }
    
    public function sendGoalReached($to, $campaign) {
        $message = "🎊 *PARABÉNS! META ATINGIDA!* 🎊\n\n";
        $message .= "Sua campanha \"*{$campaign['title']}*\" atingiu a meta!\n\n";
        $message .= "💰 Valor arrecadado: R$ " . number_format($campaign['current_amount'], 2, ',', '.') . "\n";
        $message .= "🎯 Meta: R$ " . number_format($campaign['goal_amount'], 2, ',', '.') . "\n\n";
        $message .= "Agora você pode sacar o valor arrecadado em sua conta.\n\n";
        $message .= "Acesse: " . base_url("dashboard/campanhas/{$campaign['id']}");
        
        return $this->sendMessage($to, $message);
    }
    
    public function sendDonationThankYou($to, $donation, $campaign) {
        $message = "🙏 *Obrigado pela sua doação!*\n\n";
        $message .= "Sua doação de R$ " . number_format($donation['amount'], 2, ',', '.') . " para a campanha \"*{$campaign['title']}*\" foi confirmada!\n\n";
        $message .= "Você está fazendo a diferença! ❤️\n\n";
        $message .= "Acompanhe o progresso: " . base_url("campanha/{$campaign['id']}");
        
        return $this->sendMessage($to, $message);
    }
    
    public function sendUrgentCampaignAlert($to, $campaign) {
        $daysLeft = max(0, (strtotime($campaign['deadline']) - time()) / 86400);
        $percentage = ($campaign['current_amount'] / $campaign['goal_amount']) * 100;
        
        $message = "⚠️ *CAMPANHA URGENTE* ⚠️\n\n";
        $message .= "A campanha \"*{$campaign['title']}*\" precisa da sua ajuda!\n\n";
        $message .= "⏰ Restam apenas " . ceil($daysLeft) . " dias\n";
        $message .= "📊 Apenas " . number_format($percentage, 1) . "% da meta atingida\n";
        $message .= "💰 Faltam R$ " . number_format($campaign['goal_amount'] - $campaign['current_amount'], 2, ',', '.') . "\n\n";
        $message .= "Doe agora: " . base_url("campanha/{$campaign['id']}");
        
        return $this->sendMessage($to, $message);
    }
    
    private function formatPhoneNumber($phone) {
        // Remover caracteres não numéricos
        $phone = preg_replace('/\D/', '', $phone);
        
        // Adicionar código do Brasil se não tiver
        if (strlen($phone) === 11 && substr($phone, 0, 1) !== '55') {
            $phone = '55' . $phone;
        } elseif (strlen($phone) === 10 && substr($phone, 0, 2) !== '55') {
            $phone = '55' . $phone;
        }
        
        return $phone;
    }
    
    private function request($method, $endpoint, $data = null) {
        $curl = curl_init();
        
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            log_message('error', "WhatsApp API Error: {$error}");
            return ['success' => false, 'error' => $error];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $decodedResponse];
        } else {
            log_message('error', "WhatsApp API Error {$httpCode}: " . $response);
            return ['success' => false, 'error' => $decodedResponse, 'http_code' => $httpCode];
        }
    }
}
```

### **7.2 Sistema de Notificações WhatsApp**

#### **Service: NotificationService**
```php
// app/Services/NotificationService.php
<?php

namespace App\Services;

use App\Libraries\WhatsAppAPI;

class NotificationService {
    private $whatsapp;
    private $userModel;
    private $notificationModel;
    
    public function __construct() {
        $this->whatsapp = new WhatsAppAPI();
        $this->userModel = model('UserModel');
        $this->notificationModel = model('NotificationModel');
    }
    
    public function notifyNewDonation($donation, $campaign) {
        // Buscar dados do criador da campanha
        $campaignOwner = $this->userModel->find($campaign['user_id']);
        
        if ($campaignOwner && !empty($campaignOwner['phone'])) {
            // Enviar notificação via WhatsApp
            $result = $this->whatsapp->sendCampaignUpdate(
                $campaignOwner['phone'], 
                $campaign, 
                $donation
            );
            
            // Registrar notificação no banco
            $this->logNotification('whatsapp', 'new_donation', $campaignOwner['id'], $result);
        }
        
        // Notificar doador se forneceu WhatsApp
        if (!empty($donation['donor_phone'])) {
            $this->whatsapp->sendDonationThankYou(
                $donation['donor_phone'], 
                $donation, 
                $campaign
            );
        }
    }
    
    public function notifyGoalReached($campaign) {
        $campaignOwner = $this->userModel->find($campaign['user_id']);
        
        if ($campaignOwner && !empty($campaignOwner['phone'])) {
            $result = $this->whatsapp->sendGoalReached($campaignOwner['phone'], $campaign);
            $this->logNotification('whatsapp', 'goal_reached', $campaignOwner['id'], $result);
        }
    }
    
    public function sendUrgentCampaignAlerts() {
        // Buscar campanhas urgentes (últimos 3 dias antes do prazo)
        $urgentCampaigns = $this->campaignModel
            ->where('deadline <=', date('Y-m-d', strtotime('+3 days')))
            ->where('deadline >=', date('Y-m-d'))
            ->where('status', 'active')
            ->where('current_amount <', 'goal_amount')
            ->findAll();
            
        foreach ($urgentCampaigns as $campaign) {
            $percentage = ($campaign['current_amount'] / $campaign['goal_amount']) * 100;
            
            // Só notificar se estiver abaixo de 80% da meta
            if ($percentage < 80) {
                // Buscar doadores anteriores desta campanha
                $pastDonors = $this->donationModel
                    ->select('DISTINCT donor_phone, donor_email')
                    ->where('campaign_id', $campaign['id'])
                    ->where('donor_phone !=', null)
                    ->where('payment_status', 'confirmed')
                    ->findAll();
                
                foreach ($pastDonors as $donor) {
                    if (!empty($donor['donor_phone'])) {
                        $this->whatsapp->sendUrgentCampaignAlert($donor['donor_phone'], $campaign);
                    }
                }
                
                // Notificar o criador também
                $owner = $this->userModel->find($campaign['user_id']);
                if ($owner && !empty($owner['phone'])) {
                    $this->whatsapp->sendUrgentCampaignAlert($owner['phone'], $campaign);
                }
            }
        }
    }
    
    public function sendWeeklySummary($userId) {
        $user = $this->userModel->find($userId);
        if (!$user || empty($user['phone'])) return;
        
        // Buscar campanhas do usuário
        $campaigns = $this->campaignModel
            ->where('user_id', $userId)
            ->where('status !=', 'deleted')
            ->findAll();
            
        if (empty($campaigns)) return;
        
        $message = "📊 *Resumo Semanal - DoarFazBem*\n\n";
        
        $totalRaised = 0;
        $activeCampaigns = 0;
        
        foreach ($campaigns as $campaign) {
            $totalRaised += $campaign['current_amount'];
            if ($campaign['status'] === 'active') {
                $activeCampaigns++;
            }
        }
        
        $message .= "💰 Total arrecadado: R$ " . number_format($totalRaised, 2, ',', '.') . "\n";
        $message .= "📈 Campanhas ativas: {$activeCampaigns}\n";
        $message .= "🎯 Campanhas concluídas: " . count(array_filter($campaigns, fn($c) => $c['status'] === 'completed')) . "\n\n";
        
        $message .= "Continue compartilhando suas campanhas para atingir suas metas! 🚀\n\n";
        $message .= "Acessar painel: " . base_url('dashboard');
        
        $this->whatsapp->sendMessage($user['phone'], $message);
    }
    
    private function logNotification($type, $event, $userId, $result) {
        $this->notificationModel->insert([
            'user_id' => $userId,
            'type' => $type,
            'event' => $event,
            'success' => $result['success'] ? 1 : 0,
            'response' => json_encode($result),
            'sent_at' => date('Y-m-d H:i:s')
        ]);
    }
}
```

### **7.3 Interface de Configuração WhatsApp**

#### **Página de Configurações do Usuário**
```html
<!-- views/user/settings.php -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-xl font-bold mb-4">Notificações WhatsApp</h3>
    
    <form id="whatsapp-settings-form">
        <div class="mb-4">
            <label class="flex items-center space-x-3">
                <input type="checkbox" name="whatsapp_enabled" <?= $user['whatsapp_enabled'] ? 'checked' : '' ?>>
                <span class="text-sm">Receber notificações via WhatsApp</span>
            </label>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium mb-2">Número do WhatsApp</label>
            <input type="tel" name="whatsapp_phone" value="<?= $user['phone'] ?>" 
                   class="w-full px-3 py-2 border rounded-lg"
                   placeholder="(11) 99999-9999">
            <p class="text-xs text-gray-500 mt-1">
                Mantenha atualizado para receber notificações importantes
            </p>
        </div>
        
        <div class="space-y-2 mb-6">
            <h4 class="font-medium">Tipos de notificação:</h4>
            
            <label class="flex items-center space-x-3">
                <input type="checkbox" name="notify_new_donation" 
                       <?= $user['notify_new_donation'] ? 'checked' : '' ?>>
                <span class="text-sm">Novas doações</span>
            </label>
            
            <label class="flex items-center space-x-3">
                <input type="checkbox" name="notify_goal_reached" 
                       <?= $user['notify_goal_reached'] ? 'checked' : '' ?>>
                <span class="text-sm">Meta atingida</span>
            </label>
            
            <label class="flex items-center space-x-3">
                <input type="checkbox" name="notify_campaign_urgent" 
                       <?= $user['notify_campaign_urgent'] ? 'checked' : '' ?>>
                <span class="text-sm">Campanhas urgentes (últimos dias)</span>
            </label>
            
            <label class="flex items-center space-x-3">
                <input type="checkbox" name="notify_weekly_summary" 
                       <?= $user['notify_weekly_summary'] ? 'checked' : '' ?>>
                <span class="text-sm">Resumo semanal</span>
            </label>
        </div>
        
        <button type="submit" class="btn-primary">
            Salvar Configurações
        </button>
    </form>
    
    <!-- Teste de conectividade -->
    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <h4 class="font-medium mb-2">Testar WhatsApp</h4>
        <p class="text-sm text-gray-600 mb-3">
            Envie uma mensagem de teste para verificar se está funcionando
        </p>
        <button type="button" id="test-whatsapp" class="btn-secondary-small">
            Enviar Teste
        </button>
    </div>
</div>

<script>
document.getElementById('test-whatsapp').addEventListener('click', async function() {
    const button = this;
    button.disabled = true;
    button.textContent = 'Enviando...';
    
    try {
        const response = await fetch('/user/test-whatsapp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Mensagem de teste enviada! Verifique seu WhatsApp.');
        } else {
            alert('❌ Erro ao enviar: ' + result.message);
        }
    } catch (error) {
        alert('❌ Erro de conexão. Tente novamente.');
    } finally {
        button.disabled = false;
        button.textContent = 'Enviar Teste';
    }
});
</script>
```

---

## 📢 **8. SISTEMA DE PUBLICIDADE** {#sistema-publicidade}

### **8.1 Estrutura de Anúncios**

#### **Tabela: advertisements**
```sql
CREATE TABLE advertisements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    link_url VARCHAR(255) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    
    -- Posicionamento
    position ENUM('header_banner', 'sidebar', 'campaign_page', 'homepage_featured', 'newsletter') NOT NULL,
    
    -- Segmentação
    target_categories JSON, -- ['medica', 'social', 'criativa']
    target_regions JSON,   -- ['SP', 'RJ', 'MG']
    target_keywords JSON,  -- ['saúde', 'educação']
    
    -- Financeiro
    price_monthly DECIMAL(10,2) NOT NULL,
    commission_percentage DECIMAL(5,2) DEFAULT 0, -- % para campanhas específicas
    
    -- Status e controle
    status ENUM('pending', 'active', 'paused', 'expired') DEFAULT 'pending',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    
    -- Métricas
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status_position (status, position),
    INDEX idx_dates (start_date, end_date)
);
```

#### **Tabela: advertisement_campaigns (para anúncios específicos)**
```sql
CREATE TABLE advertisement_campaigns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    advertisement_id INT NOT NULL,
    campaign_id INT NOT NULL,
    commission_percentage DECIMAL(5,2) DEFAULT 0,
    total_earned DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (advertisement_id) REFERENCES advertisements(id),
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id),
    UNIQUE KEY unique_ad_campaign (advertisement_id, campaign_id)
);
```

### **8.2 Sistema de Exibição de Anúncios**

#### **AdService**
```php
// app/Services/AdService.php
<?php

namespace App\Services;

class AdService {
    private $adModel;
    private $campaignModel;
    
    public function __construct() {
        $this->adModel = model('AdvertisementModel');
        $this->campaignModel = model('CampaignModel');
    }
    
    public function getAdsForPosition($position, $context = []) {
        $query = $this->adModel
            ->where('status', 'active')
            ->where('position', $position)
            ->where('start_date <=', date('Y-m-d'))
            ->where('end_date >=', date('Y-m-d'));
            
        // Aplicar segmentação se houver contexto
        if (isset($context['campaign_category'])) {
            $query->where("JSON_CONTAINS(target_categories, '\"{$context['campaign_category']}\"') OR target_categories IS NULL");
        }
        
        if (isset($context['user_region'])) {
            $query->where("JSON_CONTAINS(target_regions, '\"{$context['user_region']}\"') OR target_regions IS NULL");
        }
        
        $ads = $query->orderBy('RAND()')->limit(3)->findAll();
        
        // Registrar impressões
        foreach ($ads as $ad) {
            $this->recordImpression($ad['id'], $context);
        }
        
        return $ads;
    }
    
    public function getAdsForCampaign($campaignId) {
        $campaign = $this->campaignModel->find($campaignId);
        if (!$campaign) return [];
        
        // Anúncios gerais para categoria
        $generalAds = $this->getAdsForPosition('campaign_page', [
            'campaign_category' => $campaign['category'],
            'campaign_id' => $campaignId
        ]);
        
        // Anúncios específicos desta campanha
        $specificAds = $this->adModel
            ->select('advertisements.*')
            ->join('advertisement_campaigns ac', 'ac.advertisement_id = advertisements.id')
            ->where('ac.campaign_id', $campaignId)
            ->where('advertisements.status', 'active')
            ->findAll();
            
        return array_merge($generalAds, $specificAds);
    }
    
    public function recordClick($adId, $context = []) {
        $this->adModel->set('clicks', 'clicks + 1', false)
                     ->where('id', $adId)
                     ->update();
                     
        // Log para analytics
        $this->logAdEvent('click', $adId, $context);
        
        // Atualizar comissões se for anúncio específico de campanha
        if (isset($context['campaign_id'])) {
            $this->updateCommission($adId, $context['campaign_id']);
        }
    }
    
    private function recordImpression($adId, $context = []) {
        $this->adModel->set('impressions', 'impressions + 1', false)
                     ->where('id', $adId)
                     ->update();
                     
        $this->logAdEvent('impression', $adId, $context);
    }
    
    private function logAdEvent($event, $adId, $context) {
        $logModel = model('AdLogModel');
        $logModel->insert([
            'advertisement_id' => $adId,
            'event_type' => $event,
            'campaign_id' => $context['campaign_id'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $this->request->getIPAddress(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function updateCommission($adId, $campaignId) {
        // Buscar configuração de comissão
        $adCampaign = model('AdvertisementCampaignModel')
            ->where('advertisement_id', $adId)
            ->where('campaign_id', $campaignId)
            ->first();
            
        if ($adCampaign && $adCampaign['commission_percentage'] > 0) {
            // Implementar lógica de comissão por click ou por conversão
            // Exemplo: R$ 0,50 por click
            $commission = 0.50;
            
            model('AdvertisementCampaignModel')->set('total_earned', 'total_earned + ' . $commission, false)
                ->where('id', $adCampaign['id'])
                ->update();
        }
    }
}
```

### **8.3 Interface de Gestão de Anúncios**

#### **Painel Admin - Gestão de Anúncios**
```html
<!-- views/admin/ads/index.php -->
<div class="max-w-7xl mx-auto py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestão de Anúncios</h1>
        <button class="btn-primary" onclick="openAdModal()">
            Novo Anúncio
        </button>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-4 gap-4">
            <select name="filter_status" class="form-select">
                <option value="">Todos os Status</option>
                <option value="pending">Pendente</option>
                <option value="active">Ativo</option>
                <option value="paused">Pausado</option>
                <option value="expired">Expirado</option>
            </select>
            
            <select name="filter_position" class="form-select">
                <option value="">Todas as Posições</option>
                <option value="header_banner">Banner Topo</option>
                <option value="sidebar">Lateral</option>
                <option value="campaign_page">Página de Campanha</option>
                <option value="homepage_featured">Destaque Homepage</option>
            </select>
            
            <input type="date" name="filter_date" class="form-input">
            
            <button class="btn-secondary" onclick="applyFilters()">
                Filtrar
            </button>
        </div>
    </div>
    
    <!-- Lista de Anúncios -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anúncio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posição</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preço</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Métricas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody id="ads-table-body">
                <?php foreach ($ads as $ad): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <?php if ($ad['image_url']): ?>
                            <img src="<?= $ad['image_url'] ?>" class="h-12 w-12 object-cover rounded mr-3">
                            <?php endif; ?>
                            <div>
                                <div class="font-medium"><?= esc($ad['title']) ?></div>
                                <div class="text-sm text-gray-500"><?= esc($ad['company_name']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                            <?= ucfirst($ad['position']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <?= date('d/m/Y', strtotime($ad['start_date'])) ?> -<br>
                        <?= date('d/m/Y', strtotime($ad['end_date'])) ?>
                    </td>
                    <td class="px-6 py-4 font-medium">
                        R$ <?= number_format($ad['price_monthly'], 2, ',', '.') ?>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div><?= number_format($ad['impressions']) ?> impressões</div>
                        <div><?= number_format($ad['clicks']) ?> clicks</div>
                        <div class="text-gray-500">
                            CTR: <?= $ad['impressions'] > 0 ? number_format(($ad['clicks'] / $ad['impressions']) * 100, 2) : 0 ?>%
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 text-xs rounded-full 
                            <?= $ad['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                ($ad['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') ?>">
                            <?= ucfirst($ad['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button class="text-blue-600 hover:text-blue-800" onclick="editAd(<?= $ad['id'] ?>)">
                                Editar
                            </button>
                            <button class="text-green-600 hover:text-green-800" onclick="toggleAdStatus(<?= $ad['id'] ?>)">
                                <?= $ad['status'] === 'active' ? 'Pausar' : 'Ativar' ?>
                            </button>
                            <button class="text-red-600 hover:text-red-800" onclick="deleteAd(<?= $ad['id'] ?>)">
                                Excluir
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Criação/Edição -->
<div id="ad-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <h3 class="text-lg font-bold mb-4" id="modal-title">Novo Anúncio</h3>
                
                <form id="ad-form" enctype="multipart/form-data">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Título</label>
                            <input type="text" name="title" required class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Empresa</label>
                            <input type="text" name="company_name" required class="form-input">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Descrição</label>
                        <textarea name="description" rows="3" class="form-textarea"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Posição</label>
                            <select name="position" required class="form-select">
                                <option value="">Selecione...</option>
                                <option value="header_banner">Banner Topo - R$ 500/mês</option>
                                <option value="sidebar">Lateral - R$ 300/mês</option>
                                <option value="campaign_page">Página Campanha - R$ 200/mês</option>
                                <option value="homepage_featured">Destaque Homepage - R$ 600/mês</option>
                                <option value="newsletter">Newsletter - R$ 100/mês</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Preço Mensal (R$)</label>
                            <input type="number" name="price_monthly" step="0.01" required class="form-input">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Imagem do Anúncio</label>
                        <input type="file" name="ad_image" accept="image/*" class="form-input">
                        <p class="text-xs text-gray-500 mt-1">Tamanhos recomendados: Banner topo (728x90), Lateral (300x250)</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">URL de Destino</label>
                        <input type="url" name="link_url" required class="form-input" placeholder="https://example.com">
                    </div>
                    
                    <!-- Segmentação -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Segmentação (opcional)</label>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Categorias</label>
                                <div class="space-y-1">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="target_categories[]" value="medica">
                                        <span class="ml-2 text-sm">Médica</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="target_categories[]" value="social">
                                        <span class="ml-2 text-sm">Social</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="target_categories[]" value="criativa">
                                        <span class="ml-2 text-sm">Criativa</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Regiões</label>
                                <select name="target_regions[]" multiple class="form-select" size="3">
                                    <option value="SP">São Paulo</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="MG">Minas Gerais</option>
                                    <option value="RS">Rio Grande do Sul</option>
                                    <option value="PR">Paraná</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Data Início</label>
                            <input type="date" name="start_date" required class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Data Fim</label>
                            <input type="date" name="end_date" required class="form-input">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" class="btn-secondary" onclick="closeAdModal()">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary">
                            Salvar Anúncio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
```

### **8.4 Componentes de Exibição**

#### **Component: AdDisplay**
```php
// app/Views/components/ad_display.php
<?php 
$adService = service('AdService');
$ads = $adService->getAdsForPosition($position, $context ?? []);
?>

<?php if (!empty($ads)): ?>
<div class="ad-container ad-<?= $position ?>" data-position="<?= $position ?>">
    <?php foreach ($ads as $ad): ?>
    <div class="ad-item" data-ad-id="<?= $ad['id'] ?>">
        <?php if ($position === 'header_banner'): ?>
            <!-- Banner Topo -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <?php if ($ad['image_url']): ?>
                        <img src="<?= $ad['image_url'] ?>" class="h-12 w-auto mr-4" alt="<?= esc($ad['title']) ?>">
                        <?php endif; ?>
                        <div>
                            <h4 class="font-semibold text-gray-900"><?= esc($ad['title']) ?></h4>
                            <p class="text-sm text-gray-600"><?= esc($ad['description']) ?></p>
                        </div>
                    </div>
                    <a href="<?= $ad['link_url'] ?>" target="_blank" class="btn-primary-small ad-click" 
                       data-ad-id="<?= $ad['id'] ?>">
                        Saiba Mais
                    </a>
                </div>
                <div class="text-xs text-gray-400 mt-2">Publicidade</div>
            </div>
            
        <?php elseif ($position === 'sidebar'): ?>
            <!-- Sidebar -->
            <div class="bg-white border rounded-lg p-4 mb-4">
                <div class="text-xs text-gray-400 mb-2">Publicidade</div>
                <?php if ($ad['image_url']): ?>
                <img src="<?= $ad['image_url'] ?>" class="w-full h-32 object-cover rounded mb-3" 
                     alt="<?= esc($ad['title']) ?>">
                <?php endif; ?>
                <h4 class="font-semibold text-sm mb-2"><?= esc($ad['title']) ?></h4>
                <p class="text-xs text-gray-600 mb-3"><?= esc($ad['description']) ?></p>
                <a href="<?= $ad['link_url'] ?>" target="_blank" 
                   class="w-full btn-primary-small ad-click" data-ad-id="<?= $ad['id'] ?>">
                    Visitar Site
                </a>
            </div>
            
        <?php elseif ($position === 'campaign_page'): ?>
            <!-- Página de Campanha -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <?php if ($ad['image_url']): ?>
                    <img src="<?= $ad['image_url'] ?>" class="h-16 w-16 object-cover rounded mr-4" 
                         alt="<?= esc($ad['title']) ?>">
                    <?php endif; ?>
                    <div class="flex-1">
                        <div class="text-xs text-yellow-600 mb-1">Publicidade</div>
                        <h4 class="font-semibold text-gray-900 mb-1"><?= esc($ad['title']) ?></h4>
                        <p class="text-sm text-gray-600 mb-2"><?= esc($ad['description']) ?></p>
                        <a href="<?= $ad['link_url'] ?>" target="_blank" 
                           class="text-sm text-blue-600 hover:text-blue-800 ad-click" data-ad-id="<?= $ad['id'] ?>">
                            Clique aqui →
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<script>
// Rastrear clicks
document.querySelectorAll('.ad-click').forEach(link => {
    link.addEventListener('click', function(e) {
        const adId = this.dataset.adId;
        const context = {
            position: '<?= $position ?>',
            campaign_id: <?= $context['campaign_id'] ?? 'null' ?>,
            timestamp: Date.now()
        };
        
        // Enviar evento para analytics
        fetch('/api/ads/click', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ad_id: adId,
                context: context
            })
        }).catch(console.error);
    });
});
</script>
<?php endif; ?>
```

---

## 🎯 **9. ESTRATÉGIAS DE MARKETING** {#estratégias-marketing}

### **9.1 Marketing de Lançamento**

#### **Fase 1: Pré-Lançamento (30 dias antes)**
```
✅ Estratégias:

1. Landing Page de Captura:
   - "Em breve: A primeira plataforma 100% gratuita para campanhas médicas"
   - Formulário de inscrição para early access
   - Contador regressivo
   - Depoimentos simulados

2. Parcerias Estratégicas:
   - Hospitais locais (Salvador/Região)
   - ONGs estabelecidas
   - Influenciadores de saúde
   - Grupos de WhatsApp de mães

3. PR e Imprensa:
   - Press release sobre inovação social
   - Artigos em blogs de saúde
   - Entrevistas em rádios locais
   - Pitch para programas de TV matinais

4. SEO Preparatório:
   - Blog com artigos sobre doações
   - Palavras-chave: "vaquinha médica gratuita", "doação online Brasil"
   - Link building com sites de saúde
```

#### **Fase 2: Lançamento (Primeiros 30 dias)**
```
🚀 Estratégias:

1. Campanha de Referência:
   - Criar 1-2 campanhas próprias (casos reais conhecidos)
   - Documentar todo o processo
   - Usar como case de sucesso

2. Marketing Digital:
   - Google Ads: "vaquinha online", "crowdfunding médico"
   - Facebook/Instagram Ads para hospitais e familiares
   - LinkedIn para ONGs e empresas
   - TikTok com histórias emocionantes

3. Influencer Marketing:
   - Micro-influenciadores de saúde (10k-50k seguidores)
   - Médicos no Instagram
   - Mães blogueiras
   - Pastores/líderes religiosos

4. Guerrilla Marketing:
   - Panfletos em hospitais (com autorização)
   - Adesivos em carros com QR code
   - Presença em feiras de saúde
```

### **9.2 Estratégias de Aquisição**

#### **Marketing Digital - SEM/SEO**
```php
// Palavras-chave estratégicas
$keywordStrategy = [
    'alta_intencao' => [
        'vaquinha online gratuita',
        'crowdfunding médico brasil',
        'arrecadação de fundos saúde',
        'campanha médica sem taxa',
        'doação cirurgia online'
    ],
    
    'media_intencao' => [
        'como criar vaquinha',
        'plataforma de doações',
        'arrecadar dinheiro tratamento',
        'ajuda financeira médica',
        'sites de doação confiáveis'
    ],
    
    'baixa_intencao' => [
        'custos de cirurgia',
        'tratamento caro brasil',
        'financiar tratamento médico',
        'SUS não cobre',
        'como conseguir dinheiro emergência'
    ]
];

// Estratégia de conteúdo SEO
$contentStrategy = [
    'blog_posts' => [
        'Como criar uma campanha médica eficiente',
        '10 dicas para arrecadar mais doações online',
        'Direitos do paciente: quando o SUS não cobre',
        'Histórias inspiradoras de superação',
        'Transparência em campanhas: por que importa'
    ],
    
    'landing_pages' => [
        '/vaquinha-medica-gratuita',
        '/campanha-social-sem-taxa',
        '/ajuda-financeira-tratamento',
        '/doacao-transparente-online'
    ]
];
```

#### **Marketing de Conteúdo**
```markdown
📝 Cronograma de Conteúdo Mensal:

Semana 1: Educacional
- Segunda: "Como identificar uma campanha confiável"
- Quarta: "Direitos do doador: o que você precisa saber"
- Sexta: "Por que transparência importa em doações"

Semana 2: Inspiracional
- Segunda: História de sucesso da semana
- Quarta: Depoimento de doador satisfeito
- Sexta: Campanha em destaque

Semana 3: Técnico
- Segunda: "Passo a passo: criar sua primeira campanha"
- Quarta: "Dicas de fotografia para campanhas"
- Sexta: "Como compartilhar eficientemente"

Semana 4: Social
- Segunda: Dados sobre saúde pública no Brasil
- Quarta: Impacto social das doações online
- Sexta: Retrospectiva mensal dos resultados
```

### **9.3 Estratégias de Retenção**

#### **Programa de Fidelidade para Doadores**
```php
// app/Services/LoyaltyService.php
class LoyaltyService {
    public function calculateDonorLevel($totalDonated, $donationCount) {
        if ($totalDonated >= 1000 || $donationCount >= 10) {
            return 'gold'; // Selo de Anjo Dourado
        } elseif ($totalDonated >= 500 || $donationCount >= 5) {
            return 'silver'; // Selo de Protetor
        } elseif ($totalDonated >= 100 || $donationCount >= 2) {
            return 'bronze'; // Selo de Colaborador
        }
        return 'new'; // Novo Apoiador
    }
    
    public function getDonorBenefits($level) {
        $benefits = [
            'gold' => [
                'Certificado digital personalizado',
                'Acesso a relatórios exclusivos',
                'Canal VIP no WhatsApp',
                'Prioridade no atendimento',
                'Badge especial no perfil'
            ],
            'silver' => [
                'Relatório mensal de impacto',
                'Acesso antecipado a campanhas',
                'Badge no perfil'
            ],
            'bronze' => [
                'Newsletter personalizada',
                'Badge de colaborador'
            ]
        ];
        
        return $benefits[$level] ?? [];
    }
}
```

#### **Email Marketing Segmentado**
```php
// Segmentação de audiência
$emailSegments = [
    'novos_doadores' => [
        'criteria' => 'first_donation_date >= 30 days ago',
        'campaigns' => [
            'Boas-vindas + Como funciona',
            'Primeira campanha sugerida',
            'Dicas de segurança em doações'
        ]
    ],
    
    'doadores_recorrentes' => [
        'criteria' => 'donation_count >= 3',
        'campaigns' => [
            'Relatório de impacto mensal',
            'Campanhas exclusivas',
            'Programa de fidelidade'
        ]
    ],
    
    'criadores_ativos' => [
        'criteria' => 'campaigns_created >= 1 AND status = active',
        'campaigns' => [
            'Dicas para aumentar doações',
            'Melhores práticas de comunicação',
            'Histórias de sucesso similares'
        ]
    ],
    
    'dormentes' => [
        'criteria' => 'last_activity >= 60 days ago',
        'campaigns' => [
            'Saudade! Veja as novidades',
            'Campanhas urgentes que precisam de ajuda',
            'Ofertas especiais de reengajamento'
        ]
    ]
];
```

### **9.4 Parcerias Estratégicas**

#### **Programa de Parceiros**
```php
// Estrutura de parcerias
$partnershipPrograms = [
    'hospitais' => [
        'benefits' => [
            'Página dedicada do hospital',
            'Logo em campanhas relacionadas',
            'Dashboard exclusivo de campanhas',
            'Suporte prioritário'
        ],
        'requirements' => [
            'Mínimo 5 campanhas por mês',
            'Validação de casos médicos',
            'Termo de parceria assinado'
        ],
        'commission' => '0% (gratuito para hospitais)'
    ],
    
    'ongs' => [
        'benefits' => [
            'Campanhas sociais destacadas',
            'Ferramenta de relatórios',
            'Integração com APIs',
            'Treinamento gratuito'
        ],
        'requirements' => [
            'CNPJ ativo',
            'Comprovação de atividades',
            'Transparência financeira'
        ],
        'commission' => '0% (gratuito para ONGs)'
    ],
    
    'influencers' => [
        'benefits' => [
            'Link de referência personalizado',
            'Dashboard de performance',
            'Materiais promocionais',
            'Suporte dedicado'
        ],
        'commission' => '2% das doações indicadas',
        'requirements' => [
            'Mínimo 10k seguidores',
            'Engajamento > 3%',
            'Alinhamento com valores'
        ]
    ],
    
    'empresas' => [
        'benefits' => [
            'Selo de empresa parceira',
            'Campanhas corporativas',
            'Relatórios de impacto social',
            'Publicidade dirigida'
        ],
        'packages' => [
            'Básico: R$ 500/mês',
            'Premium: R$ 1.500/mês', 
            'Enterprise: Sob consulta'
        ]
    ]
];
```

### **9.5 Analytics e Métricas**

#### **KPIs Principais**
```php
// Dashboard de métricas
$kpis = [
    'aquisicao' => [
        'CAC' => 'Custo de Aquisição por Criador',
        'conversion_rate' => 'Taxa de conversão visitante → criador',
        'organic_growth' => 'Crescimento orgânico mensal',
        'referral_rate' => 'Taxa de indicação'
    ],
    
    'engajamento' => [
        'daily_active_users' => 'Usuários ativos diários',
        'session_duration' => 'Duração média da sessão',
        'pages_per_session' => 'Páginas por sessão',
        'bounce_rate' => 'Taxa de rejeição'
    ],
    
    'conversao' => [
        'donation_conversion' => 'Taxa visitante → doação',
        'average_donation' => 'Valor médio de doação',
        'repeat_donor_rate' => 'Taxa de doadores recorrentes',
        'campaign_success_rate' => 'Taxa de campanhas que atingem meta'
    ],
    
    'receita' => [
        'mrr' => 'Receita recorrente mensal',
        'ltv' => 'Valor de vida do cliente',
        'revenue_per_user' => 'Receita por usuário',
        'platform_fee_revenue' => 'Receita de taxas'
    ]
];

// Implementação de tracking
class AnalyticsService {
    public function trackEvent($event, $userId = null, $data = []) {
        $eventData = [
            'event' => $event,
            'user_id' => $userId,
            'session_id' => session_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'url' => current_url(),
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => json_encode($data)
        ];
        
        model('AnalyticsModel')->insert($eventData);
        
        // Enviar para Google Analytics 4 também
        $this->sendToGA4($event, $data);
    }
    
    public function getDashboardMetrics($period = '30days') {
        // Implementar queries para métricas do dashboard
        return [
            'total_users' => $this->getTotalUsers($period),
            'total_campaigns' => $this->getTotalCampaigns($period),
            'total_donations' => $this->getTotalDonations($period),
            'conversion_rate' => $this->getConversionRate($period),
            'top_campaigns' => $this->getTopCampaigns($period, 10)
        ];
    }
}
```

---

## 📅 **10. CRONOGRAMA DE DESENVOLVIMENTO** {#cronograma}

### **Fase 1: Fundação (Meses 1-2)**
```
🏗️ Mês 1:
Semana 1-2: Setup e Estrutura Base
- ✅ Configurar ambiente (Hertzner + CloudPanel)
- ✅ Instalar CodeIgniter 4 + TailwindCSS
- ✅ Configurar banco de dados
- ✅ Sistema de autenticação básico
- ✅ Layout base com header/footer

Semana 3-4: Sistema de Usuários
- ✅ Cadastro e login completo
- ✅ Verificação de email
- ✅ Perfil de usuário
- ✅ Dashboard básico
- ✅ Recuperação de senha

🎯 Mês 2:
Semana 1-2: Sistema de Campanhas
- ✅ Criar/editar campanhas
- ✅ Upload de imagens
- ✅ Categorização
- ✅ Listagem e busca
- ✅ Página individual da campanha

Semana 3-4: Integração Asaas
- ✅ Configurar API Asaas
- ✅ Sistema de split payment
- ✅ Webhooks básicos
- ✅ Cálculo de taxas
- ✅ Testes de pagamento
```

### **Fase 2: Funcionalidades Core (Meses 3-4)**
```
💳 Mês 3:
Semana 1-2: Sistema de Doações
- ✅ Interface de doação
- ✅ Lógica de taxas condicionais
- ✅ Processamento de pagamentos
- ✅ Confirmação de doações
- ✅ Histórico de doações

Semana 3-4: Dashboard e Gestão
- ✅ Dashboard do criador
- ✅ Dashboard do doador
- ✅ Métricas e gráficos
- ✅ Sistema de notificações
- ✅ Relatórios básicos

📱 Mês 4:
Semana 1-2: Integração WhatsApp
- ✅ Configurar WhatsApp Business API
- ✅ Notificações automáticas
- ✅ Templates de mensagem
- ✅ Configurações do usuário
- ✅ Testes de entrega

Semana 3-4: Sistema "Tudo ou Tudo"
- ✅ Lógica de redistribuição
- ✅ Interface de escolha para doadores
- ✅ Processamento automático
- ✅ Notificações específicas
- ✅ Relatórios de redistribuição
```

### **Fase 3: Recursos Avançados (Meses 5-6)**
```
📊 Mês 5:
Semana 1-2: Sistema de Publicidade
- ✅ Gestão de anúncios
- ✅ Segmentação de público
- ✅ Métricas de performance
- ✅ Interface administrativa
- ✅ Sistema de cobrança

Semana 3-4: SEO e Performance
- ✅ Otimização de URLs
- ✅ Meta tags dinâmicas
- ✅ Sitemap XML
- ✅ Cache de páginas
- ✅ Compressão de imagens

🚀 Mês 6:
Semana 1-2: Testes e Refinamentos
- ✅ Testes de carga
- ✅ Testes de segurança
- ✅ Correção de bugs
- ✅ Otimização de performance
- ✅ Testes em dispositivos móveis

Semana 3-4: Preparação para Lançamento
- ✅ Documentação completa
- ✅ Termos de uso e privacidade
- ✅ Treinamento de suporte
- ✅ Backup e monitoramento
- ✅ Marketing de pré-lançamento
```

### **Fase 4: Lançamento e Crescimento (Mês 7+)**
```
🎉 Mês 7: Lançamento Oficial
Semana 1: Soft Launch
- ✅ Lançar para grupo teste (50 usuários)
- ✅ Coletar feedback
- ✅ Ajustes rápidos
- ✅ Monitoramento intensivo

Semana 2-3: Marketing Ativo
- ✅ Campanhas pagas (Google/Facebook)
- ✅ PR e imprensa
- ✅ Parcerias com influencers
- ✅ Conteúdo no blog

Semana 4: Análise e Otimização
- ✅ Análise de métricas
- ✅ Feedback dos usuários
- ✅ Ajustes de UX
- ✅ Plano para mês seguinte

📈 Meses 8-12: Crescimento e Expansão
- ✅ Novos recursos baseados em feedback
- ✅ Expansão de parcerias
- ✅ Otimização de conversão
- ✅ Automação de processos
- ✅ Planejamento de escala
```

---

## ✅ **11. CHECKLIST DE IMPLEMENTAÇÃO** {#checklist}

### **Pre-Development Checklist**
```
🔧 Configuração Inicial:
□ Registrar domínio doarfazbem.com.br (✅ já feito)
□ Contratar servidor Hertzner
□ Instalar CloudPanel
□ Configurar SSL (Let's Encrypt)
□ Configurar CDN Cloudflare
□ Setup do ambiente de desenvolvimento local

🗃️ Banco de Dados:
□ Criar banco MySQL
□ Executar migrations principais
□ Configurar backup automático
□ Setup do ambiente de teste
□ Seed data para desenvolvimento

📧 Serviços Externos:
□ Conta Asaas (API de pagamento)
□ WhatsApp Business API
□ Email SMTP (SendGrid/Mailgun)
□ Google Analytics 4
□ Google Search Console
```

### **Development Phase Checklist**

#### **Backend (CodeIgniter 4)**
```
🏗️ Estrutura Base:
□ Controllers principais (Home, Campaign, Payment, User, Admin)
□ Models com relacionamentos
□ Libraries (AsaasAPI, WhatsAppAPI)
□ Services (NotificationService, AdService, LoyaltyService)
□ Helpers customizados
□ Validation rules customizadas

🔐 Segurança:
□ Input validation em todos os forms
□ SQL injection prevention
□ CSRF protection
□ XSS protection
□ File upload security
□ Rate limiting
□ SSL/HTTPS enforcement

💾 Database:
□ Tabela users
□ Tabela campaigns
□ Tabela donations
□ Tabela advertisements
□ Tabela notifications
□ Tabela analytics_events
□ Índices de performance
□ Constraints de integridade
```

#### **Frontend (TailwindCSS + Alpine.js)**
```
🎨 UI Components:
□ Header/Navigation
□ Footer
□ Cards de campanha
□ Formulário de doação
□ Dashboard layouts
□ Modal components
□ Loading states
□ Error messages

📱 Responsividade:
□ Mobile-first design
□ Tablet optimization
□ Desktop layouts
□ Touch interactions
□ Viewport meta tag
□ Flexible images

🚀 Performance:
□ CSS minification
□ JS bundling
□ Image optimization
□ Lazy loading
□ Cache headers
□ Gzip compression
```

### **Integration Checklist**

#### **Asaas Payment Gateway**
```
💳 Configurações:
□ API keys configuradas
□ Webhook endpoint funcionando
□ Split payment implementado
□ Refund system
□ Error handling robusto
□ Logs detalhados

🧪 Testes:
□ Pagamento PIX
□ Pagamento cartão
□ Split de valores
□ Webhook processing
□ Edge cases (falhas, timeouts)
□ Ambiente sandbox → produção
```

#### **WhatsApp Business API**
```
📱 Configurações:
□ Business account verificado
□ Access tokens configurados
□ Templates aprovados
□ Webhook configurado
□ Rate limiting respeitado

✉️ Mensagens:
□ Confirmação de doação
□ Nova doação para criador
□ Meta atingida
□ Campanha urgente
□ Relatório semanal
□ Testes de entrega
```

### **Testing Checklist**
```
🧪 Testes Unitários:
□ Models (validations, relationships)
□ Libraries (API calls, calculations)
□ Services (business logic)
□ Helpers (utility functions)
□ Controllers (basic flows)

🔍 Testes de Integração:
□ Payment flow end-to-end
□ Email sending
□ WhatsApp notifications
□ File uploads
□ Database transactions

👤 Testes de Usuário:
□ Cadastro e login
□ Criar campanha
□ Fazer doação
□ Receber notificações
□ Dashboard navigation
□ Mobile experience

🚨 Testes de Segurança:
□ SQL Injection attempts
□ XSS prevention
□ File upload security
□ Authentication bypass
□ Authorization checks
□ Data exposure
```

### **Pre-Launch Checklist**
```
📋 Legal e Compliance:
□ Termos de uso completos
□ Política de privacidade (LGPD)
□ Política de reembolso
□ Disclaimer médico
□ Registro de marca (se aplicável)
□ Consultoria jurídica

🎯 Marketing Preparatory:
□ Blog com conteúdo inicial
□ Redes sociais criadas
□ Logo e identidade visual
□ Materiais promocionais
□ Press kit
□ Landing page de captura

⚙️ Operacional:
□ Monitoramento (uptime, errors)
□ Backup automático configurado
□ Logs centralizados
□ Alertas críticos
□ Documentação de processos
□ Plano de incident response

📊 Analytics:
□ Google Analytics configurado
□ Facebook Pixel (se usar Facebook Ads)
□ Conversion tracking
□ Event tracking personalizado
□ Dashboard de métricas internas
□ Relatórios automáticos
```

### **Launch Day Checklist**
```
🚀 Go-Live:
□ DNS apontado para produção
□ SSL certificado ativo
□ Todos os serviços funcionando
□ Backup realizado
□ Monitoring ativo
□ Team em standby

📢 Marketing Activation:
□ Posts nas redes sociais
□ Email para lista de espera
□ Comunicados para parceiros
□ PR/imprensa notificada
□ Ads campaigns ativadas

🔍 Monitoramento:
□ Error rates
□ Performance metrics
□ User registration flow
□ Payment success rates
□ WhatsApp delivery rates
□ Server resources
```

---

## 🎯 **CONSIDERAÇÕES FINAIS**

Este documento representa um **guia completo** para desenvolvimento da plataforma DoarFazBem.com.br. Cada seção foi pensada para ser **executável** por um desenvolvedor iniciante com dedicação e foco.

### **Próximos Passos Sugeridos:**

1. **Comece pelo básico**: Implemente primeiro o sistema de usuários e campanhas
2. **Teste constantemente**: Cada funcionalidade deve ser testada antes de passar para a próxima
3. **Documente tudo**: Mantenha registro de decisões e mudanças
4. **Busque feedback cedo**: Teste com usuários reais assim que possível
5. **Monitore métricas**: Desde o início, acompanhe como as pessoas usam a plataforma

### **Recursos de Apoio:**
- **Documentação CodeIgniter 4**: https://codeigniter.com/user_guide/
- **TailwindCSS Docs**: https://tailwindcss.com/docs
- **Asaas API Docs**: https://docs.asaas.com/
- **WhatsApp Business API**: https://developers.facebook.com/docs/whatsapp

**Lembre-se**: Este projeto tem potencial para **realmente fazer a diferença** na vida das pessoas. Foque na execução gradual e consistente.

**Boa sorte! 🚀💪**

---

*Documento criado em: <?= date('d/m/Y') ?>*
*Versão: 1.0*
*Próxima revisão: Após implementação da Fase 1*
