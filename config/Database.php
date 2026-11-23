<?php
/**
 * config/Database.php
 * Conexión MySQL mediante PDO, configurable por entorno.
 */

class Database
{
    private static ?PDO $connection = null;

    private static function isLocalEnvironment(): bool
    {
        $environment = strtolower((string)(getenv('MERCEDES_ENV') ?: ''));
        $serverAddress = (string)($_SERVER['SERVER_ADDR'] ?? '');
        $serverName = strtolower((string)($_SERVER['SERVER_NAME'] ?? ''));
        $documentRoot = strtolower(str_replace('\\', '/', (string)($_SERVER['DOCUMENT_ROOT'] ?? '')));
        $scriptFilename = strtolower(str_replace('\\', '/', (string)($_SERVER['SCRIPT_FILENAME'] ?? '')));
        $isWamp = PHP_OS_FAMILY === 'Windows'
            && $serverName === 'localhost'
            && str_ends_with($documentRoot, '/wamp64/www')
            && str_starts_with($scriptFilename, $documentRoot . '/');

        return PHP_SAPI === 'cli'
            || $environment === 'local'
            || $isWamp
            || (PHP_OS_FAMILY === 'Windows' && in_array($serverAddress, ['127.0.0.1', '::1'], true));
    }

    /**
     * Usa variables de entorno cuando existen y, en su defecto, configuración
     * privada del servidor o valores locales seguros para el entorno de desarrollo.
     */
    private static function getConfig(): array
    {
        $localConfigPath = __DIR__ . '/local.php';
        $localConfig = is_file($localConfigPath) ? require $localConfigPath : [];
        if (!is_array($localConfig)) {
            throw new RuntimeException('La configuración local de la base de datos debe devolver un arreglo.');
        }

        $isLocalHost = self::isLocalEnvironment();

        $config = [
            'host' => $isLocalHost ? '127.0.0.1' : '',
            'dbname' => $isLocalHost ? 'mercedes' : '',
            'user' => $isLocalHost ? 'root' : '',
            'pass' => '',
            'charset' => 'utf8mb4',
            'app_url' => $isLocalHost ? 'http://localhost' : '',
            'ip_hmac_key' => '',
        ];

        $environmentNames = [
            'host' => 'MERCEDES_DB_HOST',
            'dbname' => 'MERCEDES_DB_NAME',
            'user' => 'MERCEDES_DB_USER',
            'pass' => 'MERCEDES_DB_PASSWORD',
            'charset' => 'MERCEDES_DB_CHARSET',
            'app_url' => 'MERCEDES_APP_URL',
            'ip_hmac_key' => 'MERCEDES_IP_HMAC_KEY',
        ];

        foreach ($environmentNames as $key => $environmentName) {
            $environmentValue = getenv($environmentName);
            if ($environmentValue !== false) {
                $config[$key] = $environmentValue;
            } elseif (array_key_exists($key, $localConfig)) {
                $config[$key] = $localConfig[$key];
            }
        }

        foreach (['host', 'dbname', 'user'] as $requiredKey) {
            if (!is_string($config[$requiredKey]) || $config[$requiredKey] === '') {
                throw new RuntimeException('Configura host, nombre de base de datos y usuario en config/local.php o en el entorno.');
            }
        }

        foreach (['host', 'dbname', 'user', 'pass', 'charset', 'app_url', 'ip_hmac_key'] as $key) {
            if (!is_string($config[$key])) {
                throw new RuntimeException('Los valores de configuración deben ser cadenas de texto.');
            }
        }

        return $config;
    }

    /**
     * Devuelve una conexión PDO ya apuntando a la base de datos del proyecto.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $config = self::getConfig();
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=' . $config['charset'];
        try {
            self::$connection = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (PDOException $e) {
            error_log('[Mercedes DB] ' . $e->getMessage());
            throw new RuntimeException('No se pudo conectar con la base de datos. Verifica la configuración de este entorno.', 0, $e);
        }

        return self::$connection;
    }

    /**
     * Devuelve una conexión al servidor MySQL sin seleccionar una base.
     * Se conserva para herramientas de instalación ejecutadas manualmente.
     */
    public static function getServerConnection(): PDO
    {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $config = self::getConfig();
        $dsn = 'mysql:host=' . $config['host'] . ';charset=' . $config['charset'];
        return new PDO($dsn, $config['user'], $config['pass'], $options);
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
        return self::getConfig()['dbname'];
    }

    public static function getIpHmacKey(): string
    {
        $key = self::getConfig()['ip_hmac_key'];
        if (!preg_match('/\A[a-f0-9]{64}\z/D', $key)) {
            throw new RuntimeException(
                'La clave HMAC de IP no está configurada correctamente.'
            );
        }

        return $key;
    }

    public static function getAppUrl(): string
    {
        $config = self::getConfig();
        $appUrl = rtrim($config['app_url'], '/');
        $parts = parse_url($appUrl);
        $isLocalUrl = is_array($parts)
            && in_array(strtolower($parts['host'] ?? ''), ['localhost', '127.0.0.1', '::1'], true);
        $isLocalEnvironment = self::isLocalEnvironment();

        if (!is_array($parts)
            || !isset($parts['scheme'], $parts['host'])
            || !in_array($parts['scheme'], ['http', 'https'], true)
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])
            || (isset($parts['path']) && !in_array($parts['path'], ['', '/'], true))
            || ($parts['scheme'] === 'http' && (!$isLocalUrl || !$isLocalEnvironment))
            || (!$isLocalEnvironment && ($isLocalUrl || $parts['scheme'] !== 'https'))) {
            throw new RuntimeException('Configura una URL HTTPS válida en MERCEDES_APP_URL o config/local.php.');
        }

        return $appUrl;
    }
    
}
