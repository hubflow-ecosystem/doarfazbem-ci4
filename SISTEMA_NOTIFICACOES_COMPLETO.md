# Sistema de Notificações Completo - DoarFazBem

## ✅ IMPLEMENTADO

### 1. Tabelas Criadas
- ✅ `campaign_creator_preferences` - Preferências dos criadores
- ✅ `admin_notification_preferences` - Preferências dos admins
- ✅ `campaign_milestones_notified` - Rastreamento de marcos
- ✅ Colunas adicionadas em `notification_preferences`:
  - `notify_campaign_goal_reached`
  - `notify_campaign_ending_soon`

### 2. Models Criados
- ✅ `CampaignCreatorPreferences.php`
- ✅ `AdminNotificationPreferences.php`
- ✅ `CampaignMilestone.php`

### 3. Services
- ✅ `NotificationService.php` - Serviço centralizado com:
  - `notifyCreatorNewDonation()` - Notificar criador ao receber doação
  - `checkAndNotifyMilestones()` - Verificar e notificar marcos (10%, 20%, etc)
  - `notifyDonorsGoalReached()` - Notificar doadores quando meta atingida
  - `notifyDonorsCampaignEndingSoon()` - Notificar doadores 7 dias antes do fim
  - `notifyAdminNewCampaign()` - Notificar admin sobre nova campanha

---

## ⚠️ FALTA IMPLEMENTAR (CRÍTICO)

### 1. Triggers no DonationModel
**Arquivo:** `app/Models/Donation.php`

Adicionar no método `afterInsert()` ou `afterUpdate()`:
```php
protected function afterUpdate(array $data)
{
    if (isset($data['data']['payment_status']) && $data['data']['payment_status'] === 'confirmed') {
        $notificationService = new \App\Services\NotificationService();
        $notificationService->notifyCreatorNewDonation($data['id']);
    }
}
```

### 2. Trigger no CampaignModel
**Arquivo:** `app/Models/CampaignModel.php`

Adicionar no método `afterInsert()`:
```php
protected function afterInsert(array $data)
{
    $notificationService = new \App\Services\NotificationService();
    $notificationService->notifyAdminNewCampaign($data['id']);
}
```

### 3. Atualizar ProcessNotifications Command
**Arquivo:** `app/Commands/ProcessNotifications.php`

Adicionar novos tipos de notificação:
- `donation_received_email`
- `donation_received_push`
- `campaign_milestone_email`
- `campaign_goal_reached_email`
- `campaign_ending_soon_email`
- `new_campaign_admin_email`

### 4. Expandir EmailNotificationService
**Arquivo:** `app/Services/EmailNotificationService.php`

Adicionar métodos para novos templates:
- `sendDonationReceivedEmail()`
- `sendMilestoneEmail()`
- `sendGoalReachedEmail()`
- `sendCampaignEndingSoonEmail()`
- `sendNewCampaignAdminEmail()`

### 5. Criar Página de Preferências para Criadores
**Arquivo:** `app/Views/dashboard/creator_notifications.php`

Interface para criadores gerenciarem:
- ✅/❌ Receber email ao receber doação
- ✅/❌ Receber push ao receber doação
- ✅/❌ Resumo diário
- ✅/❌ Resumo semanal

**Routes:**
```php
$routes->get('dashboard/creator/notifications', 'CreatorNotificationController::preferences');
$routes->post('dashboard/creator/notifications/update', 'CreatorNotificationController::updatePreferences');
```

### 6. Criar Página de Preferências para Admin
**Arquivo:** `app/Views/admin/notification_preferences.php`

Interface para admins gerenciarem:
- ✅/❌ Email ao criar nova campanha
- ✅/❌ Push ao criar nova campanha
- ✅/❌ Relatório semanal de doações
- ✅/❌ Notificações de marcos (10%, 20%, etc)
- ✅/❌ Dashboard tempo real

**Routes:**
```php
$routes->get('admin/notifications/preferences', 'AdminNotificationController::preferences');
$routes->post('admin/notifications/update', 'AdminNotificationController::updatePreferences');
```

### 7. Comando para Verificar Campanhas Acabando
**Arquivo:** `app/Commands/CheckEndingCampaigns.php`

