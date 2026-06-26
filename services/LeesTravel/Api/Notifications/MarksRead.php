<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Notification.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idN = $_POST["idNotification"] ?? $data["idNotification"] ?? null;
    
    if ($idN && is_numeric($idN)) {
        $notifManager = new Notification();
        echo $response->responseSuccessValidation($notifManager->markAsRead($idN));
    } else {
        echo $response->responseErrorMessage("ID de notificación requerido (idNotification).");
    }
} else {
    echo $response->responseError();
}
?>