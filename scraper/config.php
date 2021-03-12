<?php
$config = array(
    "db" => array(
        "dbname" => "dbname",
        "username" => "username",
        "password" => "password",
        "host" => "localhost"
    ),
    "urls" => array(
        "baseUrl" => "https://domain.ext"
    ),
    "agent" => "Aletheia/0.1 (Everybody is accountable)",
    "sources" => array(
        /*
        //###
        //### To load a module (from ./modules) follow this template:
        "domain.ext" => array(
            "nicename" => "Domain.ext",//i.e. CNN.com
            "parser" => "domain_ext",//i.e. cnn_com this is also the name of the class of its relative module
            "baseUrl" => "https://cnn.com",
            "feed" => "protocol://domain.ext/path",//RSS feed
            "fullFeedContent" => boolean, //if use content from rss instead of parsing the html page
        ),
        //###
        */
    )
);
?>