<?php

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (!self::$instance) {
            $host = 'localhost';
            $user = 'root';
            $pass = '';
            $databases = ['sportmanager'];

            $lastException = null;
            foreach ($databases as $db) {
                try {
                    self::$instance = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$instance->exec("SET NAMES utf8mb4");
                    break;
                } catch (PDOException $e) {
                    $lastException = $e;
                }
            }

            if (!self::$instance) {
                self::renderConnectionError();
                exit;
            }
        }

        return self::$instance;
    }

    private static function renderConnectionError(): void
    {
        http_response_code(503);
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error de conexión</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body {
                    min-height: 100vh;
                    background: linear-gradient(160deg, #eef4fb 0%, #f8fbff 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 24px;
                }
                .error-card {
                    max-width: 760px;
                    width: 100%;
                    border: 0;
                    border-left: 6px solid #dc3545;
                    border-radius: 14px;
                    box-shadow: 0 20px 40px rgba(23, 32, 42, 0.14);
                    background: #fff;
                }
                .error-code {
                    font-size: clamp(3.2rem, 10vw, 7rem);
                    line-height: 1;
                    font-weight: 800;
                    color: #1d3f5f;
                    margin-bottom: 0.5rem;
                }
            </style>
        </head>
        <body>
            <div class="card error-card">
                <div class="card-body p-4 p-md-5 text-center">
                    <p class="error-code">503</p>
                    <h1 class="h2 fw-bold mb-3">Fuera de juego</h1>
                    <p class="text-muted mb-4">
                        No pudimos conectar con la base de datos en este momento.
                        Intenta nuevamente en unos minutos.
                    </p>
                    <a href="../index.php" class="btn btn-primary px-4">Volver al inicio</a>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}

// Compatibilidad con el resto del proyecto.
$conexion = Database::getConnection();
