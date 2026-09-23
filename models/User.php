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
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT u.*, r.name AS role_name, r.slug AS role_slug, c.cargo AS cargo_name
            FROM usuarios u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN cargos c ON u.cargo_id = c.id
            ORDER BY u.id DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Crea un nuevo usuario. Retorna el ID insertado.
     */
    public function create(array $data): string
    {

        $stmt = $this->db->prepare("
            INSERT INTO usuarios
                (name, email, password, phone, address, role_id, grupo_id, cargo_id, is_active, documento_tipo, documento_numero)
            VALUES
                (:name, :email, :password, :phone, :address, :role_id, :grupo_id, :cargo_id, :is_active, :documento_tipo, :documento_numero)
        ");
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'role_id' => $data['role_id'],
            'grupo_id' => $data['grupo_id'] ?? null, // 👈 nuevo
            'cargo_id' => $data['cargo_id'] ?? null,
            'is_active' => $data['is_active'] ?? 1,
            'documento_tipo' => $data['documento_tipo'] ?? 'DNI',
            'documento_numero' => $data['documento_numero'] ?? null,
        ]);

        return $this->db->lastInsertId();
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
    public function getAllRoles(): array
    {
        return $this->db->query("SELECT id, name, slug FROM roles ORDER BY name ASC")->fetchAll();
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
            SELECT r.id, r.name, r.contacto, r.is_active, r.foto_perfil
            FROM riders r
            WHERE r.id_usuario = :usuarioId
            LIMIT 1
        ");
        $stmt->execute(['usuarioId' => $usuarioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}