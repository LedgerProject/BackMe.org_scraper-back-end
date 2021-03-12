<?php
require_once ('./scraper/config.php');
require_once ('./scraper/database.php');

$database = new Database($config);
$db = $database->getConnection();

$article_found = false;


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
       
        <div class="container">
        <section class="row">
            <div class="col-12">
            <?php
            if( isset($_GET['uid']) ){
                $sql = "SELECT * FROM ale_articles WHERE uid=? LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->bind_param('s', $_GET['uid']);
                $success = $stmt->execute();
                $sql_result = $stmt->get_result();
                while ($row = $sql_result->fetch_assoc()) {
                    $sql_results[] = $row;
                }
                $article = $sql_results[0];
                $stmt->close();
                if( !empty($article) ){
                    ?>
                    <?php
                    $sql = "SELECT ale_articles.*, ale_revisions.* FROM ale_articles LEFT JOIN ale_revisions ON ale_articles.id=ale_revisions.id_article WHERE ale_articles.uid=? ORDER BY ale_revisions.scrape_date ASC";
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param('s', $_GET['uid']);
                    $success = $stmt->execute();
                    $sql_result = $stmt->get_result();
                    $rev = 0;
                    while ($row = $sql_result->fetch_assoc()) {
                        if($rev == 0){
                            $ref = $row['id'];
                        }
                        ?>
                        <div class="mb-5">
                            <p>Fetched: <?php echo $row['scrape_date'];?> | <?php echo '(<a href="https://'.$row['site'].$row['url'].'" target="_blank">source</a>)'; ?></p>
                            <div id="<?php echo $row['id'];?>">
                                <h1><?php echo $row['title'];?></h1>
                                <div class="content"><?php echo $row['content'];?></div>
                            </div>
                        </div>
                        <?php
                        
                        if($rev > 0){
                        ?>
                        <script>
                        $(document).ready(function () {
                            output = htmldiff($("#<?php echo $ref;?>").html(), $("#<?php echo $row['id'];?>").html());
                            $("#<?php echo $row['id'];?>").html(output);
                        });
                        </script>
                    <?php
                        }
                        $rev++;
                    }
                    $article_found = true;
                    $stmt->close();
                }
            }
            ?>

            </div>
        </section>
        </div>
<?php if(!$article_found){ ?>
        <div class="container-fluid searchRow">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <p>
                            Currently monitored sources:
                        </p>
                        <ul class="list-unstyled list-inline">
                            <?php
                            foreach($config['sources'] as $source){
                                echo '<li class="list-inline-item mr-5"><a href="./source/'.$source['parser'].'"><img src="img/source-'.$source['parser'].'.svg" height="30"></a></li>';
                            }
                            ?>
                            
                        </ul>
                        <p>
                            You can copy/paste any article's url from the above sources to check its history.
                        </p>
                    </div>
                    <div class="col-12">
                        <form class="form-search row" action="scraper/api/v1/search" id="form-search" method="post">
                            <div class="col-8 col-md-10">
                                <label for="inputEmail" class="sr-only">Article url:</label>	
                                <input name="url" type="text" value="<?=$url;?>" placeholder="i.e.: https://.../title" class="w-100" />
                            </div>
                            <div class="col-4 col-md-2">
                                <button class="btn w-100" type="submit">Search</button>
                            </div>
                        </form>
                    </div>
                    <div id="founds" class="col-12 mt-3 d-none">
                            Search an article's url to check if we have it.
                    </div>

                    
                        
                </div>
            </div>
        </div>

        <?php
            $sql = "SELECT COUNT(*) tot FROM ale_revisions GROUP BY id_article HAVING tot > 1";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute();
            $sql_result = $stmt->get_result();
            $totRevisions = 0;
            while ($row = $sql_result->fetch_assoc()) {
                $totRevisions += $row['tot'];
            }
            $stmt->close();
            $sql = "SELECT COUNT(id) tot FROM ale_articles";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute();
            $sql_result = $stmt->get_result();
            $totArticles = 0;
            while ($row = $sql_result->fetch_assoc()) {
                $totArticles += $row['tot'];
            }
            $stmt->close();
        ?>
        <section class="container mt-5">
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="counter">
                        <span>Sources</span>
                        <?php echo number_format(count($config['sources']), 0, ',', '.'); ?>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="counter">
                        <span>Articles</span>
                        <?php echo number_format($totArticles, 0, ',', '.'); ?>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="counter">
                        <span>Revisions</span>
                        <?php echo number_format($totRevisions, 0, ',', '.'); ?>
                    </div>
                </div>
            </div>
        </section>


        <section class="container mt-5">
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="homeBox">
                        <h3>Latest articles</h3>
                        <ul class="list-unstyled">
                        <?php
                        $sql = "SELECT ale_articles.*, ale_revisions.* FROM ale_articles LEFT JOIN ale_revisions ON ale_articles.id=ale_revisions.id_article AND ale_revisions.id = (SELECT MAX(id) FROM ale_revisions WHERE id_article=ale_articles.id) ORDER BY ale_revisions.scrape_date DESC LIMIT 10";
                        $stmt = $db->prepare($sql);
                        $success = $stmt->execute();
                        $sql_result = $stmt->get_result();
                        while ($row = $sql_result->fetch_assoc()) {
                            echo '<li><img src="img/source-'.$config["sources"][strtolower($row['site'])]['parser'].'-favicon.png" height="24"> <a href="./article/'.$row['uid'].'">'.shortTitles($row['title'],6).'</a> <a href="https://'.$row['site'].$row['url'].'" target="_blank" rel="nofollow" class="source">source</a></li>';
                        }
                        $stmt->close();
                        ?>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="homeBox">
                        <h3>Latest revisions</h3>
                        <ul class="list-unstyled">
                        <?php
                        $sql = "SELECT ale_articles.*, ale_revisions.*
                        FROM ale_articles
                        INNER JOIN ale_revisions ON ale_articles.id=ale_revisions.id_article AND ale_revisions.id = (SELECT MAX(id) FROM ale_revisions WHERE id_article=ale_articles.id) AND ale_articles.id IN (SELECT id_article FROM ale_revisions GROUP BY id_article HAVING COUNT(*)>1)
                        ORDER BY ale_revisions.scrape_date DESC LIMIT 10";
                        $stmt = $db->prepare($sql);
                        $success = $stmt->execute();
                        $sql_result = $stmt->get_result();
                        while ($row = $sql_result->fetch_assoc()) {
                            echo '<li><img src="img/source-'.$config["sources"][strtolower($row['site'])]['parser'].'-favicon.png" height="24"> <a href="./article/'.$row['uid'].'">'.shortTitles($row['title'],6).'</a> <a href="https://'.$row['site'].$row['url'].'" target="_blank" rel="nofollow" class="source">source</a></li>';
                        }
                        $stmt->close();
                        ?>
                        </ul>  
                    </div>
                    
                </div>
            </div>
        </section>

<?php }//end if article_found ?>

<?php include('footer.php'); ?>

