<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Rating.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idTrip = $_POST["idTrip"] ?? null;

    if (!empty($idTrip) && is_numeric($idTrip)) {
        $ratingManager = new Rating();
        echo $response->responseSuccessValidation($ratingManager->getReviewsByTrip($idTrip));
    } else {
        echo $response->responseErrorMessage("ID de viaje no válido para obtener reseñas.");
    }
} else {
    echo $response->responseError();
}
?>