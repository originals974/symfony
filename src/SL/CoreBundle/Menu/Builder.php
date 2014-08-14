<?php

namespace SL\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Knp\Menu\MenuItem; 

class Builder extends ContainerAware
{
    /**
    * Create back end menu
    *
    * @param FactoryInterface $factory
    * @param array $options
    *
    * @return Menu $menu
    */    
    public function backEndMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root', array(
            'navbar' => true,
            'push_right' => true,
        ));

        $menu->addChild('frontEnd', array('route' => 'front_end'));
        $menu->addChild('translation', array('route' => 'jms_translation_index'));

        return $menu;
    }

    /**
    * Create front end menu
    *
    * @param FactoryInterface $factory
    * @param array $options
    *
    * @return Menu $menu
    */      
    public function frontEndMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root', array(
            'navbar' => true,
            'push_right' => true,
        ));

        $menu->addChild('BackEnd', array('route' => 'back_end'));

        return $menu;
    }

    /**
    * Create create entity menu
    *
    * @param FactoryInterface $factory
    * @param array $options
    *
    * @return Menu $menu
    */     
    public function createEntityMenu(FactoryInterface $factory, array $options)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $icon = $this->container->get('sl_core.icon');

        $menu = $factory->createItem('root', array(
            'subnavbar' => true,
            'pills' => true,
            'stacked' => true, 
            )
        );

        $entityClasses = $em->getRepository('SLCoreBundle:EntityClass\EntityClass')->fullFindAll(false);
        
        foreach($entityClasses as $entityClass) {

            $entityClassLink = $menu->addChild(
                $entityClass->getTechnicalName(), 
                array(
                    'route' => 'entity_new', 
                    'routeParameters' => array('entity_class_id' => $entityClass->getId()),
                    'label' => $entityClass->getDisplayName(),
                    'icon' => $icon->getEntityClassIcon($entityClass),
                    )
                );

            $entityClassLink->setLinkAttributes(array(
                'data-toggle' => 'modal',
                'data-target' => '#',
                )
            );
        }

        return $menu;
    }

    /**
    * Create tree back end menu
    *
    * @param FactoryInterface $factory
    * @param array $options
    *
    * @return Menu $menu
    */
    public function treeBackEndMenu(FactoryInterface $factory, array $options)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $icon = $this->container->get('sl_core.icon');

        $menu = $factory->createItem('root');
       
        /************SERVER*************/
        $server = $menu->addChild('server', array(
            'route' => 'server',
            'label' => 'server',
            )
        );
        $server->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootServerIcon('fa-lg').'"}',
            )
        );

        /************ENTITY_CLASSES*************/
        $entityClassRoot = $server->addChild('entityClass', array(
            'route' => 'entity_class', 
            'label' => 'entity_class',
            )
        );
        $entityClassRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootEntityClassIcon('fa-lg text-primary').'"}',
            )
        );
        
        $entityClasses = $em->getRepository('SLCoreBundle:EntityClass\EntityClass')->fullFindAll(false, 0);
        $this->addEntityClassItems($entityClassRoot, $entityClasses);

         /************CHOICE LIST*************/
        $choiceListRoot = $server->addChild('choiceList', array(
            'route' => 'choice_list',
            'label' => 'choice_list', 
            )
        );
        $choiceListRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootChoiceListIcon('fa-lg text-primary').'"}',
            )
        );    

        $choiceLists = $em->getRepository('SLCoreBundle:Choice\ChoiceList')->fullFindAll();

        foreach($choiceLists as $choiceList) {

            $choiceListItem = $choiceListRoot->addChild($choiceList->getTechnicalName(), array(
                        'route' => 'choice_list_show', 
                        'routeParameters' => array(
                            'id' => $choiceList->getId(),
                            ),
                        'label' => $choiceList->getDisplayName(),
                        )
                    );
            $choiceListItem->setAttributes(array(
                'id' => $choiceList->getTechnicalName(),
                'data-jstree' => '{"icon":"'.$icon->getChoiceListIcon().'"}'
                )
            );
        }

        return $menu;
    }

   /**
    * Linked $entityClasses items with their $parent
    *
    * @param MenuItem $parent
    * @param array $entityClasses
    *
    * @return void
    */
    private function addEntityClassItems(&$parent, array $entityClasses)
    {
        $icon = $this->container->get('sl_core.icon');
        $em = $this->container->get('Doctrine')->getManager();

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
                'data-jstree' => '{"icon":"'.$icon->getEntityClassIcon($entityClass).'"}'
                )
            );

            $entityClasses = $em->getRepository('SLCoreBundle:EntityClass\EntityClass')->children($entityClass, true); 

            $this->addEntityClassItems($entityClassItem, $entityClasses); 

        }
    }
}