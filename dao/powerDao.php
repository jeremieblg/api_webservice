<?php
  class PowerDao{
    // Connexion � la BDD et nom de la table
    private $conn;
    private $table_name = "powers";
    
    // Constructeur
    public function __construct($db){
        $this->conn = $db;
    }

    function findAll(){
        
      // select all query
      $query = "SELECT
              power_id, character_id, power_name
          FROM
              " . $this->table_name . "
          ORDER BY power_id DESC";

      // prepare query statement
      $stmt = $this->conn->prepare($query);
      
      // execute query
      $stmt->execute();

      return $stmt;
    }
    function find($id){
        
      // select all query
      $query = "SELECT power_id, character_id, power_name FROM " . $this->table_name . " WHERE power_id = ? LIMIT 0,1";
      // prepare query statement
      $stmt = $this->conn->prepare($query);
      
      // bind
      $stmt->bindParam(1, $id);
      // execute query
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      return $row;
    }
    function findByCharacterId($id){
        
      // select all query
      $query = "SELECT power_id, character_id, power_name FROM " . $this->table_name . " WHERE character_id = ?";
      // prepare query statement
      $stmt = $this->conn->prepare($query);
      
      // bind
      $stmt->bindParam(1, $id);
      // execute query
      $stmt->execute();
      // $row = $stmt->fetch(PDO::FETCH_ASSOC);
      return $stmt;
    }
    // create power
    function create($power){
            
      // query to insert record
      $query = "INSERT INTO " . $this->table_name . " SET character_id=:character_id, power_name=:power_name";
      // prepare query
      
      $stmt = $this->conn->prepare($query);
      
      // sanitize
      $power->character_id=htmlspecialchars(strip_tags($power->character_id));
      $power->power_name=htmlspecialchars(strip_tags($power->power_name));
      
      // bind values
      $stmt->bindParam(":character_id", $power->character_id);
      $stmt->bindParam(":power_name", $power->power_name);
      // execute query
      if($stmt->execute()){
        return true;
      }
      
      return false;   
    }
    // update power
    function update($power){
            
      // query to insert record
      $query = "UPDATE " . $this->table_name . " SET character_id=:character_id, power_name=:power_name WHERE power_id=:power_id";
      // prepare query
      
      $stmt = $this->conn->prepare($query);
      
      // sanitize
      $power->character_id=htmlspecialchars(strip_tags($power->character_id));
      $power->power_name=htmlspecialchars(strip_tags($power->power_name));
      $power->power_id=htmlspecialchars(strip_tags($power->power_id));
      
      // bind values
      $stmt->bindParam(":character_id", $power->character_id);
      $stmt->bindParam(":power_name", $power->power_name);
      $stmt->bindParam(":power_id", $power->power_id);
      // execute the query
      
      if ($stmt->execute()) {
          return true;
      }
      
      return false;   
    }

    // delete the power
    function delete($id,$powerId){
      // delete query
      if($powerId){
        $query = "DELETE FROM " . $this->table_name . " WHERE character_id = :id AND power_id = :powerId";
        // prepare query
        $stmt = $this->conn->prepare($query);
        
        // sanitize
        $id=htmlspecialchars(strip_tags($id));
        $powerId=htmlspecialchars(strip_tags($powerId));

        // bind values
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":powerId", $powerId);
      }else{
        $query = "DELETE FROM " . $this->table_name . " WHERE power_id = ?";
        // prepare query
        $stmt = $this->conn->prepare($query);
        
        // sanitize
        $id=htmlspecialchars(strip_tags($id));
        
        // bind id of record to delete
        $stmt->bindParam(1, $id);
      }

      
      // execute query
      if($stmt->execute()){
          return true;
      }
      
      return false;
    }
    
}