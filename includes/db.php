<?php
/**
 * CinemaVault - Database Connection & Helper Wrapper
 * Utilizes PDO with prepared statements, error handling, and UTF-8 encoding.
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo = null;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Provide a graceful installation/connection guide if database is not yet created
            $this->handleConnectionError($e);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    private function handleConnectionError(PDOException $e) {
        $errorMessage = $e->getMessage();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>CinemaVault - Database Connection Required</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
            <style>
                body { background-color: #0b0d14; color: #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; align-items: center; min-height: 100vh; }
                .setup-card { background: #131722; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
                .code-box { background: #07090e; border: 1px solid rgba(220, 38, 38, 0.3); border-radius: 8px; color: #f87171; font-family: monospace; padding: 12px; }
            </style>
        </head>
        <body>
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-md-10">
                        <div class="setup-card p-4 p-md-5">
                            <div class="d-flex align-items-center mb-4 text-warning">
                                <i class="bi bi-database-fill-exclamation fs-1 me-3"></i>
                                <div>
                                    <h3 class="fw-bold mb-0 text-white">Database Setup Required</h3>
                                    <small class="text-muted">CinemaVault Premium Cinematic Platform</small>
                                </div>
                            </div>
                            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger mb-4">
                                <strong>Connection Error:</strong> <?php echo htmlspecialchars($errorMessage); ?>
                            </div>
                            <h5 class="text-white fw-semibold mb-3">Quick 2-Step Setup Instructions:</h5>
                            <ol class="text-secondary ps-3 mb-4">
                                <li class="mb-2"><strong>Create Database:</strong> Open phpMyAdmin, cPanel MySQL or your terminal and create a database named <code><?php echo htmlspecialchars(DB_NAME); ?></code>.</li>
                                <li class="mb-2"><strong>Import SQL:</strong> Import the file located at <code>database/schema.sql</code> into your database.</li>
                                <li class="mb-2"><strong>Verify Credentials:</strong> Ensure your MySQL user and password in <code>includes/config.php</code> match your server.</li>
                            </ol>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="" class="btn btn-primary px-4"><i class="bi bi-arrow-clockwise me-2"></i>Retry Connection</a>
                                <a href="install.php" class="btn btn-outline-light px-4"><i class="bi bi-magic me-2"></i>One-Click Installer</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}

/**
 * Global Database Helper Function
 * @return PDO
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
