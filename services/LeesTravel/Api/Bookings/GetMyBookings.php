<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Booking.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $idUser = $data["idUser"] ?? null;

    $bookingManager = new Booking();
    if (!empty($idUser) && is_numeric($idUser)) {
        echo $response->responseSuccessValidation($bookingManager->getBookingsByUser($idUser));
    } else {
        echo $response->responseSuccessValidation($bookingManager->getAllBookings());
    }

} else { 
    echo $response->responseError(); 
}
?>