Executar diariamente via cron:
```php
public function run(array $params)
{
    $campaigns = $this->campaignModel->getEndingSoonCampaigns(); // 7 dias
    foreach ($campaigns as $campaign) {
        $this->notificationService->notifyDonorsCampaignEndingSoon($campaign['id']);
    }
}
```

### 8. Comando para Relatório Semanal Admin
**Arquivo:** `app/Commands/SendWeeklyAdminReport.php`

Executar semanalmente:
```php
public function run(array $params)
{
    $admins = $this->adminPrefsModel->getAdminsForWeeklyReport();
    foreach ($admins as $admin) {
        // Gerar relatório com estatísticas
        $report = $this->generateWeeklyReport();
        // Enviar email
    }
}
```

### 9. Atualizar Menu do Dashboard
Adicionar links:
- "Preferências de Notificações" (para criadores)
- "Notificações Admin" (para admins)

### 10. Criar Preferências Iniciais
**Quando usuário cria primeira campanha:**
```php
$creatorPrefs = new CampaignCreatorPreferences();
$creatorPrefs->getOrCreatePreferences($userId, $campaignId);
```

**Quando usuário vira admin:**
```php
$adminPrefs = new AdminNotificationPreferences();
$adminPrefs->getOrCreatePreferences($adminUserId);
```

---

## 📋 CHECKLIST DE IMPLEMENTAÇÃO RÁPIDA

### Ordem sugerida (do mais crítico para menos):

1. ✅ **Tabelas e Models** (JÁ FEITO)
2. ✅ **NotificationService** (JÁ FEITO)
3. ⚠️ **Triggers no DonationModel** (5 min) - CRÍTICO
4. ⚠️ **Trigger no CampaignModel** (5 min) - CRÍTICO
5. ⚠️ **Atualizar ProcessNotifications** (15 min) - CRÍTICO
6. ⚠️ **Expandir EmailNotificationService** (30 min)
7. ⚠️ **Página preferências criadores** (20 min)
8. ⚠️ **Página preferências admin** (20 min)
9. ⚠️ **Comando CheckEndingCampaigns** (10 min)
10. ⚠️ **Relatório semanal** (20 min)
11. ⚠️ **Atualizar menus** (5 min)
12. ⚠️ **Criar preferências iniciais** (10 min)

**Tempo total estimado:** ~2h30min

---

## 🧪 TESTES

### Testar Doação Recebida
1. Fazer doação em campanha
2. Confirmar pagamento no Asaas
3. Webhook atualiza donation para "confirmed"
4. Verificar se notificação foi enfileirada
5. Processar fila
6. Verificar se criador recebeu email/push

### Testar Marcos
1. Fazer doações até atingir 10%, 20%, etc
2. Verificar se admin recebeu notificação
3. Verificar tabela `campaign_milestones_notified`
4. Ao atingir 100%, verificar se doadores foram notificados

### Testar Campanhas Acabando
1. Criar campanha com `end_date` daqui 6 dias
2. Fazer doação nessa campanha
3. Executar comando `CheckEndingCampaigns`
4. Verificar se doador recebeu notificação

---

## 📁 ARQUIVOS CRIADOS

### Models
- `app/Models/CampaignCreatorPreferences.php`
- `app/Models/AdminNotificationPreferences.php`
- `app/Models/CampaignMilestone.php`

### Services
- `app/Services/NotificationService.php`

### Commands
- `app/Commands/CreateNotificationTables.php`

### Migrations
- `app/Database/Migrations/2025-01-17-000001-create-creator-and-admin-preferences.php`

### SQL
- `create-notification-preferences-tables.sql`

---

## 🔧 COMANDOS ÚTEIS

```bash
# Criar tabelas
php spark db:create-notification-tables

# Processar fila de notificações
php spark notifications:process

# Verificar campanhas acabando (adicionar ao cron)
php spark campaigns:check-ending

# Enviar relatório semanal admin (adicionar ao cron)
php spark admin:weekly-report
```

---

## 🎯 PRÓXIMOS PASSOS IMEDIATOS

1. Adicionar triggers no DonationModel (URGENTE)
2. Expandir ProcessNotifications para novos tipos
3. Criar templates de email
4. Criar páginas de preferências
5. Testar fluxo completo

---

*Documento criado em: 2025-11-18*
*Sistema implementado com arquitetura modular e escalável*
