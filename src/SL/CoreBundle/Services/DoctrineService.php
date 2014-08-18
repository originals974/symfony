<?php

namespace SL\CoreBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Doctrine\SLCoreEntityGenerator;
use SL\MasterBundle\Entity\AbstractEntity as MasterAbstractEntity;
use SL\DataBundle\Entity\MappedSuperclass\AbstractEntity as DataAbstractEntity;

/**
 * DoctrineService
 *
 */
class DoctrineService
{
    private $filesystem;
    private $registry;
    private $kernel;
    private $em; 
    private $databaseEm;
    private $dataBundle; 
    private $numberOfVersion; 

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param RegistryInterface $registry
     * @param HttpKernelInterface $kernel
     * @param string $dataBundlePath
     * @param integer $numberOfVersion
     */
    public function __construct(Filesystem $filesystem, RegistryInterface $registry, HttpKernelInterface $kernel, $dataBundlePath, $numberOfVersion)
    {
        $this->filesystem = $filesystem;
        $this->registry = $registry;
        $this->kernel = $kernel; 
        $this->em = $registry->getManager();
        $this->databaseEm = $registry->getManager('database');
        $this->dataBundle = $this->kernel->getBundle(str_replace('/', '', $dataBundlePath)); 
        $this->numberOfVersion = $numberOfVersion;
    }

    /**
     * Generate entity file for $entityClass
     * and update database schema
     *
     * @param EntityClass $entityClass
     *
     * @return void
     */
    public function generateEntityFileAndObjectSchema(EntityClass $entityClass){
        $this->generateEntityFile($entityClass);
        $this->doctrineSchemaUpdateForce();
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
        $entityPath = $this->getDataEntityPath($entityClass->getTechnicalName());
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
        $schemaTool = new SchemaTool($this->databaseEm);

        if($entityClass != null) {
            $metadata = $this->databaseEm->getMetadataFactory()->getMetadataFor();
            $metadatas[] = $metadata; 
        }
        else {
            $metadatas = $this->databaseEm->getMetadataFactory()->getAllMetadata();
        }

        $schemaTool->UpdateSchema($metadatas);  
    }

    /**
     * Generate $mapping for $entityClass
     *
     * @param EntityClass $entityClass
     *
     * @return array $mapping
     */
    public function generateMapping(EntityClass $entityClass) 
    {
        $mapping = array(); 

        foreach ($entityClass->getProperties() as $property) {  

            switch ($property->getFieldType()->getFormType()) {
                case 'entity':

                    $fieldMapping = array(
                        'fieldName' => $property->getTechnicalName(), 
                        'targetEntity' => $this->getDataEntityNamespace($property->getTargetEntityClass()->getTechnicalName()),
                        'versioned' => true,
                        );

                    if($property->isMultiple()){
                        $fieldMapping['mappingType'] = 'manyToMany';
                    }
                    else{
                        $fieldMapping['mappingType'] = 'manyToOne';
                    }

                    break;
                default:

                    $fieldMapping = array(
                        'mappingType' => null,
                        'fieldName' => $property->getTechnicalName(), 
                        'type' => ($property->isMultiple())?'array':$property->getFieldType()->getDataType(),
                        'length' => $property->getFieldType()->getLength(),
                        'nullable' => !$property->isRequired(),
                        'versioned' => true,
                        ); 

                    break;
            }
            $mapping[] = $fieldMapping;
        }

        return $mapping;    
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
        $entityPath = $this->getDataEntityPath($entityClass->getTechnicalName());

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
            $entityGenerator->setClassToExtend('SL\DataBundle\Entity\MappedSuperclass\AbstractEntity'); 
        }
        else{
            $entityNamespace = $this->getDataEntityNamespace($entityClass->getParent()->getTechnicalName());
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

        $entityNamespace = $this->getDataEntityNamespace($entityClass->getTechnicalName());
        $mapping = $this->generateMapping($entityClass);

        $class = new ClassMetadataInfo($entityNamespace);
        $class->customRepositoryClassName = 'SL\DataBundle\Entity\Repository\SharedEntityRepository';

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
            else {
                $class->mapField($fieldMapping);
            }
        }

        return $class; 
    }

    /**
     * Get namespace of $entityName
     *
     * @param string $entityName
     *
     * @return string $entityNamespace
     */
    public function getDataEntityNamespace($entityName)
    {
        $entityNamespace = $this->registry->getAliasNamespace($this->dataBundle->getName()).'\\'.$entityName;
        return $entityNamespace; 
    }

    /**
     * Get path of $entityName
     *
     * @param string $entityName
     *
     * @return string $entityPath
     */
    private function getDataEntityPath($entityName)
    {
        $entityPath = $this->dataBundle->getPath().'/Entity/'.str_replace('\\', '/', $entityName).'.php';
        return $entityPath; 
    }

    /**
     * Delete $entity
     *
     * @param MasterAbstractEntity $entity
     * @param boolean $hardDelete If true, remove entity from database
     * else softdelete entity
     *
     * @return void
     */
    public function entityDelete(MasterAbstractEntity $entity, $hardDelete=false){

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
        $logEntries = $this->databaseEm->getRepository('SLDataBundle:LogEntry')->getLogEntries($entity); 

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
}
