# 🚀 Alterações para Deploy - DoarFazBem

## ⚠️ IMPORTANTE
Estas alterações foram feitas no ambiente LOCAL (c:\laragon\www\doarfazbem).
Você precisa aplicá-las no servidor de PRODUÇÃO (https://doarfazbem.ai).

---

## 📝 Arquivos Modificados

### 1. `app/Views/campaigns/list.php`

**Problema:** Dois elementos x-data causando conflito no Alpine.js

**Solução:** Linha 5 - Adicionar wrapper único com x-data

**ANTES (linha 5-6):**
```php
<!-- Breadcrumb e Filtros -->
<section class="bg-white border-b" x-data="campaignFilter(<?= json_encode($campaigns) ?>)">
```

**DEPOIS:**
```php
<div x-data="campaignFilter(<?= json_encode($campaigns) ?>)">
<!-- Breadcrumb e Filtros -->
<section class="bg-white border-b">
```

**ANTES (linha 84):**
```php
<!-- Grid de Campanhas -->
<section class="py-12" x-data="campaignFilter(<?= json_encode($campaigns) ?>)">
```

**DEPOIS:**
```php
<!-- Grid de Campanhas -->
<section class="py-12">
```

**ANTES (linha 162 - última linha antes de <?= $this->endSection() ?>):**
```php
</section>

<?= $this->endSection() ?>
```

**DEPOIS:**
```php
</section>
</div>

<?= $this->endSection() ?>
```

---

### 2. `app/Controllers/AdminController.php`

**Problema:** Código buscando doações com status='paid', mas testes usam status='received'

**Solução:** Substituir TODAS as ocorrências de `'paid'` por `'received'`

**Faça busca e substituição global no arquivo:**
- Buscar: `'paid'`
- Substituir por: `'received'`

**OU aplique as alterações manualmente:**

**Linha 240:** `->where('status', 'received');`
**Linha 258:** `AND donations.status = "received"`
**Linha 307:** `->where('status', 'received')`
**Linha 316:** `->where('status', 'received')`
**Linha 329:** `->where('donations.status', 'received')`
**Linha 342:** `->where('donations.status', 'received')`
**Linha 354:** `->where('status', 'received')`
**Linha 365:** `->where('status', 'received')`
**Linha 394:** `->where('status', 'received')`
**Linha 202:** `->where('status', 'received')->countAllResults()`

---

## 🔧 Como Aplicar no Servidor

### Opção 1: Via FTP/SFTP
1. Conecte ao servidor usando FileZilla ou similar
2. Navegue até a pasta do projeto
3. Faça backup dos arquivos originais
4. Substitua os arquivos modificados

### Opção 2: Via SSH
```bash
# Conectar ao servidor
ssh usuario@doarfazbem.ai

# Navegar até a pasta do projeto
cd /caminho/para/doarfazbem

# Fazer backup
cp app/Views/campaigns/list.php app/Views/campaigns/list.php.bak
cp app/Controllers/AdminController.php app/Controllers/AdminController.php.bak

# Editar os arquivos usando nano ou vi
nano app/Views/campaigns/list.php
nano app/Controllers/AdminController.php
```

### Opção 3: Via Git (se configurado)
```bash
# No servidor, fazer pull das alterações
cd /caminho/para/doarfazbem
git pull origin master
```

---

## ✅ Verificação Pós-Deploy

Após aplicar as alterações, teste:

1. **Limpar cache do navegador** (Ctrl+Shift+Del)
2. Acessar: https://doarfazbem.ai/campaigns
   - ✅ Deve mostrar 7 campanhas ativas
3. Acessar: https://doarfazbem.ai/admin/dashboard
   - ✅ Cards devem mostrar valores (R$ 4.827,00 etc)
4. Acessar: https://doarfazbem.ai/dashboard/my-donations
   - ✅ Tabela deve mostrar a doação de R$ 314

---

## 📞 Suporte

Se continuar com problemas após o deploy:
1. Verifique os logs do servidor
2. Verifique o console do navegador (F12)
3. Confirme que os arquivos foram realmente atualizados no servidor

---

**Data:** 2025-11-15
**Desenvolvedor:** Claude Code
