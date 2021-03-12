<?php
require_once ('../../config.php');
include_once ('../../database.php');
require_once ('shared/utilities.php'); 
require_once ('../../libs/simple_html_dom/simple_html_dom.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$utilities = new Utilities();
$payload = json_decode(file_get_contents("php://input"));

if( !empty($payload->url)
    && $utilities->checkSupport($payload->url) ){

    $domain = str_replace('www.','',parse_url($payload->url)["host"]);
    $scraper_path = '../../modules/'.$config["sources"][$domain]["parser"].'.php';
    require_once ( $scraper_path );
    $database = new Database($config);
    $db = $database->getConnection();

    $url = $payload->url;
    $page_path = parse_url($payload->url)["path"];
    

    ini_set('user_agent', 'Aletheia/0.1');

    $sourceSite = new $config["sources"][$domain]["parser"]();
    $page = $sourceSite->doScraping($url);

    /*foreach($page as $v) {
        echo '<h2>REAL TIME SCRAPING</h2><br>';
        echo '<h1>'.$v['title'].'</h1><br>';
        foreach($v['content'] as $p){
        echo '<p>';
        echo $p;
        echo '</p>';
        }    
    }*/


    if( !empty($page) ){
            
        //Check if this article has ever been scraped
        $sql = "SELECT * FROM ale_articles WHERE site=? AND url=? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss', $config["sources"][$domain]['nicename'], $page_path);
        $success = $stmt->execute();
        $sql_result = $stmt->get_result();
        while ($row = $sql_result->fetch_assoc()) {
            $sql_results[] = $row;
        }
        $article = $sql_results[0];
        $stmt->close();

        if( !empty($article) ){
            $id_article = $article['id'];
        }else{
            $sql = "INSERT INTO ale_articles (`uid`, `site`, `url`, `first_scrape`, `last_scrape`) VALUES (?,?,?, NOW(), NOW())";
            $stmt = $db->prepare($sql);
            $stmt->bind_param('sss', uniqid(), $config["sources"][$domain]['nicename'], $page_path);
            $success = $stmt->execute();
            $id_article = $stmt->insert_id;
            $stmt->close();
        }

        //Check if the content has changed since last scrape
        $title = $page[0]['title'];
        $content = implode($page[0]['content']);

        $sql = "SELECT * FROM `ale_revisions` WHERE id_article=? AND title_hash=? AND content_hash=? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iss', $id_article, md5($title), md5($content));
        $success = $stmt->execute();
        $stmt->store_result();
        $count = $stmt->num_rows;
        $stmt->close();

        $newReview = '';

        if($count < 1){
            $sql = "INSERT INTO `ale_revisions` (`id_article`, `title`, `content`, `title_hash`, `content_hash`, `scrape_date`) VALUES (?,?,?,?,?,NOW() )";

            if($stmt = $db->prepare($sql)) {
                $stmt->bind_param('issss', $id_article, $title, $content, md5($title), md5($content));
                $success = $stmt->execute();
                $newReview = ', new review';
                //update articles last fetched
                $sql = "UPDATE ale_articles SET `last_scrape` = NOW() WHERE id=? LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->bind_param('i', $id_article);
                $success = $stmt->execute();
            } else {
                $error = $db->errno . '-' . $db->error;
                echo $error;
                exit();
            }
        }
        
        //Get article's uid
        $sql = "SELECT uid FROM ale_articles WHERE id=? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $id_article);
        $success = $stmt->execute();
        $stmt->bind_result($uid);
        $stmt->fetch();

        $result = array(
            'result' => array(
                "status" => "ok",
                "code" => 0,
                "msg" => "Page found".$newReview,
                "article" => $page,
                "uid" => $uid
            )
        );
        

    }else{
        $errorMsg = 'Page not found';
        $result = array(
            'result' => array(
                "status" => "nok",
                "code" => 101,
                "msg" => $errorMsg
            )
        );
    }
    http_response_code(201);
    echo json_encode($result);

}else{
    http_response_code(201);
    $result = array(
        'result' => array(
            "status" => "nok",
            "code" => 400,
            "msg" => "Missing or unsupported URL",//$payload//"Bad request"
            "payload" => $payload
        )
    );
    echo json_encode($result);
    //print_r($payload);
}

?>