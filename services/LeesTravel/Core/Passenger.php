<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class PasajeroBE {
    public $idPassenger;
    public $idBooking;
    public $idTraveler;
    public $idTrip;
    public $cabinNumber;
    public $boardingStatus;
    public $specialAssistance;

    public function __construct($idP = null, $idB = null, $idT = null, $idTr = null, $cabin = null, $status = 0, $special = null) {
        $this->idPassenger = $idP;
        $this->idBooking = $idB;
        $this->idTraveler = $idT;
        $this->idTrip = $idTr;
        $this->cabinNumber = $cabin;
        $this->boardingStatus = $status;
        $this->specialAssistance = $special;
    }
}

class Pasajero {
    private $connection;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }

    public function createPassenger(PasajeroBE $p) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "INSERT INTO passenger (Id_Booking, Id_Traveler, Id_Trip, Cabin_Number, Boarding_Status, Special_Assistance) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                $p->idBooking, 
                $p->idTraveler, 
                $p->idTrip, 
                $p->cabinNumber, 
                $p->boardingStatus, 
                $p->specialAssistance
            ]);
            return $response->responseMessageArray($success, "Pasajero asignado al viaje con éxito", ["id" => $this->connection->lastInsertId()]);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function updatePassenger(PasajeroBE $p) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "UPDATE passenger SET Id_Booking = ?, Id_Traveler = ?, Id_Trip = ?, Cabin_Number = ?, Special_Assistance = ? WHERE Id_Passenger = ?";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                $p->idBooking, 
                $p->idTraveler, 
                $p->idTrip, 
                $p->cabinNumber, 
                $p->specialAssistance, 
                $p->idPassenger
            ]);
            return $response->responseMessageArray($success, "Información del pasajero actualizada", null);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function updateBoardingStatusApp($idPassenger, $newStatus) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        
        $validStatuses = ['Por Abordar', 'Abordado', 'No Se Presentó'];
        
        if (!in_array($newStatus, $validStatuses)) {
            return $response->responseErrorMessage("Estado de abordaje no válido.");
        }

        try {
            $sqlCheck = "SELECT p.Id_Trip, t.Status FROM passenger p 
                        INNER JOIN trips t ON p.Id_Trip = t.Id_Trip 
                        WHERE p.Id_Passenger = ?";
            $stmtCheck = $this->connection->prepare($sqlCheck);
            $stmtCheck->execute([$idPassenger]);
            $tripInfo = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$tripInfo) {
                return $response->responseMessageArray(false, "Pasajero o viaje no encontrado.", null);
            }

            if ($newStatus === 'Abordado' && $tripInfo['Status'] !== 'En Curso') {
                return $response->responseMessageArray(false, "El embarque está bloqueado porque el viaje no ha sido iniciado formalmente.", null);
            }

            $sqlUpdate = "UPDATE passenger SET Boarding_Status = ? WHERE Id_Passenger = ?";
            $stmtUpdate = $this->connection->prepare($sqlUpdate);
            $success = $stmtUpdate->execute([$newStatus, $idPassenger]);

            return $response->responseMessageArray($success, "Estado actualizado a: $newStatus", null);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function deletePassenger($idPassenger) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "DELETE FROM passenger WHERE Id_Passenger = ?";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([$idPassenger]);
            return $response->responseMessageArray($success, "Pasajero removido del manifiesto", null);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getPassengersByTrip($idTrip) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT 
                        p.*, 
                        t.First_Name, t.Last_Name, t.Id_Card_Passport, t.Nationality,
                        TIMESTAMPDIFF(YEAR, t.Birth_Date, CURDATE()) AS Age,
                        m.Chronic_Diseases, m.Allergies,
                        u.Access_Role
                    FROM passenger p
                    INNER JOIN travelers t ON p.Id_Traveler = t.Id_Traveler
                    LEFT JOIN medical_records m ON t.Id_Traveler = m.Id_Traveler
                    LEFT JOIN system_users u ON p.Id_Traveler = u.Id_Traveler
                    WHERE p.Id_Trip = :id"; 
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $idTrip);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $response->responseMessageArray(true, "Manifiesto obtenido", $data);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getTravelerById($idTraveler) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        
        try {
            $sql = "SELECT * FROM travelers WHERE Id_Traveler = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $idTraveler, PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return $response->responseMessageArray(true, "Viajero encontrado por ID", $row);
            }
            return $response->responseMessageArray(false, "No existe viajero con ese ID", null);
            
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    } 

    public function getPassengerDetail($idPassengerToView, $idTravelerRequesting, $roleRequesting) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        
        try {
            if ($roleRequesting === 'Pasajero') {
                $checkSql = "SELECT Id_Traveler FROM passenger WHERE Id_Passenger = :idPass";
                $stmtCheck = $this->connection->prepare($checkSql);
                $stmtCheck->execute([':idPass' => $idPassengerToView]);
                $owner = $stmtCheck->fetchColumn();

                if ($owner != $idTravelerRequesting) {
                    return $response->responseMessageArray(false, "Error 403: Acceso denegado. No puedes ver datos de otros pasajeros.", null);
                }
            }

            $sql = "SELECT p.Id_Passenger, p.Cabin_Number, p.Special_Assistance, p.Boarding_Status, p.Id_Booking, p.Id_Traveler, p.Id_Trip,
                        t.First_Name, t.Last_Name, t.Birth_Date, t.Gender, t.Nationality, 
                        t.Id_Card_Passport, t.Document_Type,
                        TIMESTAMPDIFF(YEAR, t.Birth_Date, CURDATE()) AS Age,
                        m.Blood_Type, m.Allergies, m.Chronic_Diseases, m.Observations
                    FROM passenger p 
                    INNER JOIN travelers t ON p.Id_Traveler = t.Id_Traveler 
                    LEFT JOIN medical_records m ON t.Id_Traveler = m.Id_Traveler
                    WHERE p.Id_Passenger = :idPass";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':idPass' => $idPassengerToView]);
            $detail = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($detail) {
                $detail['Id_Passenger']       = intval($detail['Id_Passenger']);
                $detail['Age']                = intval($detail['Age'] ?? 0);
                $detail['Special_Assistance'] = $detail['Special_Assistance'] ?? "Ninguna";
                $detail['Blood_Type']         = $detail['Blood_Type'] ?? "N/D";
                $detail['Allergies']          = $detail['Allergies'] ?? "Sin alergias registradas";
                $detail['Chronic_Diseases']   = $detail['Chronic_Diseases'] ?? "Ninguna";
                $detail['Observations']       = $detail['Observations'] ?? "Sin observaciones adicionales";

                return $response->responseMessageArray(true, "Detalle del pasajero obtenido", $detail);
            }
            
            return $response->responseMessageArray(false, "Pasajero no encontrado en el sistema", null);

        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getAllPassengers() {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT 
                        p.*, 
                        t.First_Name, 
                        t.Last_Name, 
                        t.Id_Card_Passport, 
                        t.Nationality,
                        TIMESTAMPDIFF(YEAR, t.Birth_Date, CURDATE()) AS Age,
                        m.Chronic_Diseases, 
                        m.Allergies,
                        u.Access_Role
                    FROM passenger p
                    INNER JOIN travelers t ON p.Id_Traveler = t.Id_Traveler
                    LEFT JOIN medical_records m ON t.Id_Traveler = m.Id_Traveler
                    LEFT JOIN system_users u ON p.Id_Traveler = u.Id_Traveler
                    ORDER BY t.Last_Name ASC"; 
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $response->responseMessageArray(true, "Todos los pasajeros obtenidos correctamente", $data);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }
}
?>