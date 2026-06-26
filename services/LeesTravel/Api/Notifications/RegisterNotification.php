<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Notification.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idU   = $_POST["idUser"] ?? $data["idUser"] ?? null;
    $title = $_POST["title"] ?? $data["title"] ?? null;
    $msg   = $_POST["message"] ?? $data["message"] ?? null;
    
    if ($idU && $title && $msg) {
        $notifManager = new Notification();
        $be = new NotificationBE(null, $idU, $title, $msg);
        
        echo $response->responseSuccessValidation($notifManager->createNotification($be));
    } else {
        echo $response->responseErrorMessage("Datos incompletos para crear la notificación (idUser, title, message).");
    }
} else {
    echo $response->responseError();
}
?>