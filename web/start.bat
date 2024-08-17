@echo off
setlocal

:: Vérifier si Docker Desktop est installé
where docker >nul 2>nul
if errorlevel 1 (
    echo Docker Desktop n'est pas installé.
    echo Téléchargement et installation de Docker Desktop...
    :: Télécharger et installer Docker Desktop
    :: Remplacez l'URL suivante par la dernière version disponible si nécessaire
    powershell -Command "Invoke-WebRequest -Uri 'https://desktop.docker.com/win/stable/Docker%20Desktop%20Installer.exe' -OutFile 'DockerDesktopInstaller.exe'"
    start /wait DockerDesktopInstaller.exe
    :: Nettoyer le fichier d'installation
    del DockerDesktopInstaller.exe
) else (
    echo Docker Desktop est déjà installé.
)

:: Vérifier si Docker Desktop est en cours d'exécution
tasklist /FI "IMAGENAME eq Docker Desktop.exe" 2>NUL | find /I /N "Docker Desktop.exe">NUL
if not "%ERRORLEVEL%"=="0" (
    echo Docker Desktop n'est pas en cours d'exécution. Veuillez le démarrer manuellement.
    exit /b 1
)

:: Exécuter les commandes npm et composer
echo Exécution des commandes npm et composer...
npm install && npm update && npm run build
composer install && composer update
docker compose up

endlocal
pause