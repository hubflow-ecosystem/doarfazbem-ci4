# Remover TODAS as tarefas DoarFazBem
# Execute como Administrador (Bot�o direito > Executar como Administrador)

Write-Host ""
Write-Host "=======================================" -ForegroundColor Cyan
Write-Host "  REMOVER TAREFAS DOARFAZBEM" -ForegroundColor Cyan
Write-Host "=======================================" -ForegroundColor Cyan
Write-Host ""

# Lista de nomes de tarefas para remover
$taskNames = @(
    "DoarFazBem-Notifications",
    "DoarFazBem Notifica��es"
)

foreach ($taskName in $taskNames) {
    Write-Host "Tentando remover: $taskName" -ForegroundColor Yellow

    try {
        Unregister-ScheduledTask -TaskName $taskName -Confirm:$false -ErrorAction Stop
        Write-Host "  OK - Tarefa removida!" -ForegroundColor Green
    } catch {
        if ($_ -like "*n�o foi encontrad*" -or $_ -like "*not found*") {
            Write-Host "  INFO - Tarefa n�o existe" -ForegroundColor Gray
        } else {
            Write-Host "  ERRO - $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    Write-Host ""
}

Write-Host "Verificando tarefas restantes..." -ForegroundColor Cyan
$remaining = Get-ScheduledTask | Where-Object {$_.TaskName -like '*DoarFazBem*' -or $_.TaskName -like '*Notifica*'}

if ($remaining) {
    Write-Host ""
    Write-Host "ATEN��O: Ainda existem tarefas:" -ForegroundColor Yellow
    $remaining | Format-Table TaskName, State -AutoSize
    Write-Host ""
    Write-Host "Execute este script como ADMINISTRADOR para remov�-las." -ForegroundColor Yellow
} else {
    Write-Host ""
    Write-Host "Nenhuma tarefa DoarFazBem encontrada. Tudo limpo!" -ForegroundColor Green
}

Write-Host ""
Write-Host "Pressione qualquer tecla para fechar..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
