<?php

function sendResponse(
    int $httpCode,
    string $status,
    string $message,
    $data = null
): void {

    http_response_code($httpCode);

    header('Content-Type: application/json; charset=utf-8');

    $payload = [
        'status'  => $status,
        'message' => $message,
        'data'    => $data
    ];

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}