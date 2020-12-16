<?php
require_once ('./scraper/config.php');
require_once ('./scraper/config.php');
require_once ('./scraper/database.php');

$database = new Database($config);
$db = $database->getConnection();

$article_found = false;

?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="Oxjno Web Development - https://oxjno.com">
		<title>Aletheia</title>

		<!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<!-- Custom styles for this template -->
		<link href="css/style.css" rel="stylesheet">

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <!-- Bootstrap JS -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

		
		<script>
            $(document).ready(function () {
                $.fn.serializeObject = function()
                {
                    var o = {};
                    var a = this.serializeArray();
                    $.each(a, function() {
                        if (o[this.name] !== undefined) {
                            if (!o[this.name].push) {
                                o[this.name] = [o[this.name]];
                            }
                            o[this.name].push(this.value || '');
                        } else {
                            o[this.name] = this.value || '';
                        }
                    });
                    return o;
                };
                $("#form-search").submit(function(event){
                    event.preventDefault();
                    $('#founds').empty();
                    var post_url = $(this).attr("action");
                    var request_method = $(this).attr("method");
                    var form_data = $(this).serializeObject();
                    console.log(form_data);
                    $.ajax({
                        url : post_url,
                        type: request_method,
                        data : JSON.stringify(form_data),
                        dataType: 'json',
                        contentType: 'application/json;charset=UTF-8'
                    }).done(function(response){ //
                        console.log('mhm',response);
                        if(response.result.status=='ok'){
                            console.log('response');
                            //window.location.href = '/result';
                            //$('#founds').append('<p>Content from: '+form_data.url+'</p>');
                            //$('#founds').append('<h3>'+response.result.article[0].title+'</h3>');
                            //response.result.article[0].content.forEach( (element, index) => {
                            //    $('#founds').append('<p>'+element+'</p>'); 
                            //});
                            window.location.href = '?uid='+response.result.uid;

                        }else{
                            alert(response.result.msg);
                            console.log('response no');
                        }
                    });
                });

            });
        </script>

	</head>
	<body>
        <div class="container">

        <section class="row">
            <div class="col-12">

            <h1><a href="./">Aletheia</a></h1>
            <h2>Everyone is accountable.</h2>
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
                    $sql = "SELECT ale_articles.*, ale_revisions.* FROM ale_articles LEFT JOIN ale_revisions ON ale_articles.id=ale_revisions.id_article WHERE ale_articles.uid=? ORDER BY ale_revisions.scrape_date DESC";
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param('s', $_GET['uid']);
                    $success = $stmt->execute();
                    $sql_result = $stmt->get_result();
                    while ($row = $sql_result->fetch_assoc()) { ?>
                        <p>Fetched: <?php echo $row['scrape_date'];?> | <?php echo '(<a href="https://'.$row['site'].$row['url'].'" target="_blank">source</a>)'; ?></p>
                        <h1><?php echo $row['title'];?></h1>
                        <p><?php echo $row['content'];?></p>
                    <?php
                    }
                    $article_found = true;
                    $stmt->close();
                }
            }
            if(!$article_found){
            ?>
            <p>
                Supported sources:
            </p>
            <ul>
                <?php
                foreach($config['sources'] as $source){
                    echo '<li><a href="https://'.$source['nicename'].'" target="_blank">'.$source['nicename'].'</a></li>';
                }
                ?>
                
            </ul>
            <p>
                You can copy/paste any article's url from the above sources to check its history.
            </p>
            <hr>
            <form class="form-search" action="scraper/api/v1/search" id="form-search" method="post">
                <label for="inputEmail" class="sr-only">Article url:</label>	
                <input name="url" type="text" value="<?=$url;?>" placeholder="i.e.: https://.../title" />
                <button class="btn my-3 recovery" type="submit">Search</button>
            </form>
            <hr>
            <div id="founds">
                Search an article's url to check if we have it.
            </div>
            <?php }//end if article_found ?>

            </div>
        </section>

        </div>

        <section class="container mt-5">
            <div class="row">
                <div class="col-12">
                <h3>Latest articles</h3>
                <ul>
                <?php
                $sql = "SELECT ale_articles.*, ale_revisions.* FROM ale_articles LEFT JOIN ale_revisions ON ale_articles.id=ale_revisions.id_article AND ale_revisions.id = (SELECT MAX(id) FROM ale_revisions WHERE id_article=ale_articles.id) ORDER BY ale_revisions.scrape_date DESC LIMIT 3";
                $stmt = $db->prepare($sql);
                $success = $stmt->execute();
                $sql_result = $stmt->get_result();
                while ($row = $sql_result->fetch_assoc()) {
                    echo '<li>['.$row['site'].'] <a href="./?uid='.$row['uid'].'">'.$row['title'].'</a> (<a href="https://'.$row['site'].$row['url'].'" target="_blank">source</a>)</li>';
                }
                $stmt->close();
                ?>
                </ul>
                </div>
            </div>
        </section>
        <footer class="container mt-5 mb-2">
            <div class="row">
                <div class="col-12 text-right">
                    Powered by <a href="https://oxjno.com">Oxjno</a>
                </div>
            </div>
        </footer>
		
	</body>
</html>

