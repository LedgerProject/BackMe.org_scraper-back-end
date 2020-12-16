<?php
require_once ('./config.php');
include_once ('./database.php');
require_once ('./api/v1/shared/utilities.php'); 
require_once ('./libs/simple_html_dom/simple_html_dom.php');


$utilities = new Utilities();

echo $config['sources']['nos.nl']['feed'];

$url = $config['sources']['nos.nl']['feed'];

if(@simplexml_load_file($url)){
    $feeds = simplexml_load_file($url);
}else{
    $invalidurl = true;
    exit('invalid url');
}

if(!empty($feeds)){
    foreach ($feeds->channel->item as $item) {
        //debugging items found
        echo $item->link . ' -> ' . $utilities->getURL($item->guid) . '<br>' ;
        //TODO: verify items to rescrape, call api
    }
}else{
    exit('empty feed');
}

?>