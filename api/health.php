<?php

require_once __DIR__ . '/../bootstrap.php';
/** @var PDO $pdo */

requireMethod('GET');

$dbInfo = $pdo->query("SELECT DATABASE() AS db_name, VERSION() AS db_version")->fetch();

sendResponse(200, 'success', 'API Sudut Biru POS berjalan normal.', [
    'app_env'     => $_ENV['APP_ENV'] ?? 'local',
    'database'    => $dbInfo['db_name'],
    'db_version'  => $dbInfo['db_version'],
    'server_time' => date('Y-m-d H:i:s'),
]);