<?php

class PowerService{
    
    protected $powerDao;
    
    // Constructeur
    public function __construct($db){
        $this->powerDao = new PowerDao($db);
    }
    
    // Find all powers
    function findAll(){
        return $this->powerDao->findAll();
    }
    
    // Find power by id
    function find($id){
        
        $row = $this->powerDao->find($id);

        $power = new Power();
        
        // set values to object properties
        $power->power_id = $row['power_id'];
        $power->character_id = $row['character_id'];
        $power->power_name = $row['power_name'];

        return $power;
    }
    // Find power by id
    function findByCharacterId($id){
        return $this->powerDao->findByCharacterId($id);
    }
    
    // create character
    function create($power){
        return $this->powerDao->create($power);
    }
    
    // update the character
    function update($power){
        return $this->powerDao->update($power);
    }
    
    // delete the character
    function delete($power,$id){
        return $this->powerDao->delete($power,$id);
    }
    
    // Find all characters
    function search($keywords){
        return $this->powerDao->search($keywords);
    }
    
    // Find characters with pagination
    function findAllWithPaging($from_record_num, $records_per_page) {
        return $this->powerDao->findAllWithPaging($from_record_num, $records_per_page);
    }
    
    // Count number of characters
    function count() {
        return $this->powerDao->count();
    }
    
}

?>