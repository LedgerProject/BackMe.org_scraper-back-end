<?php
ini_set('max_execution_time', '420');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once ('config.php');
include_once ('database.php');
require_once ('api/v1/shared/utilities.php'); 


$utilities = new Utilities();
$database = new Database($config);
$feeds = array();
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

function appendToQueue($feeds){
    global $config;
    global $database;

    shuffle($feeds);
    $db = $database->getConnection();
    foreach ($feeds as $item) {
        
        if (filter_var($item, FILTER_VALIDATE_URL) === FALSE) {
            continue;//not a valid url
        }
        $domain = str_replace('www.','',parse_url($item)["host"]);
        if(!isset( parse_url($item)["path"])){
            continue;//not a valid url
        }
        $page_path = parse_url($item)["path"];
        if(empty($page_path)){
            continue;//not a valid url
        }
        //Check if this article has ever been scraped
        $sql = "SELECT * FROM ale_articles WHERE site=? AND url=? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $config["sources"][$domain]['nicename'], $page_path);
        $success = $stmt->execute();
        $stmt->store_result();
        if( $stmt->num_rows == 0){
            echo $item . '<br>' ;
            $sql = "INSERT IGNORE INTO ale_crawling (`url`) VALUES (?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param('s', $item);
            $success = $stmt->execute();
            $id_crawl = $stmt->insert_id;
            echo $id_crawl.'<br>' ;
        }
        $stmt->close();

        
    }
}

function scrapeSource($feeds){
    global $config;
    global $utilities;
    shuffle($feeds);
    //echo 'We are going to scrape:<br><pre>';
    //print_r($feeds);
    //echo '</pre><br><br><br>';
    foreach ($feeds as $item) {
        //echo $item . '<br>' ;
        //Call API
        $randomWait = random_int (0, 1);
        sleep($randomWait);
        //echo 'waiting '.$randomWait.'sec. <br>';
        $result = $utilities->callAPI('POST', $config['urls']['baseUrl'].'/scraper/api/v1/search', json_encode(array('url'=>$item)));
        //echo '<pre>';
        //print_r($result);
        //echo '</pre>';
        //$result = json_decode($result);
        //echo $result->result->status;
        //echo '<br><br>' ;
    }
    return true;
}

function parseSource($source){
    global $feeds;
    $url = $source['feed'];
    $baseUrl = $source['baseUrl'];

    $scraper_path = 'modules/'.$source["parser"].'.php';
    require_once ( $scraper_path );

    $sourceSite = new $source["parser"]();
    $getFeeds = $sourceSite->doCrawling($url);

    if(!empty($getFeeds)){
        foreach ($getFeeds as $item) {
            $feeds[] = $baseUrl.$item;
        } 
    }else{
        //echo 'empty feed :(';
    }
}
$singleCrawl = false;

//You can crawl a specific source by passing the source name in get: crawler.php?site=source_ext or as third parameter in the cron
if( isset($_GET['site']) || isset($argv[3]) ){

    if(isset($_GET['site'])){ $site = $_GET['site']; }
    if(isset($argv[2])){ $site = $argv[3]; }

    $site = strtolower(str_replace('_', '.', $site));
    if(array_key_exists($site, $config['sources']) ){
       $singleCrawl = true;
       $source = $config['sources'][$site];
    }else{
        exit('Unsupported source.');
    }
}
if($singleCrawl){
    parseSource($source);
    appendToQueue($feeds);
    //scrapeSource($feeds);
}else{
    $mode = 'all';
    if(isset($argv[2])){
        $mode = $argv[2];
    }
    if($mode == 'all'){
        foreach($config['sources'] as $source){
            if($source['active']=='yes'){
                parseSource($source);
            } 
        }
        appendToQueue($feeds);
    }else{
        $db = $database->getConnection();
        echo $mode.PHP_EOL;
        $sql = "SELECT * FROM ale_crawling ORDER BY rand() LIMIT ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $mode);
        $success = $stmt->execute();
        $sql_result = $stmt->get_result();
        $count = $sql_result->num_rows;
        //echo 'count:'.$count.PHP_EOL;
        if($count>0){
            $ids = array();
            while ($row = $sql_result->fetch_assoc()) {
                $ids[] = $row['id'];
                $feeds[] = $row['url'];
            }
            //echo '<pre>';
            //print_r($feeds);
            //echo '</pre>';
            $exec = scrapeSource($feeds);
            if($exec){
                $idsFlat = implode(',', $ids);
                //echo $idsFlat;
                $delete = "DELETE FROM ale_crawling WHERE id IN($idsFlat) LIMIT ?";
                //echo $delete;
                $stmt = $db->prepare($delete);
                $stmt->bind_param('i', $mode);
                $success = $stmt->execute();
                $stmt->close();
            }
        }else{
            $stmt->close();
        }

    }
    
}

?>