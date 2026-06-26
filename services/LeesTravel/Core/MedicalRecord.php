<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');
class MedicalRecordBE {
    public $idFile;
    public $idTraveler; 
    public $bloodType;
    public $allergies;
    public $chronicDiseases;
    public $currentMedication;
    public $observations;

    public function __construct($idF=null, $idT=null, $blood=null, $all=null, $chronic=null, $med=null, $obs=null) {
        $this->idFile = $idF;
        $this->idTraveler = $idT;
        $this->bloodType = $blood;
        $this->allergies = $all;
        $this->chronicDiseases = $chronic;
        $this->currentMedication = $med;
        $this->observations = $obs;
    }
}

class MedicalRecord {
    private $connection;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }
    public function getRecordByPassenger($idPassenger) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();

        try {
            $sql = "SELECT 
                        mr.Id_File, 
                        t.Id_Traveler,
                        t.First_Name, 
                        t.Last_Name, 
                        mr.Blood_Type, 
                        mr.Allergies, 
                        mr.Chronic_Diseases, 
                        mr.Current_Medication, 
                        mr.Observations
                    FROM passenger p
                    INNER JOIN travelers t ON p.Id_Traveler = t.Id_Traveler
                    LEFT JOIN medical_records mr ON t.Id_Traveler = mr.Id_Traveler
                    WHERE p.Id_Passenger = :id";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':id' => $idPassenger]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return $response->responseMessageArray(false, "No se encontró información para el pasajero proporcionado.", null);
            }

            return $response->responseMessageArray(true, "Datos recuperados", $data);

        } catch (PDOException $e) { 
            return $response->structMessageErrorCustom("Error SQL: " . $e->getMessage()); 
        }
    }

    public function createRecord(MedicalRecordBE $m) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();

        try {
            $sql = "INSERT INTO medical_records (Id_Traveler, Blood_Type, Allergies, Chronic_Diseases, Current_Medication, Observations) 
                    VALUES (:idT, :blood, :all, :chronic, :med, :obs)
                    ON DUPLICATE KEY UPDATE 
                        Blood_Type = :blood2, 
                        Allergies = :all2, 
                        Chronic_Diseases = :chronic2, 
                        Current_Medication = :med2, 
                        Observations = :obs2";
            
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                ':idT' => $m->idTraveler, 
                ':blood' => $m->bloodType, 
                ':all' => $m->allergies, 
                ':chronic' => $m->chronicDiseases,
                ':med' => $m->currentMedication,
                ':obs' => $m->observations,
                ':blood2' => $m->bloodType, 
                ':all2' => $m->allergies, 
                ':chronic2' => $m->chronicDiseases,
                ':med2' => $m->currentMedication,
                ':obs2' => $m->observations
            ]);

            return $response->responseMessageArray($success, "Operación médica exitosa", [
                "id" => intval($this->connection->lastInsertId())
            ]);

        } catch (PDOException $e) { 
            return $response->structMessageErrorCustom("Error al procesar registro: " . $e->getMessage()); 
        }
    }
    public function updateRecord(MedicalRecordBE $m) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();

        try {
            $sql = "UPDATE medical_records SET 
                        Blood_Type = :blood, 
                        Allergies = :all, 
                        Chronic_Diseases = :chronic, 
                        Current_Medication = :med, 
                        Observations = :obs 
                    WHERE Id_File = :idF";

            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([
                ':blood' => $m->bloodType, 
                ':all' => $m->allergies, 
                ':chronic' => $m->chronicDiseases,
                ':med' => $m->currentMedication,
                ':obs' => $m->observations, 
                ':idF' => $m->idFile
            ]);

            return $response->responseMessageArray($success, "Registro actualizado correctamente", null);
        } catch (PDOException $e) { 
            return $response->structMessageErrorCustom("Error al actualizar: " . $e->getMessage()); 
        }
    }
    public function getTravelerIdByPassenger($idPassenger) {
        if (!$this->connection) return null;
        
        try {
            $sql = "SELECT Id_Traveler FROM passenger WHERE Id_Passenger = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':id' => $idPassenger]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ? $res['Id_Traveler'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
    public function deleteRecord($idFile) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();

        try {
            $sql = "DELETE FROM medical_records WHERE Id_File = :id";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([':id' => $idFile]);
            return $response->responseMessageArray($success, "Registro eliminado del sistema", null);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }
}