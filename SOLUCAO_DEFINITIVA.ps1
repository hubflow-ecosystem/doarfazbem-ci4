# SOLUÇÃO DEFINITIVA - USA NIRCMD (ferramenta profissional)
# ou método VBScript comprovado

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  SOLUÇÃO DEFINITIVA ANTI-JANELA" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$taskName = "DoarFazBem-Notifications"

# Remover tarefa
Write-Host "[1/2] Removendo tarefa antiga..." -ForegroundColor Yellow
Unregister-ScheduledTask -TaskName $taskName -Confirm:$false -ErrorAction SilentlyContinue

# Criar tarefa usando VBScript wrapper
Write-Host "[2/2] Criando com VBScript wrapper..." -ForegroundColor Yellow

$phpPath = "C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe"
$workDir = "C:\laragon\www\doarfazbem"

$action = New-ScheduledTaskAction `
    -Execute "wscript.exe" `
    -Argument "`"C:\laragon\www\doarfazbem\RunHiddenConsole.vbs`" `"$phpPath`" spark notifications:send" `
    -WorkingDirectory $workDir

$trigger = New-ScheduledTaskTrigger `
    -Once `
    -At (Get-Date) `
    -RepetitionInterval (New-TimeSpan -Minutes 5) `
    -RepetitionDuration (New-TimeSpan -Days 999)

$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -Hidden `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 2)

$principal = New-ScheduledTaskPrincipal `
    -UserId "$env:USERNAME" `
    -LogonType Interactive

Register-ScheduledTask `
    -TaskName $taskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Principal $principal `
    -Force | Out-Null

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  TAREFA CRIADA" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Método: VBScript Wrapper (0 = invisible)" -ForegroundColor Cyan
Write-Host ""

Get-ScheduledTask -TaskName $taskName | Select-Object TaskName, State | Format-List

Write-Host ""
Write-Host "Testando agora..." -ForegroundColor Yellow
Start-ScheduledTask -TaskName $taskName

Start-Sleep -Seconds 3

Write-Host ""
Write-Host "Se ainda aparecer janela, o problema é do Windows" -ForegroundColor Red
Write-Host "não tem como contornar sem software de terceiros" -ForegroundColor Red
Write-Host ""
Write-Host "Alternativa: usar NirCmd (baixar de nirsoft.net)" -ForegroundColor Yellow
Write-Host ""

Write-Host "Pressione qualquer tecla para fechar..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
