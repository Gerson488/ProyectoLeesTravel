<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Core/Trips.php');
require_once(__DIR__ . '/../../Core/Upload.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    $idTrip = $data["idTrip"] ?? null;
    $confirmForce = $data["confirmForce"] ?? false;

    if ($idTrip && is_numeric($idTrip)) {

        $tripsManager = new Trips();

        $setting = new Setting();
        $db = $setting->getConnection();

        if (!$confirmForce) {

            $sqlItinerary = "SELECT COUNT(*) AS total
                             FROM daily_itinerary
                             WHERE Id_Trip = :id";

            $stmtItinerary = $db->prepare($sqlItinerary);
            $stmtItinerary->execute([
                ':id' => $idTrip
            ]);

            $countItinerary = $stmtItinerary
                ->fetch(PDO::FETCH_ASSOC)['total'];

            $sqlPromo = "SELECT COUNT(*) AS total
                         FROM marketing_promos
                         WHERE Id_Trip = :id";

            $stmtPromo = $db->prepare($sqlPromo);
            $stmtPromo->execute([
                ':id' => $idTrip
            ]);

            $countPromo = $stmtPromo
                ->fetch(PDO::FETCH_ASSOC)['total'];

            if ($countItinerary > 0 || $countPromo > 0) {

                $message = "Este viaje tiene asociado: ";

                $message .= ($countItinerary > 0)
                    ? "$countItinerary días de itinerario "
                    : "";

                $message .= ($countItinerary > 0 && $countPromo > 0)
                    ? "y "
                    : "";

                $message .= ($countPromo > 0)
                    ? "$countPromo promoción/es. "
                    : "";

                $message .= "¿Estás seguro de que deseas eliminarlo? Se borrarán todos los datos vinculados.";

                echo json_encode([
                    "status" => 202,
                    "message" => $message,
                    "data" => [
                        "requireConfirmation" => true
                    ]
                ]);

                exit;
            }
        }
        $currentTrip = $tripsManager->getTripById($idTrip);
        $imagePath = $currentTrip['data']['Trip_Photo'] ?? null;
        $upload = new Upload();
        $upload->deleteImage($imagePath);

        $db->prepare(
            "DELETE FROM daily_itinerary
             WHERE Id_Trip = :id"
        )->execute([
            ':id' => $idTrip
        ]);

        $db->prepare(
            "DELETE FROM marketing_promos
             WHERE Id_Trip = :id"
        )->execute([
            ':id' => $idTrip
        ]);

        echo $response->responseSuccessValidation(
            $tripsManager->deleteTrip($idTrip)
        );

    } else {

        echo $response->responseError();
    }

} else {

    echo $response->responseError();
}
?>