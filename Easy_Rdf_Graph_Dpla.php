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
            //$sourceResources[] = $agg->getResource('http://dp.la/terms/SourceResource'); //fail
            $sourceResources[] = $agg->getResource(new EasyRdf_Resource('http://dp.la/terms/SourceResource')); //success!
            //$sourceResources[] = $this->getResource($agg, 'http://dp.la/terms/SourceResource'); //fail
            //$sourceResources[] = $this->getSingleProperty($agg, 'http://dp.la/terms/SourceResource', 'resource'); //success!
        }
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
