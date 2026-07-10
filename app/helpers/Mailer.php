<?php
/**
 * =====================================================
 * HELPER: Mailer
 * =====================================================
 * Envoltorio delgado sobre PHPMailer para el envío de
 * correos transaccionales (hoy: recuperación de contraseña).
 * Las credenciales SMTP viven en config/mail.php (gitignored).
 * =====================================================
 */

require_once BASE_PATH . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mailer
{
    private static function config(): array
    {
        return require BASE_PATH . '/config/mail.php';
    }

    public static function enviarRecuperacionPassword(string $correoDestino, string $nombreDestino, string $enlace): bool
    {
        $cfg = self::config();
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $cfg['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $cfg['username'];
            $mail->Password = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'];
            $mail->Port = $cfg['port'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($cfg['from_email'], $cfg['from_name']);
            $mail->addAddress($correoDestino, $nombreDestino);

            $mail->isHTML(true);
            $mail->Subject = 'Recupera tu contraseña — Colegio San Cristóbal';
            $mail->Body = self::plantillaHtml($nombreDestino, $enlace);
            $mail->AltBody = "Hola {$nombreDestino},\n\nSolicitaste restablecer tu contraseña. Abre este enlace (válido 30 minutos):\n{$enlace}\n\nSi no fuiste tú, ignora este mensaje.";

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('Mailer::enviarRecuperacionPassword falló: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private static function plantillaHtml(string $nombre, string $enlace): string
    {
        $nombreEsc = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $enlaceEsc = htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8');
        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto; color:#1f2d24;">
            <h2 style="color:#0a932c;">Recupera tu contraseña</h2>
            <p>Hola {$nombreEsc},</p>
            <p>Recibimos una solicitud para restablecer tu contraseña en el Portal Académico del Colegio San Cristóbal.</p>
            <p style="margin: 24px 0;">
                <a href="{$enlaceEsc}" style="background:#0a932c;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;display:inline-block;font-weight:bold;">
                    Restablecer contraseña
                </a>
            </p>
            <p>Este enlace vence en 30 minutos. Si no solicitaste este cambio, puedes ignorar este correo con tranquilidad.</p>
        </div>
        HTML;
    }
}
