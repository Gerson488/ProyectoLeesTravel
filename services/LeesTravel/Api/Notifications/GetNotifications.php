<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Notification.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idU = $_POST["idUser"] ?? $data["idUser"] ?? null;
    
    if ($idU && is_numeric($idU)) {
        $notifManager = new Notification();
        echo $response->responseSuccessValidation($notifManager->getNotificationsByUser($idU));
    } else {
        echo $response->responseErrorMessage("ID de usuario no válido o no proporcionado.");
    }
} else {
    echo $response->responseError();
}
?>