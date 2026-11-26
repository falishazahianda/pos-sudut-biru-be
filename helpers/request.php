<?php

function getRequestData(): array
{
    $raw  = file_get_contents('php://input');
    $json = json_decode($raw, true);

    if (is_array($json)) {
        return $json;
    }

    return $_POST;
}

function requireMethod(string|array $allowedMethods): void
{
    $methods = is_array($allowedMethods) ? $allowedMethods : [$allowedMethods];
    $methods = array_map('strtoupper', $methods);

    if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
        sendResponse(405, 'error', 'Method tidak diizinkan. Gunakan: ' . implode(', ', $methods) . '.');
    }
}