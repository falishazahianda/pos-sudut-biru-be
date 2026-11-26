<?php
function sendResponse(int $httpCode, string $status, string $message, $data = null): void
{
    http_response_code($httpCode);

    $payload = [
        'status'  => $status,
        'message' => $message,
    ];

    if ($data !== null) {
        $payload['data'] = $data;
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}