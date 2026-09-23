<?php
/**
 * models/SettingsModel.php
 * Acceso a la configuración global persistida en settings.
 */

require_once __DIR__ . '/../config/Database.php';

class SettingsModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT setting_value FROM settings WHERE setting_key = :setting_key LIMIT 1'
        );
        $stmt->execute(['setting_key' => $key]);
        $value = $stmt->fetchColumn();

        return $value === false ? $default : (string) $value;
    }

    public function getBoolean(string $key, bool $default = true): bool
    {
        $value = $this->get($key);

        return $value === null ? $default : in_array(strtolower($value), ['1', 'true', 'si', 'yes'], true);
    }

    public function set(string $key, string $value, string $description): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO settings (setting_key, setting_value, description)
             VALUES (:setting_key, :setting_value, :description)
             ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                description = VALUES(description)'
        );

        return $stmt->execute([
            'setting_key' => $key,
            'setting_value' => $value,
            'description' => $description,
        ]);
    }
}
