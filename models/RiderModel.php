<?php
/**
 * models/RiderModel.php
 * Capa de acceso a datos para la entidad riders.
 */
require_once __DIR__ . '/../config/Database.php';

class RiderModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll($search = '')
    {
        if (!empty($search)) {
            $sql = "SELECT * FROM riders 
                    WHERE name LIKE :search 
                       OR contacto LIKE :search 
                    ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            $likeSearch = "%{$search}%";
            $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $sql = "SELECT * FROM riders ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM riders WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($data)
    {
        $sql = "INSERT INTO riders (name, contacto, is_active, foto_perfil) 
                VALUES (:name, :contacto, :is_active, :foto_perfil)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':contacto', $data['contacto']);
        $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);
        $stmt->bindParam(':foto_perfil', $data['foto_perfil']);

        return $stmt->execute();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE riders 
                SET name = :name, contacto = :contacto, is_active = :is_active, foto_perfil = :foto_perfil 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':contacto', $data['contacto']);
        $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);
        $stmt->bindParam(':foto_perfil', $data['foto_perfil']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Alterna el estado is_active entre 0 y 1 (activación/desactivación reversible).
     */
    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE riders SET is_active = NOT is_active WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM riders WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
