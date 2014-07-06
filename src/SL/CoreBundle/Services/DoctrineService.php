<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
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

    public function __construct(Filesystem $filesystem, RegistryInterface $registry, HttpKernelInterface $kernel, $bundlePath)
    {
        $this->filesystem = $filesystem;
        $this->registry = $registry;
        $this->kernel = $kernel; 
        $this->databaseEm = $registry->getManager('database');
        $this->em = $registry->getManager(); 
        $this->bundlePath = $bundlePath;
        $this->bundleName = str_replace('/', '', $this->bundlePath);
        $this->bundle = $this->kernel->getBundle($this->bundleName); 
    }

     /**
     * Creates entity file for Object
     *
     *@param string $entityName The entity name
     *@param string $format Format of entity configuration file (yaml, yml, annotation)
     *
     * READ-ONLY: The field mappings of the class.
     * Keys are field names and values are mapping definitions.
     *
     * The mapping definition array has the following values:
     *
     * - <b>fieldName</b> (string)
     * The name of the field in the Entity.
     *
     * - <b>type</b> (string)
     * The type name of the mapped field. Can be one of Doctrine's mapping types
     * or a custom mapping type.
     *
     * - <b>columnName</b> (string, optional)
     * The column name. Optional. Defaults to the field name.
     *
     * - <b>length</b> (integer, optional)
     * The database length of the column. Optional. Default value taken from
     * the type.
     *
     * - <b>id</b> (boolean, optional)
     * Marks the field as the primary key of the entity. Multiple fields of an
     * entity can have the id attribute, forming a composite key.
     *
     * - <b>nullable</b> (boolean, optional)
     * Whether the column is nullable. Defaults to FALSE.
     *
     * - <b>columnDefinition</b> (string, optional, schema-only)
     * The SQL fragment that is used when generating the DDL for the column.
     *
     * - <b>precision</b> (integer, optional, schema-only)
     * The precision of a decimal column. Only valid if the column type is decimal.
     *
     * - <b>scale</b> (integer, optional, schema-only)
     * The scale of a decimal column. Only valid if the column type is decimal.
     *
     * - <b>'unique'</b> (string, optional, schema-only)
     * Whether a unique constraint should be generated for the column.
     *
     *
     *
     * READ-ONLY: The association mappings of this class.
     *
     * The mapping definition array supports the following keys:
     *
     * - <b>fieldName</b> (string)
     * The name of the field in the entity the association is mapped to.
     *
     * - <b>targetEntity</b> (string)
     * The class name of the target entity. If it is fully-qualified it is used as is.
     * If it is a simple, unqualified class name the namespace is assumed to be the same
     * as the namespace of the source entity.
     *
     * - <b>mappedBy</b> (string, required for bidirectional associations)
     * The name of the field that completes the bidirectional association on the owning side.
     * This key must be specified on the inverse side of a bidirectional association.
     *
     * - <b>inversedBy</b> (string, required for bidirectional associations)
     * The name of the field that completes the bidirectional association on the inverse side.
     * This key must be specified on the owning side of a bidirectional association.
     *
     * - <b>cascade</b> (array, optional)
     * The names of persistence operations to cascade on the association. The set of possible
     * values are: "persist", "remove", "detach", "merge", "refresh", "all" (implies all others).
     *
     * - <b>orderBy</b> (array, one-to-many/many-to-many only)
     * A map of field names (of the target entity) to sorting directions (ASC/DESC).
     * Example: array('priority' => 'desc')
     *
     * - <b>fetch</b> (integer, optional)
     * The fetching strategy to use for the association, usually defaults to FETCH_LAZY.
     * Possible values are: ClassMetadata::FETCH_EAGER, ClassMetadata::FETCH_LAZY.
     *
     * - <b>joinTable</b> (array, optional, many-to-many only)
     * Specification of the join table and its join columns (foreign keys).
     * Only valid for many-to-many mappings. Note that one-to-many associations can be mapped
     * through a join table by simply mapping the association as many-to-many with a unique
     * constraint on the join table.
     *
     * - <b>indexBy</b> (string, optional, to-many only)
     * Specification of a field on target-entity that is used to index the collection by.
     * This field HAS to be either the primary key or a unique column. Otherwise the collection
     * does not contain all the entities that are actually related.
     *
     * A join table definition has the following structure:
     * <pre>
     * array(
     *     'name' => <join table name>,
     *      'joinColumns' => array(<join column mapping from join table to source table>),
     *      'inverseJoinColumns' => array(<join column mapping from join table to target table>)
     * )
     * </pre>
     * @param array $mapping
     * @param boolean $withRepository True if a repository file must be created false otherwise
     */
    public function doctrineGenerateEntity($entityName, $format, array $mapping = array(), $withRepository = false)
    {
        //Configure the bundle (needed if the bundle does not contain any Entities yet)
        $config = $this->registry->getManager(null)->getConfiguration();
        $config->setEntityNamespaces(array_merge(
            array($this->bundle->getName() => $this->bundle->getNamespace().'\\Entity'),
            $config->getEntityNamespaces()
        ));

        //Define entity path and class path for the entity 
        $entityClass = $this->getEntityClass($entityName);
        $entityPath = $this->getEntityPath($entityName);

        $class = new ClassMetadataInfo($entityClass);
        if ($withRepository) {
            $class->customRepositoryClassName = $entityClass.'Repository';
        }

        //Mapped default fields
        $class->mapField(array('fieldName' => 'id', 'type' => 'integer', 'id' => true));
        $class->mapField(array('fieldName' => 'objectTechnicalName', 'type' => 'string'));
        $class->mapField(array('fieldName' => 'displayName', 'type' => 'string'));
        $class->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);
        
        //Mapped Property
        foreach ($mapping as $fieldMapping) {

            if(array_key_exists('targetEntity',$fieldMapping)){
                $class->mapManyToMany($fieldMapping);
            }
            else {
                $class->mapField($fieldMapping);
            }
        }

        $entityGenerator = $this->getEntityGenerator();
        if ('annotation' === $format) {
            $entityGenerator->setGenerateAnnotations(true);
            $entityCode = $entityGenerator->generateEntityClass($class);
            $mappingPath = $mappingCode = false;
        } else {
            $cme = new ClassMetadataExporter();
            $exporter = $cme->getExporter('yml' == $format ? 'yaml' : $format);
            $mappingPath = $this->bundle->getPath().'/Resources/config/doctrine/'.str_replace('\\', '.', $entity).'.orm.'.$format;

            $mappingCode = $exporter->exportClassMetadata($class);
            $entityGenerator->setGenerateAnnotations(false);
            $entityCode = $entityGenerator->generateEntityClass($class);
        }

        //Create entity file
        $this->filesystem->mkdir(dirname($entityPath));
        file_put_contents($entityPath, $entityCode);

        //Create configuration file 
        if ($mappingPath) {
            $this->filesystem->mkdir(dirname($mappingPath));
            file_put_contents($mappingPath, $mappingCode);
        }

        //Create repository file
        if ($withRepository) {
            $path = $this->bundle->getPath().str_repeat('/..', substr_count(get_class($bundle), '\\'));
            $this->getRepositoryGenerator()->writeEntityRepositoryClass($class->customRepositoryClassName, $path);
        }
    }

    /**
    * Initialise an EntityGenerator Object
    *
    * @return EntityGenerator $entityGenerator
    *
    */
    private function getEntityGenerator()
    {
        //Variables initialisation
        $entityGenerator = new EntityGenerator();

        //Variable configuration
        $entityGenerator->setGenerateAnnotations(true);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(true);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');

        return $entityGenerator;
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

    /*
    * Remove entity class file
    *
    * @param string $entityName Entity name
    *
    * @return string SQL requests
    *
    */
    public function removeDoctrineFiles($entityName)
    {
        //Get path of entity class file
        $entityPath = $this->getEntityPath($entityName);

        //Remove entity class file
        $this->filesystem->remove(array($entityPath));
    }

    /**
     * Create database mapping array for Object
     *
     * @param Object $object Object
     *
     * @return Array $mapping A array of Object mapping
     */
    public function getMappingByObject(&$mapping, Object $object) 
    {
        //Get property fields of the object
        $object = $this->em->getRepository('SLCoreBundle:Object')
                               ->findFullById($object->getId());

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
                        'nullable' => !$property->getIsRequired()
                        ); 

                    break;
            }
            array_push($mapping, $fieldMapping);
        }
    }


    /**
     * Get namespace of an entity
     *
     * @param string $entityName The entity
     *
     * @return string $entityClass The namespace of the entity
     */
    public function getEntityClass($entityName)
    {
        $entityClass = $this->registry->getAliasNamespace($this->bundle->getName()).'\\'.$entityName;
        return $entityClass; 
    }

    /**
     * Get path of an entity file
     *
     * @param string $entityName The entity
     *
     * @return string $entityClass The path of the entity file
     */
    private function getEntityPath($entityName)
    {
        $entityPath = $this->bundle->getPath().'/Entity/'.str_replace('\\', '/', $entityName).'.php';
        return $entityPath; 
    }

    /**
     * Create Entity file and update database schema for an Object
     *
     * @param Object $object Object
     *
     * @return Array $sql SQL request executed
     */
    public function updateObjectSchema(Object $object)
    {

        $mapping = array();

        //Get Object metadata 
        $this->getMappingByObject($mapping, $object); 
        
        //Get parent Object metadata   
        if($object->getParent()){
            $this->getMappingByObject($mapping, $object->getParent()); 
        }

        //Generate entity file
        $this->doctrineGenerateEntity($object->getTechnicalName(), 'annotation', $mapping);  

        //Get child Object metadata 
        foreach ($object->getChildren() as $child) {
            $this->getMappingByObject($mapping, $child); 

            //Generate entity file
            $this->doctrineGenerateEntity($child->getTechnicalName(), 'annotation', $mapping);  
        }

        //Update database schema
        $sql = $this->doctrineSchemaUpdateForce();

        return $sql;
    }

    /**
     * Delete Entity file and update database schema for an Object
     *
     * @param Object $object Object
     *
     * @return Array $sql SQL request executed
     */
    public function deleteObjectSchema(Object $object)
    {
        //Remove entity file
        $this->removeDoctrineFiles($object->getTechnicalName());

        //Update database schema
        $sql = $this->doctrineSchemaUpdateForce();

        return $sql;
    }
}
