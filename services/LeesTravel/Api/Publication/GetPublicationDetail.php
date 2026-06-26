<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Publication.php'); 
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    $idP = $_GET["idPost"] ?? null;

    if ($idP) {
        $pubManager = new Publication();
        $result = $pubManager->getFullPublicationById($idP);
        echo json_encode($result);
    } else {
        echo $response->responseErrorMessage("idPost es requerido para ver el detalle.");
    }
} else {
    echo $response->responseError();
}
?>