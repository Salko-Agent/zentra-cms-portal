<?php
// ============================================================
// Zentra – PDO Singleton
// ============================================================

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    if (!defined('DB_HOST')) {
        require_once dirname(__DIR__) . '/config.php';
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $pdo;
}
