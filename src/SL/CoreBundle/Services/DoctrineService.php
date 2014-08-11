<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

//Custom classes
use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Doctrine\SLCoreEntityGenerator;

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

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param RegistryInterface $registry
     * @param HttpKernelInterface $kernel
     * @param String $dataBundlePath
     */
    public function __construct(Filesystem $filesystem, RegistryInterface $registry, HttpKernelInterface $kernel, $dataBundlePath)
    {
        $this->filesystem = $filesystem;
        $this->registry = $registry;
        $this->kernel = $kernel; 
        $this->em = $registry->getManager();
        $this->databaseEm = $registry->getManager('database');
        $this->dataBundle = $this->kernel->getBundle(str_replace('/', '', $dataBundlePath)); 
    }

    /**
     * Create mapping and entity file for entityClass
     *
     * @param EntityClass\EntityClass $entityClass EntityClass
     *
     * @return Array $mapping Mapping array for entityClass
     */
    public function doctrineGenerateEntityFileByEntityClass(EntityClass $entityClass)
    {
        $mapping = $this->doctrineGenerateMappingByEntityClass($entityClass);
        $this->doctrineGenerateEntityFileByMapping($entityClass, $mapping);

        return $mapping;
    }

    /**
    * Remove entity file for entityClass
    *
    * @param EntityClass\EntityClass $entityClass EntityClass
    */
    public function removeDoctrineFiles(EntityClass $entityClass)
    {
        //Get path of entity class file
        $entityPath = $this->getDataEntityPath($entityClass->getTechnicalName());

        //Remove entity class file
        $this->filesystem->remove(array($entityPath));
    }

    /**
    * Update database schema
    *
    * @return string SQL requests
    */
    public function doctrineSchemaUpdateForce()
    {
        $schemaTool = new SchemaTool($this->databaseEm);

        $metadatas = $this->databaseEm->getMetadataFactory()->getAllMetadata();

        $schemaTool->UpdateSchema($metadatas);  

        return $schemaTool->getUpdateSchemaSql($metadatas, true);
    }

    /**
     * Create mapping for entityClass
     *
     * @param EntityClass\EntityClass $entityClass EntityClass
     */
    public function doctrineGenerateMappingByEntityClass(EntityClass $entityClass) 
    {
        $mapping = array(); 

        //Create a mapping array
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
     * Create entity file for entityClass
     *
     * @param EntityClass\EntityClass $entityClass
     * @param array $mapping
     */
    public function doctrineGenerateEntityFileByMapping(EntityClass $entityClass, array $mapping = array())
    {
        //Define entity path and namespace for the entity 
        $entityNamespace = $this->getDataEntityNamespace($entityClass->getTechnicalName());
        $entityPath = $this->getDataEntityPath($entityClass->getTechnicalName());

        //Create entity code
        $entityGenerator = $this->initEntityGenerator($entityClass);
        $class = $this->initClassMetadataInfo($entityClass, $entityNamespace, $mapping);
        $entityCode = $entityGenerator->generateEntityClass($class);
        
        //Create entity file
        $this->filesystem->mkdir(dirname($entityPath));
        file_put_contents($entityPath, $entityCode);
    }

    /**
     * Initialize EntityGenerator
     *
     * @param EntityClass\EntityClass $entityClass
     */
    private function initEntityGenerator(EntityClass $entityClass) {

        $entityGenerator = new SLCoreEntityGenerator();

        //Variable configuration
        $entityGenerator->setGenerateAnnotations(true);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(true);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');
        $entityGenerator->setGenerateAnnotations(true);

        if($entityClass->getParent() === null){
            $entityNamespace = $this->getDataEntityNamespace('AbstractEntity');
            $entityGenerator->setClassToExtend($entityNamespace); 
        }
        else{
            $entityNamespace = $this->getDataEntityNamespace($entityClass->getParent()->getTechnicalName());
            $entityGenerator->setClassToExtend($entityNamespace); 
        }

        return $entityGenerator;
    }

    /**
     * Initialize ClassMetadataInfo
     *
     * @param EntityClass\EntityClass $entityClass
     * @param string $entityNamespace
     * @param array $mapping
     */
    private function initClassMetadataInfo(EntityClass $entityClass, $entityNamespace, array $mapping = array()) {

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
     * Get namespace of a data bundle entity
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
     * Get path of a data bundle entity file
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
     * Delete entity with id $entityId
     *
     * @param string $entityFullName <BundleName>:<EntityName>(Ex : 'SLCoreBundle:EntityClass\Property')
     * @param integer $entityId
     * @param boolean $hardDelete If true, remove entity from database
     *
     * @return void
     */
    public function entityDelete($entityFullName, $entityId, $hardDelete=false){

        $entity = $this->em->getRepository($entityFullName)->find($entityId); 
        $this->em->remove($entity);
        $this->em->flush();

        if($hardDelete) {
            $filters = $this->em->getFilters();
            $filters->disable('softdeleteable');

            $entity = $this->em->getRepository($entityFullName)->find($entityId); 
            $this->em->remove($entity);
            $this->em->flush();
            
            $filters->enable('softdeleteable');
        }
    }
}
