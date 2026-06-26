<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/MedicalRecord.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idP = $data["idPassenger"] ?? null;

    if (!empty($idP) && is_numeric($idP)) {
        try {
            $medicalManager = new MedicalRecord();
            $resultado = $medicalManager->getRecordByPassenger($idP);
            
            echo $response->responseSuccessValidation($resultado);

        } catch (Exception $e) {
            echo $response->responseErrorMessage("Error interno: " . $e->getMessage());
        }
    } else {
        echo $response->responseErrorMessage("ID de pasajero requerido o inválido (idPassenger).");
    }
} else {
    echo $response->responseError();
}
?>