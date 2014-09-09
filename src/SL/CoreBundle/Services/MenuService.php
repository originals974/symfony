<?php

namespace SL\CoreBundle\Services;

use Doctrine\ORM\EntityManager;
use Knp\Menu\MenuItem;

use SL\CoreBundle\Services\IconService;
use SL\CoreBundle\Entity\EntityClass\EntityClass;

/**
 * Menu Service
 *
 */
class MenuService
{
    private $em;
    private $iconService;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param IconService $iconService
     *
     */
    public function __construct(EntityManager $em, IconService $iconService)
    {
        $this->em = $em;
        $this->iconService = $iconService; 
    }

    /**
    * Add an $entityClass item to $dropdown
    *
    * @param MenuItem $dropdown
    * @param EntityClass $entityClass
    *
    * @return void
    */   
    public function addEntityClassDropDownMenu(MenuItem &$dropdown, EntityClass $entityClass)
    {
        $entityClassLink = $dropdown->addChild(
                $entityClass->getTechnicalName(), 
                array(
                    'route' => 'entity_new', 
                    'routeParameters' => array('entity_class_id' => $entityClass->getId()),
                    'label' => $entityClass->getDisplayName(),
                    'icon' => $this->iconService->getEntityClassIcon($entityClass),
                    )
            );

        $entityClassLink->setLinkAttributes(array(
            'data-toggle' => 'modal',
            'data-target' => '#',
            )
        );
    }

    /**
    * Linked $entityClasses items with their $parent
    *
    * @param MenuItem $parent
    * @param array $entityClasses
    *
    * @return void
    */
    public function addEntityClassItems(MenuItem &$parent, array $entityClasses)
    {
        foreach ($entityClasses as $entityClass) {
            
            $entityClassItem = $parent->addChild($entityClass->getTechnicalName(), array(
                        'route' => 'entity_class_show', 
                        'routeParameters' => array(
                            'entity_class_id' => $entityClass->getId(),
                            ),
                        'label' => $entityClass->getDisplayName(),
                        )
                    );
            $entityClassItem->setAttributes(array(
                'id' => $entityClass->getTechnicalName(), 
                'data-jstree' => '{"icon":"'.$this->iconService->getEntityClassIcon($entityClass).'"}'
                )
            );

            $entityClasses = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->children($entityClass, true); 

            $this->addEntityClassItems($entityClassItem, $entityClasses); 

        }
    }
}
