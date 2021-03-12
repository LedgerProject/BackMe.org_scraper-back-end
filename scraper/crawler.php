<?php
ini_set('max_execution_time', '300');
require_once ('config.php');
include_once ('database.php');
require_once ('api/v1/shared/utilities.php'); 
require_once ('libs/simple_html_dom/simple_html_dom.php');


$utilities = new Utilities();
//$source = $config['sources']['geenstijl.nl'];
//$source = $config['sources']['nos.nl'];
$feeds = array();
function scrapeSource($feeds){
    global $config;
    global $utilities;
    shuffle($feeds);
    echo 'We are going to scrape:<br><pre>';
    print_r($feeds);
    echo '</pre><br><br><br>';
    foreach ($feeds as $item) {
        echo $item . '<br>' ;
        //Call API
        $randomWait = random_int (0, 1);
        sleep($randomWait);
        echo 'waiting '.$randomWait.'sec. <br>';
        $result = $utilities->callAPI('POST', $config['urls']['baseUrl'].'/scraper/api/v1/search', json_encode(array('url'=>$item)));
        echo '<pre>';
        print_r($result);
        echo '</pre>';
        //$result = json_decode($result);
        //echo $result->result->status;
        echo '<br><br>' ;
    }
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

//You can crawl a specific source by passing the source name in get: crawler.php?site=source_ext
if( isset($_GET['site']) ){
    $site = strtolower(str_replace('_', '.', $_GET['site']));
    if(array_key_exists($site, $config['sources']) ){
       $singleCrawl = true;
       $source = $config['sources'][$site];
    }
}
if($singleCrawl){
    parseSource($source);
    scrapeSource($feeds);
}else{
    foreach($config['sources'] as $source){
        parseSource($source);
    }
    scrapeSource($feeds);
}

?>