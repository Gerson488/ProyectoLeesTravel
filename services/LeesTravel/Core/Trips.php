<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class TripsBE {
    public $idTrip;
    public $destinationName;
    public $shipName;
    public $cruiseLine;
    public $departurePort;
    public $arrivalPort;
    public $tripPhoto; 
    public $startDate;
    public $endDate;
    public $price;
    public $status; 
    public $maxCapacity;
    public $requiresVisa;
    public $includesFlight;
    public $durationNights;
    public $description;

    public function __construct(
        $idTrip = null, $destinationName = null, $shipName = null, $cruiseLine = null,
        $departurePort = null, $arrivalPort = null, $tripPhoto = 'default.jpg', 
        $startDate = null, $endDate = null, $price = 0.00, $status = 'Programado', 
        $maxCapacity = 0, $requiresVisa = 1, $includesFlight = 0, $durationNights = null, 
        $description = null
    ) {
        $this->idTrip = $idTrip;
        $this->destinationName = $destinationName;
        $this->shipName = $shipName;
        $this->cruiseLine = $cruiseLine;
        $this->departurePort = $departurePort;
        $this->arrivalPort = $arrivalPort;
        $this->tripPhoto = $tripPhoto; 
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->price = $price;
        $this->status = $status; 
        $this->maxCapacity = $maxCapacity;
        $this->requiresVisa = $requiresVisa;
        $this->includesFlight = $includesFlight;
        $this->durationNights = $durationNights;
        $this->description = $description;
    }
}

