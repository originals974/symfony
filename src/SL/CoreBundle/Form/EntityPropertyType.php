<?php

namespace SL\CoreBundle\Form;

//Symfony classes
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

//Custom classes
use SL\CoreBundle\Entity\Object;

class EntityPropertyType extends PropertyType
{
    private $object; 

    public function __construct(Object $object)
    {
        $this->object = $object; 
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $object = $this->object;

        $builder
            ->add('displayName' , 'text',  array(
                'label' =>  'displayName',
                'attr' => array(
                    'max_length' => '255',
                    )
                )
            ) 
            ->add('targetObject', 'entity', array(
                'class' => 'SLCoreBundle:Object',
                'property' => 'displayName',
                'query_builder' => function(EntityRepository $er) use($object) {
                                      return $er->findOtherObject($object);
                                    },
                'label' =>  'object',
                'attr' => array(
                        'class'       => 'col-lg-4'
                    ) 
                )
            )
            ->add('isMultiple' , 'checkbox', array(
                'label' =>  'isMultiple',
                'required' => false,
                )
            )
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'entity_property';
    }
}
