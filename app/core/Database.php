<?php
class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $lastException = null;
<<<<<<< HEAD
        foreach (['b14_42588174_SPORTMANAGER', 'sport_manager'] as $database) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=localsql300.byethost14.com;dbname={$database};charset=utf8mb4",
                    'b14_42588174',
                    'Soy1crack123',
=======
<<<<<<< HEAD
        foreach (['b14_42588174_SPORTMANAGER', 'b14_42588174_SPORTMANAGER'] as $database) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=sql300.byethost14.com;dbname={$database};charset=utf8mb4",
                    'b14_42588174',
                    'Soy1crack123',
=======
        foreach (['sportmanager', 'sport_manager'] as $database) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=localhost;dbname={$database};charset=utf8mb4",
                    'root',
                    '',
>>>>>>> 4d7093d966a860ecc3ca8870582adf6f7b82deac
>>>>>>> 430b67eaf5868b6d60404776773cbcc2505b3910
                    $options
                );
                return self::$pdo;
            } catch (PDOException $e) {
                $lastException = $e;
            }
        }

        throw $lastException ?? new PDOException('No se pudo conectar a la base de datos.');
    }
}
