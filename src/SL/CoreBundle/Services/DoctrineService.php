<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
//use Doctrine\ORM\Tools\EntityGenerator;

//Custom classes
use SL\CoreBundle\Entity\Object;
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
    private $coreBundle;
    private $dataBundle; 

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param RegistryInterface $registry
     * @param HttpKernelInterface $kernel
     * @param String $bundlePath
     */
    public function __construct(Filesystem $filesystem, RegistryInterface $registry, HttpKernelInterface $kernel, $coreBundlePath, $dataBundlePath)
    {
        $this->filesystem = $filesystem;
        $this->registry = $registry;
        $this->kernel = $kernel; 
        $this->em = $registry->getManager(); 
        $this->databaseEm = $registry->getManager('database');
        $this->coreBundle = $this->kernel->getBundle(str_replace('/', '', $coreBundlePath)); 
        $this->dataBundle = $this->kernel->getBundle(str_replace('/', '', $dataBundlePath)); 
    }

    /**
     * Create mapping and entity file for object
     *
     * @param Object $object Object
     *
     * @return Array $mapping Mapping array for object
     */
    public function doctrineGenerateEntityFileByObject(Object $object)
    {
        $mapping = $this->doctrineGenerateMappingByObject($object);
        $this->doctrineGenerateEntityFileByMapping($object, $mapping);

        return $mapping;
    }

    /**
    * Remove entity file for object
    *
    * @param Object $object Object
    */
    public function removeDoctrineFiles(Object $object)
    {
        //Get path of entity class file
        $entityPath = $this->getDataEntityPath($object->getTechnicalName());

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
     * Create mapping for object
     *
     * @param Object $object Object
     */
    public function doctrineGenerateMappingByObject(Object $object) 
    {
        $mapping = array(); 

        //Create a mapping array
        foreach ($object->getProperties() as $property) {  

            switch ($property->getFieldType()->getFormType()) {
                case 'entity':

                    $fieldMapping = array(
                        'fieldName' => $property->getTechnicalName(), 
                        'targetEntity' => $this->getDataEntityClass($property->getTargetObject()->getTechnicalName()),
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
            array_push($mapping, $fieldMapping);
        }

        return $mapping;    
    }

    /**
     * Create entity file for object
     *
     * @param Object $object
     * @param array $mapping
     */
    public function doctrineGenerateEntityFileByMapping(Object $object, array $mapping = array())
    {
        //Define entity path and class path for the entity 
        $entityClass = $this->getDataEntityClass($object->getTechnicalName());
        $entityPath = $this->getDataEntityPath($object->getTechnicalName());

        //Create entity code
        $entityGenerator = $this->initEntityGenerator($object);
        $class = $this->initClassMetadataInfo($object, $entityClass, $mapping);
        $entityCode = $entityGenerator->generateEntityClass($class);
        
        //Create entity file
        $this->filesystem->mkdir(dirname($entityPath));
        file_put_contents($entityPath, $entityCode);
    }

    /**
     * Initialize EntityGenerator
     *
     * @param Object $object
     */
    private function initEntityGenerator(Object $object) {

        //$entityGenerator = new EntityGenerator();
        $entityGenerator = new SLCoreEntityGenerator();

        //Variable configuration
        $entityGenerator->setGenerateAnnotations(true);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(true);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');
        $entityGenerator->setGenerateAnnotations(true);

        if($object->getParent() === null){
            $entityClass = $this->getDataEntityClass('AbstractEntity');
            $entityGenerator->setClassToExtend($entityClass); 
        }
        else{
            $entityClass = $this->getDataEntityClass($object->getParent()->getTechnicalName());
            $entityGenerator->setClassToExtend($entityClass); 
        }

        return $entityGenerator;
    }

    /**
     * Initialize ClassMetadataInfo
     *
     * @param Object $object
     * @param string $entityClass
     * @param array $mapping
     */
    private function initClassMetadataInfo(Object $object, $entityClass, array $mapping = array()) {

        $class = new ClassMetadataInfo($entityClass);

        if($object->getParent() === null){
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
     * Get namespace of a core bundle entity
     *
     * @param string $entityName
     *
     * @return string $entityClass
     */
    public function getCoreEntityClass($entityName)
    {
        $entityClass = $this->registry->getAliasNamespace($this->coreBundle->getName()).'\\'.$entityName;
        return $entityClass; 
    }

    /**
     * Get path of a core bundle entity file
     *
     * @param string $entityName
     *
     * @return string $entityPath
     */
    private function getCoreEntityPath($entityName)
    {
        $entityPath = $this->coreBundle->getPath().'/Entity/'.str_replace('\\', '/', $entityName).'.php';
        return $entityPath; 
    }

    /**
     * Get namespace of a data bundle entity
     *
     * @param string $entityName
     *
     * @return string $entityClass
     */
    public function getDataEntityClass($entityName)
    {
        $entityClass = $this->registry->getAliasNamespace($this->dataBundle->getName()).'\\'.$entityName;
        return $entityClass; 
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
}
