<?php
require_once ('./scraper/config.php');
require_once ('./scraper/database.php');

$database = new Database($config);
$db = $database->getConnection();

if( isset($_GET['site']) ){
    $site = strtolower(str_replace('_', '.', $_GET['site']));
    if(!array_key_exists($site, $config['sources']) ){
        notFound();
    }
}else{
    notFound();
}
function notFound(){
    /*header('HTTP/1.0 404 Not Found');
    readfile('404.php');
    exit();*/
    header('Location: /aletheia/');
}
function shortTitles($string, $wordsreturned){
    $retval = $string;
    $string = preg_replace('/(?<=\S,)(?=\S)/', ' ', $string);
    $string = str_replace("\n", " ", $string);
    $array = explode(" ", $string);
    if (count($array)<=$wordsreturned){
      $retval = $string;
    }else{
      array_splice($array, $wordsreturned);
      $retval = implode(" ", $array)." ...";
    }
    return $retval;
}

?>
<?php include('header.php'); ?>
       

<div class="container-fluid searchRow">
    <div class="container">
        <div class="row">
            <div class="col-12">
                    <?php
                        echo '<img src="../img/source-'.$config['sources'][$site]['parser'].'.svg" height="30">';
                    ?>
                <p>
                    In this page you can have an overview of the news from <?php
                        echo '<a href="https://'.$config['sources'][$site]['nicename'].'" target="_blank">https://'.$config['sources'][$site]['nicename'].'</a>';
                    ?>.
                </p>
            </div>
            <div id="founds" class="col-12 mt-3 d-none">
                    Search an article's url to check if we have it.
            </div>
                
        </div>
    </div>
</div>

<section class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="homeBox">
                <h3>Latest revisions</h3>
                <ul class="list-unstyled">
                <?php
                $sql = "SELECT ale_articles.*, ale_revisions.* 
                FROM ale_articles
                INNER JOIN ale_revisions ON ale_articles.id=ale_revisions.id_article AND ale_revisions.id = (SELECT MAX(id) FROM ale_revisions WHERE id_article=ale_articles.id) AND ale_articles.id IN (SELECT id_article FROM ale_revisions GROUP BY id_article HAVING COUNT(*)>1) AND site=?
                ORDER BY ale_revisions.scrape_date DESC LIMIT 10";
                $stmt = $db->prepare($sql);
                $stmt->bind_param('s', $site);
                $success = $stmt->execute();
                $sql_result = $stmt->get_result();
                while ($row = $sql_result->fetch_assoc()) {
                    $revs = $db->query("SELECT COUNT(*) as revs FROM ale_revisions WHERE id_article=".$row['id_article'])->fetch_object()->revs; 
                    echo '<li><img src="../img/source-'.$config["sources"][strtolower($row['site'])]['parser'].'-favicon.png" height="24"> <a href="../article/'.$row['uid'].'">'.shortTitles($row['title'],12).'</a> '.($revs>0?'<span class="revs">'.$revs.'</span>':'').' <a href="https://'.$row['site'].$row['url'].'" target="_blank" rel="nofollow" class="source">source</a></li>';
                }
                $stmt->close();
                ?>
                </ul>  
            </div>
            
        </div>
    </div>
</section>
<section class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="homeBox">
                <h3>Latest articles</h3>
                <ul class="list-unstyled">
                <?php
                $sql = "SELECT ale_articles.*, ale_revisions.* FROM ale_articles LEFT JOIN ale_revisions ON ale_articles.id=ale_revisions.id_article AND ale_revisions.id = (SELECT MAX(id) FROM ale_revisions WHERE id_article=ale_articles.id) WHERE site=? ORDER BY ale_revisions.scrape_date DESC LIMIT 10";
                $stmt = $db->prepare($sql);
                $stmt->bind_param('s', $site);
                $success = $stmt->execute();
                $sql_result = $stmt->get_result();
                while ($row = $sql_result->fetch_assoc()) {
                    $revs = $db->query("SELECT COUNT(*) as revs FROM ale_revisions WHERE id_article=".$row['id'])->fetch_object()->revs; 
                    echo '<li><img src="../img/source-'.$config["sources"][strtolower($row['site'])]['parser'].'-favicon.png" height="24"> <a href="../article/'.$row['uid'].'">'.shortTitles($row['title'],12).'</a> '.($revs>0?'<span class="revs">'.$revs.'</span>':'').' <a href="https://'.$row['site'].$row['url'].'" target="_blank" rel="nofollow" class="source">source</a></li>';
                }
                $stmt->close();
                ?>
                </ul>  
            </div>
            
        </div>
    </div>
</section>

<?php include('footer.php'); ?>

