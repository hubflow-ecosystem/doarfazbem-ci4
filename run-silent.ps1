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
