<?php

class ApiToken
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function crear(int $usuarioId): string
    {
        // Token aleatorio de 32 bytes (64 caracteres hexadecimales)
        $token = bin2hex(random_bytes(32));

        // Solo almacenamos su hash
        $tokenHash = hash('sha256', $token);

        $zonaHoraria = new DateTimeZone('America/Asuncion');

        // Vencimiento: 7 días
        $expiraEn = (new DateTimeImmutable(
            'now',
            $zonaHoraria
        ))
            ->modify('+7 days')
            ->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            INSERT INTO api_tokens (
                usuario_id,
                token_hash,
                expira_en
            )
            VALUES (
                :usuario_id,
                :token_hash,
                :expira_en
            )
        ");

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':token_hash' => $tokenHash,
            ':expira_en' => $expiraEn,
        ]);

        // El token original se entrega una sola vez al cliente
        return $token;
    }

    public function verificar(string $token): array|false
    {
        $ahora = (new DateTimeImmutable('now', new DateTimeZone('America/Asuncion')))->format('Y-m-d H:i:s');

        // Los tokens generados tienen 64 caracteres hexadecimales.
        if (!preg_match('/\A[a-f0-9]{64}\z/D', $token)) {
            return false;
        }

        $tokenHash = hash('sha256', $token);

        $stmt = $this->db->prepare("
            SELECT
                u.id,
                u.name,
                u.email,
                u.role_id
            FROM api_tokens t
            INNER JOIN usuarios u ON u.id = t.usuario_id
            INNER JOIN roles r ON r.id = u.role_id
            WHERE t.token_hash = :token_hash
            AND t.expira_en > :ahora
            AND t.revocado_en IS NULL
            AND u.is_active = 1
            AND u.activated_at IS NOT NULL
            AND r.slug = 'rider'
            LIMIT 1
        ");

        $stmt->execute([
            ':token_hash' => $tokenHash,
            ':ahora' => $ahora
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function revocar(string $token): bool
    {
        // Validar el formato del token
        if (!preg_match('/\A[a-f0-9]{64}\z/D', $token)) {
            return false;
        }

        // Buscarlo mediante su hash
        $tokenHash = hash('sha256', $token);

        $zonaHoraria = new DateTimeZone('America/Asuncion');

        $ahora = (new DateTimeImmutable(
            'now',
            $zonaHoraria
        ))->format('Y-m-d H:i:s');

        $stmt = $this->db->prepare("
            UPDATE api_tokens
            SET revocado_en = :ahora
            WHERE token_hash = :token_hash
            AND revocado_en IS NULL
            AND expira_en > :ahora_expiracion
        ");

        $stmt->execute([
            ':ahora' => $ahora,
            ':token_hash' => $tokenHash,
            ':ahora_expiracion' => $ahora
        ]);

        return $stmt->rowCount() === 1;
    }

}