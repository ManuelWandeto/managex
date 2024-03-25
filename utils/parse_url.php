<?php

function getUrlParts($url) {
    $parsedUrl = parse_url($url);
    $url = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    $origin = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    if(isset($parsedUrl['port'])) {
        $url .= ':'. $parsedUrl['port'];
        $origin .= ':'. $parsedUrl['port'];
    }
    $url.= $parsedUrl['path'];
    
    $queryArr = array();
    if($parsedUrl['query']) {
        parse_str($parsedUrl['query'], $queryArr);
    }
    return ["url" => $url,"query"=> $queryArr, "origin" => $origin];
}