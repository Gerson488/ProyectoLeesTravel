<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Config/Setting.php');
require_once(__DIR__ . '/../../Core/History.php');
require_once(__DIR__ . '/../../Response/Response.php');
$response = new Response();
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idTr  = $data["Id_Trip"] ?? null;
    $idP   = $data["Id_Passenger"] ?? null; 
    $desc  = $data["Event_Description"] ?? null;
    $idG   = $data["Id_Guia_User"] ?? null; 

    if ($idTr !== null && $idP !== null && $desc !== null && $idG !== null) {
        $historyManager = new History();
        $be = new HistoryBE(null, (int)$idTr, (int)$idP, $desc, null, (int)$idG);
        
        echo json_encode($historyManager->createLog($be));
    } else {
        echo json_encode($response->responseErrorMessage("Datos incompletos. Recibido: " . $json));
    }
} else {
    echo json_encode($response->responseError());
}
?>