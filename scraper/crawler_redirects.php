<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ini_set('max_execution_time', '300');
require_once ('config.php');
include_once ('database.php');
require_once ('api/v1/shared/utilities.php'); 


$utilities = new Utilities();
$database = new Database($config);
$db = $database->getConnection();

$sql = "SELECT * FROM `ale_articles` WHERE `site` = 'Nos.nl' LIMIT 150 OFFSET 1500";
$stmt = $db->prepare($sql);
$success = $stmt->execute();
$sql_result = $stmt->get_result();
while ($row = $sql_result->fetch_assoc()) {
    $originalUrl = 'https://nos.nl'.$row['url'];
    $newUrl = parse_url($utilities->getUrl($originalUrl), PHP_URL_PATH);
    if($row['url'] != $newUrl){
        echo 'Found one:<br>';
        echo $originalUrl.'<br>';
        echo $newUrl.'<br><br>';
        $sql = "INSERT IGNORE INTO `ale_redirects`(`original_url`, `new_url`, `original_uid`) VALUES (?,?,?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sss', $row['url'], $newUrl, $row['uid']);
        $success = $stmt->execute();
    }
    $randomWait = random_int (0, 1);
    sleep($randomWait);
}
$stmt->close();
?>