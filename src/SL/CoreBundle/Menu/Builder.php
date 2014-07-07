<?php

namespace SL\CoreBundle\Menu;

//Symfony classes
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Entity\Property;

class Builder extends ContainerAware
{
    /**
    * Create BackEnd Menu
    *
    * @return $menu The BackEnd menu
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
    * @return $menu The FrontEnd menu
    */    
    public function frontMenu(FactoryInterface $factory, array $options)
    {
        //Menu configuration
        $menu = $factory->createItem('root', array(
            'navbar' => true,
            'push_right' => true,
        ));

        //Menu item
        $menu->addChild('backEnd', array('route' => 'back_end'));
        $menu->addChild('search', array('route' => 'search'));

        return $menu;
    }

    /**
    * Create lateral FrontEnd Menu
    *
    * @return $menu lateral FrontEnd menu
    */    
    public function lateralFrontEndMenu(FactoryInterface $factory, array $options)
    {
        //Variables initialisation
        $icon = $this->container->get('sl_core.icon');
        $em = $this->container->get('Doctrine')->getManager();

        //Menu configuration
        $menu = $factory->createItem('root', array(
            'subnavbar' => true,
            'pills' => true,
            'stacked' => true, 
        ));

        //Generate a item for each Object
        $objects = $em->getRepository('SLCoreBundle:Object')->findBy(
            array(
                'isEnabled' => true,
                'isParent' => true,
                ),
            array(
                'displayOrder' => 'asc',
                )
            );

        foreach($objects as $object) {

            //For Object without children
            if($object->getchildrenObject()->count() == 0) {
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
            //For Object with children
            else{

                //Create dropdown menu
                $objectDropDown = $menu->addChild($object->getTechnicalName(), array(
                    'dropdown' => true,
                    'caret' => true,
                    'label' => $object->getDisplayName(),
                    'icon' => $icon->getObjectIcon($object),
                    )
                );

                //Add parent Object link
                $objectLink = $objectDropDown->addChild(
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

                //Add parent Object link
                foreach($object->getchildrenObject() as $childObject) {
                    $objectLink = $objectDropDown->addChild(
                    $childObject->getTechnicalName(), 
                    array(
                        'route' => 'front_new', 
                        'routeParameters' => array('id' => $childObject->getId()),
                        'label' => $childObject->getDisplayName(),
                        'icon' => $icon->getObjectIcon($childObject),
                        )
                    );

                    $objectLink->setLinkAttributes(array(
                        'data-toggle' => 'modal',
                        'data-target' => '#',
                        )
                    );
                }
            }
        }

        return $menu;
    }


    /**
    * Create BackEnd Tree Menu
    *
    * @return $menu The BackEnd Tree Menu
    */
    public function lateralBackEndMenu(FactoryInterface $factory, array $options)
    {
        //Variables initialisation
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

        //Select all Objects and associated Properties
        $documents = $em->getRepository('SLCoreBundle:Object')->findFullAllDocument();

        //Create a node for each Document node
        foreach($documents as $document) {

            $this->addObjectAndSubObjectsItems($documentRoot, $document, 1); 
        }

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
        
        //Select all Objects and associated Properties
        $objects = $em->getRepository('SLCoreBundle:Object')->findFullAllObject();

        //Create a node for each Object node
        foreach($objects as $object) {

            $this->addObjectAndSubObjectsItems($objectRoot, $object, 0); 
        }

         /************DATA LIST*************/
        //Create a node for DataLists
        $dataListRoot = $server->addChild('dataList', array(
            'route' => 'data_list',
            'label' => 'list', 
            )
        );
        $dataListRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootDataListIcon('fa-lg text-primary').'"}',
            )
        );    

        //Select all DataLists
        $dataLists = $em->getRepository('SLCoreBundle:DataList')->findFullAll();

        //Create a node for each DataList
        foreach($dataLists as $dataList) {

            //Create a node for DataList
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

            //Get DataListValue of DataList
            $dataListValues = $dataList->getDataListValues();

            //Create a node for each DataListValue
            foreach($dataListValues as $dataListValue) {

                //Create a node for DataListValue
                $dataListValueItem = $dataListItem->addChild($dataListValue->getTechnicalName(), array(
                    'label' => $dataListValue->getDisplayName(),
                    )
                );

                $dataListValueItem->setAttributes(array(
                    'id' => $dataListValue->getTechnicalName(),
                    'data-jstree' => '{"icon":"'.$icon->getDataListValueIcon($dataListValue).'"}',
                    )
                ); 
            }
        }

        return $menu;
    }

    /**
    * Create Object and subObjects items
    *
    * @param $parent Parent Node
    * @param Object $object The Object and its subObjects to add in tree menu
    *
    */
    private function addObjectAndSubObjectsItems(&$parent, Object $object, $isDocument)
    {
        //Variable initialisation
        $icon = $this->container->get('sl_core.icon');

        //Create a node for Object
        $objectItem = $this->addObjectItem($parent, $object); 

        //Get Children of Object
        $childrenObject = $object->getchildrenObject();

        //Create Sub Object node
        $label = ($isDocument)?'sub_document':'sub_object'; 

        $subObjectRoot = $objectItem->addChild('subObject', array(
            'label' => $label,
            )
        );

        $subObjectRoot->setAttributes(array(
            'data-jstree' => '{"icon":"'.$icon->getRootSubObjectIcon().'"}',
            )
        );

        //Create a node for each Objects' Children
        foreach($childrenObject as $childObject) {

            //Create a node for Object
            $childObjectItem = $this->addObjectItem($subObjectRoot, $childObject); 

            //Get Properties of child Object
            $properties = $childObject->getProperties();

            //Create a node for each Child Objects' Properties
            foreach($properties as $property) {

                //Create a node for Property
                $this->addPropertyItem($childObjectItem, $childObject, $property); 
            }
        }
        
        //Get Properties of Object
        $properties = $object->getProperties();

        //Create a node for each Objects' Properties
        foreach($properties as $property) {
            $this->addPropertyItem($objectItem, $object, $property); 
        }
    }

    /**
    * Create an Object Node
    *
    * @param $parent Parent Node
    * @param Object $object The Object to add in tree menu
    *
    * @return $objectItem The added node
    */
    private function addObjectItem(&$parent, Object $object)
    {
        //Variable initialisation
        $icon = $this->container->get('sl_core.icon');

        //Create a node for Object
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

        return $objectItem; 
    }

    /**
    * Create an Property Node
    *
    * @param $parent Parent Node
    * @param Object $object The parent Object of the Property
    * @param Property $property The Property to add in tree menu
    *
    * @return $objectItem The added node
    */
    private function addPropertyItem(&$parent, Object $object, Property $property)
    {
        //Variable initialisation
        $icon = $this->container->get('sl_core.icon');

        //Create a node for Property
        $propertyItem = $parent->addChild($property->getTechnicalName(), array(
                    'label' => $property->getDisplayName(),
                    )
                );

        //Choose icon
        $icon = '{"icon":"'.$icon->getPropertyIcon($property).'"}';

        //Set node icon 
        $propertyItem->setAttributes(array(
            'id' => $property->getTechnicalName(), 
            'data-jstree' => $icon,
            )
        ); 

        return $propertyItem;
    }
}