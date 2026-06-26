<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/History.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idTrip = isset($data["idTrip"]) ? trim($data["idTrip"]) : null;
    
    if (!empty($idTrip)) {
        $historyManager = new History();
        $result = $historyManager->getHistoryByTrip((int)$idTrip);
        echo json_encode($result);
    } else { 
        echo json_encode($response->responseErrorMessage("ID de viaje requerido para el panel de control.")); 
    }
} else { 
    echo json_encode($response->responseError()); 
}
?>