<?php
/**
 * models/DetalleTarifaModel.php
 * Capa de acceso a datos para la entidad detalles_tarifas.
 */
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/EncabezadoTarifaModel.php';

class DetalleTarifaModel
{
    private PDO $db;
    private EncabezadoTarifaModel $encabezadoModel;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->encabezadoModel = new EncabezadoTarifaModel();
    }

    // Obtener todos los detalles de un encabezado
    public function getByEncabezado(int $id_encabezado): array
    {
        $stmt = $this->db->prepare("SELECT * FROM detalles_tarifas WHERE id_encabezado_tarifa = ? ORDER BY costo ASC");
        $stmt->execute([$id_encabezado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar un detalle por ID
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM detalles_tarifas WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // Crear un nuevo detalle
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO detalles_tarifas (id_encabezado_tarifa, desde, hasta, costo, disponible) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['id_encabezado_tarifa'],
            $data['desde'],
            $data['hasta'],
            $data['costo'],
            $data['disponible'] ?? 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Actualizar un detalle
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE detalles_tarifas 
            SET desde = ?, hasta = ?, costo = ?, disponible = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['desde'],
            $data['hasta'],
            $data['costo'],
            $data['disponible'],
            $id
        ]);
    }

    // Toggle habilitar/deshabilitar detalle (solo si el encabezado está activo)
    public function toggle(int $id): bool
    {
        $detalle = $this->findById($id);
        if (!$detalle) return false;

        $encabezado = $this->encabezadoModel->findById($detalle['id_encabezado_tarifa']);
        if (!$encabezado || !$encabezado['is_active']) {
            // No se puede habilitar/deshabilitar si el encabezado está inactivo
            return false;
        }

        $nuevoEstado = $detalle['disponible'] ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE detalles_tarifas SET disponible = ? WHERE id = ?");
        return $stmt->execute([$nuevoEstado, $id]);
    }

    // Eliminar un detalle
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM detalles_tarifas WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getActivosByEncabezadoActivo(): array {
        $sql = "SELECT dt.id, dt.costo
                FROM detalles_tarifas dt
                JOIN encabezado_tarifa et ON dt.id_encabezado_tarifa = et.id_encabezado_tarifa
                WHERE et.is_active = 1 AND dt.disponible = 1
                ORDER BY dt.costo ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByGrupo(int $grupoId): array {
        $sql = "SELECT dt.id, dt.costo
                FROM detalles_tarifas dt
                JOIN encabezado_tarifa et 
                ON dt.id_encabezado_tarifa = et.id_encabezado_tarifa
                JOIN grupo_encabezado_tarifa ge 
                ON et.id_encabezado_tarifa = ge.id_encabezado_tarifa
                WHERE ge.id_grupo = :grupoId
                AND et.is_active = 1
                ORDER BY dt.costo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['grupoId' => $grupoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}