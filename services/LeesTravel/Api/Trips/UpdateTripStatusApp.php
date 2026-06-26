<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Trips.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $inputData = json_decode(file_get_contents("php://input"), true);

    $idTrip = $inputData["idTrip"] ?? null;
    $status = $inputData["status"] ?? null;

    if (!empty($idTrip) && is_numeric($idTrip) && !empty($status)) {
        
        $validStatuses = ['Programado', 'En Curso', 'Finalizado', 'Cancelado'];
        
        if (in_array($status, $validStatuses)) {
            $tripsManager = new Trips();
            echo json_encode($tripsManager->updateTripStatusApp((int)$idTrip, $status));
        } else {
            echo $response->responseErrorMessage("El estado enviado no es válido.");
        }

    } else {
        echo $response->responseErrorMessage("Faltan parámetros obligatorios en el JSON (idTrip o status).");
    }
} else {
    echo $response->responseError();
}
?>