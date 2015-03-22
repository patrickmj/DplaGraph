<?php
require 'vendor/autoload.php';
$graphUrl = 'http://api.dp.la/v2/items?q=beer&api_key=7c4895102e58687066d83ac5f8260159';

$sUrl = 'http://localhost/Omeka/api/items';

$jsonld = '
   [ {
      "@context": {
        "@vocab": "http://purl.org/dc/terms/", 
        "LCSH": "http://id.loc.gov/authorities/subjects", 
        "aggregatedDigitalResource": "dpla:aggregatedDigitalResource", 
        "begin": {
          "@id": "dpla:dateRangeStart", 
          "@type": "xsd:date"
        }, 
        "collection": "dpla:aggregation", 
        "coordinates": "dpla:coordinates", 
        "dataProvider": "edm:dataProvider", 
        "dpla": "http://dp.la/terms/", 
        "edm": "http://www.europeana.eu/schemas/edm/", 
        "end": {
          "@id": "dpla:dateRangeEnd", 
          "@type": "xsd:date"
        }, 
        "hasView": "edm:hasView", 
        "isShownAt": "edm:isShownAt", 
        "name": "xsd:string", 
        "object": "edm:object", 
        "originalRecord": "dpla:originalRecord", 
        "provider": "edm:provider", 
        "sourceResource": "edm:sourceResource", 
        "state": "dpla:state", 
        "stateLocatedIn": "dpla:stateLocatedIn"
      }, 
      "@id": "http://dp.la/api/items/2709fce6f5fd80225fef5083c86d4bac", 
      "_id": "virginia--uva-lib:1042454", 
      "_rev": "1-d7cf9de9b01e3856fcac1a6590f426db", 
      "collection": {
        "@id": "http://dp.la/api/collections/virginia--744806"
      }, 
      "dataProvider": "Special Collections, University of Virginia Library, Charlottesville, Va.", 
      "hasView": {
        "@id:": "http://fedoraproxy.lib.virginia.edu/fedora/objects/uva-lib:1042454/methods/djatoka:StaticSDef/getStaticImage", 
        "rights": [
          "For more information about the use of this material, please go to http://search.lib.virginia.edu/terms."
        ]
      }, 
      "id": "2709fce6f5fd80225fef5083c86d4bac", 
      "ingestDate": "2013-03-27T12:55:23.353010", 
      "ingestType": "item", 
      "isShownAt": "http://search.lib.virginia.edu/catalog/uva-lib:1042454", 
      "object": "http://fedoraproxy.lib.virginia.edu/fedora/objects/uva-lib:1042454/methods/djatoka:StaticSDef/getThumbnail", 
      "provider": {
        "@id": "http://dp.la/api/contributor/virginia", 
        "name": "University of Virginia Library"
      }, 
      "score": 1.0898182, 
      "sourceResource": {
        "creator": "Rufus W., Holsinger, 1866-1930", 
        "date": {
          "begin": "1914-08-25", 
          "displayDate": "1914-08-25", 
          "end": "1914-08-25"
        }, 
        "description": "Plate is in good condition.", 
        "extent": "5x7", 
        "format": [
          "Glass negatives"
        ], 
        "identifier": "uva-lib:1042454", 
        "language": {
          "name": "eng"
        }, 
        "rights": [
          "For more information about the use of this material, please go to http://search.lib.virginia.edu/terms."
        ], 
        "subject": [
          {
            "name": "Photography"
          }, 
          {
            "name": "Portraits"
          }, 
          {
            "name": "Costumes and clothes"
          }, 
          {
            "name": "Yodeling"
          }, 
          {
            "name": "Holsinger Studio (Charlottesville, Va.)"
          }
        ], 
        "title": "Alpine Yodellers Tyrolean", 
        "type": "image"
      }
    } ]

';

//$data = file_get_contents($graphUrl);


//$res = 'http://dp.la/api/items/8a592e4e307d96a9be3d99bb41ac825c#sourceResource';
//$graph->addResource($res, 'dc:subject', 'http://subject.example.com');

class EasyRdf_Graph_Dpla extends EasyRdf_Graph
{

    public function loadFromDplaUrl($url)
    {
        $response = file_get_contents($url);
        $this->addFromApiResponse($response);
    }
    
    public function sourceResources()
    {
        $type = 'http://www.openarchives.org/ore/terms/Aggregation';
        $aggs = $this->allOfType($type);
        $sourceResources = array();
        foreach ($aggs as $agg) {
            echo $agg . "<br>";
            //echo $agg->dump();
            $sourceResources[] = $agg->getResource('http://dp.la/terms/SourceResource');
            //$sourceResources[] = $this->getLiteral($agg, 'http://www.europeana.eu/schemas/edm/dataProvider');
            //var_dump($sourceResources); die();
            //$sourceResources[] = $this->getResource($agg, 'http://www.europeana.eu/schemas/edm/isShownAt');
        }
        
        //$byProperySourceResources = $this->resourcesMatching('http://www.europeana.eu/schemas/edm/sourceResource');
        //$sourceResources = array_merge($sourceResources, $byProperySourceResources);
       
        
        return $sourceResources;
    }
    /**
     * Extract the literals (as skos:prefLabel) from the collection of BNodes
     * 
     * @return array
     */
    public function dcSubjectLiterals($res)
    {
        $extracted = array();
        $subjects = $this->allResources($res, 'dc11:subject');
        $subjects = array_merge($subjects, $this->allResources($res, 'dc:subject'));
        foreach ($subjects as $subject) {
            $prefLabels = $subject->allLiterals('skos:prefLabel');
            foreach ($prefLabels as $label) {
                $extracted[] = $label->getValue();
            }
        }
        return $extracted;
    }
    
    /**
     * Take a response from DPLA's API and parse the docs into the current graph
     * 
     * @param string $response
     */
    public function addFromApiResponse($response)
    {
        $array = json_decode($response, true);
        $docs = $array['docs'];
        
        $docsJson = json_encode($docs);
        $this->parse($docsJson, 'jsonld');
    }
    
}

$dplaGraph = new EasyRdf_Graph_Dpla();
//$dplaGraph->parse($jsonld, 'jsonld');
$graphUrl = 'http://localhost/DplaGraph/data.json';
$dplaGraph->loadFromDplaUrl($graphUrl);



//sleep(20);
//print_r($dplaGraph->dcSubjectLiterals());


//echo $dplaGraph->serialise('turtle');
//die();

$resources =  $dplaGraph->sourceResources();
var_dump($resources);

die();
$subjects = array();
foreach ($resources as $resource) {
    var_dump($resource);
    $subjects = array_merge($subjects, $dplaGraph->dcSubjectLiterals($resource));
}

print_r($subjects);
//var_dump($resources);

//$turtle = $graph->serialise('turtle');
//echo $turtle;

/*
$me = $foaf->primaryTopic();
echo "My name is: ".$me->get('foaf:name')."\n";

*/