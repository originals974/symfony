<?php

namespace SL\CoreBundle\Menu;

//Symfony classes
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Knp\Menu\MenuItem; 

//Custom classes

class Builder extends ContainerAware
{
    /**
    * Create BackEnd Menu
    *
    * @return $menu
    */    
    public function backMenu(FactoryInterface $factory, array $options)
    {
        //Menu configuration
        $menu = $factory->createItem('root', array(
            'navbar' => true,
            'push_right' => true,
        ));

        //Menu item
        $menu->addChild('frontEnd', array('route' => 'front_end'));
        $menu->addChild('translation', array('route' => 'jms_translation_index'));

        return $menu;
    }

    /**
    * Create FrontEnd Menu
    *
    * @return $menu 
    */    
    public function mainFrontMenu(FactoryInterface $factory, array $options)
    {
        //Menu configuration
        $menu = $factory->createItem('root', array(
            'navbar' => true,
            'push_right' => true,
        ));

        //Menu item
        $menu->addChild('backEnd', array('route' => 'back_end'));

        return $menu;
    }

    /**
    * Create entityClass FrontEnd Menu
    *
    * @return $menu
    */    
    public function newEntityClassFrontMenu(FactoryInterface $factory, array $options)
    {
        $em = $this->container->get('Doctrine')->getManager();

        //Menu configuration
        $menu = $factory->createItem('root', array(
            'subnavbar' => true,
            'pills' => true,
            'stacked' => true, 
            )
        );

        $entityClasses = $em->getRepository('SLCoreBundle:EntityClass')->fullFindAll(false);

        $menu = $this->addFrontChildrenEntityClassItems($menu, $entityClasses);

        return $menu;
    }

    /**
    * Add children item to menu for front end part
    *
    * @param MenuItem $menu
    * @param Array $entityClasses
    *
    * @return MenuItem $menu
    */
    private function addFrontChildrenEntityClassItems(MenuItem $menu, array $entityClasses)
    {
        $icon = $this->container->get('sl_core.icon');

        foreach($entityClasses as $entityClass) {

            $entityClassLink = $menu->addChild(
                $entityClass->getTechnicalName(), 
                array(
                    'route' => 'front_new', 
                    'routeParameters' => array('id' => $entityClass->getId()),
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
    * Create BackEnd Tree Menu
    */
    public function lateralBackEndMenu(FactoryInterface $factory, array $options)
    {
        $em = $this->container->get('Doctrine')->getManager();
        $icon = $this->container->get('sl_core.icon');

        //Create root menu
        $menu = $factory->createItem('root');
       
        /************SERVER*************/
        //Create server node
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
        //Create EntityClass node
        $entityClassRoot = $server->addChild('entityClass', array(
            'route' => 'entity_class', 
            'label' => 'entity_class',
            )
        );
        $entityClassRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootEntityClassIcon('fa-lg text-primary').'"}',
            )
        );
        
        //Select all root entityClasses
        $entityClasses = $em->getRepository('SLCoreBundle:EntityClass')->fullFindAll(false, 0);

        $this->addBackChildrenEntityClassItems($entityClassRoot, $entityClasses);

         /************CHOICE LIST*************/
        //Create node for ChoiceLists
        $choiceListRoot = $server->addChild('choiceList', array(
            'route' => 'choice_list',
            'label' => 'choice_list', 
            )
        );
        $choiceListRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootChoiceListIcon('fa-lg text-primary').'"}',
            )
        );    

        //Select all choicelists
        $choiceLists = $em->getRepository('SLCoreBundle:Choice\ChoiceList')->fullFindAll();

        //Create a node for each choicelist
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
                'data-jstree' => '{"icon":"'.$icon->getChoiceListIcon($choiceList).'"}'
                )
            );
        }

        return $menu;
    }

   /**
    * Add children item to menu for back end part
    *
    * @param MenuItem $parent
    * @param Array $entityClasses
    *
    * @return boolean true
    */
    private function addBackChildrenEntityClassItems(&$parent, array $entityClasses)
    {
        $icon = $this->container->get('sl_core.icon');
        $em = $this->container->get('Doctrine')->getManager();

        foreach ($entityClasses as $entityClass) {
            
            $entityClassItem = $parent->addChild($entityClass->getTechnicalName(), array(
                        'route' => 'entity_class_show', 
                        'routeParameters' => array(
                            'id' => $entityClass->getId(),
                            ),
                        'label' => $entityClass->getDisplayName(),
                        )
                    );
            $entityClassItem->setAttributes(array(
                'id' => $entityClass->getTechnicalName(), 
                'data-jstree' => '{"icon":"'.$icon->getEntityClassIcon($entityClass).'"}'
                )
            );

            $entityClasses = $em->getRepository('SLCoreBundle:EntityClass')->children($entityClass, true); 

            $this->addBackChildrenEntityClassItems($entityClassItem, $entityClasses); 

        }

        return true; 
    }
}