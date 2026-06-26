<?php
if (ob_get_level()) ob_end_clean();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}
if (file_exists(__DIR__ . '/../Config/Cors.php')) {
    require_once(__DIR__ . '/../Config/Cors.php');
}
$dni = isset($_GET['dni']) ? $_GET['dni'] : null;

if (!$dni || strlen($dni) !== 8 || !is_numeric($dni)) {
    echo json_encode(["success" => false, "message" => "DNI no válido (debe tener 8 números)"]);
    exit;
}
$token = "sk_14359.HchiyHkuFauDan9QKYJesD5vqhDT9m9J"; 
$url = "https://api.decolecta.com/v1/reniec/dni/" . $dni;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer $token",
    "Accept: application/json"
));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);
if ($curlError) {
    echo json_encode(["success" => false, "message" => "Error de red: " . $curlError]);
    exit;
}

$data = json_decode($response, true);
$persona = isset($data['data']) ? $data['data'] : $data;

if ($httpCode === 200 && isset($persona['nombres'])) {
    $nombres = $persona['nombres'] ?? '';
    $apePaterno = $persona['apellido_paterno'] ?? $persona['paterno'] ?? '';
    $apeMaterno = $persona['apellido_materno'] ?? $persona['materno'] ?? '';
    
    $fullName = trim("$nombres $apePaterno $apeMaterno");

    echo json_encode([
        "success" => true,
        "nombreCompleto" => $fullName,
        "dni" => $dni
    ], JSON_UNESCAPED_UNICODE);

} else {
    $msg = "No encontrado";
    if ($httpCode === 401) $msg = "Token inválido o expirado";
    if ($httpCode === 402) $msg = "Sin saldo en Decolecta";
    if (isset($data['message'])) $msg = $data['message'];

    echo json_encode([
        "success" => false, 
        "message" => $msg,
        "code" => $httpCode
    ]);
}