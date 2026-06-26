<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Rating.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idR     = $data["idReview"] ?? null;
    $idU     = $data["idUser"] ?? null;
    $rating  = $data["rating"] ?? null;
    $comment = $data["comment"] ?? "";

    if ($idR && $idU && $rating) {
        $ratingManager = new Rating();
        $be = new ReviewBE($idR, null, $idU, $rating, $comment);
        echo $response->responseSuccessValidation($ratingManager->updateReview($be));
    } else {
        echo $response->responseErrorMessage("Datos insuficientes en el JSON para actualizar.");
    }
} else {
    echo $response->responseError();
}
?>