# ============================================
# Script DEFINITIVO para Cron Job Silencioso
# DoarFazBem - ZERO JANELAS
# ============================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Corrigindo Cron Job Silencioso" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$taskName = "DoarFazBem-Notifications"

# 1. REMOVER TAREFA ANTIGA
Write-Host "[1/3] Removendo tarefa antiga..." -ForegroundColor Yellow
Unregister-ScheduledTask -TaskName $taskName -Confirm:$false -ErrorAction SilentlyContinue

# 2. CRIAR SCRIPT POWERSHELL SILENCIOSO
Write-Host "[2/3] Criando script PowerShell silencioso..." -ForegroundColor Yellow

$silentScriptPath = "C:\laragon\www\doarfazbem\run-silent.ps1"
$silentScript = @'
# Script que executa PHP sem janela
$phpPath = "C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe"
$workDir = "C:\laragon\www\doarfazbem"

# Configurar processo oculto
$psi = New-Object System.Diagnostics.ProcessStartInfo
$psi.FileName = $phpPath
$psi.Arguments = "spark notifications:send"
$psi.WorkingDirectory = $workDir
$psi.WindowStyle = [System.Diagnostics.ProcessWindowStyle]::Hidden
$psi.CreateNoWindow = $true
$psi.UseShellExecute = $false
$psi.RedirectStandardOutput = $true
$psi.RedirectStandardError = $true

# Executar
$process = [System.Diagnostics.Process]::Start($psi)
$process.WaitForExit()
'@

$silentScript | Out-File -FilePath $silentScriptPath -Encoding UTF8 -Force

# 3. CRIAR NOVA TAREFA COM POWERSHELL OCULTO
Write-Host "[3/3] Criando tarefa com execução OCULTA..." -ForegroundColor Yellow

$action = New-ScheduledTaskAction `
    -Execute "powershell.exe" `
    -Argument "-WindowStyle Hidden -NoProfile -ExecutionPolicy Bypass -File `"$silentScriptPath`"" `
    -WorkingDirectory "C:\laragon\www\doarfazbem"

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
    -ExecutionTimeLimit (New-TimeSpan -Minutes 2) `
    -Priority 7

$principal = New-ScheduledTaskPrincipal `
    -UserId "$env:USERDOMAIN\$env:USERNAME" `
    -LogonType Interactive `
    -RunLevel Limited

Register-ScheduledTask `
    -TaskName $taskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Principal $principal `
    -Force | Out-Null

if ($?) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "  SUCESSO! TAREFA SILENCIOSA CRIADA" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Configuração:" -ForegroundColor Cyan
    Write-Host "  - Executar: PowerShell em modo OCULTO" -ForegroundColor White
    Write-Host "  - Intervalo: A cada 5 minutos" -ForegroundColor White
    Write-Host "  - Janelas: NENHUMA (100% silencioso)" -ForegroundColor Green
    Write-Host ""
    Write-Host "Status:" -ForegroundColor Cyan
    Get-ScheduledTask -TaskName $taskName | Select-Object TaskName, State | Format-List
} else {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "  ERRO!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
}

Write-Host ""
Write-Host "Pressione qualquer tecla para fechar..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
