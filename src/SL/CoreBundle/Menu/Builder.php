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
    * Create object FrontEnd Menu
    *
    * @return $menu
    */    
    public function newObjectFrontMenu(FactoryInterface $factory, array $options)
    {
        $em = $this->container->get('Doctrine')->getManager();

        //Menu configuration
        $menu = $factory->createItem('root', array(
            'subnavbar' => true,
            'pills' => true,
            'stacked' => true, 
            )
        );

        $objects = $em->getRepository('SLCoreBundle:Object')->fullFindAll(false);

        $menu = $this->addFrontChildrenObjectItems($menu, $objects);

        return $menu;
    }

    /**
    * Add children item to menu for front end part
    *
    * @param MenuItem $menu
    * @param Array $objects
    *
    * @return MenuItem $menu
    */
    private function addFrontChildrenObjectItems(MenuItem $menu, array $objects)
    {
        $icon = $this->container->get('sl_core.icon');

        foreach($objects as $object) {

            $objectLink = $menu->addChild(
                $object->getTechnicalName(), 
                array(
                    'route' => 'front_new', 
                    'routeParameters' => array('id' => $object->getId()),
                    'label' => $object->getDisplayName(),
                    'icon' => $icon->getObjectIcon($object),
                    )
                );

            $objectLink->setLinkAttributes(array(
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

        /************OBJECTS*************/
        //Create Object node
        $objectRoot = $server->addChild('object', array(
            'route' => 'object', 
            'label' => 'object',
            )
        );
        $objectRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootObjectIcon('fa-lg text-primary').'"}',
            )
        );
        
        //Select all root objects
        $objects = $em->getRepository('SLCoreBundle:Object')->fullFindAll(false, 0);

        $this->addBackChildrenObjectItems($objectRoot, $objects);

         /************CHOICE LIST*************/
        //Create node for ChoiceLists
        $choiceListRoot = $server->addChild('choiceList', array(
            'route' => 'choice_list',
            'label' => 'list', 
            )
        );
        $choiceListRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootChoiceListIcon('fa-lg text-primary').'"}',
            )
        );    

        //Select all choicelists
        $choiceLists = $em->getRepository('SLCoreBundle:ChoiceList')->fullFindAll();

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
    * @param Array $objects
    *
    * @return boolean true
    */
    private function addBackChildrenObjectItems(&$parent, array $objects)
    {
        $icon = $this->container->get('sl_core.icon');
        $em = $this->container->get('Doctrine')->getManager();

        foreach ($objects as $object) {
            
            $objectItem = $parent->addChild($object->getTechnicalName(), array(
                        'route' => 'object_show', 
                        'routeParameters' => array(
                            'id' => $object->getId(),
                            ),
                        'label' => $object->getDisplayName(),
                        )
                    );
            $objectItem->setAttributes(array(
                'id' => $object->getTechnicalName(), 
                'data-jstree' => '{"icon":"'.$icon->getObjectIcon($object).'"}'
                )
            );

            $objects = $em->getRepository('SLCoreBundle:Object')->children($object, true); 

            $this->addBackChildrenObjectItems($objectItem, $objects); 

        }

        return true; 
    }
}