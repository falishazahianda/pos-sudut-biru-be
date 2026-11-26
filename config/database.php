<?php
// Muat .env secara mandiri jika file ini dipanggil sebelum bootstrap.php (Tahap 5)
if (!isset($_ENV['DB_HOST'])) {
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        }
    }
}

$host     = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port     = $_ENV['DB_PORT'] ?? '3306';
$dbname   = $_ENV['DB_NAME'] ?? 'sudut_biru_pos';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';
$charset  = 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lempar exception jika query error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil query otomatis array asosiatif bersih
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Gunakan native prepared statement (anti SQL Injection)
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    $isLocal = ($_ENV['APP_ENV'] ?? 'local') === 'local';
    $message = $isLocal
        ? 'Koneksi database gagal: ' . $e->getMessage()
        : 'Terjadi gangguan koneksi pada server database.';

    if (function_exists('sendResponse')) {
        sendResponse(500, 'error', $message);
    }

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'error',
        'message' => $message,
    ]);
    exit;
}

return $pdo;