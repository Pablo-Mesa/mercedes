<?php
/**
 * models/User.php
 * Capa de acceso a datos para la entidad usuarios.
 */

require_once __DIR__ . '/../config/Database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Busca un usuario por su correo (usado en el login).
     */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.slug AS role_slug, r.name AS role_name
            FROM usuarios u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.email = :email
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Busca un usuario por ID, incluyendo el slug de su rol.
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.slug AS role_slug, r.name AS role_name
            FROM usuarios u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Devuelve el listado completo de usuarios con su rol y cargo.
     */
    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT u.*, r.name AS role_name, r.slug AS role_slug
            FROM usuarios u
            JOIN roles r ON u.role_id = r.id
            WHERE u.role_id IN (1,2,4)
            ORDER BY u.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔑 Listar Riders
    public function getAllRiders(): array {
        $stmt = $this->db->query("
            SELECT u.*, g.nombre AS grupo_nombre
            FROM usuarios u
            LEFT JOIN grupos g ON u.grupo_id = g.id_grupo
            WHERE u.role_id = 3
            ORDER BY u.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear Rider
    public function insertRider(array $data): array
    {
        // Verificar si el correo ya existe
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $data['email']]);
        if ($stmt->fetch()) {
            // Lanzamos excepción controlada
            throw new Exception("El correo {$data['email']} ya está registrado. Usa otro.");
        }

        // Generar token de activación
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 86400); // 24h

        // Password temporal (hash aleatorio)
        $passwordTemporal = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO usuarios (
                name, email, phone, password, role_id, is_active, grupo_id, foto_perfil,
                documento_tipo, documento_numero,
                activation_token_hash, activation_expires_at, activated_at
            )
            VALUES (
                :name, :email, :phone, :password, 3, :is_active, :grupo_id, :foto_perfil,
                :documento_tipo, :documento_numero,
                :activation_token_hash, :activation_expires_at, NULL
            )
        ");

        $stmt->execute([
            ':name'                  => $data['name'],
            ':email'                 => $data['email'],
            ':phone'                 => $data['phone'],
            ':password'              => $passwordTemporal,
            ':is_active'             => $data['is_active'] ?? 1,
            ':grupo_id'              => $data['grupo_id'] ?? null,
            ':foto_perfil'           => $data['foto_perfil'] ?? 'default.png',
            ':documento_tipo'        => $data['documento_tipo'] ?? 'DNI',
            ':documento_numero'      => $data['documento_numero'] ?? null,
            ':activation_token_hash' => $tokenHash,
            ':activation_expires_at' => $expiresAt,
        ]);

        $riderId = (int)$this->db->lastInsertId();

        // Retornar ID y token original (para enviar enlace)
        return [ 'id' => $riderId, 'activation_token' => $token ];
    }

    // Actualizar Rider
    // 🔑 Actualizar Rider
    public function updateRider(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios SET
                name = :name, email = :email, phone = :phone,
                is_active = :is_active, foto_perfil = :foto_perfil
            WHERE id = :id AND role_id = 3
        ");
        return $stmt->execute([
            ':name'        => $data['name'],
            ':email'       => $data['email'],
            ':phone'       => $data['phone'],
            ':is_active'   => $data['is_active'],
            ':foto_perfil' => $data['foto_perfil'],
            ':id'          => $id
        ]);
    }

    // 🔑 Alternar estado
    public function toggleRiderStatus(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET is_active = NOT is_active WHERE id = :id AND role_id = 3");
        return $stmt->execute(['id' => $id]);
    }

    // 🔑 Eliminar Rider
    public function deleteRider(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id AND role_id = 3");
        return $stmt->execute(['id' => $id]);
    }

    // 🔑 Asignar Rider a grupos
    public function assignRiderToGroups(int $riderId, array $groupIds): void
    {
        $stmt = $this->db->prepare("DELETE FROM rider_grupo WHERE rider_id = ?");
        $stmt->execute([$riderId]);

        $sql = "INSERT INTO rider_grupo (rider_id, grupo_id) VALUES (:rider_id, :grupo_id)";
        $stmt = $this->db->prepare($sql);

        foreach ($groupIds as $groupId) {
            $stmt->execute([
                ':rider_id' => $riderId,
                ':grupo_id' => $groupId
            ]);
        }
    }

    // 🔑 Obtener grupos de un Rider
    public function getGroupsByRider(int $riderId): array
    {
        $sql = "SELECT grupo_id FROM rider_grupo WHERE rider_id = :rider_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':rider_id' => $riderId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // 🔑 Producciones de un Rider
    public function getProduccionByRider(int $riderId): array
    {
        $sql = "SELECT p.id, p.fecha_creacion, tu.turno, dt.costo AS tarifa_detalle,
                    a.nombre AS accion_nombre, p.rendicion, g.nombre AS grupo_nombre
                FROM produccion p
                JOIN turnos tu ON p.id_turno = tu.id
                JOIN detalles_tarifas dt ON p.id_detalle_tarifa = dt.id
                JOIN acciones_rider a ON p.id_accion = a.id_accion
                JOIN grupos g ON p.id_grupo = g.id_grupo
                WHERE p.id_rider = :riderId
                ORDER BY p.fecha_creacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['riderId' => $riderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo usuario. Retorna el ID insertado.
     */
    public function create(array $data): array
    {
        // 1. Generar token de activación
        $token = bin2hex(random_bytes(32)); // token original
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 86400); // 24h

        // 2. Password temporal (hash aleatorio, nunca en texto plano)
        $passwordTemporal = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

        // 3. Insertar usuario
        $stmt = $this->db->prepare("
            INSERT INTO usuarios
                (name, email, password, phone, address, role_id, grupo_id, cargo_id,
                is_active, documento_tipo, documento_numero,
                activation_token_hash, activation_expires_at, activated_at)
            VALUES
                (:name, :email, :password, :phone, :address, :role_id, :grupo_id, :cargo_id,
                :is_active, :documento_tipo, :documento_numero,
                :activation_token_hash, :activation_expires_at, NULL)
        ");

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $passwordTemporal, // Rider definirá su contraseña al activar
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'role_id' => $data['role_id'],
            'grupo_id' => $data['grupo_id'] ?? null,
            'cargo_id' => $data['cargo_id'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'documento_tipo' => $data['documento_tipo'] ?? 'DNI',
            'documento_numero' => $data['documento_numero'] ?? null,
            'activation_token_hash' => $tokenHash,
            'activation_expires_at' => $expiresAt,
        ]);

        $userId = $this->db->lastInsertId();

        // 4. Retornar ID y token original (para enviar enlace)
        return [
            'id' => $userId,
            'activation_token' => $token
        ];
    }

    /**
     * Actualiza un usuario existente. Si no se envía password, se conserva la actual.
     */
    public function update(int $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare("
                UPDATE usuarios SET
                    name = :name, email = :email, password = :password, phone = :phone,
                    address = :address, role_id = :role_id, grupo_id = :grupo_id, cargo_id = :cargo_id,
                    documento_tipo = :documento_tipo, documento_numero = :documento_numero
                WHERE id = :id
            ");
            return $stmt->execute([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'role_id' => $data['role_id'],
                'grupo_id' => $data['grupo_id'] ?? null,
                'cargo_id' => $data['cargo_id'] ?? null,
                'documento_tipo' => $data['documento_tipo'] ?? 'DNI',
                'documento_numero' => $data['documento_numero'] ?? null,
                'id' => $id,
            ]);
        }

        $stmt = $this->db->prepare("
            UPDATE usuarios SET
                name = :name, email = :email, phone = :phone,
                address = :address, role_id = :role_id, cargo_id = :cargo_id,
                documento_tipo = :documento_tipo, documento_numero = :documento_numero
            WHERE id = :id
        ");
        return $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'role_id' => $data['role_id'],
            'cargo_id' => $data['cargo_id'] ?? null,
            'documento_tipo' => $data['documento_tipo'] ?? 'DNI',
            'documento_numero' => $data['documento_numero'] ?? null,
            'id' => $id,
        ]);
    }

    /**
     * Alterna el estado is_active entre 0 y 1 (activación/desactivación reversible).
     */
    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET is_active = NOT is_active WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Devuelve todos los roles disponibles (para poblar selects en formularios).
     */
    public function getAllRoles(): array {
        $stmt = $this->db->query("SELECT * FROM roles WHERE id IN (1,2,4) ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve todos los cargos disponibles (para poblar selects en formularios).
     */
    public function getAllCargos(): array
    {
        return $this->db->query("SELECT id, cargo, slug FROM cargos ORDER BY cargo ASC")->fetchAll();
    }

    /**
     * Devuelve el rider vinculado a un usuario, si existe.
     */
    public function findRiderByUsuario(int $usuarioId): array|false
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email, u.is_active, u.foto_perfil
            FROM usuarios u
            WHERE u.id = :usuarioId AND u.role_id = 3
            LIMIT 1
        ");
        $stmt->execute(['usuarioId' => $usuarioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function activateUser(int $userId, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET
                password = :password,
                activated_at = :activated_at,
                activation_token_hash = :activation_token_hash,
                activation_expires_at = :activation_expires_at
            WHERE id = :id
        ");

        $stmt->execute([
            'password' => $data['password'],
            'activated_at' => $data['activated_at'],
            'activation_token_hash' => $data['activation_token_hash'], // normalmente NULL
            'activation_expires_at' => $data['activation_expires_at'], // normalmente NULL
            'id' => $userId,
        ]);
    }

    public function getRiderById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM usuarios
            WHERE id = :id AND role_id = 3
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function setActivationToken(int $id, string $hash, string $expires): bool
    {
        $stmt = $this->db->prepare("
            UPDATE usuarios
            SET
                activation_token_hash = :hash,
                activation_expires_at = :expires,
                activated_at = NULL
            WHERE id = :id
        ");

        return $stmt->execute([
            ':hash' => $hash,
            ':expires' => $expires,
            ':id' => $id,
        ]);
    }

}