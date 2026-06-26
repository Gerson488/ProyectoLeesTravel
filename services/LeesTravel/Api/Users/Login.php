<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Usuario.php');
require_once(__DIR__ . '/../../Response/Response.php');
require_once(__DIR__ . '/../../vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data["email"] ?? null;
    $password = $data["password"] ?? null;

    if ($email && $password) {

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $usuarioManager = new Usuario();
            $loginRes = $usuarioManager->getLogin($email, $password);

            if ($loginRes && isset($loginRes['status']) && $loginRes['status'] === 200) {

                $userData = $loginRes['data'];

                $token = generarJWT(
                    $userData['Id_User'],
                    $userData['Access_Role']
                );

                $loginRes['data']['token'] = $token;

                echo json_encode($loginRes);

            } else {
                echo json_encode($loginRes);
            }

        } else {
            echo $response->responseErrorMessage("Formato de correo inválido.");
        }

    } else {
        echo $response->responseErrorMessage("El correo y la contraseña son obligatorios.");
    }

} else {
    echo $response->responseError();
}

function generarJWT($idUser, $role) {

    $secret = $_ENV['JWT_SECRET'] ?? '';

    $header = json_encode([
        'typ' => 'JWT',
        'alg' => 'HS256'
    ]);

    $payload = json_encode([
        'iss' => 'leestravel_api',
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24),
        'idUser' => $idUser,
        'role' => $role
    ]);

    $base64UrlHeader = str_replace(
        ['+', '/', '='],
        ['-', '_', ''],
        base64_encode($header)
    );

    $base64UrlPayload = str_replace(
        ['+', '/', '='],
        ['-', '_', ''],
        base64_encode($payload)
    );

    $signature = hash_hmac(
        'sha256',
        $base64UrlHeader . "." . $base64UrlPayload,
        $secret,
        true
    );

    $base64UrlSignature = str_replace(
        ['+', '/', '='],
        ['-', '_', ''],
        base64_encode($signature)
    );

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}
?>