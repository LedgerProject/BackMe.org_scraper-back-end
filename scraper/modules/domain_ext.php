<?php
//This is an example of a module.
//File name and class name must match the parser attribute set in the config (first level domain without www and replacing the dot with an underscore.)
require_once ('sources.php');
class domain_ext extends Sources{

    public function doScraping($page = '') {
        $html = new DOMDocument;
        $html->loadHTMLFile($page);
        $html->preserveWhiteSpace = false;
        
        //parse the html content to extract the title
        $item['title'] = $html->getElementsByTagName('h1')[0]->nodeValue;
        //parse the html content to extract the article content
        $finder = new DOMXpath($html);
        $classname = 'longText';
        $article = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]")[0];
        $content=$article->getElementsByTagName('p');
        foreach($content as $p) {
            $item['content'][] = $p->nodeValue;
        }
        $scraped[] = $item;
        unset($html);
        return $scraped;

    }

    public function doCrawling($feedUrl){
        
        //retrive the articles' url from the rss feed
        if(@simplexml_load_file($feedUrl)){
            $xmlFeeds = simplexml_load_file($feedUrl);
            $feed = array();
            foreach ($xmlFeeds->item as $item) {
                $feed[] = parse_url($item->link, PHP_URL_PATH);
            }
            return $feed;
        }else{
            return null;
        }

    }

}
?>