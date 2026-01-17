<?php
/**
 * Utilidad para envío de correos usando PHPMailer
 * 
 * Uso:
 * $mailer = new MailSender();
 * $mailer->sendPasswordReset($email, $resetLink, $username);
 */

// Cargar autoload de Composer (PHPMailer)
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailSender {
    private $mail;
    private $config;

    public function __construct() {
        $this->config = require __DIR__ . '/config/mail.php';
        
        $this->mail = new PHPMailer(true);
        
        try {
            // Configuración del servidor SMTP
            $this->mail->isSMTP();
            $this->mail->Host = $this->config['host'];
            $this->mail->Port = $this->config['port'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->config['username'];
            $this->mail->Password = $this->config['password'];
            $this->mail->SMTPSecure = $this->config['encryption'];
            
            // Configuración de remitente
            $this->mail->setFrom(
                $this->config['from']['address'],
                $this->config['from']['name']
            );
            
            // Charset
            $this->mail->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log('PHPMailer Error: ' . $e->getMessage());
            throw new Exception('Error configurando sistema de correo');
        }
    }

    /**
     * Enviar correo de restablecimiento de contraseña
     */
    public function sendPasswordReset($toEmail, $resetLink, $username) {
        try {
            $this->mail->addAddress($toEmail, $username);
            
            $this->mail->Subject = 'Restablecimiento de Contraseña - Sistema de Encuesta';
            $this->mail->isHTML(true);
            $this->mail->Body = $this->getPasswordResetTemplate($username, $resetLink);
            $this->mail->AltBody = strip_tags("Hola $username,\n\nHaz clic en el siguiente enlace para restablecer tu contraseña:\n$resetLink\n\nEste enlace expirará en 1 hora.\n\nSi no solicitaste esto, ignora este correo.");
            
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Error sending password reset email: ' . $e->getMessage());
            throw new Exception('No se pudo enviar el correo de restablecimiento');
        } finally {
            $this->mail->clearAllRecipients();
        }
    }

    /**
     * Template HTML para correo de restablecimiento
     */
    private function getPasswordResetTemplate($username, $resetLink) {
        $appName = $this->config['from']['name'];
        
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; border-radius: 5px 5px 0 0; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px; }
                .button { display: inline-block; background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .button:hover { background-color: #0056b3; }
                .footer { font-size: 12px; color: #666; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
                .warning { color: #856404; background-color: #fff3cd; padding: 10px; border-radius: 3px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Restablecimiento de Contraseña</h1>
                </div>
                <div class='content'>
                    <p>¡Hola <strong>$username</strong>!</p>
                    
                    <p>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el botón de abajo para crear una nueva contraseña:</p>
                    
                    <center>
                        <a href='$resetLink' class='button'>Restablecer Contraseña</a>
                    </center>
                    
                    <p>O copia y pega este enlace en tu navegador:</p>
                    <p style='word-break: break-all; background-color: #e9ecef; padding: 10px; border-radius: 3px;'>
                        <code>$resetLink</code>
                    </p>
                    
                    <div class='warning'>
                        <strong>⏰ Importante:</strong> Este enlace expirará en <strong>1 hora</strong>. Si no lo usas antes, deberás solicitar un nuevo enlace de restablecimiento.
                    </div>
                    
                    <p>Si <strong>no</strong> solicitaste restablecer tu contraseña, puedes ignorar este correo. Tu cuenta seguirá siendo segura.</p>
                    
                    <div class='footer'>
                        <p>Este es un correo automático, por favor no respondas. Si tienes problemas, contacta al administrador.</p>
                        <p>&copy; " . date('Y') . " $appName. Todos los derechos reservados.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>
