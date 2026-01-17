# Script de instalación para Windows
# Ejecutar como: powershell -ExecutionPolicy Bypass -File install-mail.ps1

Write-Host "======================================"
Write-Host "Instalación: Sistema de Envío de Correos"
Write-Host "======================================"
Write-Host ""

# Verificar si Composer está instalado
$composerPath = Get-Command composer -ErrorAction SilentlyContinue
if ($null -eq $composerPath) {
    Write-Host "❌ Composer no está instalado" -ForegroundColor Red
    Write-Host "Descárgalo desde: https://getcomposer.org/" -ForegroundColor Yellow
    exit 1
}

Write-Host "✓ Composer detectado" -ForegroundColor Green
Write-Host ""

# Instalar dependencias
Write-Host "📦 Instalando PHPMailer..." -ForegroundColor Cyan
composer require phpmailer/phpmailer

Write-Host ""
Write-Host "✓ PHPMailer instalado correctamente" -ForegroundColor Green
Write-Host ""

# Crear archivo .env
$envPath = ".env"
if (!(Test-Path $envPath)) {
    Write-Host "📝 Creando archivo .env..." -ForegroundColor Cyan
    Copy-Item ".env.example" ".env"
    Write-Host "✓ Archivo .env creado" -ForegroundColor Green
    Write-Host ""
    Write-Host "⚠️  SIGUIENTE PASO: Edita el archivo .env con tus credenciales SMTP" -ForegroundColor Yellow
} else {
    Write-Host "ℹ️  Archivo .env ya existe" -ForegroundColor Blue
}

Write-Host ""
Write-Host "======================================"
Write-Host "✓ Instalación completada" -ForegroundColor Green
Write-Host "======================================"
Write-Host ""
Write-Host "PRÓXIMOS PASOS:" -ForegroundColor Yellow
Write-Host "1. Edita .env con tus credenciales SMTP"
Write-Host "2. Lee README_MAIL.md para ejemplos de configuración"
Write-Host "3. Prueba el sistema en /public/forgot-password.html"
Write-Host ""
