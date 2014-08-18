<?php

namespace SL\CoreBundle\Services;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;
use Symfony\Bridge\Doctrine\RegistryInterface;

use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\EntityClass\Property;
use SL\MasterBundle\Entity\AbstractEntity;

/**
 * Entity Service
 *
 */
class EntityService
{
    private $formFactory;
    private $router;
    private $em; 
    private $databaseEm;
    private $numberOfVersion; 

    /**
     * Constructor
     *
     * @param FormFactory $formFactory
     * @param Router $router
     * @param RegistryInterface $registry
     * @param integer $numberOfVersion
     *
     */
    public function __construct(FormFactory $formFactory, Router $router, RegistryInterface $registry, $numberOfVersion)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->em = $registry->getManager();
        $this->databaseEm = $registry->getManager('database');
        $this->numberOfVersion = $numberOfVersion;
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
                'entity_class_id' => $entity->getEntityClassId(),
                )
            ),
            'method' => 'POST',
            'data_class' => get_class($entity),
            'attr' => array(
                'valid-target' => '',  
                'no-valid-target' => 'ajax-modal',
                'mode' => 'add',  
                ),
            'submit_label' => 'create',
            'submit_color' => 'primary',
            'entity_class_id' => $entity->getEntityClassId(),
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
                'entity_class_id' => $entity->getEntityClassId(),
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
            'submit_label' => 'update',
            'submit_color' => 'primary',
            'entity_class_id' => $entity->getEntityClassId(),
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
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            'entity_class_id' => $entity->getEntityClassId(),
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
     * Check if $entityClass contains entities
     *
     * @param EntityClass $entityClass
     *
     * @return boolean $entitiesExist
     */
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

     /**
     * Check if entities $property have not null value
     *
     * @param Property $property
     *
     * @return boolean $propertyHasNotNullValues
     */
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
     * Calculate display name of $entity 
     * by using calculated name of associated entity class
     *
     * @param AbstractEntity $entity
     *
     * @return string $displayName
     */
    public function calculateDisplayName(AbstractEntity $entity) 
    { 
        $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->find($entity->getEntityClassId()); 

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
    * Refresh displayName of all entities associated to $entityClass
    *
    * @param EntityClass $entityClass 
    *
    * @return void
    */
    public function refreshDisplayName(EntityClass $entityClass){

        $entities = $this->databaseEm ->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())
                                ->findAll(); 

        foreach($entities as $entity) {

            $displayName = $this->calculateDisplayName($entity, $entityClass); 
            $entity->setDisplayName($displayName); 

        }

        $this->databaseEm->flush();
    }
}