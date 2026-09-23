<?php
/**
 * models/TurnoModel.php
 * Capa de acceso a datos para la entidad turnos.
 */
require_once __DIR__ . '/../config/Database.php';

class TurnoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM turnos ORDER BY hora_inicio ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM turnos WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Devuelve todos los turnos asociados a un grupo.
     */
    public function getByGrupo(int $id_grupo): array
    {
        $sql = "SELECT * FROM turnos WHERE id_grupo = :id_grupo ORDER BY hora_inicio ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_grupo', $id_grupo, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): bool
    {
        $sql = "INSERT INTO turnos (turno, hora_inicio, hora_fin, url_icono, id_grupo) 
                VALUES (:turno, :hora_inicio, :hora_fin, :url_icono, :id_grupo)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':turno' => $data['turno'],
            ':hora_inicio' => $data['hora_inicio'],
            ':hora_fin' => $data['hora_fin'],
            ':url_icono' => $data['url_icono'],
            ':id_grupo' => $data['id_grupo']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE turnos 
                SET turno = :turno, hora_inicio = :hora_inicio, hora_fin = :hora_fin, 
                    url_icono = :url_icono, id_grupo = :id_grupo
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':turno' => $data['turno'],
            ':hora_inicio' => $data['hora_inicio'],
            ':hora_fin' => $data['hora_fin'],
            ':url_icono' => $data['url_icono'],
            ':id_grupo' => $data['id_grupo'],
            ':id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM turnos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}