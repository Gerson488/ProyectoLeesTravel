<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/History.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idP = $data["idPassenger"] ?? null;
    
    if ($idP) {
        $historyManager = new History();
        echo json_encode($historyManager->getHistoryByPassenger((int)$idP));
    } else { 
        echo json_encode($response->responseErrorMessage("ID de pasajero requerido.")); 
    }
} else { 
    echo json_encode($response->responseError()); 
}
?>