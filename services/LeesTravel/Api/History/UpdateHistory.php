<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/History.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idH   = $data["idHistory"] ?? null;
    $idTr  = $data["idTrip"] ?? null;         
    $idP   = $data["idPassenger"] ?? null;    
    $desc  = $data["description"] ?? null;
    $date  = $data["eventDate"] ?? null;      
    $idG   = $data["idGuiaUser"] ?? null;
    if ($idH && $idTr && $idP && $desc && $idG) {
        $historyManager = new History();
        $be = new HistoryBE((int)$idH, (int)$idTr, (int)$idP, $desc, $date, (int)$idG);
        echo json_encode($historyManager->updateLog($be));
    } else { 
        echo json_encode($response->responseErrorMessage("Faltan datos obligatorios para actualizar (idHistory, idTrip, idPassenger, description, idGuiaUser).")); 
    }
} else { 
    echo json_encode($response->responseError()); 
}
?>