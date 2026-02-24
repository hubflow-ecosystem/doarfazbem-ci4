# Script PowerShell para configurar Task Scheduler automaticamente
# Execute este arquivo como Administrador: clique com botão direito > "Executar como Administrador"

Write-Host "=== Configurando Task Scheduler para DoarFazBem Notificações ===" -ForegroundColor Green
Write-Host ""

# Nome da tarefa
$taskName = "DoarFazBem Notificações"

# Verificar se tarefa já existe
$existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue

if ($existingTask) {
    Write-Host "Tarefa '$taskName' já existe. Removendo..." -ForegroundColor Yellow
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

# Configurar ação (executar batch)
$action = New-ScheduledTaskAction -Execute "C:\laragon\www\doarfazbem\run-notifications.bat" -WorkingDirectory "C:\laragon\www\doarfazbem"

# Configurar gatilho (a cada 5 minutos, por 999 dias - praticamente indefinido)
$trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 5) -RepetitionDuration (New-TimeSpan -Days 999)

# Configurar settings
$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -RunOnlyIfNetworkAvailable:$false `
    -DontStopOnIdleEnd `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 2)

# Configurar principal (executar com usuário atual)
$principal = New-ScheduledTaskPrincipal -UserId "$env:USERDOMAIN\$env:USERNAME" -LogonType Interactive -RunLevel Limited

# Registrar tarefa
Register-ScheduledTask `
    -TaskName $taskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Principal $principal `
    -Description "Processa fila de notificações do DoarFazBem a cada 5 minutos"

Write-Host ""
Write-Host "✅ Tarefa '$taskName' criada com sucesso!" -ForegroundColor Green
Write-Host ""
Write-Host "Detalhes:" -ForegroundColor Cyan
Write-Host "  - Executar: C:\laragon\www\doarfazbem\run-notifications.bat" -ForegroundColor White
Write-Host "  - Intervalo: A cada 5 minutos" -ForegroundColor White
Write-Host "  - Início: Imediatamente" -ForegroundColor White
Write-Host ""
Write-Host "Para verificar, abra o Task Scheduler (Agendador de Tarefas) e procure por '$taskName'" -ForegroundColor Yellow
Write-Host ""
Write-Host "Pressione qualquer tecla para fechar..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
