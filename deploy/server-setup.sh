#!/bin/bash
##
# Script de setup inicial do servidor para DoarFazBem
# Executar como root no servidor 77.42.64.222
# Uso: bash server-setup.sh
##

set -e

DOMAIN="doarfazbem.com.br"
APP_USER="doarfazbem"
APP_PATH="/home/${APP_USER}/htdocs/${DOMAIN}"
DB_NAME="doarfazbem_prod"
DB_USER="doarfazbem"
DB_PASS='@GAd8EDSS5Ypn4er@'
GITHUB_REPO="git@github.com:hubflow-ecosystem/doarfazbem-ci4.git"
PHP_VERSION="8.2"

echo "========================================================"
echo "  Setup inicial DoarFazBem — ${DOMAIN}"
echo "========================================================"

# 1. Criar usuário do sistema (se não existir)
echo "--- [1/10] Criando usuário ${APP_USER} ---"
if ! id "${APP_USER}" &>/dev/null; then
    useradd -m -s /bin/bash "${APP_USER}"
    echo "Usuário ${APP_USER} criado."
else
    echo "Usuário ${APP_USER} já existe."
fi

# 2. Criar estrutura de diretórios
echo "--- [2/10] Criando diretórios ---"
mkdir -p "${APP_PATH}"
chown -R "${APP_USER}:${APP_USER}" "/home/${APP_USER}"

# 3. Instalar PHP 8.2 e extensões necessárias (se não instalado)
echo "--- [3/10] Verificando PHP ${PHP_VERSION} ---"
if ! php${PHP_VERSION} --version &>/dev/null; then
    echo "Instalando PHP ${PHP_VERSION}..."
    add-apt-repository ppa:ondrej/php -y
    apt-get update
    apt-get install -y \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-json \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-redis \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-opcache
else
    echo "PHP ${PHP_VERSION} já instalado."
fi

# 4. Configurar PHP-FPM pool para o usuário
echo "--- [4/10] Configurando PHP-FPM pool ---"
cat > "/etc/php/${PHP_VERSION}/fpm/pool.d/${APP_USER}.conf" << EOF
[${APP_USER}]
user = ${APP_USER}
group = ${APP_USER}
listen = /run/php/php${PHP_VERSION}-fpm-${APP_USER}.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
pm.max_requests = 500

php_admin_value[error_log] = /var/log/php-fpm/${APP_USER}-error.log
php_admin_flag[log_errors] = on
EOF

mkdir -p /var/log/php-fpm
systemctl reload php${PHP_VERSION}-fpm
echo "PHP-FPM pool configurado."

# 5. Criar banco de dados MySQL
echo "--- [5/10] Criando banco de dados MySQL ---"
mysql -u root -e "
  CREATE DATABASE IF NOT EXISTS ${DB_NAME}
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
  CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost'
    IDENTIFIED BY '${DB_PASS}';
  GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
  FLUSH PRIVILEGES;
"
echo "Banco de dados ${DB_NAME} criado."

# 6. Clonar repositório GitHub
echo "--- [6/10] Clonando repositório ---"
if [ -f "${APP_PATH}/.env" ]; then
    echo "Repositório já existe — fazendo git pull."
    cd "${APP_PATH}"
    git pull origin main
else
    echo "Clonando ${GITHUB_REPO}..."
    cd "/home/${APP_USER}/htdocs"
    git clone "${GITHUB_REPO}" "${DOMAIN}"
fi

# 7. Instalar dependências PHP
echo "--- [7/10] Instalando dependências Composer ---"
cd "${APP_PATH}"
if ! command -v composer &>/dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi
composer install --no-dev --optimize-autoloader --no-interaction

# 8. Configurar .env de produção
echo "--- [8/10] Configurando .env ---"
if [ ! -f "${APP_PATH}/.env" ]; then
    cp "${APP_PATH}/.env.production" "${APP_PATH}/.env"
    echo ".env criado a partir de .env.production"
else
    echo ".env já existe — não sobrescrevendo."
fi

# 9. Ajustar permissões
echo "--- [9/10] Ajustando permissões ---"
chown -R "${APP_USER}:${APP_USER}" "${APP_PATH}"
chmod -R 755 "${APP_PATH}"
chmod -R 775 "${APP_PATH}/writable"
chmod 640 "${APP_PATH}/.env"

# Criar pasta de sessões e cache
mkdir -p "${APP_PATH}/writable/session"
mkdir -p "${APP_PATH}/writable/cache"
mkdir -p "${APP_PATH}/writable/logs"
chown -R "${APP_USER}:${APP_USER}" "${APP_PATH}/writable"
chmod -R 775 "${APP_PATH}/writable"

# 10. Configurar Nginx
echo "--- [10/10] Configurando Nginx ---"
cp "${APP_PATH}/deploy/nginx.conf" "/etc/nginx/sites-available/${DOMAIN}"

# Remover default se existir
rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

# Habilitar vhost
ln -sf "/etc/nginx/sites-available/${DOMAIN}" "/etc/nginx/sites-enabled/${DOMAIN}"

# Testar configuração
nginx -t && systemctl reload nginx
echo "Nginx configurado e recarregado."

# Configurar cron jobs
echo "--- Configurando cron jobs ---"
CRON_FILE="/var/spool/cron/crontabs/${APP_USER}"
cat > "${CRON_FILE}" << 'CRONEOF'
# DoarFazBem — Cron Jobs
# Verificar campanhas próximas do fim (a cada hora)
0 * * * * cd /home/doarfazbem/htdocs/doarfazbem.com.br && php spark campaigns:check-ending >> /dev/null 2>&1

# Enviar notificações pendentes (a cada 5 minutos)
*/5 * * * * cd /home/doarfazbem/htdocs/doarfazbem.com.br && php spark notifications:send >> /dev/null 2>&1

# Relatório semanal para admins (toda segunda às 08:00)
0 8 * * 1 cd /home/doarfazbem/htdocs/doarfazbem.com.br && php spark reports:weekly-admin >> /dev/null 2>&1

# Limpar sessões e caches expirados (diário às 02:00)
0 2 * * * cd /home/doarfazbem/htdocs/doarfazbem.com.br && php spark cache:clear >> /dev/null 2>&1
CRONEOF
chown "${APP_USER}:crontab" "${CRON_FILE}" 2>/dev/null || true
chmod 600 "${CRON_FILE}" 2>/dev/null || true

# Rodar migrations
echo "--- Rodando migrations ---"
cd "${APP_PATH}"
php spark migrate --no-interaction

echo ""
echo "========================================================"
echo "  Setup concluído!"
echo "========================================================"
echo ""
echo "  Próximos passos:"
echo "  1. Verificar .env em: ${APP_PATH}/.env"
echo "  2. Verificar DNS Cloudflare: doarfazbem.com.br → 77.42.64.222"
echo "  3. Testar: curl -I https://doarfazbem.com.br"
echo ""
