# =========================================================
# SCRIPT DE CONFIGURACIÓN INICIAL DEL PROYECTO
# =========================================================
<#
  Template Base MVC - Inicializador Automático
  Autor: Desarrollador Principal
  Fecha: Julio 2026
  Versión: 6.0 (Estandarizada con Rutas Absolutas y Scroll Fijo)
  Descripción: Este script crea de forma automatizada la estructura 
  de directorios y archivos base para el proyecto web en PHP nativo.
#>

Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host "        INICIALIZADOR DE TEMPLATE MVC - V6.0            " -ForegroundColor Cyan
Write-Host "=========================================================" -ForegroundColor Cyan

$root = Get-Location

$folders = @(
    "config", "controllers", "models", "views", "views/layouts", "views/usuarios", 
    "public", "public/css", "public/js", "public/images"
)
foreach ($folder in $folders) {
    $path = [System.IO.Path]::Combine($root, $folder)
    if (!(Test-Path $path)) {
        New-Item -ItemType Directory -Path $path -Force | Out-Null
        Write-Host "[✔] Carpeta creada: $folder" -ForegroundColor Green
    }
}

$files = @(
    "config/Database.php", "config/setup.php", "controllers/AuthController.php", 
    "controllers/DashboardController.php", "controllers/UserController.php", "models/User.php", 
    "views/login.php", "views/dashboard.php", "views/layouts/main.php", "views/usuarios/index.php", 
    "views/usuarios/form.php", "public/css/style.css", "public/js/main.js", "public/images/favicon.svg", 
    ".htaccess", "index.php"
)
foreach ($file in $files) {
    $path = [System.IO.Path]::Combine($root, $file)
    if (!(Test-Path $path)) {
        New-Item -ItemType File -Path $path -Force | Out-Null
        Write-Host "[✔] Archivo creado: $file" -ForegroundColor Green
    }
}

Write-Host "`n=========================================================" -ForegroundColor Cyan
Write-Host "Estructura del proyecto MVC generada correctamente." -ForegroundColor Yellow
Write-Host "Ahora pega el contenido de cada archivo en su ubicacion correspondiente." -ForegroundColor Yellow
Write-Host "=========================================================" -ForegroundColor Cyan
