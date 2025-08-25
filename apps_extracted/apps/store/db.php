<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo;
    if ($pdo === null) {
        if (DB_HOST === false || DB_NAME === false) {
            throw new RuntimeException('Database not configured');
        }
        $user = DB_USER !== false ? DB_USER : '';
        $pass = DB_PASS !== false ? DB_PASS : '';
        $pdo = new PDO(DB_DSN, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

class DummyStatement
{
    public function fetch($mode = null)
    {
        return false;
    }
    public function fetchAll($mode = null)
    {
        return [];
    }
    public function fetchColumn($column = 0)
    {
        return false;
    }
}

/**
 * Helper to run a prepared statement.
 */
function db_query(string $sql, array $params = [])
{
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (Throwable $e) {
        error_log('DB query failed: ' . $e->getMessage());
        return new DummyStatement();
    }
}
