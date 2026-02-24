@echo off
echo.
echo ============================================
echo   REMOVER TAREFAS DOARFAZBEM
echo ============================================
echo.
echo Removendo todas as tarefas...
echo.

schtasks /Delete /TN "DoarFazBem-Notifications" /F 2>nul
if %errorlevel%==0 (
    echo [OK] DoarFazBem-Notifications removida
) else (
    echo [INFO] DoarFazBem-Notifications nao encontrada
)

schtasks /Delete /TN "DoarFazBem Notificacoes" /F 2>nul
if %errorlevel%==0 (
    echo [OK] DoarFazBem Notificacoes removida
) else (
    echo [INFO] DoarFazBem Notificacoes nao encontrada
)

echo.
echo Verificando tarefas restantes...
echo.
schtasks /Query | findstr /i "DoarFazBem"

if %errorlevel%==0 (
    echo.
    echo [ATENCAO] Ainda existem tarefas DoarFazBem!
    echo Execute este arquivo como ADMINISTRADOR
    echo Clique com botao direito e selecione "Executar como administrador"
) else (
    echo.
    echo [SUCESSO] Nenhuma tarefa DoarFazBem encontrada!
    echo Tudo limpo!
)

echo.
echo Pressione qualquer tecla para fechar...
pause >nul
