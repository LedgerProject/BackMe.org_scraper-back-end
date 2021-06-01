<?php
require_once ('config.php');
include_once ('database.php');
require_once ('api/v1/shared/utilities.php'); 

$utilities = new Utilities();
$auth = false;

if( isset($_GET['authKey']) ){
    $code = $_GET['authKey'];
    $auth = $utilities->checkAuth($code);
}
if( isset($argv[1]) ){
    $code = $argv[1];
    $auth = $utilities->checkAuth($code);
}
if(!$auth){
    exit('Nice try.');
}

$database = new Database($config);
$db = $database->getConnection();

$sql = "SELECT a.id, a.uid, a.url, r.id AS revID, r.title_hash, r.content_hash
FROM `ale_articles` AS a LEFT JOIN `ale_revisions` AS r ON a.`id`=r.`id_article`
WHERE r.`id_transaction` IS NULL ORDER BY a.`first_scrape` ASC LIMIT 50";

$stmt = $db->prepare($sql);
$success = $stmt->execute();
$sql_result = $stmt->get_result();
if ( $sql_result->num_rows > 0 ){
    echo $sql_result->num_rows.'<br>';
    $utilities = new Utilities();
    $result = $utilities->callAPI('POST', 'https://yourdomain:3301/api/Aletheia-SignData', '{"data":""}');
    print_r($result);
}
?>