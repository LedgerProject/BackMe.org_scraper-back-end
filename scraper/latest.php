<?php
require_once ('config.php');
include_once ('database.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$utilities = new Utilities();
$database = new Database($config);
$db = $database->getConnection();


$sql = "SELECT a.id, a.uid, a.url, r.id AS revID, r.title_hash, r.content_hash
FROM `ale_articles` AS a INNER JOIN `ale_revisions` AS r ON a.`id`=r.`id_article`
WHERE r.`id_transaction` IS NULL ORDER BY a.`first_scrape` ASC LIMIT 50";

$stmt = $db->prepare($sql);
$success = $stmt->execute();
$sql_result = $stmt->get_result();

$pages = array();
while ($row = $sql_result->fetch_assoc()) {
    $item = $row['url'];
    $title = $row['title_hash'];
    $content = $row['content_hash'];
    $pages[$row['uid'].'-'.$row['revID']] = hash("sha256", $item.$title.$content);//sha256 works better with zenroom
}
$stmt->close();

$result = array(
    'articles' => $pages
);

echo json_encode($pages);

?>