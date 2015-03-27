<?php
require 'vendor/autoload.php';
require 'config.php';

$graphUrl = "http://api.dp.la/v2/items?q=beer&api_key=$key";

include('Easy_Rdf_Graph_Dpla.php');

$dplaGraph = new EasyRdf_Graph_Dpla();
//$graphUrl = 'http://localhost/DplaGraph/data.json';
$dplaGraph->loadFromDplaUrl($graphUrl);

$resources =  $dplaGraph->sourceResources();

$vals = array();
foreach ($resources as $resource) {
    $properties = array( 'dc11:publisher');
    $vals = array_merge($subjects, $dplaGraph->prefLabelsForBNodeObjects($resource, $properties));
}

?>
<pre>
<?php print_r($vals); ?>

<?php // echo $dplaGraph->serialise('turtle'); ?>
</pre>

