<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/MedicalRecord.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idP     = $data["idPassenger"] ?? null;
    $blood   = $data["bloodType"] ?? $data["Blood_Type"] ?? null;
    $all     = $data["allergies"] ?? $data["Allergies"] ?? "ninguna";
    $chronic = $data["chronicDiseases"] ?? $data["Chronic_Diseases"] ?? "ninguna";
    $med     = $data["currentMedication"] ?? $data["Current_Medication"] ?? "ninguna";
    $obs     = $data["observations"] ?? $data["Observations"] ?? "";

    if (!empty($idP) && !empty($blood)) {
        try {
            $medicalManager = new MedicalRecord();
            $idTraveler = $medicalManager->getTravelerIdByPassenger($idP);

            if ($idTraveler) {
                $be = new MedicalRecordBE(null, $idTraveler, $blood, $all, $chronic, $med, $obs);
                echo $response->responseSuccessValidation($medicalManager->createRecord($be));
            } else {
                echo $response->responseErrorMessage("No se encontró un viajero asociado a este pasajero.");
            }
        } catch (Exception $e) {
            echo $response->responseErrorMessage("Error en el servidor: " . $e->getMessage());
        }
    } else {
        echo $response->responseErrorMessage("El ID del Pasajero y el Grupo Sanguíneo son obligatorios.");
    }
} else { 
    echo $response->responseError();
}
?>