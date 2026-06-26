<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/MedicalRecord.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idF = $data["idFile"] ?? null;

    if (!empty($idF) && is_numeric($idF)) {
        $medicalManager = new MedicalRecord();
        echo $response->responseSuccessValidation($medicalManager->deleteRecord($idF));
    } else {
        echo $response->responseErrorMessage("ID de ficha médica no válido o no proporcionado.");
    }
} else {
    echo $response->responseError();
}
?>