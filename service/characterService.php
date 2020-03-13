<?php
class CharacterService{
    
    protected $characterDao;
    
    // Constructeur
    public function __construct($db){
        $this->characterDao = new CharacterDao($db);
    }
    
    // Find all characters
    function findAll(){
        return $this->characterDao->findAll();
    }
    
    // Fin character by id
    function find($id){
        
        $row = $this->characterDao->find($id);
        
        $character = new Character();
        
        // set values to object properties
        $character->character_id = $row['characters_id'];
        $character->first_name = $row['first_name'];
        $character->last_name = $row['last_name'];
        $character->hero_name = $row['hero_name'];
        $character->age = $row['age'];
        $character->created = $row['created'];
        $character->modified = $row['modified'];
        
        return $character;
    }
    
    // create character
    function create($character){
        $character->created = date('Y-m-d H:i:s');
        $character->modified = date('Y-m-d H:i:s');
        return $this->characterDao->create($character);
    }
    
    // update the character
    function update($character){
        $character->modified = date('Y-m-d H:i:s');
        return $this->characterDao->update($character);
    }
    
    // delete the character
    function delete($id){
        return $this->characterDao->delete($id);
    }
    
    // Find all characters
    function search($keywords){
        return $this->characterDao->search($keywords);
    }
    
    // Find characters with pagination
    function findAllWithPaging($from_record_num, $records_per_page) {
        return $this->characterDao->findAllWithPaging($from_record_num, $records_per_page);
    }
    
    // Count number of characters
    function count() {
        return $this->characterDao->count();
    }
    
}

?>