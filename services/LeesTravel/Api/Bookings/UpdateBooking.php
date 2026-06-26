<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Booking.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $idBooking  = $data["idBooking"] ?? null;
    $status     = $data["bookingStatus"] ?? null;
    $passengers = $data["passengers"] ?? null; 

    if (!empty($idBooking) && !empty($status)) {
        $bookingManager = new Booking();
        echo $response->responseSuccessValidation($bookingManager->updateBookingStatus($idBooking, $status, $passengers));
    } else {
        echo $response->responseErrorMessage("Faltan datos (idBooking, bookingStatus).");
    }
} else { 
    echo $response->responseError(); 
}
?>