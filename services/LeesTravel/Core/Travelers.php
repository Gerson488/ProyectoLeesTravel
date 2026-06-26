    <?php
    require_once(__DIR__ . '/../Config/Setting.php');
    require_once(__DIR__ . '/../Response/Response.php');

    class TravelerBE {
        public $idTraveler;
        public $firstName;
        public $lastName;
        public $birthDate;
        public $gender;
        public $nationality;
        public $documentType;
        public $idCardPassport;
        public $emergencyContact;
        public $emergencyPhone;

        public function __construct(
            $idTraveler = null, $firstName = null, $lastName = null, 
            $birthDate = null, $gender = null, $nationality = null, 
            $documentType = null, $idCardPassport = null, 
            $emergencyContact = null, $emergencyPhone = null
        ) {
            $this->idTraveler = $idTraveler;
            $this->firstName = $firstName;
            $this->lastName = $lastName;
            $this->birthDate = $birthDate;
            $this->gender = $gender;
            $this->nationality = $nationality;
            $this->documentType = $documentType;
            $this->idCardPassport = $idCardPassport;
            $this->emergencyContact = $emergencyContact;
            $this->emergencyPhone = $emergencyPhone;
        }
    }

    class Traveler {
        private $connection;

        public function __construct() {
            $setting = new Setting();
            $this->connection = $setting->getConnection();
        }

        public function createTraveler(TravelerBE $traveler) {
            $response = new Response();
            if (!$this->connection) return $response->structMessageErrorService();

            try {
                $sql = "INSERT INTO travelers (First_Name, Last_Name, Birth_Date, Gender, Nationality, Document_Type, Id_Card_Passport, Emergency_Contact, Emergency_Phone) 
                        VALUES (:fname, :lname, :bdate, :gender, :nat, :dtype, :doc, :econtact, :ephone)";
                
                $stmt = $this->connection->prepare($sql);
                $stmt->bindParam(':fname', $traveler->firstName);
                $stmt->bindParam(':lname', $traveler->lastName);
                $stmt->bindParam(':bdate', $traveler->birthDate);
                $stmt->bindParam(':gender', $traveler->gender);
                $stmt->bindParam(':nat', $traveler->nationality);
                $stmt->bindParam(':dtype', $traveler->documentType);
                $stmt->bindParam(':doc', $traveler->idCardPassport);
                $stmt->bindParam(':econtact', $traveler->emergencyContact);
                $stmt->bindParam(':ephone', $traveler->emergencyPhone);
                
                $stmt->execute();
                $idCreated = $this->connection->lastInsertId();

                return $response->responseMessageArray(true, "Viajero registrado correctamente", ["idTraveler" => intval($idCreated)]);

            } catch (PDOException $e) {
                return $response->structMessageErrorCustom("Error al crear viajero: " . $e->getMessage());
            }
        }

        public function getTravelerByDocument($docNumber) {
            $response = new Response();
            try {
                $sql = "SELECT * FROM travelers WHERE Id_Card_Passport = :doc";
                $stmt = $this->connection->prepare($sql);
                $stmt->bindParam(':doc', $docNumber);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    return $response->responseMessageArray(true, "Viajero encontrado", $row);
                }
                return $response->responseMessageArray(false, "No existe viajero con ese documento", null);
            } catch (PDOException $e) {
                return $response->structMessageErrorCustom($e->getMessage());
            }
        }

        public function updateTraveler(TravelerBE $traveler) {
            $response = new Response();
            try {
                $sql = "UPDATE travelers SET 
                            First_Name = :fname, Last_Name = :lname, Birth_Date = :bdate, 
                            Gender = :gender, Nationality = :nat, Document_Type = :dtype, 
                            Id_Card_Passport = :doc, Emergency_Contact = :econtact, 
                            Emergency_Phone = :ephone 
                        WHERE Id_Traveler = :id";
                
                $stmt = $this->connection->prepare($sql);
                $stmt->bindParam(':fname', $traveler->firstName);
                $stmt->bindParam(':lname', $traveler->lastName);
                $stmt->bindParam(':bdate', $traveler->birthDate);
                $stmt->bindParam(':gender', $traveler->gender);
                $stmt->bindParam(':nat', $traveler->nationality);
                $stmt->bindParam(':dtype', $traveler->documentType);
                $stmt->bindParam(':doc', $traveler->idCardPassport);
                $stmt->bindParam(':econtact', $traveler->emergencyContact);
                $stmt->bindParam(':ephone', $traveler->emergencyPhone);
                $stmt->bindParam(':id', $traveler->idTraveler);
                
                $success = $stmt->execute();
                return $response->responseMessageArray($success, "Datos del viajero actualizados", null);
            } catch (PDOException $e) {
                return $response->structMessageErrorCustom($e->getMessage());
            }
        }

        public function deleteTraveler($idTraveler) {
            $response = new Response();
            try {
                $sql = "DELETE FROM travelers WHERE Id_Traveler = :id";
                $stmt = $this->connection->prepare($sql);
                $stmt->bindParam(':id', $idTraveler);
                $success = $stmt->execute();
                return $response->responseMessageArray($success, "Viajero eliminado del sistema", null);
            } catch (PDOException $e) {
                return $response->structMessageErrorCustom($e->getMessage());
            }
        }

    public function getAllTravelers() {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        try {
            $sql = "SELECT * FROM travelers ORDER BY Last_Name ASC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $response->responseMessageArray(true, "Lista de viajeros obtenida", $stmt->fetchAll(PDO::FETCH_ASSOC));
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
                    return $response->responseMessageArray(true, "Viajero encontrado", $row);
                }
                return $response->responseMessageArray(false, "No existe viajero con ese ID", null);
            } catch (PDOException $e) {
                return $response->structMessageErrorCustom($e->getMessage());
            }
        }
}
?>