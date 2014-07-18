<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Form; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;  
use Symfony\Bridge\Doctrine\RegistryInterface;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Services\JSTreeService;

/**
 * Object Service
 *
 */
class ObjectService
{
    private $registry; 
    private $em;
    private $databaseEm;
    private $translator;

    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     * @param Translator $translator
     *
     */
    public function __construct(RegistryInterface $registry, Translator $translator)
    {
        $this->registry = $registry;
        $this->em = $registry->getManager();
        $this->databaseEm = $registry->getManager('database');
        $this->translator = $translator;
    }

   /**
     * Verify integrity of an Object before delete
     *
     * @param Object $object Object to delete
     *
     * @return Array $integrityError Title and error message
     */
    public function integrityControlBeforeDelete(Object $object) 
    {
        $integrityError = null;

        //Check if the Object is link to another
        $targetObject = $this->em->getRepository('SLCoreBundle:EntityProperty')->findByTargetObject($object);

        if($targetObject != null){
            $title = $this->translator->trans('delete.error.title');
            $message = $this->translator->trans('delete.object.reference.error.message');

            $integrityError = array(
                'title' => $title,
                'message' => $message,
                );

            return $integrityError;
        }

        return $integrityError; 
    }

    /**
     * Calculate displayName attribute of a new entity 
     * by using calculatedName attribute of Object
     *
     * @param Mixed $entity Entity
     * @param Object $object Object
     *
     * @return String $displayName DisplayName of new entity
     */
    public function calculateDisplayName($entity, Object $object) 
    { 
        $patternString = $object->getCalculatedName();

        $patternArray = explode("%", $patternString);

        foreach($patternArray as $key => $pattern) {
            
            if(strpos(strtolower($pattern), 'property') !== false){

                $methodName = 'get'.ucfirst($pattern);
                $patternArray[$key] = $entity->$methodName(); 
            }
        }

        $displayName = implode($patternArray);

        return $displayName; 
    }

    /**
    * Refresh displayName of entity linked to Object
    *
    * @param Object $object Object 
    *
    */
    public function refreshCalculatedName(Object $object){

        $entities = $this->databaseEm ->getRepository('SLDataBundle:'.$object->getTechnicalName())
                                ->findAll(); 

        foreach($entities as $entity) {

            $displayName = $this->calculateDisplayName($entity, $object); 
            $entity->setDisplayName($displayName); 

        }

        $this->databaseEm->flush();

        return true; 
    }

    /**
     * Get hierarchy path of an Object  
     *
     * @param Object $object Object
     *
     * @return String $path Hierarchy path of Object
     */
    public function getObjectPath($object){
        
        //Get all parent Object
        $objects = $this->em->getRepository('SLCoreBundle:Object')->getPath($object); 

        $path=""; 
        foreach($objects as $object){
            $path = $path."/".$object->getDisplayName(); 
        }

        return $path; 
    }
}
