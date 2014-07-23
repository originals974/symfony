<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\EntityGenerator;

//Custom classes
use SL\CoreBundle\Entity\Object;

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
    private $bundlePath;
    private $bundleName;
    private $bundle; 

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param RegistryInterface $registry
     * @param HttpKernelInterface $kernel
     * @param String $bundlePath
     */
    public function __construct(Filesystem $filesystem, RegistryInterface $registry, HttpKernelInterface $kernel, $bundlePath)
    {
        $this->filesystem = $filesystem;
        $this->registry = $registry;
        $this->kernel = $kernel; 
        $this->em = $registry->getManager(); 
        $this->databaseEm = $registry->getManager('database');
        $this->bundlePath = $bundlePath;
        $this->bundleName = str_replace('/', '', $this->bundlePath);
        $this->bundle = $this->kernel->getBundle($this->bundleName); 
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
        $entityPath = $this->getEntityPath($object->getTechnicalName());

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

            switch ($property->getFieldType()->getTechnicalName()) {
                case 'entity':

                     $fieldMapping = array(
                        'fieldName' => $property->getTechnicalName(), 
                        'targetEntity' => $this->getEntityClass($property->getTargetObject()->getTechnicalName()),
                        );

                    break;
                default:

                    $fieldMapping = array(
                        'fieldName' => $property->getTechnicalName(), 
                        'type' => $property->getFieldType()->getDataType(),
                        'length' => $property->getFieldType()->getLength(),
                        'nullable' => !$property->isRequired()
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
        $entityClass = $this->getEntityClass($object->getTechnicalName());
        $entityPath = $this->getEntityPath($object->getTechnicalName());

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

        $entityGenerator = new EntityGenerator();

        //Variable configuration
        $entityGenerator->setGenerateAnnotations(true);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(true);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');
        $entityGenerator->setGenerateAnnotations(true);

        if($object->getParent() != null){
            $entityClass = $this->getEntityClass($object->getParent()->getTechnicalName());
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

        if($object->getParent() == null){
            //Mapped default fields
            $class->mapField(array('fieldName' => 'id', 'type' => 'integer', 'id' => true));
            $class->mapField(array('fieldName' => 'objectId', 'type' => 'integer'));
            $class->mapField(array('fieldName' => 'displayName', 'type' => 'string'));
            $class->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);
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

            if(array_key_exists('targetEntity',$fieldMapping)){
                $class->mapManyToMany($fieldMapping);
            }
            else {
                $class->mapField($fieldMapping);
            }
        }

        return $class; 
    }

     /**
     * Get namespace of an entity
     *
     * @param String $entityName
     *
     * @return String $entityClass
     */
    public function getEntityClass($entityName)
    {
        $entityClass = $this->registry->getAliasNamespace($this->bundle->getName()).'\\'.$entityName;
        return $entityClass; 
    }

    /**
     * Get path of an entity file
     *
     * @param String $entityName
     *
     * @return String $entityPath
     */
    private function getEntityPath($entityName)
    {
        $entityPath = $this->bundle->getPath().'/Entity/'.str_replace('\\', '/', $entityName).'.php';
        return $entityPath; 
    }
}
