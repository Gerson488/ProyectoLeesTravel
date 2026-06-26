<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class PublicationBE {
    public ?string $idPost;
    public ?string $idTrip;
    public ?string $idUser;
    public ?string $title;
    public ?string $description;
    public ?string $latitude;
    public ?string $longitude;
    public string $moderationStatus;
    public int $isPublic;
    public int $isDeleted;
    public ?string $updatedAt;
    public array $images = []; 

    public function __construct(
        ?string $idP=null, ?string $idT=null, ?string $idU=null, ?string $title=null, 
        ?string $desc=null, ?string $lat=null, ?string $lng=null, array $images=[], 
        string $status='Pendiente', int $public=0, int $isDel=0, ?string $updatedAt=null
    ) {
        $this->idPost = $idP;
        $this->idTrip = $idT;
        $this->idUser = $idU;
        $this->title = $title;
        $this->description = $desc;
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->images = $images;
        $this->moderationStatus = $status;
        $this->isPublic = $public;
        $this->isDeleted = $isDel;
        $this->updatedAt = $updatedAt;
    }
}

class Publication {
    private ?\PDO $connection;
    private string $basePath;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
        $this->basePath = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR;
    }

    private function generateUUID(): string {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function getPublicationById(string $idPost) {
        try {
            $sql = "SELECT * FROM blog_post WHERE Id_Post = ? AND Is_Deleted = 0";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$idPost]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) { return null; }
    }

    public function registerPublication(PublicationBE $p) {
        $response = new Response();
        try {
            $this->connection->beginTransaction();

            $idPost = !empty($p->idPost) ? $p->idPost : $this->generateUUID();

            $sql = "INSERT INTO blog_post (Id_Post, Id_Trip, Id_User, Title, Description, Latitude, Longitude, Moderation_Status, Is_Public) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente', 0)";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$idPost, $p->idTrip, $p->idUser, $p->title, $p->description, $p->latitude, $p->longitude]);

            if (!empty($p->images)) {
                $sqlImg = "INSERT INTO blog_multimedia (Id_Multimedia, Id_Post, Url_File, File_Type) VALUES (?, ?, ?, 'image')";
                $stmtImg = $this->connection->prepare($sqlImg);
                foreach ($p->images as $url) {
                    $idMedia = $this->generateUUID();
                    $stmtImg->execute([$idMedia, $idPost, $url]);
                }
            }

            $this->connection->commit();
            return $response->responseMessageArray(true, "Publicación enviada a moderación", ["idPost" => $idPost]);
        } catch (\Exception $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function updatePublication(PublicationBE $p) {
        $response = new Response();
        try {
            $this->connection->beginTransaction();

            $sql = "UPDATE blog_post SET Title = ?, Description = ?, Latitude = ?, Longitude = ?, Moderation_Status = 'Pendiente', Is_Public = 0, Updated_At = CURRENT_TIMESTAMP 
                    WHERE Id_Post = ? AND Id_User = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$p->title, $p->description, $p->latitude, $p->longitude, $p->idPost, $p->idUser]);

            $sqlOld = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ? AND Is_Deleted = 0";
            $stmtOld = $this->connection->prepare($sqlOld);
            $stmtOld->execute([$p->idPost]);
            $dbImages = $stmtOld->fetchAll(\PDO::FETCH_COLUMN);

            $toDeletePhysically = array_diff($dbImages, $p->images);

            $sqlHide = "UPDATE blog_multimedia SET Is_Deleted = 1 WHERE Id_Post = ?";
            $this->connection->prepare($sqlHide)->execute([$p->idPost]);

            if (!empty($p->images)) {
                foreach ($p->images as $url) {
                    $sqlCheck = "SELECT Id_Multimedia FROM blog_multimedia WHERE Id_Post = ? AND Url_File = ? LIMIT 1";
                    $stmtCheck = $this->connection->prepare($sqlCheck);
                    $stmtCheck->execute([$p->idPost, $url]);
                    $existing = $stmtCheck->fetch();

                    if ($existing) {
                        $sqlReactivate = "UPDATE blog_multimedia SET Is_Deleted = 0, Updated_At = CURRENT_TIMESTAMP WHERE Id_Multimedia = ?";
                        $this->connection->prepare($sqlReactivate)->execute([$existing['Id_Multimedia']]);
                    } else {
                        $sqlIns = "INSERT INTO blog_multimedia (Id_Multimedia, Id_Post, Url_File, File_Type) VALUES (?, ?, ?, 'image')";
                        $idMedia = $this->generateUUID();
                        $this->connection->prepare($sqlIns)->execute([$idMedia, $p->idPost, $url]);
                    }
                }
            }

            $this->connection->commit();
            return $response->responseMessageArray(true, "Publicación actualizada correctamente", ["deleteFiles" => $toDeletePhysically]);
        } catch (\Exception $e) {
            if ($this->connection->inTransaction()) $this->connection->rollBack();
            return $response->responseMessageArray(false, $e->getMessage(), null);
        }
    }

    public function deletePublication(string $idPost, string $idUser) {
        $response = new Response();
        try {
            $post = $this->getPublicationById($idPost);
            if (!$post || $post['Id_User'] != $idUser) {
                return $response->responseMessageArray(false, "No autorizado o publicación inexistente", null);
            }

            $this->connection->beginTransaction();

            $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ? AND Is_Deleted = 0";
            $stmtImg = $this->connection->prepare($sqlImg);
            $stmtImg->execute([$idPost]);
            $files = $stmtImg->fetchAll(\PDO::FETCH_COLUMN);

            $sqlPost = "UPDATE blog_post SET Is_Deleted = 1, Updated_At = CURRENT_TIMESTAMP WHERE Id_Post = ?";
            $this->connection->prepare($sqlPost)->execute([$idPost]);

            $sqlDelImg = "UPDATE blog_multimedia SET Is_Deleted = 1, Updated_At = CURRENT_TIMESTAMP WHERE Id_Post = ?";
            $this->connection->prepare($sqlDelImg)->execute([$idPost]);

            $this->connection->commit();

            return $response->responseMessageArray(true, "Publicación eliminada correctamente", ["deleteFiles" => $files]);
        } catch (\Exception $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            return $response->responseMessageArray(false, $e->getMessage(), null);
        }
    }

    public function getPublicationsWeb() {
        $response = new Response();
        try {
            $sql = "SELECT p.*, t.First_Name, t.Last_Name 
                    FROM blog_post p 
                    INNER JOIN system_users u ON p.Id_User = u.Id_User 
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE p.Is_Public = 1 
                      AND p.Moderation_Status = 'Aprobado' 
                      AND p.Is_Deleted = 0
                    ORDER BY p.Published_Date DESC LIMIT 3"; 
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $publications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($publications as &$pub) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ? AND Is_Deleted = 0";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$pub['Id_Post']]);
                $pub['gallery'] = $stmtImg->fetchAll(\PDO::FETCH_COLUMN);

                $pub['Author_Name'] = trim($pub['First_Name'] . ' ' . $pub['Last_Name']);
                $pub['Cover_Image'] = !empty($pub['gallery']) ? $pub['gallery'][0] : null;

                $firstInitial = !empty($pub['First_Name']) ? strtoupper(substr($pub['First_Name'], 0, 1)) : 'U';
                $lastInitial = !empty($pub['Last_Name']) ? strtoupper(substr($pub['Last_Name'], 0, 1)) : '';
                $pub['User_Initials'] = $firstInitial . $lastInitial;

                $pub['Time_Ago'] = $this->timeElapsedString($pub['Published_Date'] ?? $pub['Updated_At']);
                $pub['Likes_Count'] = isset($pub['Likes_Count']) ? $pub['Likes_Count'] : rand(10, 150);
            }

            return $response->responseMessageArray(true, "Últimas experiencias obtenidas para la web", $publications);
        } catch (\PDOException $e) {
            return $response->responseMessageArray(false, $e->getMessage(), null);
        }
    }

    public function getPublicationsByTripAndUser(string $idTrip, string $idUser) {
        $response = new Response();
        try {
            $sql = "SELECT p.*, t.First_Name, t.Last_Name 
                    FROM blog_post p 
                    LEFT JOIN system_users u ON p.Id_User = u.Id_User 
                    LEFT JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE p.Id_Trip = ? AND p.Id_User = ? AND p.Is_Deleted = 0
                    ORDER BY p.Published_Date DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$idTrip, $idUser]);
            $publications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($publications as &$pub) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ? AND Is_Deleted = 0";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$pub['Id_Post']]);
                $pub['gallery'] = $stmtImg->fetchAll(\PDO::FETCH_COLUMN);
            }

            return $response->responseMessageArray(true, "Publicaciones del viaje recuperadas", $publications);
        } catch (\PDOException $e) {
            return $response->responseMessageArray(false, "Error SQL: " . $e->getMessage(), null);
        }
    }

    public function getAllPublicPublicationsApp() {
        $response = new Response();
        try {
            $sql = "SELECT p.*, t.First_Name, t.Last_Name 
                    FROM blog_post p 
                    INNER JOIN system_users u ON p.Id_User = u.Id_User 
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE p.Is_Public = 1 
                      AND p.Moderation_Status = 'Aprobado' 
                      AND p.Is_Deleted = 0
                    ORDER BY p.Published_Date DESC"; 
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $publications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($publications as &$pub) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ? AND Is_Deleted = 0";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$pub['Id_Post']]);
                $pub['gallery'] = $stmtImg->fetchAll(\PDO::FETCH_COLUMN);
            }

            return $response->responseMessageArray(true, "Catálogo completo de publicaciones obtenido", $publications);
        } catch (\PDOException $e) {
            return $response->responseMessageArray(false, $e->getMessage(), null);
        }
    }

    public function getAllPendingPublications() {
        $response = new Response();
        try {
            $sql = "SELECT p.*, t.First_Name, t.Last_Name 
                    FROM blog_post p 
                    INNER JOIN system_users u ON p.Id_User = u.Id_User 
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE p.Moderation_Status = 'Pendiente' AND p.Is_Deleted = 0 
                    ORDER BY p.Published_Date ASC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($posts as &$p) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ? AND Is_Deleted = 0";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$p['Id_Post']]);
                $p['gallery'] = $stmtImg->fetchAll(\PDO::FETCH_COLUMN);
            }
            return $response->responseMessageArray(true, "Publicaciones pendientes recuperadas", $posts);
        } catch (\PDOException $e) { 
            return $response->responseMessageArray(false, $e->getMessage(), null); 
        }
    }

    public function moderatePublication(string $idPost, string $status, int $isPublic) {
        $response = new Response();
        try {
            $sql = "UPDATE blog_post SET Moderation_Status = ?, Is_Public = ? WHERE Id_Post = ?";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([$status, $isPublic, $idPost]);
            if ($status === 'Aprobado') {
                $mensajeAccion = "Publicación aprobada y visible en el muro";
            } elseif ($status === 'Rechazado') {
                $mensajeAccion = "Publicación rechazada y ocultada";
            } elseif ($status === 'Pendiente') {
                $mensajeAccion = "La publicación ha sido devuelta a revisión";
            } else {
                $mensajeAccion = "Estado de publicación actualizado";
            }

            return $response->responseMessageArray($success, $mensajeAccion, null);
        } catch (\PDOException $e) { 
            return $response->responseMessageArray(false, $e->getMessage(), null); 
        }
    }

    public function getFullPublicationById(string $idPost) {
        $response = new Response();
        try {
            $sql = "SELECT p.*, t.First_Name, t.Last_Name 
                    FROM blog_post p 
                    INNER JOIN system_users u ON p.Id_User = u.Id_User 
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE p.Id_Post = ? AND p.Is_Deleted = 0";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$idPost]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($post) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ? AND Is_Deleted = 0";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$idPost]);
                $post['gallery'] = $stmtImg->fetchAll(\PDO::FETCH_COLUMN);

                return $response->responseMessageArray(true, "Detalle obtenido", $post);
            }
            return $response->responseMessageArray(false, "La publicación no existe", null);
        } catch (\PDOException $e) {
            return $response->responseMessageArray(false, $e->getMessage(), null);
        }
    }

    public function getAllPublicationsAdmin() {
        $response = new Response();
        try {
            $sql = "SELECT p.*, t.First_Name, t.Last_Name 
                    FROM blog_post p 
                    INNER JOIN system_users u ON p.Id_User = u.Id_User 
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE p.Is_Deleted = 0 
                    ORDER BY p.Published_Date DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $publications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($publications as &$pub) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ? AND Is_Deleted = 0";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$pub['Id_Post']]);
                $pub['gallery'] = $stmtImg->fetchAll(\PDO::FETCH_COLUMN);
            }

            return $response->responseMessageArray(true, "Historial completo obtenido para administración", $publications);
        } catch (\PDOException $e) {
            return $response->responseMessageArray(false, $e->getMessage(), null);
        }
    }

    public function getFullPublicationAdminById(string $idPost) {
        $response = new Response();
        try {
            $sql = "SELECT p.*, t.First_Name, t.Last_Name 
                    FROM blog_post p 
                    INNER JOIN system_users u ON p.Id_User = u.Id_User 
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE p.Id_Post = ?"; 
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$idPost]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($post) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ?";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$idPost]);
                $post['gallery'] = $stmtImg->fetchAll(\PDO::FETCH_COLUMN);

                return $response->responseMessageArray(true, "Detalle administrativo obtenido", $post);
            }
            return $response->responseMessageArray(false, "La publicación no existe en el sistema", null);
        } catch (\PDOException $e) {
            return $response->responseMessageArray(false, $e->getMessage(), null);
        }
    }

    private function timeElapsedString(string $datetime, bool $full = false): string {
        if (empty($datetime)) return "Recientemente";
        
        $now = new \DateTime;
        $ago = new \DateTime($datetime);
        $diff = $now->diff($ago);

        $weeks = floor($diff->d / 7);
        $days = $diff->d % 7;

        $string = array(
            'y' => 'año',
            'm' => 'mes',
            'w' => 'semana',
            'd' => 'día',
            'h' => 'hora',
            'i' => 'minuto',
            's' => 'segundo',
        );

        $diffValues = array(
            'y' => $diff->y,
            'm' => $diff->m,
            'w' => $weeks,
            'd' => $days,
            'h' => $diff->h,
            'i' => $diff->i,
            's' => $diff->s,
        );

        foreach ($string as $k => &$v) {
            if ($diffValues[$k]) {
                $v = $diffValues[$k] . ' ' . $v . ($diffValues[$k] > 1 ? 's' : '');
            } else { 
                unset($string[$k]); 
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? 'Hace ' . implode(', ', $string) : 'Justo ahora';
    }
}
?>