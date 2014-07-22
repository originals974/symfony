<?php

namespace SL\CoreBundle\Menu;

//Symfony classes
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Knp\Menu\MenuItem; 

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Entity\Property;

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

        $objects = $em->getRepository('SLCoreBundle:Object')->findAllActiveObjects();

        $menu = $this->addFrontChildrenObjectItems($menu, $objects);

        return $menu;
    }


    /**
    * Create document FrontEnd Menu
    *
    * @return $menu
    */    
    public function newDocumentFrontMenu(FactoryInterface $factory, array $options)
    {
        $em = $this->container->get('Doctrine')->getManager();

        //Menu configuration
        $menu = $factory->createItem('root', array(
            'subnavbar' => true,
            'pills' => true,
            'stacked' => true, 
            )
        );

        $documents = $em->getRepository('SLCoreBundle:Object')->findAllActiveDocuments();

        $menu = $this->addFrontChildrenObjectItems($menu, $documents);
 
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
        $em = $this->container->get('Doctrine')->getManager();

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

        /************DOCUMENTS*************/
        //Create Document node
        $documentRoot = $server->addChild('document', array(
            'route' => 'object', 
            'routeParameters' => array(
                'isDocument' => 1,
            ),
            'label' => 'document',
            )
        );
        $documentRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootDocumentIcon('fa-lg text-primary').'"}',
            )
        );

        //Select all root documents
        $documents = $em->getRepository('SLCoreBundle:Object')->findRootDocuments();

        $this->addBackChildrenObjectItems($documentRoot, $documents);


         /************OBJECTS*************/
        //Create Object node
        $objectRoot = $server->addChild('object', array(
            'route' => 'object', 
            'routeParameters' => array(
                'isDocument' => 0,
            ),
            'label' => 'object',
            )
        );
        $objectRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootObjectIcon('fa-lg text-primary').'"}',
            )
        );
        
        //Select all root objects
        $objects = $em->getRepository('SLCoreBundle:Object')->findRootObjects();

        $this->addBackChildrenObjectItems($objectRoot, $objects);

         /************DATA LIST*************/
        //Create node for DataLists
        $dataListRoot = $server->addChild('dataList', array(
            'route' => 'data_list',
            'label' => 'list', 
            )
        );
        $dataListRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootDataListIcon('fa-lg text-primary').'"}',
            )
        );    

        //Select all datalists
        $dataLists = $em->getRepository('SLCoreBundle:DataList')->findFullAll();

        //Create a node for each datalist
        foreach($dataLists as $dataList) {

            $dataListItem = $dataListRoot->addChild($dataList->getTechnicalName(), array(
                        'route' => 'data_list_show', 
                        'routeParameters' => array(
                            'id' => $dataList->getId(),
                            ),
                        'label' => $dataList->getDisplayName(),
                        )
                    );
            $dataListItem->setAttributes(array(
                'id' => $dataList->getTechnicalName(),
                'data-jstree' => '{"icon":"'.$icon->getDataListIcon($dataList).'"}'
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

            $objects = $em->getRepository('SLCoreBundle:Object')->children($object, true, 'displayOrder'); 

            $this->addBackChildrenObjectItems($objectItem, $objects); 

        }

        return true; 
    }
}