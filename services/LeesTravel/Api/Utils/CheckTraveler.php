<?php
require_once(__DIR__ . '/../Config/Cors.php');
require_once(__DIR__ . '/../../Config/Setting.php');
require_once(__DIR__ . '/../../Response/Response.php'); 

$data = json_decode(file_get_contents("php://input"), true);
$dni = $data['docNumber'] ?? $data['dni'] ?? $_GET['dni'] ?? null;

if (!$dni) {
    echo json_encode([
        "status" => 400,
        "success" => false, 
        "message" => "Documento no proporcionado"
    ]);
    exit;
}

try {
    $setting = new Setting();
    $connection = $setting->getConnection();
    $sql = "SELECT Id_Traveler, First_Name, Last_Name 
            FROM travelers 
            WHERE Id_Card_Passport = ? 
            LIMIT 1";

    $stmt = $connection->prepare($sql);
    $stmt->execute([$dni]);
    $traveler = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($traveler) {
        echo json_encode([
            "status" => 200,
            "success" => true,
            "data" => [
                "Id_Traveler" => $traveler['Id_Traveler'],
                "First_Name" => $traveler['First_Name'],
                "Last_Name" => $traveler['Last_Name'],
                "Full_Name" => $traveler['First_Name'] . " " . $traveler['Last_Name']
            ],
            "nombreCompleto" => $traveler['First_Name'] . " " . $traveler['Last_Name'],
            "idTraveler" => $traveler['Id_Traveler']
        ]);
    } else {
        echo json_encode([
            "status" => 201,
            "success" => false,
            "message" => "Viajero no encontrado en la base de datos."
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => 500,
        "success" => false, 
        "message" => $e->getMessage()
    ]);
}
?>