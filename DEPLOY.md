# 🚀 Guia de Deploy - DoarFazBem

Este guia detalha como fazer o deploy da plataforma DoarFazBem para produção (doarfazbem.com.br).

---

## 📋 Pré-requisitos

- [x] Servidor com PHP 8.1+ (recomendado 8.2)
- [x] MySQL 8.0+
- [x] Composer instalado
- [x] Node.js e NPM instalados
- [x] Acesso SSH ao servidor
- [x] Domínio doarfazbem.com.br configurado
- [x] Certificado SSL (Let's Encrypt recomendado)

---

## 🔧 Passo 1: Preparar o Servidor

### 1.1 Instalar Dependências

```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar PHP 8.2 e extensões necessárias
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-gd php8.2-intl php8.2-zip -y

# Instalar MySQL
sudo apt install mysql-server -y

# Instalar Nginx (ou Apache)
sudo apt install nginx -y

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar Node.js e NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
```

### 1.2 Configurar MySQL

```bash
sudo mysql_secure_installation

# Criar banco de dados e usuário
sudo mysql -u root -p

CREATE DATABASE doarfazbem_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'doarfazbem_user'@'localhost' IDENTIFIED BY 'SENHA_SUPER_SEGURA';
GRANT ALL PRIVILEGES ON doarfazbem_prod.* TO 'doarfazbem_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 📦 Passo 2: Fazer Upload dos Arquivos

### 2.1 Clonar/Upload do Projeto

```bash
# Via Git (recomendado)
cd /var/www/
git clone SEU_REPOSITORIO doarfazbem
cd doarfazbem

# OU via FTP/SFTP
# Fazer upload de todos os arquivos para /var/www/doarfazbem
```

### 2.2 Configurar Permissões

```bash
cd /var/www/doarfazbem

# Permissões de pastas
sudo chown -R www-data:www-data .
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;

# Permissões especiais para writable
sudo chmod -R 777 writable/
sudo chmod -R 755 public/uploads/
```

---

## ⚙️ Passo 3: Configurar Ambiente

### 3.1 Copiar arquivo .env de produção

```bash
cp .env.production .env
```

### 3.2 Editar .env com credenciais reais

```bash
nano .env
```

**ATENÇÃO:** Altere as seguintes variáveis:

```env
# Banco de Dados
database.default.database = doarfazbem_prod
database.default.username = doarfazbem_user
database.default.password = SENHA_SUPER_SEGURA

# Gerar nova chave de criptografia
encryption.key = EXECUTAR_php_spark_key:generate

# Asaas - Credenciais de PRODUÇÃO
ASAAS_API_KEY = SUA_CHAVE_PRODUCAO
ASAAS_ENVIRONMENT = production
ASAAS_WALLET_ID = SEU_WALLET_ID
```

### 3.3 Gerar chave de criptografia

```bash
php spark key:generate
```

Copie a chave gerada e cole no `.env` em `encryption.key`.

---

## 🗄️ Passo 4: Instalar Dependências e Compilar

```bash
# Instalar dependências PHP
composer install --no-dev --optimize-autoloader

# Instalar dependências Node
npm install

# Compilar CSS de produção
npm run build
```

---

## 🗃️ Passo 5: Executar Migrações do Banco

```bash
php spark migrate
```

**IMPORTANTE:** Se houver erro de tabelas duplicadas, execute:

```bash
# Verificar status
php spark migrate:status

# Se necessário, criar tabelas manualmente
mysql -u doarfazbem_user -p doarfazbem_prod

# Copiar SQLs necessários conforme erros
```

---

## 🌐 Passo 6: Configurar Nginx

### 6.1 Criar arquivo de configuração

```bash
sudo nano /etc/nginx/sites-available/doarfazbem.com.br
```

**Conteúdo:**

```nginx
server {
    listen 80;
    server_name doarfazbem.com.br www.doarfazbem.com.br;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name doarfazbem.com.br www.doarfazbem.com.br;

    root /var/www/doarfazbem/public;
    index index.php index.html;

    # SSL Certificates (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/doarfazbem.com.br/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/doarfazbem.com.br/privkey.pem;

    # SSL Config
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Logs
    access_log /var/log/nginx/doarfazbem-access.log;
    error_log /var/log/nginx/doarfazbem-error.log;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache de assets estáticos
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 6.2 Ativar site e SSL

```bash
# Ativar site
sudo ln -s /etc/nginx/sites-available/doarfazbem.com.br /etc/nginx/sites-enabled/

# Testar configuração
sudo nginx -t

# Instalar Let's Encrypt
sudo apt install certbot python3-certbot-nginx -y

# Gerar certificado SSL
sudo certbot --nginx -d doarfazbem.com.br -d www.doarfazbem.com.br

# Recarregar Nginx
sudo systemctl reload nginx
```

---

## 🔐 Passo 7: Configurar APIs Externas

### 7.1 Google OAuth

1. Acesse: https://console.cloud.google.com/
2. Vá em "Credentials" > "OAuth 2.0 Client IDs"
3. Adicione URL autorizada: `https://doarfazbem.com.br/auth/google/callback`

### 7.2 Asaas (Gateway de Pagamento)

1. Acesse: https://www.asaas.com
2. Obtenha suas credenciais de PRODUÇÃO
3. Configure webhook: `https://doarfazbem.com.br/webhook/asaas`
4. Atualize `.env` com as credenciais

### 7.3 Google Analytics

1. Crie propriedade GA4: https://analytics.google.com/
2. Copie Measurement ID
3. Adicione no `.env`: `GA_MEASUREMENT_ID`

---

## ✅ Passo 8: Verificações Finais

### 8.1 Checklist de Segurança

- [ ] Arquivo `.env` com credenciais seguras
- [ ] Certificado SSL ativo
- [ ] Permissões corretas (writable 777)
- [ ] Firewall configurado (UFW)
- [ ] MySQL com usuário específico (não root)
- [ ] Backups automáticos configurados

### 8.2 Testar Funcionalidades

```bash
# Acessar site
https://doarfazbem.com.br

# Testar:
- [ ] Homepage carrega
- [ ] Login/Registro funcionam
- [ ] Criar campanha funciona
- [ ] Upload de imagem funciona
- [ ] Doação via PIX funciona
- [ ] Doação via Boleto funciona
- [ ] Doação via Cartão funciona
- [ ] Emails são enviados
- [ ] Dashboard carrega
```

---

## 🔄 Passo 9: Configurar Backups Automáticos

### 9.1 Script de Backup

```bash
sudo nano /usr/local/bin/backup-doarfazbem.sh
```

**Conteúdo:**

```bash
#!/bin/bash

# Configurações
BACKUP_DIR="/var/backups/doarfazbem"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="doarfazbem_prod"
DB_USER="doarfazbem_user"
DB_PASS="SENHA_SUPER_SEGURA"

# Criar diretório
mkdir -p $BACKUP_DIR

# Backup do Banco
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup dos arquivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/doarfazbem/public/uploads

# Manter apenas últimos 7 dias
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup concluído: $DATE"
```

```bash
# Tornar executável
sudo chmod +x /usr/local/bin/backup-doarfazbem.sh

# Adicionar ao cron (backup diário às 3h)
sudo crontab -e

# Adicionar linha:
0 3 * * * /usr/local/bin/backup-doarfazbem.sh
```

---

## 📊 Passo 10: Monitoramento

### 10.1 Logs

```bash
# Ver logs do Nginx
sudo tail -f /var/log/nginx/doarfazbem-error.log

# Ver logs da aplicação
tail -f /var/www/doarfazbem/writable/logs/log-*.log
```

### 10.2 Status dos Serviços

```bash
# Nginx
sudo systemctl status nginx

# MySQL
sudo systemctl status mysql

# PHP-FPM
sudo systemctl status php8.2-fpm
```

---

## 🎉 Deploy Concluído!

Seu site está no ar em: **https://doarfazbem.com.br**

### Próximos Passos Recomendados:

1. ✅ Configurar Cloudflare (CDN + proteção DDoS)
2. ✅ Configurar Google Search Console
3. ✅ Adicionar sitemap.xml
4. ✅ Configurar robots.txt
5. ✅ Monitorar com Uptime Robot
6. ✅ Configurar alertas de erro (Sentry)

---

## 🆘 Troubleshooting

### Erro: "500 Internal Server Error"

```bash
# Ver logs
sudo tail -f /var/log/nginx/doarfazbem-error.log
tail -f /var/www/doarfazbem/writable/logs/log-*.log

# Verificar permissões
sudo chmod -R 777 /var/www/doarfazbem/writable/
```

### Erro: "Database connection failed"

```bash
# Testar conexão MySQL
mysql -u doarfazbem_user -p doarfazbem_prod

# Verificar credenciais no .env
```

### Erro: "Assets não carregam (CSS/JS)"

```bash
# Recompilar assets
cd /var/www/doarfazbem
npm run build

# Limpar cache Nginx
sudo systemctl reload nginx
```

---

## 📞 Suporte

Para dúvidas, entre em contato:
- Email: contato@doarfazbem.com.br
- Documentação: https://docs.doarfazbem.com.br
