<?php
$host = '127.0.0.1';
$db = 'billing_book';
$user = 'postgres';
$pass = 'postgres';
$charset = 'utf8mb4';

$dsn = "pgsql:host=$host;port=5432;dbname=$db;";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query('SELECT * FROM invoice_scans ORDER BY id DESC LIMIT 5');
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']} | Status: {$row['status']} | Error: {$row['error_message']} | Created: {$row['created_at']}\n";
    }
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int) $e->getCode());
}
