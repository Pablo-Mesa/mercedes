<?php
/**
 * app/services/MailerService.php
 * Servicio simple para envío de correos electrónicos.
 */
class MailerService
{
    /**
     * Envía un correo electrónico.
     *
     * @param string $to       Dirección de correo destino
     * @param string $subject  Asunto del correo
     * @param string $body     Cuerpo del mensaje (texto plano)
     * @param string|null $from Dirección de remitente (opcional)
     * @return bool
     */
    public static function send(string $to, string $subject, string $body, ?string $from = null): bool
    {
        $headers = [];

        // Remitente
        $fromAddress = $from ?? 'no-reply@mercedes.local';
        $headers[] = "From: {$fromAddress}";

        // Cabeceras básicas
        $headers[] = "Reply-To: {$fromAddress}";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        $headers[] = "Content-Type: text/plain; charset=UTF-8";

        // Unir cabeceras
        $headersString = implode("\r\n", $headers);

        // Enviar correo
        return mail($to, $subject, $body, $headersString);
    }
}
