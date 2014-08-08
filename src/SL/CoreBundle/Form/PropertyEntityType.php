<?php

namespace SL\CoreBundle\Form;

//Symfony classes
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

//Custom classes

class PropertyEntityType extends PropertyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityClassId = $options['entityClass_id'];

        $builder
            ->add('displayName' , 'text',  array(
                'label' =>  'displayName',
                'attr' => array(
                    'max_length' => '255',
                    )
                )
            ) 
            ->add('targetEntityClass', 'entity', array(
                'empty_value' => '',
                'class' => 'SLCoreBundle:EntityClass',
                'property' => 'displayName',
                'query_builder' => function(EntityRepository $er) use($entityClassId) {
                                      return $er->findOtherEntityClass($entityClassId);
                                    },
                'label' =>  'entity_class',
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
