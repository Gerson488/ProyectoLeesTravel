<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Rating.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idTrip  = $_POST["idTrip"] ?? $data["idTrip"] ?? null;
    $idUser  = $_POST["idUser"] ?? $data["idUser"] ?? null;
    $rating  = $_POST["rating"] ?? $data["rating"] ?? null;
    $comment = $_POST["comment"] ?? $data["comment"] ?? "";

    if ($idTrip && $idUser && isset($rating)) {
        if (is_numeric($rating) && $rating >= 1 && $rating <= 5) {
            $ratingManager = new Rating();
            $be = new ReviewBE(null, $idTrip, $idUser, (int)$rating, $comment);
            
            echo $response->responseSuccessValidation($ratingManager->createReview($be));
        } else {
            echo $response->responseErrorMessage("La calificación debe estar entre 1 y 5.");
        }
    } else {
        echo $response->responseErrorMessage("Datos incompletos.");
    }
} else {
    echo $response->responseError();
}
?>