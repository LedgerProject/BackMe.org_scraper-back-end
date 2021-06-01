<?php
class Utilities{

   public function checkAuth($code){

      global $config;

      if($code != $config['authKey']){
         return false;
      }else{
         return true;
      }

   }
    
   public function checkSupport($page){

      global $config;

      if(!parse_url($page)){
         return false;
      }
      $site = str_replace('www.','',parse_url($page)["host"]);
      if( array_key_exists($site, $config['sources']) ){
         return true;
      }
      return false;
   }

    public function isLink($url){
        return(bool)preg_match('~HTTP/1\.\d\s+200\s+OK~', @current(get_headers($url)));
    }

    public function getRedirectURL($url, $agent = null){

      global $config;
      if($agent == null){ $agent = $config['agent']; }
      
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_USERAGENT, $agent);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
      $html = curl_exec($ch); 
      $redirectedUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); 
      curl_close($ch); 
      
      return $redirectedUrl;
   }

    public function getURL($url, $agent = null){

        global $config;
        if($agent == null){ $agent = $config['agent']; }

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

    public function callAPI($method, $url, $data){
        $curl = curl_init();
        switch ($method){
           case "POST":
              curl_setopt($curl, CURLOPT_POST, 1);
              if ($data)
                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
              break;
           case "PUT":
              curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
              if ($data)
                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
              break;
           default:
              if ($data)
                 $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
           'Content-Type: application/json',
        ));
         //curl_setopt($curl, CURLOPT_VERBOSE, true);
         //$verbose = fopen('php://temp', 'w+');
         //curl_setopt($curl, CURLOPT_STDERR, $verbose);
        //curl_setopt($curl, CURLOPT_USERAGENT, 'Googlebot-News');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        // EXECUTE:
        $result = curl_exec($curl);
        if($result === FALSE){ $result = array($url); }


         //if ($result === FALSE) {
         //   printf("cUrl error (#%d): %s<br>\n", curl_errno($handle),
         //         htmlspecialchars(curl_error($handle)));
         //}
         //rewind($verbose);
         //$result = stream_get_contents($verbose);

         //echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
         //die("Connection Failure");

        curl_close($curl);
        $response = json_decode($result, true);
        return $response;
     }
 
}
?>