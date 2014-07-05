<?php

namespace SL\CoreBundle\Form;

//Symfony classes
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
