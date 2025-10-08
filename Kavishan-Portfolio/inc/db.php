<?php

declare(strict_types=1);

/**
 * Database connection helper.
 *
 * Update the DSN, username, and password placeholders below to match
 * your local MySQL credentials.
 */

const DB_HOST = '127.0.0.1';
const DB_NAME = 'kavishan_portfolio';
const DB_DSN = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
const DB_USER = 'root';
const DB_PASS = '';

/**
 * Returns a shared PDO instance.
 *
 * @return PDO
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $options);
    } catch (PDOException $exception) {
        if (strpos($exception->getMessage(), 'Unknown database') !== false) {
            initializeDatabase($options);
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $options);
        } else {
            throw $exception;
        }
    }

    return $pdo;
}

/**
 * Creates the application database if it does not exist and runs the seed script.
 *
 * @param array<int|string, mixed> $options
 *
 * @return void
 */
function initializeDatabase(array $options): void
{
    $sqlFile = __DIR__ . '/../database.sql';
    if (!is_readable($sqlFile)) {
        throw new RuntimeException('Database seed file missing at ' . $sqlFile);
    }

    $hostDsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
    $pdo = new PDO($hostDsn, DB_USER, DB_PASS, $options);
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new RuntimeException('Unable to read database seed file at ' . $sqlFile);
    }
    $pdo->exec($sql);
}

/**
 * Fetches all settings from the settings table and returns an associative array.
 *
 * @param PDO $pdo
 *
 * @return array<string, string>
 */
function getSettings(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');

    $settings = [];
    foreach ($stmt as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    return $settings;
}

/**
 * Returns all projects ordered for display.
 *
 * @param PDO $pdo
 *
 * @return array<int, array<string, mixed>>
 */
function getProjects(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM projects ORDER BY display_order ASC, category_name ASC');

    return $stmt->fetchAll();
}

/**
 * Creates or updates a single setting value.
 */
function setSetting(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO settings (setting_key, setting_value)
         VALUES (:key, :value)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([
        'key'   => $key,
        'value' => $value,
    ]);
}
