# Script que roda em VERDADEIRO background - SEM JANELAS
# Usa Start-Process com -WindowStyle Hidden

$phpExe = "C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe"
$workDir = "C:\laragon\www\doarfazbem"
$args = "spark notifications:send"

# Iniciar processo COMPLETAMENTE oculto
Start-Process -FilePath $phpExe `
              -ArgumentList $args `
              -WorkingDirectory $workDir `
              -WindowStyle Hidden `
              -NoNewWindow `
              -Wait
