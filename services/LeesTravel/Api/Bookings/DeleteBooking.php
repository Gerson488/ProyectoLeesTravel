<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Booking.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $idBooking = $data["idBooking"] ?? null;

    if (!empty($idBooking) && is_numeric($idBooking)) {
        $bookingManager = new Booking();
        echo $response->responseSuccessValidation($bookingManager->deleteBooking($idBooking));
    } else {
        echo $response->responseErrorMessage("ID de reserva no válido.");
    }
} else { echo $response->responseError(); }
?>