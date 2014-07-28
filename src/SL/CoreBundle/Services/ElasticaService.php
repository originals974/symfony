<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;
use Symfony\Component\Yaml\Dumper;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Services\JSTreeService;

/**
 * ElasticaService
 *
 */
class ElasticaService
{
    private $em;
    private $router;
    private $jsTreeService;
    private $bundlePath;
    private $configPath; 

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param Router $router
     * @param String $bundlePath
     * @param String $configPath   
     */
    public function __construct(EntityManager $em, Router $router, JSTreeService $jsTreeService, $bundlePath, $configPath)
    {
        $this->em = $em; 
        $this->router = $router; 
        $this->jsTreeService = $jsTreeService; 
        $this->bundlePath = str_replace("/","\\",$bundlePath);
        $this->configPath = $configPath;
    }

    /**
     * Update app/config/elastica.yml file
     *
     * @param Int $start Object id start
     * @param Int $end Object id end
     *
     * @return String $elasticaYamlConfig
     */
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

    /**
     * Get elastica default config 
     *
     * @return array $elasticaArrayConfig
     */
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

    /**
     * Get elastica type config 
     *
     * @param String $objectName
     *
     * @return array $elasticaArrayConfig
     */
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

    /**
     * Convert Doctrine Collection to JSTree data
     *
     * @param array $data Result array
     * @param DoctrineCollection $entities
     *
     * @return array $array
     */
    public function EntitiesToJSTreeData(array &$data, $entities) {

        foreach($entities as $entity){
            $this->EntitieToJSTreeData($data, $entity);
        }
    }

    /**
     * Convert Doctrine Entity to JSTree data
     *
     * @param array $data Result array
     * @param Mixed $entity
     *
     * @return array $array
     */
    public function EntitieToJSTreeData(array &$data, $entity) {

        $object = $this->em->getRepository('SLCoreBundle:Object')->find($entity->getObjectId());

        $node = array(); 
        $node['text'] = $this->jsTreeService->shortenTextNode($entity->getDisplayName(),50); 
        $node['icon'] = 'fa '.$object->getIcon();
        $node['li_attr'] = array(
            'guid' => $entity->getGuid(),
        );
        $node['a_attr'] = array(
            'href' => $this->router->generate('front_show', array(
                'id' => $entity->getObjectId(),
                'entity_id' => $entity->getId(),
                )
            ),
        );

        $entityProperties = $this->em->getRepository('SLCoreBundle:Property')
                               ->findEntityPropertyByObject($object);

        foreach($entityProperties as $entityProperty){

            if($entityProperty->isMultiple()){

                $collection = $entity->{"get".$entityProperty->getTechnicalName()}();
                if($collection != null) {
                    $node['children'] = array();  
                    $this->EntitiesToJSTreeData($node['children'], $collection); 
                }
            }
            else {
                $subEntity = $entity->{"get".$entityProperty->getTechnicalName()}();
                if($subEntity != null) {
                    $node['children'] = array();  
                    $this->EntitieToJSTreeData($node['children'], $subEntity); 
                }
            }
        }
        array_push($data, $node);  
    }
}
