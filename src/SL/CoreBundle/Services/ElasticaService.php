<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\DynamicIndex;

//Custom classes


/**
 * ElasticaService
 *
 */
class ElasticaService
{
    private $em;
    private $elasticaType; 

    public function __construct(EntityManager $em, DynamicIndex $elasticaIndex)
    {
        $this->em = $em; 
        $this->elasticaIndex = $elasticaIndex;
    }

    public function elasticaRequest($searchPattern) 
    {
        // Define a Query. We want a string query.
        $elasticaQueryString  = new \Elastica\Query\QueryString();

        //'And' or 'Or' default : 'Or'
        $elasticaQueryString->setDefaultOperator('AND');
        $elasticaQueryString->setQuery($searchPattern);

        // Create the actual search object with some data.
        $elasticaQuery        = new \Elastica\Query();
        $elasticaQuery->setQuery($elasticaQueryString);

        //Search on the index.
        $elasticaResultSet    = $this->elasticaIndex->search($elasticaQuery);

        return $elasticaResultSet; 
    }

    public function elasticSearchToBootstrapTree(&$array) {

        //Get rules configuration for elestica results array convertion
        $configurations = $this->rulesConfiguration(); 

        //Convert elastica results array to bootstrap tree dataset
        $this->arrayFormat(
            $array, 
            $configurations['keysToKeep'], 
            $configurations['keysToRename'], 
            $configurations['nodeToCreate'],
            $configurations['keysTranslationTable'],
            $configurations['iconTable']
        ); 

        return $array; 
    }

    private function rulesConfiguration() {

        //Variables Initialisation
        $keysToKeep = array('id', 'object_technical_name', 'nodes', 'icon'); 
        $keysToRename = array('display_name'); 
        $nodeToCreate = array(); 
        $keysTranslationTable = array(
            'display_name' => 'text',
        ); 
        $iconTable = array(); 

        //Get all application objects
        $objects = $this->em->getRepository('SLCoreBundle:Object')->findAll();

        foreach($objects as $object) {
            array_push($nodeToCreate, strtolower($object->getTechnicalName())); 

            //Create Icon table
            $iconTable[strtolower($object->getTechnicalName())] = 'glyphicon '.$object->getIcon(); 
        }

        //Add translate keys to keep array
        foreach($keysTranslationTable as $keyTranslationTable) {
            array_push($keysToKeep ,$keyTranslationTable); 
        }

        //Return configuration
        return array(
            'keysToKeep' => $keysToKeep,
            'keysToRename' => $keysToRename,
            'nodeToCreate' => $nodeToCreate, 
            'keysTranslationTable' => $keysTranslationTable,
            'iconTable' => $iconTable,
        );
    }

    /*private function arrayFormat(&$arrays, $keysToKeep, $keysToRename, $nodeToCreate, $keysTranslationTable, $iconTable)
    {
        if(is_array($arrays))
        {
            foreach($arrays as &$array)
            {
                if(is_array($array))
                {
                    foreach($array as $key=>&$arrayElement)
                    { 
                        //Rename key
                        if(in_array($key, $keysToRename))
                        {
                            $newKey = $keysTranslationTable[$key];

                            $array[$newKey] = $arrayElement;
                            unset($array[$key]); 

                            if(is_array($arrayElement)) {  
                                $this->arrayFormat($array[$newKey], $keysToKeep, $keysToRename, $keysTranslationTable, $iconTable);
                            }
                        }
                        
                        //Remove key
                        if (!in_array($key, $keysToKeep)) {
                            unset($array[$key]);
                        }

                        //Add icon key
                        $array['icon'] = $iconTable[strtolower($array['object_technical_name'])]; 
                    }
                }
            }
        }
    }*/

    public function arrayFormat(&$array, $keysToKeep, $keysToRename, $nodeToCreate, $keysTranslationTable, $iconTable)
    {
        if(is_array($array)){

            foreach($array as $key=>&$value){ 

                if(is_numeric($key)){
                    $this->arrayFormat($value, $keysToKeep, $keysToRename, $nodeToCreate, $keysTranslationTable, $iconTable);
                }
                else { 
                    //Add icon key
                    if(!array_key_exists('icon', $array)) {
                        $array['icon'] = $iconTable[strtolower($array['object_technical_name'])]; 
                    }

                    //Rename key
                    if(in_array($key, $keysToRename))
                    {
                        $newKey = $keysTranslationTable[$key];

                        $array[$newKey] = $value;
                        unset($array[$key]); 
                    }

                    //Remove key
                    if (!in_array($key, $keysToKeep)) {
                        unset($array[$key]);
                    }

                    //Nodes creation
                    if(in_array($key, $nodeToCreate)){

                        if(!array_key_exists('nodes', $array)) {
                            $array['nodes'] = array();  
                        }

                        foreach($value as &$subArray) {
                            array_push($array['nodes'], $subArray); 
                            $this->arrayFormat($array['nodes'], $keysToKeep, $keysToRename, $nodeToCreate, $keysTranslationTable, $iconTable);
                        }
                        unset($array[$key]);
                    }
                }
            } 
        }
    }
}
