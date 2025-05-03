<?php
class Database
{
    private static $instance = null;
    private $connection = null;

    private $host = 'localhost';
    private $db_name = 'kadodo_db';
    private $username = 'root';
    private $password = '';

    private function __construct()
    {
        try {
            // Initialiser la connexion
            $this->connection = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

            // Forcer le fuseau horaire à UTC
            $this->connection->exec("SET time_zone = '+00:00'");
            $this->connection->exec("SET NAMES utf8mb4");
        } catch (PDOException $e) {
            error_log("Erreur de connexion : " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}

//-------------------------------------------------------BASE DE DONNEES CONFIG ZONE------------------------------------------------


// Récupération de l'instance unique de la bdd
$dbase = Database::getInstance();
$dbase_connected = $dbase->getConnection();

//-----------------------------------------------------------------------------------------------------------------------------------

//-----------------------------------------------------affichage des tout concernat les actualités---------------------------------------------------------------

$check_annonce = $dbase_connected->query("SELECT annonce.*, utilisateur.*
FROM annonce
JOIN moderateur ON annonce.id_moderateur = moderateur.id_moderateur
JOIN utilisateur ON moderateur.id_utilisateur = utilisateur.id_utilisateur
ORDER BY annonce.date_pub_annonce DESC;");
$get_annonce = $check_annonce->fetchAll(PDO::FETCH_ASSOC);

//-------------------------------------------------------------------------------------------------------------------------------------

//------------------------------------------------affichage de tout sur les blogs--------------------------
$check = $dbase_connected->query("SELECT publication_blog.*, utilisateur.*
    FROM publication_blog
    JOIN moderateur ON publication_blog.id_moderateur = moderateur.id_moderateur
    JOIN utilisateur ON moderateur.id_utilisateur = utilisateur.id_utilisateur
    ORDER BY publication_blog.date_creation DESC;");
$get_mod = $check->fetchAll(PDO::FETCH_ASSOC);
