# 📬 Instalação do Sistema de Notificações - DoarFazBem

Sistema completo de notificações por email e push notifications.

---

## ✅ O QUE JÁ ESTÁ PRONTO

- ✅ **Backend completo** (Models, Services, Controllers, Command)
- ✅ **Banco de dados** (Tabelas criadas via migration)
- ✅ **Email SMTP** configurado
- ✅ **Firebase** configurado com Service Account
- ✅ **Scripts de instalação** prontos

---

## 🚀 INSTALAÇÃO EM 2 PASSOS

### **PASSO 1: Instalar Cron Job**

Abra o **PowerShell** (não precisa ser como Administrador) e execute:

```powershell
cd C:\laragon\www\doarfazbem
.\instalar-cron.ps1
```

Isso vai:
- Criar tarefa no Task Scheduler do Windows
- Configurar para rodar a cada 5 minutos
- Processar fila de notificações automaticamente

**OU** se preferir instalar manualmente:
1. Clique com botão direito em [`instalar-cron.ps1`](instalar-cron.ps1)
2. Selecione "Executar com PowerShell"

---

### **PASSO 2: Testar o Sistema**

#### **Opção A: Teste Manual (Recomendado para primeira vez)**

```powershell
cd C:\laragon\www\doarfazbem
.\testar-notificacoes.ps1
```

Ou rode diretamente:
```bash
php spark notifications:send
```

#### **Opção B: Teste Completo (End-to-End)**

1. **Fazer uma doação:**
   - Acesse qualquer campanha: http://doarfazbem.ai/campaigns
   - Faça uma doação (qualquer valor)
   - **Marque os checkboxes** de notificação
   - Complete o pagamento

2. **Postar atualização:**
   - Faça login como criador da campanha
   - Acesse a campanha
   - Poste uma nova atualização

3. **Verificar envio:**
   - Execute: `.\testar-notificacoes.ps1`
   - Verifique o email do doador
   - Deve chegar email com a atualização

---

## 📊 MONITORAMENTO

### Verificar notificações pendentes no banco:

```sql
-- Ver fila
SELECT * FROM notification_queue WHERE status = 'pending';

-- Ver enviadas hoje
SELECT * FROM notification_queue
WHERE status = 'sent'
AND DATE(sent_at) = CURDATE()
ORDER BY sent_at DESC;

-- Ver falhadas
SELECT * FROM notification_queue WHERE status = 'failed';

-- Estatísticas
SELECT status, COUNT(*) as total
FROM notification_queue
GROUP BY status;
```

### Verificar logs:

```bash
# Ver último log
tail -f writable/logs/log-2025-11-17.log

# Ou abra o arquivo em:
writable/logs/log-YYYY-MM-DD.log
```

### Verificar tarefa do Windows:

```powershell
# Ver status
schtasks /Query /TN "DoarFazBem-Notifications" /FO LIST /V

# Ver histórico de execuções
Get-WinEvent -LogName "Microsoft-Windows-TaskScheduler/Operational" |
  Where-Object {$_.Message -like "*DoarFazBem*"} |
  Select-Object -First 10
```

---

## 🔧 GERENCIAMENTO

### Pausar cron job temporariamente:

```powershell
schtasks /Change /TN "DoarFazBem-Notifications" /DISABLE
```

### Reativar cron job:

```powershell
schtasks /Change /TN "DoarFazBem-Notifications" /ENABLE
```

### Executar manualmente (força execução imediata):

```powershell
schtasks /Run /TN "DoarFazBem-Notifications"
```

### Remover tarefa:

```powershell
schtasks /Delete /TN "DoarFazBem-Notifications" /F
```

---

## 📁 ARQUIVOS CRIADOS

```
doarfazbem/
├── firebase-credentials.json           # Credenciais Firebase (NÃO COMMITAR!)
├── run-notifications.bat              # Batch executado pelo cron
├── instalar-cron.ps1                  # Script de instalação
├── testar-notificacoes.ps1            # Script de teste
├── task-scheduler-config.xml          # Configuração XML da tarefa
│
├── app/
│   ├── Commands/
│   │   └── SendNotifications.php      # Command para cron
│   ├── Controllers/
│   │   └── NotificationController.php # Controller de preferências
│   ├── Models/
│   │   ├── NotificationPreference.php # Model de preferências
│   │   └── NotificationQueue.php      # Model da fila
│   └── Services/
│       ├── EmailNotificationService.php  # Envio de emails
│       └── PushNotificationService.php   # Push notifications Firebase
│
└── app/Database/Migrations/
    └── 2025-11-17-add-notification-preferences.php
```

---

## ❓ TROUBLESHOOTING

### "Nenhuma notificação pendente"
✅ **Normal!** Significa que a fila está vazia. Faça uma doação e poste uma atualização.

### "Firebase não configurado"
❌ Verifique se `firebase-credentials.json` existe e `FIREBASE_PROJECT_ID` está no `.env`

### "SMTP Error"
❌ Verifique configurações de email no `.env`:
- `email.SMTPHost`
- `email.SMTPUser`
- `email.SMTPPass`

### "Access token error"
❌ Problema com credenciais Firebase. Verifique:
- Arquivo `firebase-credentials.json` está correto
- Service Account tem permissão para Firebase Cloud Messaging

### Tarefa não executa automaticamente
❌ Verifique:
```powershell
# Status da tarefa
schtasks /Query /TN "DoarFazBem-Notifications"

# Se não existir, reinstale:
.\instalar-cron.ps1
```

---

## 🎉 PRONTO!

Agora o sistema está completo e rodando!

**Funcionalidades ativas:**
- ✅ Email notifications (via SMTP)
- ✅ Push notifications (via Firebase)
- ✅ Cron job automatizado (a cada 5 minutos)
- ✅ Preferências de usuário
- ✅ Unsubscribe via email
- ✅ Retry automático (3 tentativas)
- ✅ Limpeza de notificações antigas

**Próximos passos opcionais:**
- Criar views de gerenciamento de preferências
- Implementar JavaScript para push token no frontend
- Adicionar analytics de notificações

---

**Dúvidas?** Consulte `SISTEMA_NOTIFICACOES.md` para mais detalhes técnicos.
