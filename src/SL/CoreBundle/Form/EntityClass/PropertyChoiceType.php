<?php

namespace SL\CoreBundle\Form\EntityClass;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class PropertyChoiceType extends PropertyType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder
            ->add('displayName' , 'text',  array(
                'label' =>  'displayName',
                'attr' => array(
                    'max_length' => '255',
                    )
                )
            ) 
            ->add('choiceList', 'entity', array(
                'empty_value' => '',
                'class' => 'SLCoreBundle:Choice\ChoiceList',
                'property' => 'displayName',
                'query_builder' => function(EntityRepository $er) {
                                      return $er->fullFindAllQb();
                                    },
                'label' =>  'choice_list',
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
        return 'sl_core_property_choice';
    }
}
