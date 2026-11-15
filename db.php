<?php
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=classwall', 'postgres', 'moran123', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed.');
}