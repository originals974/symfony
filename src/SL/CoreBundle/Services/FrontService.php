<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\Translator;
use Symfony\Bridge\Doctrine\RegistryInterface;

//Custom classes
use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\EntityClass\Property;
use SL\DataBundle\Entity\LogEntry;
use SL\CoreBundle\Form\FrontType;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\EntityClass\EntityClassService;

/**
 * Front Service
 *
 */
class FrontService
{
    private $formFactory;
    private $router;
    private $em;
    private $databaseEm;
    private $entityClassService;
    private $doctrineService;
    private $translator;

    /**
     * Constructor
     *
     * @param FormFactory $formFactory
     * @param Router $router
     * @param RegistryInterface $registry
     * @param EntityClassService $entityClassService
     * @param DoctrineService $doctrineService
     * @param Translator $translator
     *
     */
    public function __construct(FormFactory $formFactory, Router $router, RegistryInterface $registry, EntityClassService $entityClassService, DoctrineService $doctrineService, Translator $translator)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->em = $registry->getManager();
        $this->databaseEm = $registry->getManager('database');
        $this->entityClassService = $entityClassService;
        $this->doctrineService = $doctrineService;
        $this->translator = $translator;
    }

    /**
    * Creates entity form
    *
    * @param EntityClass\EntityClass $entityClass EntityClass type of new entity
    * @param Mixed $entity
    *
    * @return Form $form
    */
    public function createCreateForm(EntityClass $entityClass, $entity)
    {
        $entityClass = $this->doctrineService->getDataEntityClass($entityClass->getTechnicalName());

        $form = $this->formFactory->create(new FrontType($this->em, $entityClass, $this->entityClassService, $this->translator), $entity, array(
            'action' => $this->router->generate('front_create', array(
                'id' => $entityClass->getId(),
                )
            ),
            'method' => 'POST',
            'attr' => array(
                'mode' => 'add',  
                'valid-target' => '',  
                'no-valid-target' => 'ajax-modal',
                ),
            'submit_label' => 'create',
            'submit_color' => 'primary',
            'entityClass' => $entityClass,
            )
        );

        return $form;
    }

    /**
    * Update entity form
    *
    * @param EntityClass\EntityClass $entityClass EntityClass type of update entity
    * @param Mixed $entity
    *
    * @return Form $form
    */
    public function createEditForm(EntityClass $entityClass, $entity)
    {
        $entityClass = $this->doctrineService->getDataEntityClass($entityClass->getTechnicalName());

        $form = $this->formFactory->create(new FrontType($this->em, $entityClass, $this->entityClassService, $this->translator), $entity, array(
            'action' => $this->router->generate('front_update', array(
                'id' => $entityClass->getId(),
                'entity_id' => $entity->getId(),
                )
            ),
            'method' => 'PUT',
            'attr' => array(
                'mode' => 'update',  
                'valid-target' => '',  
                'no-valid-target' => 'ajax-modal',
                ),
            'submit_label' => 'update',
            'submit_color' => 'primary',
            'entityClass' => $entityClass,
            )
        );
        
        return $form;
    }


    /**
     * Delete entity form
     *
     * @param EntityClass\EntityClass $entityClass  EntityClass type of remove entity
     * @param Mixed $entity
     *
     * @return Form $form Delete form
     */
    public function createDeleteForm(EntityClass $entityClass, $entity)
    {
        $entityClass = $this->doctrineService->getDataEntityClass($entityClass->getTechnicalName());

        $form = $this->formFactory->create(new FrontType($this->em, $entityClass, $this->entityClassService, $this->translator), $entity, array(
            'action' => $this->router->generate('front_delete', array(
                'id' => $entityClass->getId(),
                'entity_id' => $entity->getId(),
                )
            ),
            'method' => 'DELETE',
            'attr' => array(
                'mode' => 'delete',  
                'valid-target' => '',  
                'no-valid-target' => 'ajax-modal',
                ),
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            'entityClass' => $entityClass,
            )
        );

        return $form;
    }

    /**
    * Update entity version form
    *
    * @param EntityClass\EntityClass $entityClass 
    * @param Mixed $entity
    * @param integer $limit
    *
    * @return Form $form
    */
    public function createEditVersionForm(EntityClass $entityClass, $entity, LogEntry $logEntry, $limit = 5)
    {   
        $form = $this->formFactory->create('sl_core_entity_version', $logEntry, array(
            'action' => $this->router->generate('front_update_version', array(
                'id' => $entityClass->getId(),
                'entity_id' => $entity->getId(),
                )
            ),
            'entity' => $entity,
            'limit' => $limit,
            )
        );

        return $form;
    }

    public function entitiesExist(EntityClass $entityClass){

        $entities = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->findAll();

        if(count($entities) != 0){
            $entitiesExist = true; 
        } 
        else {
            $entitiesExist = false;  
        }

        return $entitiesExist; 
    }

    public function propertyHasNotNullValues(Property $property){

        $entityCount = $this->databaseEm->getRepository('SLDataBundle:'.$property->getEntityClass()->getTechnicalName())
                                        ->findNotNullValuesByProperty($property);

        if(array_shift($entityCount) == 0){
            $propertyHasNotNullValues = false; 
        } 
        else {
            $propertyHasNotNullValues = true;  
        }

        return $propertyHasNotNullValues; 
    }

    /**
     * Calculate displayName of a new entity 
     * by using calculatedName attribute of entityClass
     *
     * @param Mixed $entity
     * @param EntityClass\EntityClass $entityClass
     *
     * @return String $displayName DisplayName of new entity
     */
    public function calculateDisplayName($entity, EntityClass $entityClass) 
    { 
        $patternString = $entityClass->getCalculatedName();

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
    * Refresh displayName of entity linked to entityClass
    *
    * @param EntityClass\EntityClass $entityClass 
    *
    */
    public function refreshCalculatedName(EntityClass $entityClass){

        $entities = $this->databaseEm ->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())
                                ->findAll(); 

        foreach($entities as $entity) {

            $displayName = $this->calculateDisplayName($entity, $entityClass); 
            $entity->setDisplayName($displayName); 

        }

        $this->databaseEm->flush();

        return true; 
    }

}
