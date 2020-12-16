<?php
function doScraping($page = '') {

    $html = file_get_html($page);

    foreach($html->find('article') as $article) {
        $item['title'] = trim($article->find('h1', 0)->plaintext);
        foreach($article->find('div.[class*=contentBody_]') as $content) {
            foreach($content->find('p') as $p) {
                $item['content'][] = $p->innertext;
            }
        }
        $scraped[] = $item;
    }
    
    // clean up memory
    $html->clear();
    unset($html);

    return $scraped;
}
?>