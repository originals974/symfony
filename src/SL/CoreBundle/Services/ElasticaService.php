<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Elastica\Index;
use Symfony\Component\Yaml\Dumper;

//Custom classes
use SL\CoreBundle\Entity\Object;

/**
 * ElasticaService
 *
 */
class ElasticaService
{
    private $em;
    private $elasticaIndex;
    private $bundlePath;
    private $configPath; 
    private $router; 

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param Index $elasticaIndex
     * @param String $bundlePath
     * @param String $configPath   
     * @param $router
     *
     */
    public function __construct(EntityManager $em, Index $elasticaIndex, $bundlePath, $configPath, $router)
    {
        $this->em = $em; 
        $this->elasticaIndex = $elasticaIndex;
        $this->bundlePath = str_replace("/","\\",$bundlePath);
        $this->configPath = $configPath; 
        $this->router = $router; 
    }

    public function updateElasticaConfigFile($start, $end){

        $dumper = new Dumper();

        //Get default elastica config
        $elasticaArrayConfig = $this->getElasticaDefaultConfigArray(); 

        $objects = $this->em->getRepository('SLCoreBundle:Object')->findAll();

        $typeArray = array();

        for ($i = $start; $i <=$end; $i++) {

            $objectName = 'Object'.$i; 
            
            //Get types elastica config
            $typeArray = $this->getElasticaTypeConfigArray($objectName);

            $elasticaArrayConfig['fos_elastica']['indexes']['slcore']['types'][$objectName] = $typeArray;
        }

        //Update elastica config file
        $elasticaYamlConfig = $dumper->dump($elasticaArrayConfig);
        $elasticaYamlConfig = str_replace("chr(126)", chr(126), $elasticaYamlConfig);
        file_put_contents($this->configPath.'elastica.yml', $elasticaYamlConfig);

        return $elasticaYamlConfig;
    }

    private function getElasticaDefaultConfigArray() {

        $elasticaArrayConfig = array(
            'fos_elastica' => array(
                'clients' => array(
                    'default' => array(
                        'host' => 'localhost', 
                        'port' => 9200,
                        ),
                    ),
                'serializer' => array(
                    'callback_class' => 'FOS\ElasticaBundle\Serializer\Callback',
                    'serializer' => 'serializer',
                    ),
                'indexes' => array(
                    'slcore' => array(
                        'client' => 'default',
                        'types' => null,
                        ),
                    ),
                )
            );

        return $elasticaArrayConfig; 
    }

    private function getElasticaTypeConfigArray($objectName) {

        $typeArray = array(
                    'persistence' => array(
                        'driver' => 'orm',
                        'model' => $this->bundlePath.'\\Entity\\'.$objectName,
                        'provider' => 'chr(126)',
                        'listener' => 'chr(126)',
                        'finder' => 'chr(126)',
                        ),
                    ); 

        return $typeArray; 
    }

    public function elasticSearchToJSTree(&$array) {

        $iconTable = $this->getIconTable(); 
        $this->arrayFormat($array, $iconTable); 

        $keyToKeep = array('icon', 'a_attr', 'text', 'children', 'href');
        $this->array_unset_recursive($array, $keyToKeep); 

        return $array; 
    }

    private function getIconTable(){

        $iconTable = array(); 

        //Get all application objects
        $objects = $this->em->getRepository('SLCoreBundle:Object')->findAll();

        foreach($objects as $object) {
            //Create Icon table
            $iconTable[$object->getId()] = 'fa '.$object->getIcon(); 
        }

        return $iconTable; 
    }

    private function arrayFormat(&$array, $iconTable)
    {
        if(is_array($array)){

            foreach($array as $key=>&$value){ 

                if(is_numeric($key)){
                    $this->arrayFormat($value, $iconTable);
                }
                else { 

                    //Rename display_name key
                    if($key == 'display_name') {
                       
                        $array['text'] = $value;
                        //unset($array[$key]);
                    }
                    //Add href attribute
                    elseif($key == 'object_id') {
                        $array['a_attr'] = array(
                            'href' => $this->router->generate('front_show', array(
                                'id' => $array['object_id'],
                                'entity_id' => $array['id'],
                                )
                            )
                        );
                        //unset($array[$key]);
                    }
                    //Children creation 
                    elseif(is_array($array[$key]) && strpos($key, '_entity_property') !==false ) {

                        if(!array_key_exists('children', $array)) {
                            $array['children'] = array();  
                        }

                        foreach($value as &$subArray) {
                            array_push($array['children'], $subArray); 
                            $this->arrayFormat($array['children'], $iconTable);
                        }
                        //unset($array[$key]);
                    }
                    //Remove key
                    /*elseif($key != 'id' && $key != 'icon' && $key != 'a_attr' && $key != 'text' && $key != 'children' && $key != 'object_id') {
                        unset($array[$key]);
                    }*/

                    //Add icon key
                    if(!array_key_exists('icon', $array)) {
                        $array['icon'] = $iconTable[$array['object_id']]; 
                    }
                }
            } 
        }
    }

    private function array_unset_recursive(&$array, $keyToKeep) {
        
        if (!is_array($keyToKeep)) {
            $keyToKeep = array($keyToKeep);
        }
   
        foreach ($array as $key => &$value) {
            if (!in_array($key, $keyToKeep)) {
                unset($array[$key]);
            }
            else if (is_array($value)) {
                $this->array_unset_recursive($value, $keyToKeep);
            }
        }
    }
}
