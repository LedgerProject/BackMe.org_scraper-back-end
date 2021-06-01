<?php
require_once ('config.php');
include_once ('database.php');
require_once ('api/v1/shared/utilities.php'); 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$payload = file_get_contents("php://input");
$utilities = new Utilities();
$database = new Database($config);
$db = $database->getConnection();

$aletheiaLastSigned = json_encode(
                                    array(
                                        'data'=>array(
                                            'aletheiaLastSigned' => json_decode($payload, true)
                                            )
                                        )
                                    );

$result = $utilities->callAPI('POST', 'https://yourdomain:3301/api/Aletheia-StoreInSawroom-v2', $aletheiaLastSigned);

$tid = $result['aletheiaTransactionID'];
echo $tid;

foreach($result['aletheiaLastSigned']['articles'] as $uid_id => $val) {
    $uid = explode("-", $uid_id)[0];
    $id = explode("-", $uid_id)[1];
    $sql = "UPDATE `ale_revisions` SET `data_structure`=?, `id_transaction`=? WHERE `id`=? AND `id_article` = (SELECT id FROM `ale_articles` WHERE uid=?) LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('isis', $config['dataStructure'], $tid, $id, $uid);
    $success = $stmt->execute();
}
$stmt->close();
?> 