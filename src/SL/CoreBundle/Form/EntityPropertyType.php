<?php

namespace SL\CoreBundle\Form;

//Symfony classes
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

//Custom classes
use SL\CoreBundle\Entity\Object;

class EntityPropertyType extends PropertyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $objectId = $options['object_id'];

        $builder
            ->add('displayName' , 'text',  array(
                'label' =>  'displayName',
                'attr' => array(
                    'max_length' => '255',
                    )
                )
            ) 
            ->add('targetObject', 'entity', array(
                'empty_value' => '',
                'class' => 'SLCoreBundle:Object',
                'property' => 'displayName',
                'query_builder' => function(EntityRepository $er) use($objectId) {
                                      return $er->findOtherObject($objectId);
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
           ->add('submit', 'submit', array(
                'label' => $options['submit_label'],
                'attr' => array(
                    'class'=>'btn btn-'.$options['submit_color'].' btn-sm'
                    ),
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
