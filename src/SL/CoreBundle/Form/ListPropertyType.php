<?php

namespace SL\CoreBundle\Form;

//Symfony classes
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class ListPropertyType extends PropertyType
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
            ->add('dataList', 'entity', array(
                'empty_value' => '',
                'class' => 'SLCoreBundle:DataList',
                'property' => 'displayName',
                'query_builder' => function(EntityRepository $er) {
                                      return $er->findEnabledDataList();
                                    },
                'label' =>  'data_list',
                'attr' => array(
                        'class'       => 'col-lg-4'
                    ) 
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
        return 'list_property';
    }
}
