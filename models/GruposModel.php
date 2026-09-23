
<?php
/**
 * models/GruposModel.php
 * Capa de acceso a datos para la entidad grupos.
 */
require_once __DIR__ . '/../config/Database.php';

class GruposModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll($search = '')
    {
        if (!empty($search)) {
            $sql = "SELECT g.id_grupo, g.nombre, g.icono, p.titulo AS punto_titulo, g.descripcion, g.activo
                    FROM grupos g
                    JOIN punto_control p ON g.id_punto_control = p.id
                    WHERE g.nombre LIKE :search OR p.titulo LIKE :search
                    ORDER BY g.id_grupo DESC";
            $stmt = $this->db->prepare($sql);
            $likeSearch = "%{$search}%";
            $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $sql = "SELECT g.id_grupo, g.nombre, g.icono, p.titulo AS punto_titulo, g.descripcion, g.activo
                    FROM grupos g
                    JOIN punto_control p ON g.id_punto_control = p.id
                    ORDER BY g.nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM grupos WHERE id_grupo = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($data)
    {
        $sql = "INSERT INTO grupos (id_punto_control, nombre, descripcion, icono, activo) 
                VALUES (:id_punto_control, :nombre, :descripcion, :icono, :activo)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_punto_control', $data['id_punto_control'], PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':icono', $data['icono']);
        $stmt->bindParam(':activo', $data['activo'], PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE grupos 
                SET id_punto_control = :id_punto_control, nombre = :nombre, descripcion = :descripcion, 
                    icono = :icono, activo = :activo
                WHERE id_grupo = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_punto_control', $data['id_punto_control'], PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':icono', $data['icono']);
        $stmt->bindParam(':activo', $data['activo'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM grupos WHERE id_grupo = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
    
    /**
     * Devuelve los grupos asociados a un rider.
     */
    public function getByRider(int $riderId): array
    {
        $sql = "SELECT g.id_grupo, g.nombre, g.icono, p.titulo AS punto_titulo
                FROM grupos g
                JOIN punto_control p ON g.id_punto_control = p.id
                JOIN rider_grupo rg ON g.id_grupo = rg.grupo_id
                WHERE rg.rider_id = :riderId
                ORDER BY g.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':riderId', $riderId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}