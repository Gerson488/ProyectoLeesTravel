<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Publication.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idPost = $data["idPost"] ?? null;
    $status = $data["status"] ?? null; 
    if ($idPost && $status) {
        if ($status === 'Aprobado' || $status === 'Rechazado' || $status === 'Pendiente') {
            $isPublic = ($status === 'Aprobado') ? 1 : 0;
            $pubManager = new Publication();
            echo $response->responseSuccessValidation($pubManager->moderatePublication($idPost, $status, $isPublic));
            
        } else {
            echo $response->responseErrorMessage("Estado no válido. Debe ser 'Aprobado', 'Rechazado' o 'Pendiente'.");
        }
    } else {
        echo $response->responseErrorMessage("El ID de la publicación y el estado son requeridos.");
    }
} else {
    echo $response->responseError();
}
?>