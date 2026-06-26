<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Trips.php');
require_once(__DIR__ . '/../../Core/Upload.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $idTrip         = $_POST["idTrip"] ?? null;
    $destination    = $_POST["destinationName"] ?? null;
    $ship           = $_POST["shipName"] ?? null;
    $cruiseLine     = $_POST["cruiseLine"] ?? null;
    $departure      = $_POST["departurePort"] ?? null;
    $arrival        = $_POST["arrivalPort"] ?? null;
    $start          = $_POST["startDate"] ?? null;
    $end            = $_POST["endDate"] ?? null;
    $price          = $_POST["price"] ?? null;
    $status         = $_POST["status"] ?? 'Programado';
    $capacity       = $_POST["maxCapacity"] ?? null;
    $requiresVisa   = isset($_POST["requiresVisa"]) && ($_POST["requiresVisa"] === 'true' || $_POST["requiresVisa"] == 1) ? 1 : 0;
    $includesFlight = isset($_POST["includesFlight"]) && ($_POST["includesFlight"] === 'true' || $_POST["includesFlight"] == 1) ? 1 : 0;
    $durationNights = !empty($_POST["durationNights"]) ? (int)$_POST["durationNights"] : null;
    $description    = $_POST["description"] ?? null;

    if (!empty($idTrip) && is_numeric($idTrip)) {

        $params = [$destination, $ship, $departure, $arrival, $start, $end, $price, $capacity];

        if (validationParametros($params)) {

            if (is_numeric($price) && is_numeric($capacity) && strlen($destination) >= 5) {

                if (validationDate($start) && validationDate($end)) {

                    if (strtotime($end) >= strtotime($start)) {

                        $tripsManager = new Trips();
                        $currentTrip = $tripsManager->getTripById($idTrip);

                        if ($currentTrip['status'] != 200 || empty($currentTrip['data'])) {
                            echo $response->responseErrorMessage("El viaje no existe.");
                            exit;
                        }

                        $oldImagePath = $currentTrip['data']['Trip_Photo'];
                        $finalImagePath = $oldImagePath;

                        if (isset($_FILES['imageTrip']) && $_FILES['imageTrip']['error'] === UPLOAD_ERR_OK) {

                            $uploader = new Upload();
                            $uploadResult = $uploader->saveImage($_FILES['imageTrip'], 'TravelProfile');

                            if ($uploadResult['status'] == 200) {

                                $finalImagePath = $uploadResult['data']['url'];

                                if (
                                    $oldImagePath &&
                                    $oldImagePath !== 'default_trip.png' &&
                                    $oldImagePath !== 'Imag/Photos/TravelProfile/default.jpg'
                                ) {
                                    $fullPathToDelete = dirname(__DIR__, 2) . "/" . $oldImagePath;

                                    if (file_exists($fullPathToDelete)) {
                                        @unlink($fullPathToDelete);
                                    }
                                }

                            } else {
                                echo json_encode($uploadResult);
                                exit;
                            }
                        }

                        $tripBE = new TripsBE(
                            $idTrip,
                            $destination,
                            $ship,
                            $cruiseLine,
                            $departure,
                            $arrival,
                            $finalImagePath,
                            $start,
                            $end,
                            $price,
                            $status,
                            $capacity,
                            $requiresVisa,
                            $includesFlight,
                            $durationNights,
                            $description
                        );

                        echo $response->responseSuccessValidation(
                            $tripsManager->updateTrip($tripBE)
                        );

                    } else {
                        echo $response->responseErrorMessage("La fecha de fin no puede ser anterior a la de inicio.");
                    }

                } else {
                    echo $response->responseErrorMessage("Formato de fechas incorrecto (YYYY-MM-DD).");
                }

            } else {
                echo $response->responseErrorMessage("Datos inválidos (Destino muy corto o precio/capacidad no numéricos).");
            }

        } else {
            echo $response->responseErrorMessage("Faltan campos obligatorios para actualizar el crucero.");
        }

    } else {
        echo $response->responseErrorMessage("Se requiere un ID de viaje válido para actualizar.");
    }

} else {
    echo $response->responseError();
}

function validationDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function validationParametros($parametros) {
    foreach ($parametros as $param) {
        if (!isset($param) || trim((string)$param) === "") {
            return false;
        }
    }
    return true;
}
?>