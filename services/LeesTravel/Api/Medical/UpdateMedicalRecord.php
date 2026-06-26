<?php
require_once(__DIR__ . '/../Config/Cors.php'); 
require_once(__DIR__ . '/../../Core/MedicalRecord.php');
require_once(__DIR__ . '/../../Response/Response.php');

$response = new Response();
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idF       = $data["idFile"] ?? null;
    $blood     = $data["bloodType"] ?? $data["Blood_Type"] ?? null; 
    $allergies = $data["allergies"] ?? $data["Allergies"] ?? "ninguna";  
    $chronic   = $data["chronicDiseases"] ?? $data["Chronic_Diseases"] ?? "ninguna";
    $med       = $data["currentMedication"] ?? $data["Current_Medication"] ?? "ninguna";
    $obs       = $data["observations"] ?? $data["Observations"] ?? "";

    if (!empty($idF) && is_numeric($idF)) {
        try {
            $medicalManager = new MedicalRecord();
            $be = new MedicalRecordBE(
                $idF,       
                null,   
                $blood,    
                $allergies,
                $chronic, 
                $med, 
                $obs       
            );
            
            echo $response->responseSuccessValidation($medicalManager->updateRecord($be));

        } catch (Exception $e) {
            echo $response->responseErrorMessage("Error al actualizar: " . $e->getMessage());
        }
    } else {
        echo $response->responseErrorMessage("Se requiere un ID de ficha médica válido (idFile).");
    }
} else {
    echo $response->responseError();
}
?>