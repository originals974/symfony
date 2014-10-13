<?php

namespace SL\CoreBundle\Services;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Persistence\Mapping\MappingException; 

use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\EntityClass\Property;
use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;

/**
 * Entity Service
 *
 */
class EntityService
{
    private $formFactory;
    private $router;
    private $em; 
    private $numberOfVersion; 
    private $dateFormat; 

    /**
     * Constructor
     *
     * @param FormFactory $formFactory
     * @param Router $router
     * @param EntityManager $em
     * @param integer $numberOfVersion
     *
     */
    public function __construct(FormFactory $formFactory, Router $router, EntityManager $em, $numberOfVersion, $dateFormat)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->em = $em;
        $this->numberOfVersion = $numberOfVersion;
        $this->dateFormat = $dateFormat;
    }

    /**
    * Create create form for $entity
    *
    * @param AbstractEntity $entity
    *
    * @return Form $form
    */
    public function createCreateForm(AbstractEntity $entity)
    {  
        $form = $this->formFactory->create('sl_core_entity', $entity, array(
            'action' => $this->router->generate('entity_create', array(
                'entity_class_id' => $entity->getEntityClass()->getId(),
                )
            ),
            'method' => 'POST',
            'data_class' => get_class($entity),
            'attr' => array(
                'valid-target' => '',  
                'no-valid-target' => 'ajax-modal',
                'mode' => 'add',  
                ),
            'submit_label' => 'create.label',
            'submit_color' => 'primary',
            'entity_class_id' => $entity->getEntityClass()->getId(),
            )
        );

        return $form;
    }

    /**
    * Create update form for $entity
    *
    * @param AbstractEntity $entity
    *
    * @return Form $form
    */
    public function createEditForm(AbstractEntity $entity)
    {
        $form = $this->formFactory->create('sl_core_entity', $entity, array(
            'action' => $this->router->generate('entity_update', array(
                'entity_class_id' => $entity->getEntityClass()->getId(),
                'entity_id' => $entity->getId(),
                'class_namespace' => $entity->getClass(), 
                )
            ),
            'method' => 'PUT',
            'data_class' => get_class($entity),
            'attr' => array(
                'mode' => 'update',  
                'valid-target' => '',  
                'no-valid-target' => 'ajax-modal',
                ),
            'submit_label' => 'update.label',
            'submit_color' => 'primary',
            'entity_class_id' => $entity->getEntityClass()->getId(),
            )
        );
        
        return $form;
    }

    /**
     * Create delete form for $entity
     *
     * @param AbstractEntity $entity
     *
     * @return Form $form
     */
    public function createDeleteForm(AbstractEntity $entity)
    {
        $form = $this->formFactory->create('sl_core_entity', $entity, array(
            'action' => $this->router->generate('entity_delete', array(
                'entity_id' => $entity->getId(),
                'class_namespace' => $entity->getClass(), 
                )
            ),
            'method' => 'DELETE',
            'data_class' => get_class($entity),
            'attr' => array(
                'mode' => 'delete',  
                'valid-target' => '',  
                'no-valid-target' => 'ajax-modal',
                ),
            'submit_label' => 'delete.label',
            'submit_color' => 'danger',
            'entity_class_id' => $entity->getEntityClass()->getId(),
            )
        );

        return $form;
    }

    /**
     * Create search form for entity
     *
     * @return Form $form
     */
    public function createSearchForm()
    {
        $form = $this->formFactory->create('sl_core_search', null, array(
            'action' => $this->router->generate('search'),
            'method' => 'POST',
            'attr' => array(
                'id' => 'sl_corebundle_search',
                'class' => 'form-inline',
                'valid-target' => 'search_result', 
                'mode' => 'search',
                ),
            )
        );

        return $form;
    }

    /**
     * Create update version form for $entity
     * This form display last $limit versions
     *
     * @param AbstractEntity $entity
     * @param integer $limit
     *
     * @return Form $form
     */
    public function createEditVersionForm(AbstractEntity $entity)
    {   
        $form = $this->formFactory->create('sl_core_entity_version', null, array(
            'action' => $this->router->generate('entity_update_version', array(
                'entity_id' => $entity->getId(),
                'class_namespace' => $entity->getClass(), 
                )
            ),
            'entity' => $entity,
            'limit' => $this->numberOfVersion,
            )
        );

        return $form;
    }

     /**
     * Check if entities $property have not null value
     *
     * @param Property $property
     *
     * @return boolean $propertyHasNotNullValues
     */
    public function propertyHasNotNullValues(Property $property){

        try
        {
            $entityCount = $this->em->getRepository('SLCoreBundle:Generated\\'.$property->getEntityClass()->getTechnicalName())
                                ->findNotNullValuesByProperty($property);

            if(array_shift($entityCount) == 0){
                $propertyHasNotNullValues = false; 
            } 
            else {
                $propertyHasNotNullValues = true;  
            }

        }
        catch (MappingException $me)
        {
            $propertyHasNotNullValues = false; 
        }

        return $propertyHasNotNullValues; 
    }

    /**
     * Calculate display name of $entity 
     * by using calculated name of associated entity class
     *
     * @param AbstractEntity $entity
     *
     * @return string $displayName
     */
    public function calculateDisplayName(AbstractEntity $entity) 
    { 
        $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->find($entity->getEntityClass()->getId()); 

        $patternString = $entityClass->getCalculatedName();

        if($patternString != null){
            $patternArray = explode("%", $patternString);

            foreach($patternArray as $key => $pattern) {
                
                if(strpos(strtolower($pattern), 'property') !== false){

                    $methodName = 'get'.ucfirst($pattern);
                    $value = $entity->$methodName(); 

                    if(is_array($value)){
                        $value = implode (', ', $value);
                    }
                    else if(is_object($value)){
                        
                        if($value instanceof \DateTime){
                            $value = $value->format($this->dateFormat);
                        }
                        else if($value instanceof PersistentCollection){
                            
                            $temp = array(); 
                            foreach($value as $entityOfCollection){
                                $temp[] = $entityOfCollection->getDisplayName(); 
                            }
                            $value = implode (', ', $temp);
                        }
                        else{
                            $value = $value->getDisplayName();
                        }
                    }

                    $patternArray[$key] = $value; 
                }
            }

            $displayName = implode($patternArray);
        }
        else {
            if($entityClass->isDocument()){
                $displayName = $entity->getDocument()->file->getClientOriginalName();
            }
            else{
                $displayName = "Undefined";
            }
        }

        return $displayName; 
    }

    /**
    * Refresh displayName of all entities associated to $entityClass
    *
    * @param EntityClass $entityClass 
    *
    * @return void
    */
    public function refreshDisplayName(EntityClass $entityClass){

        $entities = $this->em->getRepository('SLCoreBundle:Generated\\'.$entityClass->getTechnicalName())
                                ->findAll(); 

        foreach($entities as $entity) {

            $displayName = $this->calculateDisplayName($entity, $entityClass); 
            $entity->setDisplayName($displayName); 

        }

        $this->em->flush(); 
    }

    /**
     * Check if $entityClass contains entities
     *
     * @param EntityClass $entityClass
     *
     * @return boolean $entitiesExist
     */
    public function entitiesExist(EntityClass $entityClass){

        try
        {
            $entities = $this->em->getRepository('SLCoreBundle:Generated\\'.$entityClass->getTechnicalName())->findAll();

            if(count($entities) != 0){
                $entitiesExist = true; 
            } 
            else {
                $entitiesExist = false;  
            }
        }
        catch (MappingException $me)
        {
            $entitiesExist = false; 
        }

        return $entitiesExist; 
    }

    /**
     * Remove link with others entities 
     * before $entityToDetach delete
     *
     * @param AbstractEntity $$entityToDetach
     *
     * @return void
     */
    public function detachEntity(AbstractEntity $entityToDetach){

        $targetEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->find($entityToDetach->getEntityClass()->getId());

        $properties = $this->em->getRepository('SLCoreBundle:EntityClass\PropertyEntity')->findByTargetEntityClass($targetEntityClass);

        foreach($properties as $property){

            $entityClass  = $property->getEntityClass(); 
            $entities = $this->em->getRepository('SLCoreBundle:Generated\\'.$entityClass->getTechnicalName())->findAll(); 

            foreach($entities as $entity){

                if($property->isMultiple()){
                    $entity->{'remove'.$property->getTechnicalName()}($entityToDetach); 
                }
                else{
                    $entity->{'set'.$property->getTechnicalName()}(null);
                }
            }
        }
    }

}
