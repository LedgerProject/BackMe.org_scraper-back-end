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
    "agent" => "Aletheia/0.9",
    "version" => "0.9.0",
    "dataStructure" => 2,
    "authKey" => "SECRET_API_KEY_HERE",
    "sources" => array(
        /*
        //###
        //### To load a module (from ./modules) follow this template:
        "domain.ext" => array(
            "name" => "Name",//ie. CNN
            "nicename" => "Domain.ext",//i.e. CNN.com
            "parser" => "domain_ext",//i.e. cnn_com this is also the name of the class of its relative module
            "baseUrl" => "https://cnn.com",//without slash trail
            "feed" => "protocol://domain.ext/path",//RSS feed
            "fullFeedContent" => boolean,//if use content from rss instead of parsing the html page
            "active" => boolean,//if true the crawler will parse this source
            "lang" => "xx",//ISO two chars language identifier in lowercase, it is just to categorize the sources
        ),
        //###
        */
    )
);
?>