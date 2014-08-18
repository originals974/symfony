<?php

namespace SL\CoreBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;
use Symfony\Component\Yaml\Dumper;

use SL\CoreBundle\Services\JSTreeService;
use SL\DataBundle\Entity\MappedSuperclass\AbstractEntity;

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
     * @param integer $start EntityClass id start
     * @param integer $end EntityClass id end
     *
     * @return void
     */
    public function updateElasticaConfigFile($start, $end){

        $dumper = new Dumper();

        $staticElasticaConfig = $this->getStaticElasticaConfig(); 

        $typeElasticaConfig = array();
        for ($i = $start; $i <=$end; $i++) {

            $entityClassName = 'EntityClass'.$i; 
            $typeElasticaConfig = $this->getTypeElasticaConfig($entityClassName);

            $staticElasticaConfig['fos_elastica']['indexes']['slcore']['types'][$entityClassName] = $typeElasticaConfig;
        }

        //Update elastica config file
        $elasticaYamlConfig = $dumper->dump($staticElasticaConfig);
        $elasticaYamlConfig = str_replace("chr(126)", chr(126), $elasticaYamlConfig);
        file_put_contents($this->configPath.'elastica.yml', $elasticaYamlConfig);
    }

    /**
     * Get static elastica config 
     *
     * @return array $staticElasticaConfig
     */
    private function getStaticElasticaConfig() {

        $staticElasticaConfig = array(
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

        return $staticElasticaConfig; 
    }

    /**
     * Get type elastica config 
     *
     * @param string $entityClassName
     *
     * @return array $typeElasticaConfig
     */
    private function getTypeElasticaConfig($entityClassName) {

        $typeElasticaConfig = array(
                    'persistence' => array(
                        'driver' => 'orm',
                        'model' => $this->bundlePath.'\\Entity\\'.$entityClassName,
                        'provider' => 'chr(126)',
                        'listener' => 'chr(126)',
                        'finder' => 'chr(126)',
                        ),
                    ); 

        return $typeElasticaConfig; 
    }

    /**
     * Convert entities $data array to JSTree data
     *
     * @param array $data
     * @param array $entities
     *
     * @return void
     */
    public function entitiesToJSTreeData(array &$data, array $entities) {

        foreach($entities as $entity){
            $this->entityToJSTreeData($data, $entity);
        }
    }

    /**
     * Convert entity $data array to JSTree data
     *
     * @param array $data
     * @param AbstractEntity $entity
     *
     * @return void
     */
    public function entityToJSTreeData(array &$data, AbstractEntity $entity) {

        $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->find($entity->getEntityClassId());

        $node = array(); 
        $node['text'] = $this->jsTreeService->shortenTextNode($entity->getDisplayName(),50); 
        $node['icon'] = 'fa '.$entityClass->getIcon();
        $node['li_attr'] = array(
            'guid' => $entity->getGuid(),
        );
        $node['a_attr'] = array(
            'href' => $this->router->generate('entity_show', array(
                'entity_class_id' => $entity->getEntityClassId(),
                'entity_id' => $entity->getId(),
                'class_namespace' => $entity->getClass(),
                )
            ),
        );

        $propertiesEntity = $this->em->getRepository('SLCoreBundle:EntityClass\Property')
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
