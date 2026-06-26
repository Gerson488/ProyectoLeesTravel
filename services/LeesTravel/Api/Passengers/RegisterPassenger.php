<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/Passenger.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $idBooking  = $data["idBooking"] ?? null;
    $idTraveler = $data["idTraveler"] ?? null;
    $idTrip     = $data["idTrip"] ?? null;
    $cabin      = $data["cabinNumber"] ?? null;
    $status     = $data["boardingStatus"] ?? 0;
    $special    = $data["specialAssistance"] ?? null;
    if (!empty($idTraveler) && !empty($idTrip)) {
        
        $pasajeroManager = new Pasajero();

        if (empty($idBooking) || $idBooking == 0) {
            try {
                $reflector = new ReflectionClass($pasajeroManager);
                $property = $reflector->getProperty('connection');
                $property->setAccessible(true);
                $db = $property->getValue($pasajeroManager);

                if ($db) {
                    $sqlCheck = "SELECT Id_Booking FROM bookings ORDER BY Id_Booking DESC LIMIT 1";
                    $stmtCheck = $db->prepare($sqlCheck);
                    $stmtCheck->execute();
                    $lastBookingId = $stmtCheck->fetchColumn();
                    $sqlNewBooking = "INSERT INTO bookings (Id_Booking) VALUES (NULL)";
                    $stmtBooking = $db->prepare($sqlNewBooking);
                    $stmtBooking->execute();
                    $idBooking = $db->lastInsertId();
                    if (!$idBooking) {
                        if ($lastBookingId) {
                            $idBooking = $lastBookingId;
                        } else {
                            $idBooking = 1; 
                        }
                    }
                }
            } catch (Exception $ex) {
                try {
                    $idBooking = $db->query("SELECT Id_Booking FROM bookings ORDER BY Id_Booking DESC LIMIT 1")->fetchColumn();
                } catch(Exception $e) {
                    $idBooking = 1;
                }
            }
        }
        $be = new PasajeroBE(null, $idBooking, $idTraveler, $idTrip, $cabin, $status, $special);
        echo $response->responseSuccessValidation($pasajeroManager->createPassenger($be));
    } else {
        echo $response->responseErrorMessage("Faltan datos obligatorios: Viajero (idTraveler) y Crucero (idTrip).");
    }
} else {
    echo $response->responseError();
}
?>