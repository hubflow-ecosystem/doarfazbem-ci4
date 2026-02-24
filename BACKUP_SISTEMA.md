# Sistema de Backup DoarFazBem

Sistema de backup automatizado com integração Google Drive, similar ao UpdraftPlus Pro.

## Comandos Disponíveis

```bash
# Backup completo (banco + arquivos)
php spark backup:run

# Apenas banco de dados
php spark backup:run --type=database

# Apenas arquivos
php spark backup:run --type=files

# Listar backups locais
php spark backup:list

# Listar backups locais + Google Drive
php spark backup:list --remote

# Testar configuração
php spark backup:test

# Autorizar Google Drive
php spark backup:auth
```

## Configuração

### Arquivo: `app/Config/Backup.php`

```php
// Quantidade de backups locais a manter
public int $keepLocalBackups = 3;

// Quantidade de backups no Google Drive a manter
public int $keepRemoteBackups = 7;

// Email para notificações
public string $notificationEmail = 'seu@email.com';

// Notificar apenas em caso de erro
public bool $notifyOnErrorOnly = false;
```

## Configurar Google Drive

### 1. Criar Credenciais no Google Cloud

1. Acesse: https://console.cloud.google.com/apis/credentials
2. Crie um projeto ou selecione existente
3. Ative a "Google Drive API"
4. Crie um "OAuth 2.0 Client ID" (tipo: Desktop)
5. Baixe o JSON das credenciais

### 2. Salvar Credenciais

Salve o JSON baixado em:
```
config/google-drive-credentials.json
```

### 3. Autorizar Acesso

```bash
php spark backup:auth
```

Siga as instruções:
1. Acesse a URL gerada no navegador
2. Faça login com sua conta Google
3. Autorize o acesso
4. Copie o código
5. Execute novamente com o código:

```bash
php spark backup:auth SEU_CODIGO_AQUI
```

## Agendamento via Cron

### Backup diário às 3h da manhã:

```bash
0 3 * * * cd /caminho/para/doarfazbem && php spark backup:run >> /var/log/doarfazbem-backup.log 2>&1
```

### Backup do banco a cada 6 horas:

```bash
0 */6 * * * cd /caminho/para/doarfazbem && php spark backup:run --type=database
```

### Backup semanal completo (Domingo às 2h):

```bash
0 2 * * 0 cd /caminho/para/doarfazbem && php spark backup:run
```

## Estrutura de Arquivos

```
writable/backups/
├── doarfazbem_full_2025-12-15_030000.tar    # Backup completo
├── doarfazbem_db_2025-12-15_090000.sql      # Backup do banco
└── doarfazbem_files_2025-12-15_150000.tar   # Backup de arquivos
```

## Pastas Incluídas no Backup

- `app/` - Código da aplicação
- `public/uploads/` - Uploads de usuários
- `public/assets/` - Assets do site
- `writable/uploads/` - Uploads do sistema

## Pastas Excluídas

- `writable/cache/`
- `writable/logs/`
- `writable/session/`
- `writable/debugbar/`
- `writable/backups/`
- `vendor/`
- `node_modules/`
- `.git/`

## Restauração

### Restaurar Banco de Dados (via phpMyAdmin ou CLI):

```bash
mysql -u usuario -p doarfazbem < doarfazbem_db_2025-12-15_030000.sql
```

### Restaurar Arquivos:

```bash
# Para arquivos .tar
tar -xvf doarfazbem_files_2025-12-15_030000.tar -C /caminho/destino/

# Para arquivos .zip (quando disponível)
unzip doarfazbem_files_2025-12-15_030000.zip -d /caminho/destino/
```

## Requisitos

- PHP 8.1+
- Extensão `curl` (para Google Drive)
- Extensão `zip` ou `phar` (para compressão de arquivos)

## Notificações por Email

Configure o email de notificação em `Config/Backup.php`:

```php
public string $notificationEmail = 'admin@doarfazbem.com.br';
```

Você receberá emails com:
- Status do backup (sucesso/falha)
- Tamanho dos arquivos
- Detalhes de erros (se houver)

---

Criado em: 15/12/2025
