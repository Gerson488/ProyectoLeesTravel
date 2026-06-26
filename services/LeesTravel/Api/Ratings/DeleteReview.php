<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Rating.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idR = $_POST["idReview"] ?? null;
    $idU = $_POST["idUser"] ?? null;

    if ($idR && $idU) {
        $ratingManager = new Rating();
        echo $response->responseSuccessValidation($ratingManager->deleteReview($idR, $idU));
    } else {
        echo $response->responseErrorMessage("ID de reseña y usuario requeridos.");
    }
} else {
    echo $response->responseError();
}
?>