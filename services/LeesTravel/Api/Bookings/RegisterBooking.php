<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Booking.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $passengers = $data["passengers"] ?? [];
    $status = $data["bookingStatus"] ?? 'Confirmada';
    $titular = !empty($passengers) ? $passengers[0] : null;
    $userDni = $titular["dni"] ?? $titular["User_Dni"] ?? null;

    if (!empty($userDni)) {
        $bookingManager = new Booking();
        $idUser = $bookingManager->getIdByUserDni($userDni);

        if ($idUser) {
            $be = new BookingBE(null, $idUser, null, $status);
            $idNewBooking = $bookingManager->createBooking($be);

            if ($idNewBooking) {
                $errors = 0;
                foreach ($passengers as $p) {
                    $idTraveler = $p['idTraveler'] ?? $p['Id_Traveler'] ?? null;
                    
                    if (!empty($idTraveler)) {
                        $res = $bookingManager->registerPassengerInBooking($idTraveler, $idNewBooking);
                        if (!$res) {
                            $errors++;
                        }
                    }
                }
                if ($errors === 0) {
                    echo json_encode([
                        "status" => 200,
                        "message" => "Reserva Grupal #$idNewBooking creada con " . count($passengers) . " pasajeros exitosamente."
                    ]);
                } else {
                    echo json_encode([
                        "status" => 200,
                        "message" => "Reserva creada, pero hubo inconvenientes al vincular $errors acompañantes."
                    ]);
                }
            } else {
                echo $response->responseErrorMessage("No se pudo generar el código de reserva.");
            }
        } else {
            echo $response->responseErrorMessage("El DNI '$userDni' del titular no tiene un usuario de App activo.");
        }
    } else {
        echo $response->responseErrorMessage("Se requiere al menos un pasajero titular para realizar la reserva.");
    }
} else { 
    echo $response->responseError(); 
}
?>