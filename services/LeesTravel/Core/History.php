<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class HistoryBE {
    public $idHistory;
    public $idTrip;         
    public $idPassenger;
    public $eventDescription;
    public $eventDate;      
    public $idGuiaUser;

    public function __construct($idH=null, $idTr=null, $idP=null, $desc=null, $date=null, $idG=null) {
        $this->idHistory = $idH;
        $this->idTrip = $idTr;
        $this->idPassenger = $idP;
        $this->eventDescription = $desc;
        $this->eventDate = $date;
        $this->idGuiaUser = $idG;
    }
}

class History {
    private $connection;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }

   
    public function createLog(HistoryBE $h) {
    $response = new Response();
    if (!$this->connection) return $response->structMessageErrorService();
    try {
        $sql = "INSERT INTO history (Id_Trip, Id_Passenger, Event_Description, Event_Date, Id_Guia_User) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $fechaFinal = date('Y-m-d H:i:s');

        $success = $stmt->execute([
            $h->idTrip,
            $h->idPassenger,
            $h->eventDescription,
            $fechaFinal,
            $h->idGuiaUser
        ]);

        if (!$success) {
            $err = $stmt->errorInfo();
            return $response->responseErrorMessage("Error SQL: " . $err[2]);
        }
        
        return $response->responseMessageArray(true, "Incidente registrado con éxito", ["id" => $this->connection->lastInsertId()]);
    } catch (PDOException $e) { 
        return $response->structMessageErrorCustom("Excepción: " . $e->getMessage()); 
    }
    }

    public function updateLog(HistoryBE $h) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "UPDATE history SET Id_Trip = ?, Id_Passenger = ?, Event_Description = ?, Event_Date = ?, Id_Guia_User = ? 
                    WHERE Id_History = ?";
            $stmt = $this->connection->prepare($sql);
            $fechaFinal = (!empty($h->eventDate) && trim($h->eventDate) !== "") ? $h->eventDate : date('Y-m-d H:i:s');

            $success = $stmt->execute([
                $h->idTrip,
                $h->idPassenger,
                $h->eventDescription,
                $fechaFinal,
                $h->idGuiaUser,
                $h->idHistory
            ]);
            return $response->responseMessageArray($success, "Registro de bitácora actualizado correctamente", null);
        } catch (PDOException $e) { 
            return $response->structMessageErrorCustom($e->getMessage()); 
        }
    }
    public function deleteLog($idHistory) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "DELETE FROM history WHERE Id_History = ?";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([$idHistory]);
            return $response->responseMessageArray($success, "Entrada removida de la bitácora correctamente", null);
        } catch (PDOException $e) { 
            return $response->structMessageErrorCustom($e->getMessage()); 
        }
    }
    public function getHistoryByTrip($idTrip) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT 
                        h.Id_History, 
                        h.Id_Trip, 
                        h.Id_Passenger, 
                        h.Event_Description, 
                        h.Event_Date, 
                        h.Id_Guia_User,
                        p.Cabin_Number,
                        t_pas.Id_Card_Passport,
                        CONCAT(t_pas.First_Name, ' ', t_pas.Last_Name) AS Passenger_Name,
                        CONCAT(t_guia.First_Name, ' ', t_guia.Last_Name) AS Guia_Name
                    FROM history h
                    INNER JOIN passenger p ON h.Id_Passenger = p.Id_Passenger
                    INNER JOIN travelers t_pas ON p.Id_Traveler = t_pas.Id_Traveler
                    INNER JOIN system_users u ON h.Id_Guia_User = u.Id_User
                    INNER JOIN travelers t_guia ON u.Id_Traveler = t_guia.Id_Traveler
                    WHERE h.Id_Trip = ?
                    ORDER BY h.Event_Date DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$idTrip]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $response->responseMessageArray(true, "Historial de incidentes del crucero obtenido correctamente", $data);
        } catch (PDOException $e) { 
            return $response->structMessageErrorCustom($e->getMessage()); 
        }
    }
    public function getHistoryByPassenger($idPassenger) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT 
                        h.Id_History, 
                        h.Id_Trip, 
                        h.Event_Description, 
                        h.Event_Date,
                        CONCAT(t_guia.First_Name, ' ', t_guia.Last_Name) AS Guia_Name
                    FROM history h
                    INNER JOIN system_users u ON h.Id_Guia_User = u.Id_User
                    INNER JOIN travelers t_guia ON u.Id_Traveler = t_guia.Id_Traveler
                    WHERE h.Id_Passenger = ?
                    ORDER BY h.Event_Date DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$idPassenger]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $response->responseMessageArray(true, "Historial del pasajero obtenido con éxito", $data);
        } catch (PDOException $e) { 
            return $response->structMessageErrorCustom($e->getMessage()); 
        }
    }
    public function getGuiaNameById($idUser) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT CONCAT(t.First_Name, ' ', t.Last_Name) AS Guia_Name
                    FROM system_users u
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE u.Id_User = ?";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$idUser]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $response->responseMessageArray(true, "Nombre obtenido", ["name" => $result['Guia_Name']]);
            } else {
                return $response->responseMessageArray(false, "Guía no encontrado", ["name" => "Desconocido"]);
            }
        } catch (PDOException $e) { 
            return $response->structMessageErrorCustom($e->getMessage()); 
        }
    }
}
?>