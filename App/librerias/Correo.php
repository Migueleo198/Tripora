<?php

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enviar correos con PHPMailer usando los datos SMTP de configurar.php
class Correo
{
    // Devuelve true si se ha enviado (o guardado en el log), false si ha fallado
    public static function enviar($para, $asunto, $html)
    {
        // Sin cuenta configurada: se guarda en un fichero para poder probar en local
        if (CORREO_USUARIO === '') {
            $dir = RUTA_APP . '/logs';

            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $texto = date('Y-m-d H:i:s') . " | Para: $para | $asunto\n$html\n\n";

            return file_put_contents($dir . '/correos.log', $texto, FILE_APPEND) !== false;
        }

        $correo = new PHPMailer(true);

        try {
            $correo->isSMTP();
            $correo->Host = CORREO_HOST;
            $correo->Port = CORREO_PUERTO;
            $correo->SMTPAuth = true;
            $correo->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $correo->Username = CORREO_USUARIO;
            $correo->Password = CORREO_PASSWORD;
            $correo->CharSet = 'UTF-8';

            $correo->setFrom(CORREO_USUARIO, CORREO_NOMBRE);
            $correo->addAddress($para);

            $correo->isHTML(true);
            $correo->Subject = $asunto;
            $correo->Body = $html;
            $correo->AltBody = strip_tags($html);

            $correo->send();

            return true;
        } catch (Exception $e) {
            error_log('Error al enviar correo: ' . $correo->ErrorInfo);
            return false;
        }
    }
}
