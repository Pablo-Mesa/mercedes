
<?php
/**
 * models/Dashboard.php
 * Capa de acceso a datos para la entidad grupos.
 */
require_once __DIR__ . '/../config/Database.php';

class DashboardModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getNombrePuntoDeControl(){        
        $sql = "SELECT titulo FROM punto_control LIMIT 1"; // Buena práctica añadir LIMIT 1
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

   
}