class Trips {
    private $connection;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }

    public function getAllTrips() {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT * FROM trips ORDER BY Start_Date ASC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $response->responseMessageArray(true, "Listado de cruceros obtenido", $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getTripById($idTrip) {
        $response = new Response();
        try {
            $sql = "SELECT * FROM trips WHERE Id_Trip = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $idTrip);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return $response->responseMessageArray(true, "Detalle del crucero", $result);
            }
            return $response->responseMessageArray(false, "Crucero no encontrado", null);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function createTrip(TripsBE $trip) {
        $response = new Response();
        try {
            $sql = "INSERT INTO trips (Destination_Name, Ship_Name, Cruise_Line, Departure_Port, Arrival_Port, Trip_Photo, Start_Date, End_Date, Price, Status, Max_Capacity, Requires_Visa, Includes_Flight, Duration_Nights, Description) 
                    VALUES (:name, :ship, :line, :dep, :arr, :photo, :start, :end, :price, :status, :cap, :visa, :flight, :nights, :desc)";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                ':name'     => $trip->destinationName,
                ':ship'     => $trip->shipName,
                ':line'     => $trip->cruiseLine,
                ':dep'      => $trip->departurePort,
                ':arr'      => $trip->arrivalPort,
                ':photo'    => $trip->tripPhoto, 
                ':start'    => $trip->startDate,
                ':end'      => $trip->endDate,
                ':price'    => $trip->price,
                ':status'   => $trip->status, 
                ':cap'      => $trip->maxCapacity,
                ':visa'     => $trip->requiresVisa,
                ':flight'   => $trip->includesFlight,
                ':nights'   => $trip->durationNights,
                ':desc'     => $trip->description
            ]);
            return $response->responseMessageArray(true, "Nuevo crucero programado", ["id" => $this->connection->lastInsertId()]);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function updateTrip(TripsBE $trip) {
        $response = new Response();
        try {
            $sql = "UPDATE trips SET 
                    Destination_Name = :name, Ship_Name = :ship, Cruise_Line = :line, Departure_Port = :dep, 
                    Arrival_Port = :arr, Trip_Photo = :photo, Start_Date = :start, 
                    End_Date = :end, Price = :price, Status = :status, Max_Capacity = :cap, 
                    Requires_Visa = :visa, Includes_Flight = :flight, Duration_Nights = :nights, Description = :desc 
                    WHERE Id_Trip = :id";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                ':name'     => $trip->destinationName,
                ':ship'     => $trip->shipName,
                ':line'     => $trip->cruiseLine,
                ':dep'      => $trip->departurePort,
                ':arr'      => $trip->arrivalPort,
                ':photo'    => $trip->tripPhoto, 
                ':start'    => $trip->startDate,
                ':end'      => $trip->endDate,
                ':price'    => $trip->price,
                ':status'   => $trip->status, 
                ':cap'      => $trip->maxCapacity,
                ':visa'     => $trip->requiresVisa,
                ':flight'   => $trip->includesFlight,
                ':nights'   => $trip->durationNights,
                ':desc'     => $trip->description,
                ':id'       => $trip->idTrip
            ]);
            return $response->responseMessageArray($success, "Crucero actualizado", null);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function deleteTrip($idTrip) {
        $response = new Response();
        if (!$this->connection) {
            return $response->structMessageErrorService();
        }
        try {
            $sql = "DELETE FROM trips WHERE Id_Trip = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $idTrip);
            return $response->responseMessageArray(
                $stmt->execute(),
                "Crucero eliminado",
                null
            );
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom(
                $e->getMessage()
            );
        }
    }

    public function getTripsWeb() {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT Id_Trip, Destination_Name, Ship_Name, Cruise_Line, 
                           Departure_Port, Trip_Photo, Start_Date, End_Date, 
                           Price, Status, Requires_Visa, Includes_Flight, Duration_Nights 
                    FROM trips 
                    WHERE Start_Date >= CURDATE() 
                    ORDER BY Start_Date ASC 
                    LIMIT 6";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($trips as &$trip) {
                $trip['Start_Date_Format'] = $this->formatDateSpanish($trip['Start_Date']);
                $trip['End_Date_Format'] = $this->formatDateSpanish($trip['End_Date']);
                if (empty($trip['Duration_Nights'])) {
                    $start = new DateTime($trip['Start_Date']);
                    $end = new DateTime($trip['End_Date']);
                    $trip['Nights'] = $start->diff($end)->days;
                } else {
                    $trip['Nights'] = $trip['Duration_Nights'];
                }
                $trip['Requires_Visa'] = (bool)$trip['Requires_Visa'];
                $trip['Includes_Flight'] = (bool)$trip['Includes_Flight'];
            }
            return $response->responseMessageArray(true, "Próximas salidas obtenidas", $trips);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getMyTripsByTraveler($idTraveler) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT t.*, p.Cabin_Number, p.Boarding_Status 
                    FROM trips t 
                    INNER JOIN passenger p ON t.Id_Trip = p.Id_Trip 
                    WHERE p.Id_Traveler = :idTraveler 
                    ORDER BY t.Start_Date ASC";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':idTraveler', $idTraveler, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $msg = count($result) > 0 ? "Tus viajes encontrados" : "No tienes viajes registrados";
            return $response->responseMessageArray(count($result) > 0, $msg, $result);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getActiveTrips() {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT * FROM trips WHERE Start_Date >= CURDATE() ORDER BY Start_Date ASC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $response->responseMessageArray(true, "Cruceros disponibles encontrados", $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function GetTripsChatIA() {
        try {
            $sql = "SELECT Id_Trip, Destination_Name, Start_Date, Ship_Name, Price, Status, Trip_Photo, Description 
                    FROM trips 
                    WHERE Start_Date >= CURDATE()"; 
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    private function formatDateSpanish($date) {
        if (!$date) return "";
        $timestamp = strtotime($date);
        $meses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
        return date("d", $timestamp) . " de " . $meses[date("n", $timestamp) - 1];
    }
    public function updateTripStatusApp($idTrip, $status) {
        $response = new Response();
        try {
            $sql = "UPDATE trips SET Status = :status WHERE Id_Trip = :id";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([':status' => $status, ':id' => $idTrip]);
            return $response->responseMessageArray($success, "Estado del viaje actualizado desde la aplicación correctamente", null);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }
}
?>