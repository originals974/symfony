<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;
use Symfony\Component\Yaml\Dumper;

//Custom classes
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
     * @param Int $start EntityClass id start
     * @param Int $end EntityClass id end
     *
     * @return String $elasticaYamlConfig
     */
    public function updateElasticaConfigFile($start, $end){

        $dumper = new Dumper();

        //Get default elastica config
        $elasticaArrayConfig = $this->getElasticaDefaultConfigArray(); 

        $typeArray = array();

        for ($i = $start; $i <=$end; $i++) {

            $entityClassName = 'EntityClass'.$i; 
            
            //Get types elastica config
            $typeArray = $this->getElasticaTypeConfigArray($entityClassName);

            $elasticaArrayConfig['fos_elastica']['indexes']['slcore']['types'][$entityClassName] = $typeArray;
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
     * @param String $entityClassName
     *
     * @return array $elasticaArrayConfig
     */
    private function getElasticaTypeConfigArray($entityClassName) {

        $typeArray = array(
                    'persistence' => array(
                        'driver' => 'orm',
                        'model' => $this->bundlePath.'\\Entity\\'.$entityClassName,
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
    public function entitiesToJSTreeData(array &$data, $entities) {

        foreach($entities as $entity){
            $this->entitieToJSTreeData($data, $entity);
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
    public function entitieToJSTreeData(array &$data, $entity) {

        $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->find($entity->getEntityClassId());

        $node = array(); 
        $node['text'] = $this->jsTreeService->shortenTextNode($entity->getDisplayName(),50); 
        $node['icon'] = 'fa '.$entityClass->getIcon();
        $node['li_attr'] = array(
            'guid' => $entity->getGuid(),
        );
        $node['a_attr'] = array(
            'href' => $this->router->generate('front_show', array(
                'id' => $entity->getEntityClassId(),
                'entity_id' => $entity->getId(),
                )
            ),
        );

        $propertiesEntity = $this->em->getRepository('SLCoreBundle:Property')
                               ->findPropertyEntityByEntityClass($entityClass);

        foreach($propertiesEntity as $propertyEntity){

            if($propertyEntity->isMultiple()){

                $collection = $entity->{"get".$propertyEntity->getTechnicalName()}();
                if($collection != null) {
                    $node['children'] = array();  
                    $this->entitiesToJSTreeData($node['children'], $collection); 
                }
            }
            else {
                $subEntity = $entity->{"get".$propertyEntity->getTechnicalName()}();
                if($subEntity != null) {
                    $node['children'] = array();  
                    $this->entitieToJSTreeData($node['children'], $subEntity); 
                }
            }
        }
        $data[] = $node;  
    }
}
