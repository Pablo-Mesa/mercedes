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

    public function getAll()
    {
        $sql = "SELECT * FROM turnos ORDER BY hora_inicio ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM turnos WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($data)
    {
        $sql = "INSERT INTO turnos (turno, hora_inicio, hora_fin, url_icono) 
                VALUES (:turno, :hora_inicio, :hora_fin, :url_icono)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':turno', $data['turno']);
        $stmt->bindParam(':hora_inicio', $data['hora_inicio']);
        $stmt->bindParam(':hora_fin', $data['hora_fin']);
        $stmt->bindParam(':url_icono', $data['url_icono']);

        return $stmt->execute();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE turnos 
                SET turno = :turno, hora_inicio = :hora_inicio, hora_fin = :hora_fin, url_icono = :url_icono 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':turno', $data['turno']);
        $stmt->bindParam(':hora_inicio', $data['hora_inicio']);
        $stmt->bindParam(':hora_fin', $data['hora_fin']);
        $stmt->bindParam(':url_icono', $data['url_icono']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM turnos WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
