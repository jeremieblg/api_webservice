<?php
class Database{
    
    // Donn�es de connexion
    private $host = "db";
    private $db_name = "api";
    private $username = "root";
    private $password = "root";
    public $conn;
    
    // Connexion � la BDD
    public function getConnection(){
        
        $this->conn = null;
        
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Erreur de connexion : " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
?>