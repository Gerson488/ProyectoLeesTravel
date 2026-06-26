<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Response/Response.php');

class BlogBE {
    public $idPost;
    public $idTrip;
    public $idUser;
    public $title;
    public $description;
    public $coordinatesGps;
    public $moderationStatus;
    public $isPublic;
    public $images = []; 

    public function __construct($idP=null, $idT=null, $idU=null, $title=null, $desc=null, $gps=null, $images=[], $status='Pendiente', $public=false) {
        $this->idPost = $idP;
        $this->idTrip = $idT;
        $this->idUser = $idU;
        $this->title = $title;
        $this->description = $desc;
        $this->coordinatesGps = $gps;
        $this->images = $images;
        $this->moderationStatus = $status;
        $this->isPublic = $public;
    }
}

class Blog {
    private $connection;
    private $basePath;

    public function __construct() {
        $setting = new Setting();
        $this->connection = $setting->getConnection();
        $this->basePath = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR;
    }

    public function getPostById($idPost) {
        if (!$this->connection) return null;
        try {
            $sql = "SELECT * FROM blog_post WHERE Id_Post = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$idPost]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($post) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ?";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$idPost]);
                $post['gallery'] = $stmtImg->fetchAll(PDO::FETCH_COLUMN);
            }
            return $post;
        } catch (PDOException $e) { return null; }
    }

    public function createPost(BlogBE $b) {
        $response = new Response();
        try {
            $this->connection->beginTransaction();

            $sql = "INSERT INTO blog_post (Id_Trip, Id_User, Title, Description, Coordinates_GPS, Moderation_Status, Is_Public) 
                    VALUES (?, ?, ?, ?, ?, 'Pendiente', FALSE)";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$b->idTrip, $b->idUser, $b->title, $b->description, $b->coordinatesGps]);
            $idPost = $this->connection->lastInsertId();

            if (!empty($b->images)) {
                $sqlImg = "INSERT INTO blog_multimedia (Id_Post, Url_File) VALUES (?, ?)";
                $stmtImg = $this->connection->prepare($sqlImg);
                foreach ($b->images as $url) {
                    $stmtImg->execute([$idPost, $url]);
                }
            }

            $this->connection->commit();
            return $response->responseMessageArray(true, "Post enviado a moderación con " . count($b->images) . " fotos", ["id" => $idPost]);
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) $this->connection->rollBack();
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function updatePost(BlogBE $b, $replaceImages = false) {
        $response = new Response();
        try {
            $this->connection->beginTransaction();

            $sql = "UPDATE blog_post SET Title = ?, Description = ?, Coordinates_GPS = ?, Moderation_Status = 'Pendiente', Is_Public = FALSE 
                    WHERE Id_Post = ? AND Id_User = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$b->title, $b->description, $b->coordinatesGps, $b->idPost, $b->idUser]);

            if ($replaceImages) {
                $sqlOld = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ?";
                $stmtOld = $this->connection->prepare($sqlOld);
                $stmtOld->execute([$b->idPost]);
                $oldFiles = $stmtOld->fetchAll(PDO::FETCH_COLUMN);
                $sqlDel = "DELETE FROM blog_multimedia WHERE Id_Post = ?";
                $stmtDel = $this->connection->prepare($sqlDel);
                $stmtDel->execute([$b->idPost]);

                if (!empty($b->images)) {
                    $sqlIns = "INSERT INTO blog_multimedia (Id_Post, Url_File) VALUES (?, ?)";
                    $stmtIns = $this->connection->prepare($sqlIns);
                    foreach ($b->images as $url) {
                        $stmtIns->execute([$b->idPost, $url]);
                    }
                }

                foreach ($oldFiles as $file) {
                    $path = $this->basePath . $file;
                    if (file_exists($path)) { unlink($path); }
                }
            }

            $this->connection->commit();
            return $response->responseMessageArray(true, "Post actualizado y puesto en revisión", null);
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) $this->connection->rollBack();
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function deletePost($idPost, $idUser) {
        $response = new Response();
        try {
            $post = $this->getPostById($idPost);
            if ($post && $post['Id_User'] == $idUser) {
                $this->connection->beginTransaction();

                $sql = "DELETE FROM blog_post WHERE Id_Post = ?";
                $stmt = $this->connection->prepare($sql);
                $success = $stmt->execute([$idPost]);
                
                if ($success) {
                    foreach ($post['gallery'] as $img) {
                        $path = $this->basePath . $img;
                        if (file_exists($path)) { unlink($path); }
                    }
                    $this->connection->commit();
                    return $response->responseMessageArray(true, "Post y multimedia eliminados", null);
                }
            }
            return $response->responseErrorMessage("No autorizado o post inexistente");
        } catch (Exception $e) {
            if ($this->connection->inTransaction()) $this->connection->rollBack();
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }

    public function getPublicPostsByTrip($idTrip) {
        $response = new Response();
        try {
            $sql = "SELECT b.*, u.Id_User, t.First_Name, t.Last_Name 
                    FROM blog_post b 
                    INNER JOIN system_users u ON b.Id_User = u.Id_User 
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE b.Id_Trip = ? AND b.Is_Public = TRUE AND b.Moderation_Status = 'Aprobado' 
                    ORDER BY b.Id_Post DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$idTrip]);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($posts as &$p) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ?";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$p['Id_Post']]);
                $p['gallery'] = $stmtImg->fetchAll(PDO::FETCH_COLUMN);
            }
            return $response->responseMessageArray(true, "Posts del viaje obtenidos", $posts);
        } catch (PDOException $e) { return $response->structMessageErrorCustom($e->getMessage()); }
    }

