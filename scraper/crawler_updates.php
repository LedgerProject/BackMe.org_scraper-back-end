<?php
//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);
require_once ('config.php');
include_once ('database.php');
require_once ('api/v1/shared/utilities.php'); 
require_once ('libs/simple_html_dom/simple_html_dom.php');

$utilities = new Utilities();
$database = new Database($config);
$db = $database->getConnection();
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

$frequency = 'today';
if(isset($argv[2])){
    $frequency = $argv[2];
}
switch($frequency){
    case 'today':
        $sql = "SELECT * FROM `ale_articles` WHERE `first_scrape` BETWEEN DATE_SUB(NOW(), INTERVAL 1 DAY) AND NOW() ORDER BY `ale_articles`.`first_scrape` ASC";
    break;
    case '1':
        $sql = "SELECT * FROM `ale_articles` WHERE `first_scrape` BETWEEN DATE_SUB(NOW(), INTERVAL 2 DAY) AND DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY `ale_articles`.`first_scrape` ASC";
    break;
    case '2':
        $sql = "SELECT * FROM `ale_articles` WHERE `first_scrape` BETWEEN DATE_SUB(NOW(), INTERVAL 2 DAY) AND DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY `ale_articles`.`first_scrape` ASC";
    break;
    case '3':
        $sql = "SELECT * FROM `ale_articles` WHERE `first_scrape` BETWEEN DATE_SUB(NOW(), INTERVAL 3 DAY) AND DATE_SUB(NOW(), INTERVAL 2 DAY) ORDER BY `ale_articles`.`first_scrape` ASC";
    break;
    case '7':
        $sql = "SELECT * FROM `ale_articles` WHERE DATE(`first_scrape`) = DATE(DATE_SUB(DATE(NOW()), INTERVAL 7 DAY)) ORDER BY `ale_articles`.`first_scrape` ASC";
    break;
    case '15':
        $sql = "SELECT * FROM `ale_articles` WHERE DATE(`first_scrape`) = DATE(DATE_SUB(DATE(NOW()), INTERVAL 15 DAY)) ORDER BY `ale_articles`.`first_scrape` ASC";
    break;
    case '30':
        $sql = "SELECT * FROM `ale_articles` WHERE DATE(`first_scrape`) = DATE(DATE_SUB(DATE(NOW()), INTERVAL 30 DAY)) ORDER BY `ale_articles`.`first_scrape` ASC";
    break;
    default:
        $sql = "SELECT * FROM `ale_articles` WHERE `first_scrape` BETWEEN DATE_SUB(NOW(), INTERVAL 1 DAY) AND NOW() ORDER BY `ale_articles`.`first_scrape` ASC";
    break;
}
echo 'frequency: '.$frequency.'<br><br>';
$stmt = $db->prepare($sql);
$success = $stmt->execute();
$sql_result = $stmt->get_result();
while ($row = $sql_result->fetch_assoc()) {

    $item = $config['sources'][strtolower($row['site'])]['baseUrl'].$row['url'];
    $sql = "INSERT IGNORE INTO ale_crawling (`url`) VALUES (?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('s', $item);
    $success = $stmt->execute();

    /*
    $fullLink = $baseUrl.$item;
    echo '<br>'.$fullLink.'<br>';
    echo $config['urls']['baseUrl'].'/scraper/api/v1/search';
    $randomWait = random_int (0, 1);
    sleep($randomWait);
    $result = $utilities->callAPI('POST', $config['urls']['baseUrl'].'/scraper/api/v1/search', json_encode(array('url'=>$fullLink)));

    $originalUrl = $fullLink;
    $newUrl = parse_url($utilities->getUrl($originalUrl), PHP_URL_PATH);
    if($item != $newUrl){
        $sql = "INSERT IGNORE INTO `ale_redirects`(`original_url`, `new_url`, `original_uid`) VALUES (?,?,?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sss', $item, $newUrl, $row['uid']);
        $success = $stmt->execute();
    }*/
}
$stmt->close();

?>