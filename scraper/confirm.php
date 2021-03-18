<?php
ini_set('max_execution_time', '300');
require_once ('config.php');
include_once ('database.php');
require_once ('api/v1/shared/utilities.php'); 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

//TODO: update the database for the records saved on the blockchain
//exit('allright');
//file_put_contents('payload.txt', file_get_contents("php://input") );
//$payload = json_decode(stripslashes(file_get_contents("php://input")));
$payload = file_get_contents("php://input");
$utilities = new Utilities();
$result = $utilities->callAPI('POST', 'https://apiroom.net/api/superdioz/Aletheia-StoreInSawroom', '{"data":'.$payload.'}');
file_put_contents('payload.txt', $payload/*json_encode(array('data'=>$payload, 'keys'=>''))*/);
file_put_contents('result.txt', $result);

?>