<?php 
/**
 * models/TarifaModel.php
 * Capa de acceso a datos para la entidad tarifas.
 */
require_once __DIR__ . '/../config/Database.php';

class TarifaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll($search = '')
    {
        if (!empty($search)) {//si parametro NO viene vacio
            $sql = "SELECT * FROM tarifas 
                    WHERE desde LIKE :search 
                       OR hasta LIKE :search 
                       OR costo LIKE :search 
                    ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            $likeSearch = "%{$search}%";
            $stmt->bindParam(':search', $likeSearch, PDO::PARAM_STR);
            $stmt->execute();
        } else {//consulta general - todos los registros
            $sql = "SELECT * FROM tarifas ORDER BY id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM tarifas WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($data)
    {
        $sql = "INSERT INTO tarifas (desde, hasta, costo, disponible) 
                VALUES (:desde, :hasta, :costo, :disponible)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':desde', $data['desde']);
        $stmt->bindParam(':hasta', $data['hasta']);
        $stmt->bindParam(':costo', $data['costo'], PDO::PARAM_INT);
        $stmt->bindParam(':disponible', $data['disponible'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE tarifas 
                SET desde = :desde, hasta = :hasta, costo = :costo, disponible = :disponible 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':desde', $data['desde']);
        $stmt->bindParam(':hasta', $data['hasta']);
        $stmt->bindParam(':costo', $data['costo'], PDO::PARAM_INT);
        $stmt->bindParam(':disponible', $data['disponible'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Alterna el estado is_active entre 0 y 1 (activación/desactivación reversible).
     */
    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE tarifas SET disponible = NOT disponible WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM tarifas WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    
}
