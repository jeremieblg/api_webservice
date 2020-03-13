<?php
include_once '../domain/power.php';
include_once '../dao/powerDao.php';
include_once '../service/powerService.php';
include_once './shared/pagination.php';

class PowerController {
    
    private $db;
    private $requestMethod;
    private $powerId;
    
    private $power;
    private $powerService;
    
    public function __construct($db, $requestMethod, $powerId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->powerId = $powerId;
        
        $this->power = new Power();
        $this->powerService = new PowerService($db);
    }
    
    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if($this->powerId){             
                  $response = $this->getPower($this->powerId);
                }else{
                  $response = $this->getPowers();
                }
                break;
            case 'POST':
                $response = $this->createPower();
                break;
            case 'PUT':
                $response = $this->updatePower($this->powerId);
                break;
            case 'DELETE':
                $response = $this->deletePower($this->powerId);
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
     *     path="/api/api/powers",
     *     tags={"powers"},
     *     summary="Get all powers",
     *     operationId="getPowers",
     *     @OA\Response(response="200", description="There are powers"),
     *     @OA\Response(response="204", description="No power found")
     * )
     */
    private function getPowers() {
        $stmt;
        $stmt = $this->powerService->findAll();
        $nb = $stmt->rowCount();
        
        // check if more than 0 record found
        if($nb>0){
            
            // Powers tab
            $powers_arr["records"]=array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                
                $power_item=array(
                    "power_id" => $power_id,
                    "character_id" => $character_id,
                    "power_name" => $power_name
                );
                
                array_push($powers_arr["records"], $power_item);
            }
            
            // set response code - 200 OK
            http_response_code(200);
            
            $response['body'] = json_encode($powers_arr);
            return $response;
            
        }else{
            
            // set response code - 404 Not found // ou 204
            http_response_code(404);
            
            // tell the user no products found
            $response['body'] = json_encode(array("message" => "No power found!"));
            return $response;
        }
    }
    /**
     * @OA\Get(
     *     path="/api/api/powers/{power_id}",
     *     tags={"powers"},
     *     summary="Get one power",
     *     operationId="getPower",
     *     @OA\Parameter(
     *         name="power_id",
     *         in="path",
     *         description="power_id",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         ),
     *     ),
     *     @OA\Response(response="200", description="There are powers"),
     *     @OA\Response(response="204", description="No power found")
     * )
     */
    private function getPower($powerId) {
        $powerId = (int) $powerId;
        if($powerId != 0) {
          $this->power=$this->powerService->find($powerId);
          
          // check if more than 0 record found
          if($this->power->power_id !==null){
              
              // Power
              $power_arr=array(
                "power_id" => $this->power->power_id,
                "character_id" => $this->power->character_id,
                "power_name" => $this->power->  power_name
              );
                            
              // set response code - 200 OK
              http_response_code(200);
              
              $response['body'] = json_encode($power_arr);
              return $response;
              
          }else{
            // set response code - 404 Not found // ou 204
            http_response_code(404);
            
            // tell the user no products found
            $response['body'] = json_encode(array("message" => "No power found"));
            return $response;
          }

        }else{
            
            // set response code - 404 Not found // ou 204
            http_response_code(400);
            
            // tell the user no products found
            $response['body'] = json_encode(array("message" => "Invalid parameter"));
            return $response;
        }
    }
    /**
     * @OA\Post(
     *     path="/api/api/powers",
     *     tags={"powers"},
     *     summary="Create a powers",
     *     operationId="createPower",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="character_id",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="power_name",
     *                     type="string",
     *                 ),
     *                 example={"character_id": "2", "power_name": "Force"}
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
    private function createPower() {
      // Read brut data from request body
      $data = json_decode(file_get_contents("php://input"));
      
      // control
      if (
          !empty($data->character_id) &&
          !empty($data->power_name)
          ) {
              
              $this->power->character_id = $data->character_id;
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
     * @OA\Put(
     *     path="/api/api/powers/{power_id}",
     *     tags={"powers"},
     *     summary="Create a powers",
     *     operationId="updatePower",
     *     @OA\Parameter(
     *         name="power_id",
     *         in="path",
     *         description="Power id to update",
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
     *                     property="character_id",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="power_name",
     *                     type="string",
     *                 ),
     *                 example={"character_id": "2", "power_name": "Force"}
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
     *         description="Power not found",
     *     ),
     *     @OA\Response(
     *          response="503",
     *          description="Update KO"
     *     ),
     * )
     */
    private function updatePower($id) {
      $id = (int) $id; 
      // Read brut data from request body
      $data = json_decode(file_get_contents("php://input"));
      
      // control
      if ($id != 0 &&
          (!empty($data->character_id) &&
          !empty($data->power_name))
          ) {
              
              $this->power->power_id = $id;
              $this->power->character_id = $data->character_id;
              $this->power->power_name = $data->power_name;
              
              // create the character
              if($this->powerService->update($this->power)){
                  // set response code - 200 ok
                  http_response_code(200);
                  $response['body'] = json_encode(array("message" => "power was updated."));
              } else {
                  // set response code - 503 service unavailable
                  http_response_code(503);
                  $response['body'] = json_encode(array("message" => "Unable to update power!"));
              }
      } else {
          // set response code - 400 bad request
          http_response_code(400);
          $response['body'] = json_encode(array("message" => "Unable to update power. Data is incomplete."));
      }
      return $response;
  }
  /**
     * @OA\Delete(
     *     path="/api/api/powers/{power_id}",
     *     tags={"powers"},
     *     summary="Delete a power",
     *     operationId="deletePower",
     *     @OA\Parameter(
     *         name="power_id",
     *         in="path",
     *         description="Power id to delete",
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
    private function deletePower($id) {
        
        $id = (int) $id;
        
        if ($id != 0) {

            // delete the power
            if($this->powerService->delete($id,null)){
                
                // set response code - 200 ok
                http_response_code(200);
                
                // tell the user
                $response['body'] = json_encode(array("message" => "Power was deleted."));
            }else{
                
                // set response code - 503 service unavailable
                http_response_code(503);
                
                // tell the user
                $response['body'] = json_encode(array("message" => "Unable to delete power."));
            }
            return $response;
        
        } else {
            // set response code - 400 bad request
            http_response_code(400);
            $response['body'] = json_encode(array("message" => "Unable to delete power. Wrong parameter."));
            return $response;
        }
    }
}