<?php
require_once(__DIR__ . '/../../vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

function verificarAutenticacion() {

    $headers = apache_request_headers();

    $authHeader =
        $headers['Authorization']
        ?? $headers['authorization']
        ?? $_SERVER['HTTP_AUTHORIZATION']
        ?? null;

    if (
        !$authHeader ||
        !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)
    ) {

        http_response_code(401);

        echo json_encode([
            "status" => 401,
            "message" => "Acceso denegado."
        ]);

        exit();
    }

    $token = $matches[1];

    $secret = $_ENV['JWT_SECRET'] ?? '';

    $tokenParts = explode('.', $token);

    if (count($tokenParts) !== 3) {

        http_response_code(401);

        echo json_encode([
            "status" => 401,
            "message" => "Token inválido."
        ]);

        exit();
    }

    $header = $tokenParts[0];
    $payload = $tokenParts[1];
    $signatureProvided = $tokenParts[2];

    $signature = hash_hmac(
        'sha256',
        $header . "." . $payload,
        $secret,
        true
    );

    $base64UrlSignature = str_replace(
        ['+', '/', '='],
        ['-', '_', ''],
        base64_encode($signature)
    );

    if ($base64UrlSignature !== $signatureProvided) {

        http_response_code(401);

        echo json_encode([
            "status" => 401,
            "message" => "Token inválido."
        ]);

        exit();
    }

    $payloadData = json_decode(
        base64_decode(
            str_replace(['-', '_'], ['+', '/'], $payload)
        ),
        true
    );

    if ($payloadData['exp'] < time()) {

        http_response_code(401);

        echo json_encode([
            "status" => 401,
            "message" => "Sesión expirada."
        ]);

        exit();
    }

    return $payloadData;
}
?>