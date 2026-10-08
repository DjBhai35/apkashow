<?php
/**
 * ApkaShow - Database Connection & Helper Wrapper
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
        // In production, log error and do not expose passwords/system path details
        $isLocal = (strpos(BASE_URL, 'localhost') !== false || strpos(BASE_URL, '127.0.0.1') !== false);
        $errorMessage = $isLocal ? $e->getMessage() : "Could not connect to database server. Please verify credentials in includes/config.php.";
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>ApkaShow - Database Setup Required</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
            <style>
                body { background-color: #07090e; color: #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; align-items: center; min-height: 100vh; }
                .setup-card { background: #0e121b; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8); }
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
                                    <small class="text-muted">ApkaShow Premium Cinematic Platform (apkashow.com)</small>
                                </div>
                            </div>
                            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger mb-4">
                                <strong>Status:</strong> <?php echo htmlspecialchars($errorMessage); ?>
                            </div>
                            <h5 class="text-white fw-semibold mb-3">Quick 3-Step Setup Instructions:</h5>
                            <ol class="text-secondary ps-3 mb-4">
                                <li class="mb-2"><strong>Create Database:</strong> In cPanel, phpMyAdmin or MySQL console, create a database named <code><?php echo htmlspecialchars(DB_NAME); ?></code>.</li>
                                <li class="mb-2"><strong>Import SQL:</strong> Import <code>database/schema.sql</code> into your database.</li>
                                <li class="mb-2"><strong>Update Config:</strong> Update your database user and password in <code>includes/config.php</code>.</li>
                            </ol>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="" class="btn btn-danger px-4"><i class="bi bi-arrow-clockwise me-2"></i>Retry Connection</a>
                                <a href="install.php" class="btn btn-outline-light px-4"><i class="bi bi-magic me-2"></i>Setup Wizard</a>
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
