<?php

namespace SL\CoreBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Doctrine\SLCoreEntityGenerator;
use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;
use SL\CoreBundle\Entity\MappedSuperclass\DataAbstractEntity;

/**
 * DoctrineService
 *
 */
class DoctrineService
{
    private $filesystem;
    private $registry;
    private $kernel;
    private $uploadableManager; 
    private $container; 
    private $em; 
    private $numberOfVersion; 
    private $initFixturePath; 

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param RegistryInterface $registry
     * @param HttpKernelInterface $kernel
     * @param UploadableManager $uploadableManager
     * @param Container $container
     * @param integer $numberOfVersion
     * @param string $initFixturePath
     */
    public function __construct(
        Filesystem $filesystem, 
        RegistryInterface $registry, 
        HttpKernelInterface $kernel, 
        UploadableManager $uploadableManager, 
        Container $container, 
        $numberOfVersion,
        $initFixturePath
        )
    {
        $this->filesystem = $filesystem;
        $this->registry = $registry;
        $this->kernel = $kernel; 
        $this->em = $registry->getManager();
        $this->uploadableManager = $uploadableManager; 
        $this->container = $container; 
        $this->numberOfVersion = $numberOfVersion;
        $this->initFixturePath = $initFixturePath;
        $this->coreBundle = $this->kernel->getBundle('SLCoreBundle'); 
    }

    /**
    * Remove entity file for $entityClass
    *
    * @param EntityClass $entityClass
    *
    * @return void 
    */
    public function removeEntityFile(EntityClass $entityClass)
    {
        $entityPath = $this->getEntityFilePath($entityClass->getTechnicalName());
        $this->filesystem->remove(array($entityPath));
    }

    /**
    * Update database schema for $entityClass 
    * or all database
    *
    * @param EntityClass $entityClass|null
    *
    * @return void
    */
    public function doctrineSchemaUpdateForce(EntityClass $entityClass = null)
    {
        $schemaTool = new SchemaTool($this->em);
        $metadatas = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->UpdateSchema($metadatas, false);
    }

    /**
     * Generate entity file for $entityClass
     * by using $mapping
     *
     * @param EntityClass $entityClass
     *
     * @return void
     */
    public function generateEntityFile(EntityClass $entityClass)
    {
        $entityPath = $this->getEntityFilePath($entityClass->getTechnicalName());

        //Create entity code
        $entityGenerator = $this->initEntityGenerator($entityClass);
        $class = $this->initClassMetadataInfo($entityClass);
        $entityCode = $entityGenerator->generateEntityClass($class);
        
        //Create entity file
        $this->filesystem->mkdir(dirname($entityPath));
        file_put_contents($entityPath, $entityCode);
    }

    /**
     * Initialize $entityGenerator class
     *
     * @param EntityClass $entityClass
     *
     * @return SLCoreEntityGenerator $entityGenerator
     */
    private function initEntityGenerator(EntityClass $entityClass) {

        $entityGenerator = new SLCoreEntityGenerator();

        $entityGenerator->setGenerateAnnotations(true);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(true);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');
        $entityGenerator->setGenerateAnnotations(true);

        if($entityClass->getParent() === null){
            $entityGenerator->setClassToExtend('SL\CoreBundle\Entity\MappedSuperclass\DataAbstractEntity'); 
        }
        else{
            $entityNamespace = $this->getEntityNamespace($entityClass->getParent()->getTechnicalName());
            $entityGenerator->setClassToExtend($entityNamespace); 
        }

        return $entityGenerator;
    }

    /**
     * Initialize ClassMetadataInfo class
     * for $entityClass
     *
     * @param EntityClass $entityClass
     * 
     * @return ClassMetadataInfo $class
     */
    private function initClassMetadataInfo(EntityClass $entityClass) {

        $entityNamespace = $this->getEntityNamespace($entityClass->getTechnicalName());
        $mapping = $this->generateMapping($entityClass);

        $class = new ClassMetadataInfo($entityNamespace);
        $class->customRepositoryClassName = 'SL\CoreBundle\Entity\Repository\SharedEntityRepository';

        if($entityClass->getParent() === null){
            $class->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_JOINED);
            $class->setDiscriminatorColumn(array(
                'name' => 'discr',
                'type' => 'string',
                'length' => 0,
                )
            ); 
        }

        //Mapped other fields
        foreach ($mapping as $fieldMapping) {

            if($fieldMapping['mappingType'] == 'manyToOne'){
                $class->mapManyToOne($fieldMapping);
            }
            else if($fieldMapping['mappingType'] == 'manyToMany'){
                $class->mapManyToMany($fieldMapping);
            }
            else if($fieldMapping['mappingType'] == 'oneToOne'){
                $class->mapOneToOne($fieldMapping);
            }
            else {
                $class->mapField($fieldMapping);
            }
        }

