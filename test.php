<?php
require 'vendor/autoload.php';
$graphUrl = 'http://api.dp.la/v2/items?q=beer&api_key=7c4895102e58687066d83ac5f8260159';

include('Easy_Rdf_Graph_Dpla.php');

$dplaGraph = new EasyRdf_Graph_Dpla();
//$graphUrl = 'http://localhost/DplaGraph/data.json';
$dplaGraph->loadFromDplaUrl($graphUrl);

$resources =  $dplaGraph->sourceResources();

$subjects = array();
foreach ($resources as $resource) {
    $subjects = array_merge($subjects, $dplaGraph->dcSubjectLiterals($resource));
}

print_r($subjects);
