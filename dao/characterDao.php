<?php
class CharacterDao{
    
    // Connexion � la BDD et nom de la table
    private $conn;
    private $table_name = "characters";
    
    // Constructeur
    public function __construct($db){
        $this->conn = $db;
    }
    
    // find all characters
    function findAll(){
        
        // select all query
        $query = "SELECT
                characters_id, first_name, last_name, hero_name, age, created, modified
            FROM
                " . $this->table_name . "
            ORDER BY
                created DESC";
        
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // execute query
        $stmt->execute();
        return $stmt;
    }
    
    // search character
    function search($keywords){
        
        // select all query
        $query = "SELECT
                characters_id, first_name, last_name, hero_name, age, created, modified
            FROM
                " . $this->table_name . "
            WHERE
                first_name LIKE ? OR last_name LIKE ? OR hero_name LIKE ?
            ORDER BY
                created DESC";
        
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // sanitize
        $keywords=htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        
        // bind
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);
        
        // execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // used when filling up the update character form
    function find($id){
        
        // query to read single record
        $query = "SELECT
                 characters_id, first_name, last_name, hero_name, age, created, modified
            FROM
                " . $this->table_name . "
            WHERE
                characters_id = ?
            LIMIT
                0,1";
        
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
        
        // bind id of product to be updated
        $stmt->bindParam(1, $id);
        
        // execute query
        $stmt->execute();
        
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row;
    }
    
    // create character
    function create($character){
        
        // query to insert record
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                first_name=:first_name, last_name=:last_name, hero_name=:hero_name, age=:age, created=:created, modified=:modified";
        
        // prepare query
        $stmt = $this->conn->prepare($query);
        
        // sanitize
        $character->first_name=htmlspecialchars(strip_tags($character->first_name));
        $character->last_name=htmlspecialchars(strip_tags($character->last_name));
        $character->hero_name=htmlspecialchars(strip_tags($character->hero_name));
        $character->age=htmlspecialchars(strip_tags($character->age));
        $character->created=htmlspecialchars(strip_tags($character->created));
        $character->modified=htmlspecialchars(strip_tags($character->modified));
        
        // bind values
        $stmt->bindParam(":first_name", $character->first_name);
        $stmt->bindParam(":last_name", $character->last_name);
        $stmt->bindParam(":hero_name", $character->hero_name);
        $stmt->bindParam(":age", $character->age);
        $stmt->bindParam(":created", $character->created);
        $stmt->bindParam(":modified", $character->modified);
        
        // execute query
        if($stmt->execute()){
            return true;
        }
        
        return false;   
    }
    
    // update the character
    function update($character){
        
        // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
                first_name = :first_name,
                last_name = :last_name,
                hero_name = :hero_name,
                age = :age,
                modified = :modified
            WHERE
                characters_id = :characters_id";
        
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        
        // sanitize
        $character->first_name=htmlspecialchars(strip_tags($character->first_name));
        $this->last_name=htmlspecialchars(strip_tags($character->last_name));
        $character->hero_name=htmlspecialchars(strip_tags($character->hero_name));
        $character->age=htmlspecialchars(strip_tags($character->age));
        $character->modified=htmlspecialchars(strip_tags($character->modified));
        $character->character_id=htmlspecialchars(strip_tags($character->character_id));
        
        // bind new values
        $stmt->bindParam(':first_name', $character->first_name);
        $stmt->bindParam(':last_name', $character->last_name);
        $stmt->bindParam(':hero_name', $character->hero_name);
        $stmt->bindParam(':age', $character->age);
        $stmt->bindParam(':modified', $character->modified);
        $stmt->bindParam(':characters_id', $character->character_id);
        
        // execute the query
        $stmt->execute();
        if ($stmt->rowCount() == 1) {
            return true;
        }
        
        return false;
    }
    
    // delete the character
    function delete($id){
        
        // delete query
        $query = "DELETE FROM " . $this->table_name . " WHERE characters_id = ?";
        
        // prepare query
        $stmt = $this->conn->prepare($query);
        
        // sanitize
        $id=htmlspecialchars(strip_tags($id));
        
        // bind id of record to delete
        $stmt->bindParam(1, $id);
        
        // execute query
        if($stmt->execute() && $stmt->rowCount() != 0){
            return true;
        }
        
        return false;
    }
    
    // read characters with pagination
    public function findAllWithPaging($from_record_num, $records_per_page){
        
        // select query
        $query = "SELECT
                characters_id, first_name, last_name, hero_name, age, created, modified
            FROM
                " . $this->table_name . "
            ORDER BY created DESC
            LIMIT ?, ?";
        
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
        
        // bind variable values
        $stmt->bindParam(1, $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);
        
        // execute query
        $stmt->execute();
        
        // return values from database
        return $stmt;
    }
    
    // used for paging characters
    public function count(){
        $query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";
        
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total_rows'];
    }
    
}