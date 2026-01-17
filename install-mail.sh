#!/bin/bash
# Script de instalación para configurar envío de correos

echo "======================================"
echo "Instalación: Sistema de Envío de Correos"
echo "======================================"
echo ""

# Verificar si Composer está instalado
if ! command -v composer &> /dev/null; then
    echo "❌ Composer no está instalado"
    echo "Descárgalo desde: https://getcomposer.org/"
    exit 1
fi

echo "✓ Composer detectado"
echo ""

# Instalar dependencias
echo "📦 Instalando PHPMailer..."
composer require phpmailer/phpmailer

echo ""
echo "✓ PHPMailer instalado correctamente"
echo ""

# Crear archivo .env
if [ ! -f ".env" ]; then
    echo "📝 Creando archivo .env..."
    cp .env.example .env
    echo "✓ Archivo .env creado"
    echo ""
    echo "⚠️  SIGUIENTE PASO: Edita el archivo .env con tus credenciales SMTP"
else
    echo "ℹ️  Archivo .env ya existe"
fi

echo ""
echo "======================================"
echo "✓ Instalación completada"
echo "======================================"
echo ""
echo "PRÓXIMOS PASOS:"
echo "1. Edita .env con tus credenciales SMTP"
echo "2. Lee README_MAIL.md para ejemplos de configuración"
echo "3. Prueba el sistema en /public/forgot-password.html"
echo ""