public function getAllPublicPosts() {
        $response = new Response();
        try {
            $sql = "SELECT b.*, t.First_Name, t.Last_Name 
                    FROM blog_post b 
                    INNER JOIN system_users u ON b.Id_User = u.Id_User 
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE b.Is_Public = TRUE AND b.Moderation_Status = 'Aprobado' 
                    ORDER BY b.Id_Post DESC LIMIT 20"; 
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($posts as &$p) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ?";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$p['Id_Post']]);
                $p['gallery'] = $stmtImg->fetchAll(PDO::FETCH_COLUMN);
            }

            return $response->responseMessageArray(true, "Posts públicos obtenidos", $posts);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }
    public function getAllPendingPosts() {
        $response = new Response();
        try {
            $sql = "SELECT b.*, t.First_Name, t.Last_Name 
                    FROM blog_post b 
                    INNER JOIN system_users u ON b.Id_User = u.Id_User 
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE b.Moderation_Status = 'Pendiente' ORDER BY b.Id_Post ASC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($posts as &$p) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ?";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$p['Id_Post']]);
                $p['gallery'] = $stmtImg->fetchAll(PDO::FETCH_COLUMN);
            }
            return $response->responseMessageArray(true, "Posts pendientes recuperados", $posts);
        } catch (PDOException $e) { return $response->structMessageErrorCustom($e->getMessage()); }
    }

    public function moderatePost($idPost, $status, $isPublic) {
        $response = new Response();
        try {
            $sql = "UPDATE blog_post SET Moderation_Status = ?, Is_Public = ? WHERE Id_Post = ?";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([$status, (int)$isPublic, $idPost]);
            return $response->responseMessageArray($success, "Moderación procesada correctamente", null);
        } catch (PDOException $e) { return $response->structMessageErrorCustom($e->getMessage()); }
    }
    
    public function getAllPublicPostsApp() {
        $response = new Response();
        try {
            $sql = "SELECT b.*, t.First_Name, t.Last_Name 
                    FROM blog_post b 
                    INNER JOIN system_users u ON b.Id_User = u.Id_User 
                    INNER JOIN travelers t ON u.Id_Traveler = t.Id_Traveler
                    WHERE b.Is_Public = TRUE AND b.Moderation_Status = 'Aprobado' 
                    ORDER BY b.Id_Post DESC"; 
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($posts as &$p) {
                $sqlImg = "SELECT Url_File FROM blog_multimedia WHERE Id_Post = ?";
                $stmtImg = $this->connection->prepare($sqlImg);
                $stmtImg->execute([$p['Id_Post']]);
                $p['gallery'] = $stmtImg->fetchAll(PDO::FETCH_COLUMN);
            }

            return $response->responseMessageArray(true, "Catálogo completo de posts obtenido para la App", $posts);
        } catch (PDOException $e) {
            return $response->structMessageErrorCustom($e->getMessage());
        }
    }
}
?>