# 📝 Integração WordPress + DoarFazBem

> Guia completo para integrar o WordPress com a plataforma DoarFazBem

---

## 📋 ÍNDICE

1. [Arquiteturas Recomendadas](#arquiteturas-recomendadas)
2. [Opção 1: WordPress em Subdomínio (RECOMENDADO)](#opção-1-wordpress-em-subdomínio)
3. [Opção 2: WordPress em Subdiretório](#opção-2-wordpress-em-subdiretório)
4. [Integrações Técnicas](#integrações-técnicas)
5. [Plugin Customizado DoarFazBem](#plugin-customizado-doarfazbem)
6. [Exemplos de Uso](#exemplos-de-uso)

---

## 🏗️ ARQUITETURAS RECOMENDADAS

### ✅ **Opção 1: Subdomínio (RECOMENDADO)**

```
Estrutura de Domínios:
├── app.seudominio.com        → Aplicação DoarFazBem (CodeIgniter 4)
└── blog.seudominio.com       → WordPress (Blog/Conteúdo)
```

**Vantagens:**
- ✅ **Completamente isolado** - sem conflitos de arquivos ou rotas
- ✅ **Performance otimizada** - cada aplicação roda independentemente
- ✅ **Fácil manutenção** - backups e atualizações separadas
- ✅ **SSL/HTTPS separado** - certificados independentes
- ✅ **Escalabilidade** - pode mover para servidores diferentes no futuro

**Configuração no cPanel:**

```bash
# 1. Criar subdomínio para a aplicação
Subdomínio: app.seudominio.com
Document Root: /home/usuario/app.seudominio.com/public

# 2. Criar subdomínio para o blog
Subdomínio: blog.seudominio.com
Document Root: /home/usuario/blog.seudominio.com

# 3. Instalar WordPress no blog
cPanel → Softaculous → WordPress → Instalar em blog.seudominio.com
```

---

### 🔶 **Opção 2: Subdiretório**

```
Estrutura de Pastas:
public_html/
├── doarfazbem/               → Aplicação CodeIgniter
│   ├── app/
│   ├── public/               → Document root da aplicação
│   └── ...
└── blog/                     → WordPress
    ├── wp-admin/
    ├── wp-content/
    └── index.php
```

**URLs:**
- `https://seudominio.com/` → DoarFazBem (aplicação principal)
- `https://seudominio.com/blog/` → WordPress (blog)

**Vantagens:**
- ✅ Mesmo domínio principal
- ✅ Compartilha cookies e sessões
- ✅ Mais simples para usuários (único domínio)

**Desvantagens:**
- ❌ Possíveis conflitos de rotas
- ❌ .htaccess pode conflitar
- ❌ Mais complexo de configurar

**Configuração:**

```apache
# .htaccess na raiz (public_html/)
<IfModule mod_rewrite.c>
    RewriteEngine On

    # WordPress - Redirecionar /blog/* para pasta blog/
    RewriteRule ^blog/(.*)$ /blog/$1 [L]

    # DoarFazBem - Resto vai para aplicação
    RewriteCond %{REQUEST_URI} !^/blog/
    RewriteRule ^(.*)$ /doarfazbem/public/$1 [L]
</IfModule>
```

---

## 🔗 INTEGRAÇÕES TÉCNICAS

### 1️⃣ **Single Sign-On (SSO) - Login Único**

Permite que usuários logados no DoarFazBem também fiquem logados no WordPress.

#### **Método: JWT Token Compartilhado**

**No DoarFazBem (CodeIgniter):**

```php
// app/Libraries/JWTAuth.php
<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuth
{
    private $key;

    public function __construct()
    {
        $this->key = getenv('JWT_SECRET_KEY'); // Mesma chave no .env
    }

    public function generateToken($userId, $email, $name)
    {
        $payload = [
            'iss' => base_url(),
            'iat' => time(),
            'exp' => time() + 3600, // 1 hora
            'sub' => $userId,
            'email' => $email,
            'name' => $name,
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    public function validateToken($token)
    {
        try {
            return JWT::decode($token, new Key($this->key, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }
}
```

**No WordPress (Plugin):**

```php
// wp-content/plugins/doarfazbem-integration/sso.php
<?php

function doarfazbem_sso_login() {
    if (!isset($_GET['token'])) {
        return;
    }

    $token = $_GET['token'];
    $secret_key = get_option('doarfazbem_jwt_secret');

    // Validar token JWT
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

    try {
        $payload = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($secret_key, 'HS256'));

        // Buscar ou criar usuário no WordPress
        $user = get_user_by('email', $payload->email);

        if (!$user) {
            // Criar novo usuário
            $user_id = wp_create_user($payload->email, wp_generate_password(), $payload->email);
            wp_update_user([
                'ID' => $user_id,
                'display_name' => $payload->name,
            ]);
            $user = get_user_by('id', $user_id);
        }

        // Fazer login
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);

        // Redirecionar
        wp_redirect(home_url());
        exit;

    } catch (Exception $e) {
        wp_die('Token inválido ou expirado.');
    }
}
add_action('init', 'doarfazbem_sso_login');
```

---

### 2️⃣ **Widgets e Shortcodes WordPress**

Exibir campanhas do DoarFazBem dentro do WordPress.

#### **Widget: Últimas Campanhas**

```php
// wp-content/plugins/doarfazbem-integration/widgets/latest-campaigns.php
<?php

class DoarFazBem_Latest_Campaigns_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'doarfazbem_campaigns',
            'DoarFazBem - Últimas Campanhas',
            ['description' => 'Exibe as últimas campanhas do DoarFazBem']
        );
    }

    public function widget($args, $instance) {
        $api_url = get_option('doarfazbem_api_url');
        $response = wp_remote_get($api_url . '/api/campaigns/latest?limit=3');

        if (is_wp_error($response)) {
            return;
        }

        $campaigns = json_decode(wp_remote_retrieve_body($response), true);

        echo $args['before_widget'];
        echo '<div class="doarfazbem-campaigns">';

        foreach ($campaigns as $campaign) {
            ?>
            <div class="campaign-card">
                <img src="<?= esc_url($campaign['image']) ?>" alt="<?= esc_attr($campaign['title']) ?>">
                <h3><?= esc_html($campaign['title']) ?></h3>
                <p><?= esc_html($campaign['description']) ?></p>
                <div class="progress-bar">
                    <div class="progress" style="width: <?= $campaign['percentage'] ?>%"></div>
                </div>
                <p>R$ <?= number_format($campaign['current_amount'], 2, ',', '.') ?> de R$ <?= number_format($campaign['goal_amount'], 2, ',', '.') ?></p>
                <a href="<?= esc_url($api_url . '/campaigns/' . $campaign['slug']) ?>" class="btn">Doar Agora</a>
            </div>
            <?php
        }

        echo '</div>';
        echo $args['after_widget'];
    }
}

function register_doarfazbem_widgets() {
    register_widget('DoarFazBem_Latest_Campaigns_Widget');
}
add_action('widgets_init', 'register_doarfazbem_widgets');
```

#### **Shortcode: Exibir Campanha Específica**

```php
// [doarfazbem_campaign id="123"]
function doarfazbem_campaign_shortcode($atts) {
    $atts = shortcode_atts(['id' => ''], $atts);

    if (empty($atts['id'])) {
        return '<p>ID da campanha não fornecido.</p>';
    }

    $api_url = get_option('doarfazbem_api_url');
    $response = wp_remote_get($api_url . '/api/campaigns/' . $atts['id']);

    if (is_wp_error($response)) {
        return '<p>Erro ao carregar campanha.</p>';
    }

    $campaign = json_decode(wp_remote_retrieve_body($response), true);

    ob_start();
    ?>
    <div class="doarfazbem-campaign-embed">
        <img src="<?= esc_url($campaign['image']) ?>" alt="<?= esc_attr($campaign['title']) ?>">
        <h2><?= esc_html($campaign['title']) ?></h2>
        <p><?= esc_html($campaign['description']) ?></p>
        <div class="stats">
            <span>Arrecadado: R$ <?= number_format($campaign['current_amount'], 2, ',', '.') ?></span>
            <span>Meta: R$ <?= number_format($campaign['goal_amount'], 2, ',', '.') ?></span>
        </div>
        <a href="<?= esc_url($api_url . '/donate/' . $campaign['slug']) ?>" class="btn-donate">Fazer Doação</a>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('doarfazbem_campaign', 'doarfazbem_campaign_shortcode');
```

---

### 3️⃣ **Menu Integrado**

Adicionar links do DoarFazBem no menu do WordPress:

```php
// functions.php do tema WordPress
function add_doarfazbem_menu_items($items, $args) {
    if ($args->theme_location == 'primary') {
        $app_url = 'https://app.seudominio.com';

        $items .= '<li class="menu-item"><a href="' . $app_url . '/campaigns">Campanhas</a></li>';
        $items .= '<li class="menu-item"><a href="' . $app_url . '/campaigns/create">Criar Campanha</a></li>';

        // Se usuário logado
        if (is_user_logged_in()) {
            $items .= '<li class="menu-item"><a href="' . $app_url . '/dashboard">Meu Dashboard</a></li>';
        } else {
            $items .= '<li class="menu-item"><a href="' . $app_url . '/login">Login</a></li>';
        }
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'add_doarfazbem_menu_items', 10, 2);
```

---

### 4️⃣ **API REST para Integração**

Criar endpoints no DoarFazBem para WordPress consumir.

**No DoarFazBem:**

```php
// app/Controllers/Api/CampaignsAPI.php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CampaignModel;

class CampaignsAPI extends ResourceController
{
    protected $modelName = 'App\Models\CampaignModel';
    protected $format = 'json';

    /**
     * GET /api/campaigns/latest
     */
    public function latest()
    {
        $limit = $this->request->getGet('limit') ?? 6;

        $campaigns = $this->model
            ->where('status', 'active')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->find();

        return $this->respond([
            'status' => 'success',
            'data' => $campaigns
        ]);
    }

    /**
     * GET /api/campaigns/:id
     */
    public function show($id = null)
    {
        $campaign = $this->model->find($id);

        if (!$campaign) {
            return $this->failNotFound('Campanha não encontrada');
        }

        return $this->respond([
            'status' => 'success',
            'data' => $campaign
        ]);
    }
}
```

**Rotas:**

```php
// app/Config/Routes.php
$routes->group('api', function($routes) {
    $routes->get('campaigns/latest', 'Api\CampaignsAPI::latest');
    $routes->get('campaigns/(:num)', 'Api\CampaignsAPI::show/$1');
});
```

---

## 🔌 PLUGIN CUSTOMIZADO WORDPRESS

### Estrutura do Plugin

```
wp-content/plugins/doarfazbem-integration/
├── doarfazbem-integration.php      # Arquivo principal
├── includes/
│   ├── sso.php                     # Single Sign-On
│   ├── api.php                     # Comunicação com API
│   └── settings.php                # Página de configurações
├── widgets/
│   └── latest-campaigns.php        # Widget de campanhas
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
└── vendor/                         # Composer (JWT, etc)
```

### Arquivo Principal do Plugin

```php
<?php
/**
 * Plugin Name: DoarFazBem Integration
 * Description: Integração entre WordPress e plataforma DoarFazBem
 * Version: 1.0.0
 * Author: DoarFazBem
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DOARFAZBEM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DOARFAZBEM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Carregar dependências
require_once DOARFAZBEM_PLUGIN_DIR . 'vendor/autoload.php';
require_once DOARFAZBEM_PLUGIN_DIR . 'includes/sso.php';
require_once DOARFAZBEM_PLUGIN_DIR . 'includes/api.php';
require_once DOARFAZBEM_PLUGIN_DIR . 'includes/settings.php';
require_once DOARFAZBEM_PLUGIN_DIR . 'widgets/latest-campaigns.php';

// Ativar plugin
function doarfazbem_activate() {
    add_option('doarfazbem_api_url', 'https://app.seudominio.com');
    add_option('doarfazbem_jwt_secret', wp_generate_password(64, true, true));
}
register_activation_hook(__FILE__, 'doarfazbem_activate');

// Enqueue styles e scripts
function doarfazbem_enqueue_assets() {
    wp_enqueue_style('doarfazbem-style', DOARFAZBEM_PLUGIN_URL . 'assets/css/style.css');
    wp_enqueue_script('doarfazbem-script', DOARFAZBEM_PLUGIN_URL . 'assets/js/script.js', ['jquery'], '1.0', true);
}
add_action('wp_enqueue_scripts', 'doarfazbem_enqueue_assets');
```

---

## 🎨 EXEMPLOS DE USO

### 1. **Post do Blog com Campanha Integrada**

```html
<!-- Post WordPress -->
<article>
    <h1>Ajude Maria a realizar o sonho de andar novamente</h1>

    <p>Maria, de 8 anos, precisa de uma cirurgia urgente...</p>

    <!-- Shortcode para exibir campanha -->
    [doarfazbem_campaign id="123"]

    <p>Qualquer valor ajuda! Compartilhe com seus amigos.</p>
</article>
```

### 2. **Sidebar com Últimas Campanhas**

```php
// Aparência → Widgets → Adicionar "DoarFazBem - Últimas Campanhas"
```

### 3. **Página Dedicada para Campanhas**

```php
<?php
/*
 * Template Name: Campanhas DoarFazBem
 */

get_header();

$api_url = get_option('doarfazbem_api_url');
$response = wp_remote_get($api_url . '/api/campaigns/latest?limit=12');
$campaigns = json_decode(wp_remote_retrieve_body($response), true);
?>

<div class="campaigns-archive">
    <h1>Todas as Campanhas</h1>

    <div class="campaigns-grid">
        <?php foreach ($campaigns as $campaign): ?>
            <div class="campaign-item">
                <img src="<?= $campaign['image'] ?>" alt="<?= $campaign['title'] ?>">
                <h2><?= $campaign['title'] ?></h2>
                <p><?= $campaign['description'] ?></p>
                <a href="<?= $api_url . '/donate/' . $campaign['slug'] ?>">Doar</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php get_footer(); ?>
```

---

## ⚙️ CONFIGURAÇÕES NECESSÁRIAS

### No .env do DoarFazBem:

```env
# JWT para SSO
JWT_SECRET_KEY = sua_chave_secreta_aqui_64_caracteres_minimo

# CORS para permitir WordPress acessar API
CORS_ALLOWED_ORIGINS = https://blog.seudominio.com,https://seudominio.com
```

### No WordPress (Plugin Settings):

```
Admin → DoarFazBem Settings:
- API URL: https://app.seudominio.com
- JWT Secret: [mesma chave do .env]
- Enable SSO: Yes
```

---

## 📊 RESUMO DE VANTAGENS

| Recurso | Subdomínio | Subdiretório |
|---------|------------|--------------|
| **Isolamento** | ✅ Total | ❌ Parcial |
| **Performance** | ✅ Ótima | 🔶 Boa |
| **Manutenção** | ✅ Fácil | 🔶 Média |
| **SEO** | ✅ Ótimo | ✅ Ótimo |
| **Escalabilidade** | ✅ Máxima | 🔶 Limitada |
| **Complexidade** | ✅ Baixa | ❌ Alta |

---

## ✅ RECOMENDAÇÃO FINAL

**Use a Opção 1 (Subdomínio):**

```
app.seudominio.com  → DoarFazBem (aplicação)
blog.seudominio.com → WordPress (conteúdo/blog)
```

**Motivos:**
1. ✅ Zero conflitos técnicos
2. ✅ Fácil manutenção e backup
3. ✅ Performance otimizada
4. ✅ Escalável para futuro (pode separar servidores)
5. ✅ SSL/HTTPS independente
6. ✅ Atualizações sem riscos

---

## 🚀 PRÓXIMOS PASSOS

1. ✅ Decidir arquitetura (subdomínio ou subdiretório)
2. ✅ Configurar subdomínios no cPanel
3. ✅ Instalar WordPress via Softaculous
4. ✅ Criar plugin de integração
5. ✅ Configurar JWT para SSO
6. ✅ Criar API endpoints no DoarFazBem
7. ✅ Testar integração completa

---

**📞 Suporte:** Em caso de dúvidas, consulte a documentação do CodeIgniter 4 e WordPress Codex.