        return $class; 
    }

    /**
     * Generate $mapping for $entityClass
     *
     * @param EntityClass $entityClass
     *
     * @return array $mapping
     */
    private function generateMapping(EntityClass $entityClass) 
    {
        $mapping = array(); 

        foreach ($entityClass->getProperties() as $property) {  

            $fieldMapping = array(
                'fieldName' => $property->getTechnicalName(), 
                'versioned' => true,
                );

            switch ($property->getFieldType()->getFormType()) {
                case 'entity':

                    $fieldMapping['targetEntity'] = $this->getEntityNamespace($property->getTargetEntityClass()->getTechnicalName());

                    if($property->isMultiple()){
                        $fieldMapping['mappingType'] = 'manyToMany';
                    }
                    else{
                        $fieldMapping['mappingType'] = 'manyToOne';
                    }

                    break;
                case 'file':
                    $fieldMapping['targetEntity'] = 'SL\CoreBundle\Entity\Document';
                    $fieldMapping['mappingType'] = 'oneToOne';
                    $fieldMapping['cascade'] = array('persist','remove');

                    break;
                default:
                    $fieldMapping['mappingType'] = null;
                    $fieldMapping['type'] = ($property->isMultiple())?'array':$property->getFieldType()->getDataType();
                    $fieldMapping['length'] = ($property->isMultiple())?null:$property->getFieldType()->getLength();
                    $fieldMapping['nullable'] = !$property->isRequired();

                    break;
            }
            $mapping[] = $fieldMapping;
        }

        return $mapping;    
    }


    /**
     * Get namespace of $entityName
     *
     * @param string $entityName
     *
     * @return string $entityNamespace
     */
    public function getEntityNamespace($entityName)
    {
        $entityNamespace = $this->registry->getAliasNamespace($this->coreBundle->getName()).'\\Generated\\'.$entityName;
        return $entityNamespace; 
    }

    /**
     * Get path of $entityName entity file
     *
     * @param string $entityName
     *
     * @return string $entityPath
     */
    private function getEntityFilePath($entityName)
    {
        $entityPath = $this->coreBundle->getPath().'/Entity/Generated/'.str_replace('\\', '/', $entityName).'.php';
        return $entityPath; 
    }

    /**
     * Delete $entity
     *
     * @param AbstractEntity $entity
     * @param boolean $hardDelete If true, remove entity from database
     * else softdelete entity
     *
     * @return void
     */
    public function entityDelete(AbstractEntity $entity, $hardDelete=false){

        $this->em->remove($entity);
        $this->em->flush();

        if($hardDelete) {
            $filters = $this->em->getFilters();
            $filters->disable('softdeleteable');

            $entity = $this->em->getRepository($entity->getClass())->find($entity->getId()); 
            $this->em->remove($entity);
            $this->em->flush();
            
            $filters->enable('softdeleteable');
        }
    }

    /**
     * Get last versions for $entity. 
     *
     * @param DataAbstractEntity $entity
     *
     * @return array $formatedLogEntries
     */
    public function getFormatedLogEntries(DataAbstractEntity $entity)
    {
        $logEntries = $this->em->getRepository('SLCoreBundle:LogEntry')->getLogEntries($entity); 

        $formatedLogEntries = array(); 
        foreach(array_reverse($logEntries) as $logEntry){

            $entity = clone $entity; 

            $formatedLogEntry = array(); 
            $formatedLogEntry['version'] = $logEntry->getVersion();
            $formatedLogEntry['action'] = $logEntry->getAction();
            $formatedLogEntry['loggedAt'] = $logEntry->getLoggedAt();

            $entityLogData = $logEntry->getData();
            
            foreach($entityLogData as $key => $value){ 

                $entity->{"set".$key}($value);
            }

            $formatedLogEntry['data'] = $entity;

            $formatedLogEntries[] = $formatedLogEntry;
        }
                
        return array_slice(array_reverse($formatedLogEntries), 0, $this->numberOfVersion); 
    }

    /**
     * Call uploadable manager for stof 
     * uploadable doctrine extension 
     *
     * @param EntityClass $entityClass
     * @param DataAbstractEntity $entity
     *
     * @return void
     */
    public function callUploadableManager(EntityClass $entityClass, DataAbstractEntity $entity)
    {    
        if($entityClass->isDocument()){
            $document = $entity->getDocument(); 
            $this->uploadableManager->markEntityToUpload($document, $document->getFile());
        }

        foreach($entityClass->getProperties() as $property){
            if($property->getFieldType()->getFormType() === 'file'){
                $document = $entity->{'get'.$property->getTechnicalName()}(); 
                if($document->getFile() != null){
                    $this->uploadableManager->markEntityToUpload($document, $document->getFile());
                }
            }
        }
    }

    /**
     * Load init fixtures for application 
     *
     * @return void
     */
    public function loadInitFixture()
    {
        $loader = new DataFixturesLoader($this->container);
        $loader->loadFromDirectory($this->initFixturePath);
        $fixtures = $loader->getFixtures();
        
        $purger = new ORMPurger($this->em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute($fixtures);
    }
}
