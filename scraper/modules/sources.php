<?php
$root = dirname(__FILE__);
require_once ($root.'/../libs/readability/Nodes/NodeTrait.php');
require_once ($root.'/../libs/readability/Nodes/NodeUtility.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMDocument.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMAttr.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMCdataSection.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMCharacterData.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMDocumentFragment.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMDocumentType.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMElement.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMEntity.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMEntityReference.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMNode.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMNotation.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMProcessingInstruction.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMText.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMComment.php');
require_once ($root.'/../libs/readability/Nodes/DOM/DOMNodeList.php');
require_once ($root.'/../libs/readability/Readability.php');
require_once ($root.'/../libs/readability/Configuration.php');
require_once ($root.'/../libs/readability/ParseException.php');
use andreskrey\Readability\Readability;
use andreskrey\Readability\Configuration;
use andreskrey\Readability\ParseException;
class Sources{
    protected function getRealURL($url, $agent){

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_exec($ch);
        $response = curl_exec($ch);
        preg_match_all('/^Location:(.*)$/mi', $response, $matches);
        curl_close($ch);

        return !empty($matches[1]) ? trim($matches[1][0]) : $url;
    }

    public function get_http_response_code($url) {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }

    protected function getElementsByClass(&$parentNode, $tagName, $className) {
        $nodes=array();
    
        $childNodeList = $parentNode->getElementsByTagName($tagName);
        for ($i = 0; $i < $childNodeList->length; $i++) {
            $temp = $childNodeList->item($i);
            if (stripos($temp->getAttribute('class'), $className) !== false) {
                $nodes[]=$temp;
            }
        }
    
        return $nodes;
    }

    protected function removeTagsByID($html, $ids) {
        $doc = new DOMDocument();
        $internalErrors = libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_use_internal_errors($internalErrors);
        $xpath = new DOMXPath($doc);
        foreach ($ids as $id) {
            $tags = $xpath->query("//*[@id='$id']");
            foreach ($tags as $tag) {
                $tag->parentNode->removeChild($tag);
            }
        }
        return $doc->saveHTML();
    }

    protected function removeTagsByData($html, $data, $value) {
        $doc = new DOMDocument();
        $internalErrors = libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_use_internal_errors($internalErrors);
        $xpath = new DOMXPath($doc);
        $tags = $xpath->query("//*[@data-".$data."='$value']");
        foreach ($tags as $tag) {
            $tag->parentNode->removeChild($tag);
        }
        return $doc->saveHTML();
    }

    protected function removeTagsByClass($html, $classes) {
        $doc = new DOMDocument();
        $internalErrors = libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_use_internal_errors($internalErrors);
        $xpath = new DOMXPath($doc);
        foreach ($classes as $class) {
            $tags = $xpath->query("//*[@class='$class']");
            foreach ($tags as $tag) {
                $tag->parentNode->removeChild($tag);
            }
        }
        return $doc->saveHTML();
    }

    public function isFree($url){
        return true;
    }
}
class DOMDocumentPlus extends DOMDocument{

    public function getElementsByClassName($dom, $ClassName, $tagName=null) {
        if($tagName){
            $Elements = $dom->getElementsByTagName($tagName);
        }else {
            $Elements = $dom->getElementsByTagName("*");
        }
        $Matched = array();
        for($i=0;$i<$Elements->length;$i++) {
            if($Elements->item($i)->attributes->getNamedItem('class')){
                if($Elements->item($i)->attributes->getNamedItem('class')->nodeValue == $ClassName) {
                    $Matched[]=$Elements->item($i);
                }
            }
        }
        return $Matched;
    }

}
?>
