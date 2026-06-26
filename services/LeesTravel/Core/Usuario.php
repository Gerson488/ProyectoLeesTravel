<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class UsuarioBE {
    public $idUser;
    public $idTraveler;
    public $email;
    public $password;
    public $photoPath;
    public $accessRole;
    public $userStatus;

    public function __construct(
        $idUser = null, $idTraveler = null, $email = null, 
        $password = null, $photoPath = 'default_user.png', 
        $accessRole = 'Pasajero', $userStatus = 1
    ) {
        $this->idUser = $idUser;
        $this->idTraveler = $idTraveler;
        $this->email = $email;
        $this->password = $password;
        $this->photoPath = $photoPath;
        $this->accessRole = $accessRole;
        $this->userStatus = $userStatus;
    }
}

class Usuario {
    private $connection;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
    }

    public function getLogin($email, $password) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        
        try {
            $sql = "SELECT u.Id_User, u.Id_Traveler, u.Password, u.Access_Role, u.Email, u.Photo_Path, u.User_Status,
                           t.First_Name, t.Last_Name 
                    FROM system_users u
                    LEFT JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE u.Email = :email LIMIT 1";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':email' => trim($email)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                if (intval($row['User_Status']) === 0) {
                    return $response->responseMessageArray(false, "Acceso denegado: Esta cuenta se encuentra suspendida o inactiva.", null);
                }

                if (password_verify(trim($password), $row['Password'])) {
                    $dataResponse = [
                        'Id_User'     => intval($row['Id_User']),
                        'Id_Traveler' => intval($row['Id_Traveler']),
                        'Access_Role' => $row['Access_Role'], 
                        'Full_Name'   => ($row['First_Name'] ?? 'Usuario') . " " . ($row['Last_Name'] ?? ''), 
                        'Email'       => $row['Email'],
                        'Photo'       => $row['Photo_Path'] ?? 'default_user.png'
                    ];
                    return $response->responseMessageArray(true, "¡Bienvenido de nuevo, " . ($row['First_Name'] ?? 'Usuario') . "!", $dataResponse);
                }
            }
            
            return $response->responseMessageArray(false, "El correo o la contraseña no coinciden.", null);
            
        } catch (PDOException $e) { 
            return $response->structMessageErrorCustom($e->getMessage()); 
        }
    }

    public function createUser(UsuarioBE $usuario) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();

        try {
            if ($this->emailExists($usuario->email)) {
                return $response->structMessageErrorCustom("El correo electrónico ya está registrado.");
            }

            $passHash = password_hash($usuario->password, PASSWORD_BCRYPT);
            
            $sql = "INSERT INTO system_users (Id_Traveler, Email, Password, Photo_Path, Access_Role, User_Status) 
                    VALUES (:idT, :email, :pass, :photo, :role, :status)";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':idT', $usuario->idTraveler);
            $stmt->bindParam(':email', $usuario->email);
            $stmt->bindParam(':pass', $passHash);
            $stmt->bindParam(':photo', $usuario->photoPath);
            $stmt->bindParam(':role', $usuario->accessRole);
            $stmt->bindParam(':status', $usuario->userStatus);
            
            $stmt->execute();
            $idCreated = $this->connection->lastInsertId();

            return $response->responseMessageArray(true, "Usuario del sistema creado correctamente", ["idUser" => intval($idCreated)]);

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return $response->structMessageErrorCustom("Ya está registrado.");
            } else {
                return $response->structMessageErrorCustom("No se pudo crear el usuario.");
            }
        }
    }

    public function updateUser(UsuarioBE $usuario) {
        $response = new Response();
        if (!$this->connection) return $response->structMessageErrorService();
        
        try {
            if ($this->emailExists($usuario->email, $usuario->idUser)) {
                return $response->structMessageErrorCustom("El correo electrónico ya está registrado por otro usuario.");
            }

            $sql = "UPDATE system_users SET 
                        Email = :email, 
                        Access_Role = :role, 
                        User_Status = :status";
            
            if ($usuario->password !== null && trim($usuario->password) !== "") {
                $sql .= ", Password = :pass";
            }
            
            $sql .= " WHERE Id_User = :id";
            
            $stmt = $this->connection->prepare($sql);
            
            $stmt->bindParam(':email', $usuario->email);
            $stmt->bindParam(':role', $usuario->accessRole);
            $stmt->bindParam(':status', $usuario->userStatus);
            $stmt->bindParam(':id', $usuario->idUser);
            
            if ($usuario->password !== null && trim($usuario->password) !== "") {
                $passHash = password_hash($usuario->password, PASSWORD_BCRYPT);
                $stmt->bindParam(':pass', $passHash);
            }
            
            $success = $stmt->execute();
            return $response->responseMessageArray($success, "Datos actualizados correctamente", null);
            
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return $response->structMessageErrorCustom("El correo electrónico ya está registrado por otro usuario.");
            }
            return $response->structMessageErrorCustom("No se pudo actualizar el usuario operativo.");
        }
    }

    public function deleteUser($idUser) {
        $response = new Response();
        try {
            $sql = "DELETE FROM system_users WHERE Id_User = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $idUser);
            $success = $stmt->execute();
            return $response->responseMessageArray($success, "Usuario eliminado", null);
        } catch (PDOException $e) { 
            return $response->structMessageErrorCustom($e->getMessage()); 
        }
    }

    public function getAllUsers() {
        $response = new Response();
        try {
            $sql = "SELECT u.Id_User, u.Id_Traveler, u.Email, u.Access_Role, u.User_Status, u.Photo_Path, 
                           t.First_Name, t.Last_Name, t.Id_Card_Passport 
                    FROM system_users u
                    LEFT JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    ORDER BY u.Id_User DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            return $response->responseMessageArray(true, "Lista obtenida", $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) { 
            return $response->structMessageErrorCustom($e->getMessage()); 
        }
    }

    private function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT 1 FROM system_users WHERE Email = :email";
        if ($excludeUserId !== null) {
            $sql .= " AND Id_User <> :idUser";
        }
        $sql .= " LIMIT 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':email', trim($email));

        if ($excludeUserId !== null) {
            $stmt->bindValue(':idUser', $excludeUserId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }
}
?>