<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Rating.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idTrip = $data["idTrip"] ?? null;
    
    if ($idTrip) {
        $ratingManager = new Rating();
        echo $response->responseSuccessValidation($ratingManager->getAverageRating($idTrip));
    } else {
        echo $response->responseErrorMessage("ID de viaje requerido en el JSON (idTrip).");
    }
} else {
    echo $response->responseError();
}
?>