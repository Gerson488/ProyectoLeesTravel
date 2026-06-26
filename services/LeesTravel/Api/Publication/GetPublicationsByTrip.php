<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Publication.php'); 
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    $idTrip = $_GET["idTrip"] ?? null;
    $idUser = $_GET["idUser"] ?? null;

    if ($idTrip && $idUser) {
        $pubManager = new Publication();
        $result = $pubManager->getPublicationsByTripAndUser($idTrip, $idUser);
        
        echo json_encode($result);
    } else {
        echo $response->responseErrorMessage("Se requieren idTrip e idUser en la URL.");
    }
} else {
    echo $response->responseError();
}
?>