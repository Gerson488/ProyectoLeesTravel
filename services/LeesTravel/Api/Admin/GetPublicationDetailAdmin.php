<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Publication.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idPost = $data["idPost"] ?? null;

    if ($idPost) {
        $pubManager = new Publication();
        echo $response->responseSuccessValidation($pubManager->getFullPublicationAdminById($idPost));
    } else {
        echo $response->responseErrorMessage("Se requiere el ID de la publicación.");
    }
} else {
    echo $response->responseError();
}
?>