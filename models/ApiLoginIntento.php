<?php

class ApiLoginIntento
{
    private PDO $db;

    private string $ipHmacKey;

    public function __construct()
    {
        $this->db = Database::getConnection();

        $config = require 'C:/solver-private/config.php';

        $clave = $config['ip_hmac_key'] ?? null;

        if (
            !is_string($clave) ||
            !preg_match('/\A[a-f0-9]{64}\z/D', $clave)
        ) {
            throw new RuntimeException(
                'La clave HMAC de IP no está configurada correctamente.'
            );
        }

        $this->ipHmacKey = $clave;
    }

    public function estaBloqueado(
        string $email,
        string $ip
    ): bool {
        $emailNormalizado = strtolower(trim($email));

        $emailHash = hash('sha256', $emailNormalizado);
        $ipHash = hash_hmac('sha256', $ip, $this->ipHmacKey);

        $zonaHoraria = new DateTimeZone('America/Asuncion');

        $desde = (new DateTimeImmutable(
            'now',
            $zonaHoraria
        ))
            ->modify('-15 minutes')
            ->format('Y-m-d H:i:s');

        // Comprobar intentos por correo
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM api_login_intentos
            WHERE email_hash = :email_hash
              AND creado_en >= :desde
        ");

        $stmt->execute([
            ':email_hash' => $emailHash,
            ':desde' => $desde
        ]);

        $intentosCorreo = (int) $stmt->fetchColumn();

        if ($intentosCorreo >= 5) {
            return true;
        }

        // Comprobar intentos por dirección IP
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM api_login_intentos
            WHERE ip_hash = :ip_hash
              AND creado_en >= :desde
        ");

        $stmt->execute([
            ':ip_hash' => $ipHash,
            ':desde' => $desde
        ]);

        $intentosIp = (int) $stmt->fetchColumn();

        return $intentosIp >= 20;
    }

    public function registrarFallo(
        string $email,
        string $ip
    ): void {
        $emailNormalizado = strtolower(trim($email));

        $emailHash = hash('sha256', $emailNormalizado);
        $ipHash = hash_hmac('sha256', $ip, $this->ipHmacKey);

        $ahora = (new DateTimeImmutable(
            'now',
            new DateTimeZone('America/Asuncion')
        ))->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            INSERT INTO api_login_intentos (
                email_hash,
                ip_hash,
                creado_en
            )
            VALUES (
                :email_hash,
                :ip_hash,
                :creado_en
            )
        ");

        $stmt->execute([
            ':email_hash' => $emailHash,
            ':ip_hash' => $ipHash,
            ':creado_en' => $ahora
        ]);
    }

}