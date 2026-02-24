Set WshShell = CreateObject("WScript.Shell")
' 0 = Ocultar janela completamente
' False = Não esperar conclusão
WshShell.Run "cmd /c ""cd /d C:\laragon\www\doarfazbem && C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe spark notifications:send > nul 2>&1""", 0, True
Set WshShell = Nothing
