<?php
include_once '../domain/character.php';
include_once '../dao/characterDao.php';
include_once '../service/characterService.php';
include_once '../service/powerService.php';
include_once './shared/pagination.php';

class CharacterController {
    
    private $db;
    private $requestMethod;
    private $userId;
    private $keywords;
    private $page;
    
    private $character;
    private $characterService;
    
    public function __construct($db, $requestMethod, $userId, $keywords, $page)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;
        $this->keywords = $keywords;
        $this->page = $page;
        $this->character = new Character();
        $this->characterService = new CharacterService($db);
        $this->powerService = new PowerService($db);

    }
    
    public function processRequest()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = explode( '/', $uri );
        
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->userId) {
                    $response = $this->getCharacter($this->userId);
                } else if ($this->keywords){
                    $response = $this->getCharacters($this->keywords, $this->page);
                } else if ($this->page){
                    $response = $this->getCharacters($this->keywords, $this->page);
                } else {
                    $response = $this->getCharacters(null, null);
                };
                break;
            case 'POST':
                if ($uri[5] === 'powers'){
                    $response = $this->createPowerForCharacter($this->userId);
                }else{
                    $response = $this->createCharacter();
                }
                break;
            case 'PUT':
                $response = $this->updateCharacter($this->userId);
                break;
            case 'DELETE':
                if ($uri[5] === 'powers'){
                    $response = $this->deletePowerForCharacter($this->userId,$this->keywords);
                }else{
                    $response = $this->deleteCharacter($this->userId);
                }
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        
        if ($response['body']) {
            echo $response['body'];
        }
    }
    
    /**
     * @OA\Get(
     *     path="/api/api/characters",
     *     tags={"characters"},
     *     summary="Get all characters",
     *     operationId="getCharacters",
     *     @OA\Parameter(
     *         name="s",
     *         in="query",
     *         description="Keywords",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="number of the requested page",
     *         required=false,
     *     ),
     *     @OA\Response(response="200", description="There are characters"),
     *     @OA\Response(response="204", description="No character found")
     * )
     */
    private function getCharacters($keywords, $page) {
        $stmt;
        
        // home page url
        $home_url="http://localhost:8000/api/api/";
        
        // set number of records per page
        $records_per_page = 2;
        
        if ($keywords) {
            $stmt = $this->characterService->search($keywords);
        } else if ($page) {
            // calculate for the query LIMIT clause
            $from_record_num = ($records_per_page * $page) - $records_per_page;
            $stmt = $this->characterService->findAllWithPaging($from_record_num, $records_per_page);
        } else {
            $stmt = $this->characterService->findAll();
        }
        $nb = $stmt->rowCount();
        
        // check if more than 0 record found
        if($nb>0){
            
            // Characters tab
            $characters_arr["records"]=array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $pow= $this->powerService->findByCharacterId($characters_id);
            
                // power tab
                $powers_arr=array();
                
                while ($row = $pow->fetch(PDO::FETCH_ASSOC)){
                    extract($row);
                    
                    $power_item=array(
                        "power" => "{$home_url}powers/{$power_id}"
                    );
                    
                    array_push($powers_arr, $power_item);
                }
                $character_item=array(
                    "character_id" => $characters_id,
                    "first_name" => $first_name,
                    "last_name" => $last_name,
                    "hero_name" => $hero_name,
                    "age" => $age,
                    "created" => $created,
                    "modified" => $modified,
                    "powers"=>$powers_arr
                );
                
                array_push($characters_arr["records"], $character_item);
            }
            
            if ($page) {
                // include paging
                $pagination = new Pagination();
                $total_rows=$this->characterService->count();
                $page_url="{$home_url}characters";
                $paging=$pagination->getPaging($page, $total_rows, $records_per_page, $page_url);
                $characters_arr["paging"]=$paging;
            }
            
            // set response code - 200 OK
            http_response_code(200);
            
            $response['body'] = json_encode($characters_arr);
            return $response;
            
        }else{
            
            // set response code - 404 Not found // ou 204
            http_response_code(404);
            
            // tell the user no products found
            $response['body'] = json_encode(array("message" => "No character found!"));
            return $response;
        }
    }
    
    /**
     * @OA\Get(
     *     path="/api/api/characters/{characterId}",
     *     tags={"characters"},
     *     summary="Find character by ID",
     *     description="Returns a single character",
     *     operationId="getCharacter",
     *     @OA\Parameter(
     *         name="characterId",
     *         in="path",
     *         description="Character id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Character not found"
     *     ),
     * )
     *
     * @param int $id
     */
    private function getCharacter($id) {
        $home_url="http://localhost:8000/api/api/";

        // verification
        $id = (int) $id;
        if ($id != 0) {
        
            $this->character = $this->characterService->find($id);
            $stmt= $this->powerService->findByCharacterId($id);
            
            // power tab
            $powers_arr=array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                
                $power_item=array(
                    "power" => "{$home_url}powers/{$power_id}"
                );
                
                array_push($powers_arr, $power_item);
            }
            
            if($this->character->character_id!=null){

                $character_arr = array(
                    "character_id" => $this->character->character_id,
                    "first_name" => $this->character->first_name,
                    "last_name" => $this->character->last_name,
                    "hero_name" => $this->character->hero_name,
                    "age" => $this->character->age,
                    "created" => $this->character->created,
                    "modified" => $this->character->modified,
                    "powers"=>$powers_arr
                );
                
                // set response code - 200 OK
                http_response_code(200);
                
                // show character data in json format
                $response['body'] = json_encode($character_arr);
                return $response;
                
            }else{
                
                // set response code - 404 Not found // ou 204
                http_response_code(404);
                
                // tell the user no products found
                $response['body'] = json_encode(array("message" => "No character found!"));
                return $response;
            }
            
        } else {
            // set response code
            http_response_code(400);
            
            // tell the user no products found
            $response['body'] = json_encode(array("message" => "Invalid parameter"));
            return $response;
        }
    }
    
    /**
     * @OA\Post(
     *     path="/api/api/characters",
     *     tags={"characters"},
     *     summary="Create a character",
     *     operationId="createCharacter",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="first_name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="hero_name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="age",
     *                     type="string",
     *                 ),
     *                 example={"first_name": "Bruce", "last_name": "Banner", "hero_name": "Hulk", "age": "38"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response="201",
     *          description="Create OK"
     *     ),
     *     @OA\Response(
     *          response="503",
     *          description="Create KO"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *     ),
     * )
     */
    private function createCharacter() {
        // Read brut data from request body
        $data = json_decode(file_get_contents("php://input"));
        
        // control
        if (
            !empty($data->first_name) &&
            !empty($data->last_name) &&
            !empty($data->hero_name) &&
            !empty($data->age)
            ) {
                
                $this->character->first_name = $data->first_name;
                $this->character->last_name = $data->last_name;
                $this->character->hero_name = $data->hero_name;
                $this->character->age = $data->age;
                
                // create the character
                if($this->characterService->create($this->character)){
                    // set response code - 201 created
                    http_response_code(201);
                    $response['body'] = json_encode(array("message" => "Character was created."));
                } else {
                    // set response code - 503 service unavailable
                    http_response_code(503);
                    $response['body'] = json_encode(array("message" => "Unable to create character!"));
                }
        } else {
            // set response code - 400 bad request
            http_response_code(400);
            $response['body'] = json_encode(array("message" => "Unable to create character. Data is incomplete."));
        }
        return $response;
    }
    
    /**
     * @OA\Put(
     *     path="/api/api/characters/{characterId}",
     *     tags={"characters"},
     *     summary="Update a character",
     *     operationId="updateCharacter",
     *     @OA\Parameter(
     *         name="characterId",
     *         in="path",
     *         description="Character id to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         ),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="first_name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="hero_name",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="age",
     *                     type="string",
     *                 ),
     *                 example={"first_name": "Bruce", "last_name": "Banner", "hero_name": "Hulk", "age": "38"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response="200", 
     *          description="Update OK"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Character not found",
     *     ),
     *     @OA\Response(
     *          response="503",
     *          description="Update KO"
     *     ),
     * )
     */
    private function updateCharacter($id) {
        $id = (int) $id;
        // Read brut data from request body
        $data = json_decode(file_get_contents("php://input"));
        
        // control
        if ($id != 0 
            && 
            (!empty($data->first_name) &&
            !empty($data->last_name) &&
            !empty($data->hero_name) &&
            !empty($data->age))) {
                
            $this->character->character_id = $id;
        
            // set character property values
            $this->character->first_name = $data->first_name;
            $this->character->last_name = $data->last_name;
            $this->character->hero_name = $data->hero_name;
            $this->character->age = $data->age;
                
            // update the character
            if($this->characterService->update($this->character)){
                // set response code - 200 ok
                http_response_code(200);
                $response['body'] = json_encode(array("message" => "Character was updated."));
            } else {
                // set response code - 503 service unavailable
                http_response_code(503);
                $response['body'] = json_encode(array("message" => "Unable to update character."));
            }
            return $response;
        } else {
            // set response code - 400 bad request
            http_response_code(400);
            $response['body'] = json_encode(array("message" => "Unable to update character. Data is incomplete."));
            return $response;
        }
    }
    
    /**
     * @OA\Delete(
     *     path="/api/api/characters/{characterId}",
     *     tags={"characters"},
     *     summary="Delete a character",
     *     operationId="deleteCharacter",
     *     @OA\Parameter(
     *         name="characterId",
     *         in="path",
     *         description="Character id to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         ),
     *     ),
     *     @OA\Response(
     *          response="200", 
     *          description="Delete OK"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Character not found",
     *     ),
     *     @OA\Response(
     *          response="503",
     *          description="Delete KO"
     *     ),
     * )
     */
    private function deleteCharacter($id) {
        
        $id = (int) $id;
        
        if ($id != 0) {

            // delete the character
            if($this->characterService->delete($id)){
                
                // set response code - 200 ok
                http_response_code(200);
                
                // tell the user
                $response['body'] = json_encode(array("message" => "Character was deleted."));
            }else{
                
                // set response code - 503 service unavailable
                http_response_code(503);
                
                // tell the user
                $response['body'] = json_encode(array("message" => "Unable to delete character."));
            }
            return $response;
        
        } else {
            // set response code - 400 bad request
            http_response_code(400);
            $response['body'] = json_encode(array("message" => "Unable to delete character. Wrong parameter."));
            return $response;
        }
    }
    /**
     * @OA\Post(
     *     path="/api/api/characters/{characterId}/powers",
     *     tags={"characters"},
     *     summary="Create a power for one character",
     *     operationId="createPowerForCharacter",
     *     @OA\Parameter(
     *         name="characterId",
     *         in="path",
     *         description="character id to add power",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         ),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="power_name",
     *                     type="string",
     *                 ),
     *                 example={"power_name": "vitesse"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response="201",
     *          description="Create OK"
     *     ),
     *     @OA\Response(
     *          response="503",
     *          description="Create KO"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *     ),
     * )
     */
    function createPowerForCharacter($id){
      $id = (int) $id;
      // Read brut data from request body
      $data = json_decode(file_get_contents("php://input"));
      
      // control
      if (!empty($data->power_name)) {
              
              $this->power->character_id = $id;
              $this->power->power_name = $data->power_name;
              
              // create the character
              if($this->powerService->create($this->power)){
                  // set response code - 201 created
                  http_response_code(201);
                  $response['body'] = json_encode(array("message" => "power was created."));
              } else {
                  // set response code - 503 service unavailable
                  http_response_code(503);
                  $response['body'] = json_encode(array("message" => "Unable to create power!"));
              }
      } else {
          // set response code - 400 bad request
          http_response_code(400);
          $response['body'] = json_encode(array("message" => "Unable to create power. Data is incomplete."));
      }
      return $response;
    }
    /**
     * @OA\Delete(
     *     path="/api/api/characters/{characterId}/powers/{powerId}",
     *     tags={"characters"},
     *     summary="Delete a power for one character",
     *     operationId="deletePowerForCharacter",
     *     @OA\Parameter(
     *         name="characterId",
     *         in="path",
     *         description="character id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="powerId",
     *         in="path",
     *         description="Power id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         ),
     *     ),
     *     @OA\Response(
     *          response="201",
     *          description="Delete OK"
     *     ),
     *     @OA\Response(
     *          response="503",
     *          description="Delete KO"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *     ),
     * )
     */
    function deletePowerForCharacter($id,$powerId){  
      $id = (int) $id;      
      // control
      if ($id != 0 && $powerId !=0) {
              // create the character
              if($this->powerService->delete($id,$powerId)){
                  // set response code - 201 created
                  http_response_code(201);
                  $response['body'] = json_encode(array("message" => "power was deleted."));
              } else {
                  // set response code - 503 service unavailable
                  http_response_code(503);
                  $response['body'] = json_encode(array("message" => "Unable to delete power!"));
              }
      } else {
          // set response code - 400 bad request
          http_response_code(400);
          $response['body'] = json_encode(array("message" => "Unable to delete power. Data is incomplete."));
      }
      return $response;
    }
    
    
    private function notFoundResponse() {
        // set response code - 404 Not found
        http_response_code(404);
        
        // tell the user characters does not exist
        $response['body'] = json_encode(array("message" => "No operation found!"));
        
        return $response;
    }
}