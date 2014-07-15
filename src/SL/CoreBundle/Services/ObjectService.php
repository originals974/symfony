<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Form; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;  

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Services\JSTreeService;

/**
 * Object Service
 *
 */
class ObjectService
{
    private $em;
    private $translator;
    private $jstreeService;
    private $templating;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param Translator $translator
     * @param JSTreeService $jstreeService
     * @param TimedTwigEngine $templating
     *
     */
    public function __construct(EntityManager $em, Translator $translator, JSTreeService $jstreeService, TimedTwigEngine $templating)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->jstreeService = $jstreeService;
        $this->templating = $templating;
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
     * Create JsonResponse for Object creation  
     *
     * @param Object $object Created Object
     * @param Form $form Creation Object form
     *
     * @return JsonResponse
     */
    public function createJsonResponse(Object $object, Form $form) {

        $isValid = $form->isValid(); 

        if($isValid) {
            $html = null; 
            $nodeStructure = $this->jstreeService->createNewObjectNode($object, $object->isDocument());
            $nodeProperties = array(
                'parent' => 'parent.node',
                'select' => true,  
            );
        }
        else {
            //Create form with errors
            $html = $this->templating->render('SLCoreBundle::save.html.twig', array(
                'entity' => $object,
                'form'   => $form->createView(),
                )
            ); 
            $nodeStructure = null; 
            $nodeProperties = null;
        }

        $data = array(  
            'form' => array(
                'action' => strtolower($form->getConfig()->getMethod()),
                'isValid' => $isValid,
                ),
            'html' => $html,
            'node' => array(
                'nodeStructure' => $nodeStructure,
                'nodeProperties' => $nodeProperties,
            ),
        );

        return new JsonResponse($data); 
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
