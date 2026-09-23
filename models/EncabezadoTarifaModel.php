<?php
/**
 * models/EncabezadoTarifaModel.php
 * Capa de acceso a datos para encabezados de tarifas.
 */
require_once __DIR__ . '/../config/Database.php';

class EncabezadoTarifaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT * FROM encabezado_tarifa ORDER BY id_encabezado_tarifa DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id)
    {
        $sql = "SELECT * FROM encabezado_tarifa WHERE id_encabezado_tarifa = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO encabezado_tarifa (titulo, is_active) VALUES (:titulo, :is_active)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'titulo' => $data['titulo'],
            'is_active' => $data['is_active']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE encabezado_tarifa SET titulo = :titulo, is_active = :is_active WHERE id_encabezado_tarifa = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'titulo' => $data['titulo'],
            'is_active' => $data['is_active'],
            'id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM encabezado_tarifa WHERE id_encabezado_tarifa = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Activa un encabezado y desactiva todos los demás.
     * Si ya estaba activo, lo desactiva (quedando ninguno activo).
     */
    public function setActiveEncabezado(int $id): bool
    {
        $encabezado = $this->findById($id);
        if (!$encabezado) {
            return false;
        }

        if ((int)$encabezado['is_active'] === 1) {
            // Si ya estaba activo → desactivar
            $sql = "UPDATE encabezado_tarifa SET is_active = 0 WHERE id_encabezado_tarifa = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } else {
            // Desactivar todos
            //$this->db->exec("UPDATE encabezado_tarifa SET is_active = 0");

            // Activar solo el seleccionado
            $sql = "UPDATE encabezado_tarifa SET is_active = 1 WHERE id_encabezado_tarifa = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        }
    }
}
