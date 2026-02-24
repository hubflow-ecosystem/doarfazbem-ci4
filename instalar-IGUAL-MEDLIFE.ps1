# Instalar tarefa EXATAMENTE como MedLife
# Testado e funcionando SEM janelas

Write-Host "Criando tarefa DoarFazBem (igual MedLife)..." -ForegroundColor Cyan

$taskName = "DoarFazBem-Notifications"

# Remover tarefa antiga
Unregister-ScheduledTask -TaskName $taskName -Confirm:$false -ErrorAction SilentlyContinue

# Criar ação - executar BAT
$action = New-ScheduledTaskAction `
    -Execute "C:\laragon\www\doarfazbem\run-notifications-cron.bat"

# Trigger - a cada 5 minutos
$trigger = New-ScheduledTaskTrigger `
    -Once `
    -At (Get-Date).Date `
    -RepetitionInterval (New-TimeSpan -Minutes 5)

# Settings - CRITICAL: StartWhenAvailable
$settings = New-ScheduledTaskSettingsSet `
    -StartWhenAvailable `
    -DontStopIfGoingOnBatteries `
    -AllowStartIfOnBatteries

# Principal - usuário atual com Interactive
$principal = New-ScheduledTaskPrincipal `
    -UserId "$env:USERNAME" `
    -LogonType Interactive

# Registrar
Register-ScheduledTask `
    -TaskName $taskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Principal $principal `
    -Force | Out-Null

Write-Host "OK - Tarefa criada!" -ForegroundColor Green
Write-Host ""
Write-Host "Testando execução..." -ForegroundColor Yellow
Start-ScheduledTask -TaskName $taskName

Start-Sleep -Seconds 2

Write-Host ""
Write-Host "Verifique se apareceu janela." -ForegroundColor Cyan
Write-Host "Se sim, o problema pode ser permissões do usuário" -ForegroundColor Yellow
