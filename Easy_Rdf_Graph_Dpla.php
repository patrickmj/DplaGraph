<?php
class EasyRdf_Graph_Dpla extends EasyRdf_Graph
{
    public function loadFromDplaUrl($url)
    {
        $response = file_get_contents($url); //should probably use Easy_Rdf_Http here
        $this->addFromApiResponse($response);
    }

    public function sourceResources()
    {
        $type = 'http://www.openarchives.org/ore/terms/Aggregation';
        $aggs = $this->allOfType($type);
        $sourceResources = array();
        foreach ($aggs as $agg) {
            $sourceResources[] = $agg->getResource(new EasyRdf_Resource('http://dp.la/terms/SourceResource'));
        }
        return $sourceResources;
    }

    /**
     * Extract the literals (as skos:prefLabel) from the collection of BNodes
     * 
     * @return array
     */
    public function prefLabelsForBNodeObjects($resource, $properties)
    {
        if (is_string($properties)) {
            $properties = array($properties);
        }
        $prefLabelValues = array();
        //objects are expected to be bNodes for resource passed in
        $objects = array();
        foreach ($properties as $property) {
            $objects = array_merge($objects, $this->allResources($resource, $property));
        }
        
        //admittedly weird naming for a foreach, but makes sense
        //coming in are objects of the subject resource passed in
        //each one is being worked on as an RDF _subject_ (usually bNode) in this loop, though
        foreach ($objects as $subject) {
            $prefLabels = $subject->allLiterals('skos:prefLabel');
            foreach ($prefLabels as $label) {
                $prefLabelValues[] = $label->getValue();
            }
        }
        
        $literals = array();
        foreach ($properties as $propery) {
            $literals = array_merge($literals, $this->allLiterals($resource, $property));
        }
        
        foreach ($literals as $literal) {
            $prefLabelValues[] = $literal->getValue();
        }
        return $prefLabelValues;
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
