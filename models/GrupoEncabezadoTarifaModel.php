<?php
/**
 * models/GrupoEncabezadoTarifaModel.php
 * Manejo de la relación N:M entre grupos y encabezados de tarifas.
 */
require_once __DIR__ . '/../config/Database.php';

class GrupoEncabezadoTarifaModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Devuelve todos los id_encabezado_tarifa asociados a un grupo.
     */
    public function getEncabezadosByGrupo(int $id_grupo): array {
        $sql = "SELECT id_encabezado_tarifa 
                FROM grupo_encabezado_tarifa 
                WHERE id_grupo = :id_grupo";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_grupo', $id_grupo, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Asigna un encabezado de tarifa a un grupo.
     */
    public function assignEncabezadoToGrupo(int $id_grupo, int $id_encabezado_tarifa): bool {
        $sql = "INSERT INTO grupo_encabezado_tarifa (id_grupo, id_encabezado_tarifa) 
                VALUES (:id_grupo, :id_encabezado_tarifa)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_grupo' => $id_grupo,
            ':id_encabezado_tarifa' => $id_encabezado_tarifa
        ]);
    }

    /**
     * Elimina la relación entre un grupo y un encabezado de tarifa.
     */
    public function removeEncabezadoFromGrupo(int $id_grupo, int $id_encabezado_tarifa): bool {
        $sql = "DELETE FROM grupo_encabezado_tarifa 
                WHERE id_grupo = :id_grupo AND id_encabezado_tarifa = :id_encabezado_tarifa";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_grupo' => $id_grupo,
            ':id_encabezado_tarifa' => $id_encabezado_tarifa
        ]);
    }

    /**
     * Sincroniza los encabezados de tarifa de un grupo:
     * - Elimina los que no estén en la lista nueva.
     * - Inserta los que falten.
     */
    public function syncEncabezados(int $id_grupo, array $encabezadosSeleccionados): void {
        $this->db->beginTransaction();

        // Eliminar relaciones existentes
        $sqlDelete = "DELETE FROM grupo_encabezado_tarifa WHERE id_grupo = :id_grupo";
        $stmtDelete = $this->db->prepare($sqlDelete);
        $stmtDelete->execute([':id_grupo' => $id_grupo]);

        // Insertar nuevas relaciones
        $sqlInsert = "INSERT INTO grupo_encabezado_tarifa (id_grupo, id_encabezado_tarifa) 
                      VALUES (:id_grupo, :id_encabezado_tarifa)";
        $stmtInsert = $this->db->prepare($sqlInsert);

        foreach ($encabezadosSeleccionados as $id_encabezado_tarifa) {
            $stmtInsert->execute([
                ':id_grupo' => $id_grupo,
                ':id_encabezado_tarifa' => (int)$id_encabezado_tarifa
            ]);
        }

        $this->db->commit();
    }
}
