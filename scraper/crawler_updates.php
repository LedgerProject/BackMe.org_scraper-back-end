<?php
require_once ('config.php');
include_once ('database.php');
require_once ('api/v1/shared/utilities.php'); 
require_once ('libs/simple_html_dom/simple_html_dom.php');


$utilities = new Utilities();
$database = new Database($config);
$db = $database->getConnection();

$sql = "SELECT * FROM `ale_articles` WHERE `first_scrape` BETWEEN DATE_SUB(NOW(), INTERVAL 1 DAY) AND NOW() ORDER BY `ale_articles`.`first_scrape` ASC";
$stmt = $db->prepare($sql);
$success = $stmt->execute();
$sql_result = $stmt->get_result();
while ($row = $sql_result->fetch_assoc()) {
    $item = $row['url'];
    $baseUrl = $config['sources'][strtolower($row['site'])]['baseUrl'];
    $fullLink = $baseUrl.$item;
    echo '<br>'.$fullLink.'<br>';
    echo $config['urls']['baseUrl'].'/scraper/api/v1/search';
    $randomWait = random_int (0, 1);
    sleep($randomWait);
    $result = $utilities->callAPI('POST', $config['urls']['baseUrl'].'/scraper/api/v1/search', json_encode(array('url'=>$fullLink)));
}
$stmt->close();

?>