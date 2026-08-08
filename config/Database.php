<?php
/**
 * config/Database.php
 * Conexión robusta a MySQL mediante PDO.
 * Si la base de datos 'template_mvc' aún no existe, se mantiene abierta
 * la conexión al servidor (sin seleccionar BD) para permitir que
 * config/setup.php la cree de forma automatizada.
 */

class Database
{
    private static string $host = '127.0.0.1';
    private static string $dbName = 'mercedes';
    private static string $user = 'root';
    private static string $pass = '';
    private static string $charset = 'utf8mb4';

    private static ?PDO $connection = null;

    /**
     * Devuelve una conexión PDO ya apuntando a la base de datos del proyecto.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $dsn = 'mysql:host=' . self::$host . ';dbname=' . self::$dbName . ';charset=' . self::$charset;
            self::$connection = new PDO($dsn, self::$user, self::$pass, $options);
        } catch (PDOException $e) {
            // La base de datos aún no existe: nos conectamos al servidor sin dbname
            // para que el instalador (config/setup.php) pueda crearla.
            try {
                $dsnServer = 'mysql:host=' . self::$host . ';charset=' . self::$charset;
                self::$connection = new PDO($dsnServer, self::$user, self::$pass, $options);
            } catch (PDOException $e2) {
                die('Error crítico de conexión con el servidor MySQL: ' . htmlspecialchars($e2->getMessage()));
            }
        }

        return self::$connection;
    }

    /**
     * Fuerza una conexión "sin base de datos" al servidor MySQL.
     * Utilizada exclusivamente por el instalador para crear la BD desde cero.
     */
    public static function getServerConnection(): PDO
    {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $dsn = 'mysql:host=' . self::$host . ';charset=' . self::$charset;
        return new PDO($dsn, self::$user, self::$pass, $options);
    }

    /**
     * Resetea la conexión en caché (usado tras crear la BD por primera vez).
     */
    public static function resetConnection(): void
    {
        self::$connection = null;
    }

    public static function getDbName(): string
    {
        return self::$dbName;
    }
}
