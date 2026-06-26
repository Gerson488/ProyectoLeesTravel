<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/History.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idHistory = $data["idHistory"] ?? null;
    
    if ($idHistory) {
        $historyManager = new History();
        $result = $historyManager->deleteLog((int)$idHistory);
        echo json_encode($result);
    } else { 
        echo json_encode($response->responseErrorMessage("ID de historial requerido para eliminar el registro.")); 
    }
} else { 
    echo json_encode($response->responseError()); 
}
?>