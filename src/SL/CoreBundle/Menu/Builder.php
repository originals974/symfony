<?php

namespace SL\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Knp\Menu\MenuItem; 

use SL\CoreBundle\Entity\EntityClass\EntityClass;

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

        $menu->addChild('frontEnd', array(
            'route' => 'front_end',
            'label' => 'front_end.label',
            )
        );
        $menu->addChild('translation', array(
            'route' => 'jms_translation_index',
            'label' => 'translate.label',
            )
        );

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

        $menu->addChild('BackEnd', array(
            'route' => 'back_end',
            'label' => 'back_end.label',
            )
        );

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
        $iconService = $this->container->get('sl_core.icon');
        $menuService = $this->container->get('sl_core.menu');

        $menu = $factory->createItem('root', array(
            'subnavbar' => true,
            'pills' => true,
            'stacked' => true, 
            )
        );

        $entityClasses = $em->getRepository('SLCoreBundle:EntityClass\EntityClass')->fullFindAll(0);
        
        foreach($entityClasses as $entityClass) {

            $dropdown = $menu->addChild($entityClass->getTechnicalName(), array(
                'label' => $entityClass->getDisplayName(),
                'dropdown' => true,
                'caret' => true,
                'icon' => $iconService->getEntityClassIcon($entityClass),
            ));

            $menuService->addEntityClassDropDownMenu($dropdown, $entityClass); 

            $subEntityClasses = $em->getRepository('SLCoreBundle:EntityClass\EntityClass')->children($entityClass);
            foreach($subEntityClasses as $subEntityClass){

                $menuService->addEntityClassDropDownMenu($dropdown, $subEntityClass); 
            }
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
        $iconService = $this->container->get('sl_core.icon');
        $menuService = $this->container->get('sl_core.menu');

        $menu = $factory->createItem('root');
       
        /************SERVER*************/
        $server = $menu->addChild('server', array(
            'route' => 'server',
            'label' => 'server.label',
            )
        );
        $server->setAttributes(array(
            'data-jstree' => '{"icon":"'.$iconService->getRootServerIcon('fa-lg').'"}',
            )
        );

        /************ENTITY_CLASSES*************/
        $entityClassRoot = $server->addChild('entityClass', array(
            'route' => 'entity_class', 
            'label' => 'entity_class.label',
            )
        );
        $entityClassRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$iconService->getRootEntityClassIcon('fa-lg text-primary').'"}',
            )
        );
        
        $entityClasses = $em->getRepository('SLCoreBundle:EntityClass\EntityClass')->fullFindAll(0);
        $menuService->addEntityClassItems($entityClassRoot, $entityClasses);

         /************CHOICE LIST*************/
        $choiceListRoot = $server->addChild('choiceList', array(
            'route' => 'choice_list',
            'label' => 'choice_list.label', 
            )
        );
        $choiceListRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$iconService->getRootChoiceListIcon('fa-lg text-primary').'"}',
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
                'data-jstree' => '{"icon":"'.$iconService->getChoiceListIcon().'"}'
                )
            );
        }

        return $menu;
    }
}