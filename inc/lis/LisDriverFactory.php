<?php
require_once __DIR__ . '/LisDriverInterface.php';
require_once __DIR__ . '/ClinOsDriver.php';

/**
 * Class LisDriverFactory
 * Factory for creating and returning active LIS driver instance based on database config.
 */
class LisDriverFactory {

    private static $cachedConfig = null;

    /**
     * Get global LIS config from DB
     */
    public static function getConfig(PDO $db, bool $forceRefresh = false): ?array {
        if (self::$cachedConfig !== null && !$forceRefresh) {
            return self::$cachedConfig;
        }

        $stmt = $db->query("SELECT * FROM lis_config WHERE id = 1 LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            self::$cachedConfig = $row;
            return $row;
        }

        return null;
    }

    /**
     * Check if LIS integration is enabled globally
     */
    public static function isLisEnabled(PDO $db): bool {
        $config = self::getConfig($db);
        return ($config && !empty($config['is_enabled']) && $config['is_enabled'] == 1);
    }

    /**
     * Get active LIS driver instance
     */
    public static function getDriver(PDO $db): LisDriverInterface {
        $config = self::getConfig($db);

        if (!$config) {
            throw new Exception("LIS Configuration not found in database.");
        }

        $driverName = strtolower(trim($config['provider_driver'] ?? 'clinos'));

        switch ($driverName) {
            case 'clinos':
                return new ClinOsDriver(
                    $config['base_url'] ?? '',
                    $config['emr_key'] ?? '',
                    $config['catalogue_key'] ?? ''
                );

            default:
                throw new Exception("Unsupported LIS Driver: {$driverName}");
        }
    }
}
