<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Booking.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $idBooking = $data["idBooking"] ?? null;
    if (!empty($idBooking)) {
        $bookingManager = new Booking();
        $resultado = $bookingManager->getPassengersByBooking($idBooking);
        echo json_encode($resultado);
    } else {
        echo json_encode($response->structMessageErrorCustom("El ID de la reserva es requerido para obtener sus integrantes."));
    }
} else { 
    echo json_encode($response->structMessageErrorService()); 
}
?>