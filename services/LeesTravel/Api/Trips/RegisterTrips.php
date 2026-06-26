<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Trips.php');
require_once(__DIR__ . '/../../Core/Upload.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_FILES['imageTrip']) && $_FILES['imageTrip']['error'] === UPLOAD_ERR_OK) {

        $uploader = new Upload();
        $uploadResult = $uploader->saveImage($_FILES['imageTrip'], 'TravelProfile');

        if ($uploadResult['status'] == 200) {

            $imageUrl = $uploadResult['data']['url'];

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

            $params = [
                $destination,
                $ship,
                $departure,
                $arrival,
                $start,
                $end,
                $price,
                $capacity
            ];

            if (validationParametros($params)) {

                if (
                    is_numeric($price) &&
                    is_numeric($capacity) &&
                    strlen(trim($destination)) >= 5
                ) {

                    if (validationDate($start) && validationDate($end)) {

                        if (strtotime($end) >= strtotime($start)) {

                            $tripsManager = new Trips();

                            $tripBE = new TripsBE(
                                null,
                                $destination,
                                $ship,
                                $cruiseLine,
                                $departure,
                                $arrival,
                                $imageUrl,
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
                                $tripsManager->createTrip($tripBE)
                            );

                        } else {
                            echo $response->responseErrorMessage(
                                "La fecha de fin no puede ser anterior a la de inicio."
                            );
                        }

                    } else {
                        echo $response->responseErrorMessage(
                            "Formato de fechas incorrecto (YYYY-MM-DD)."
                        );
                    }

                } else {
                    echo $response->responseErrorMessage(
                        "Datos inválidos (Destino muy corto o precio/capacidad no numéricos)."
                    );
                }

            } else {
                echo $response->responseErrorMessage(
                    "Faltan campos obligatorios para el crucero."
                );
            }

        } else {
            echo json_encode($uploadResult);
        }

    } else {
        echo $response->responseErrorMessage(
            "Se requiere la imagen del crucero para el registro."
        );
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