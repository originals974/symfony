<?php

namespace SL\CoreBundle\Form\EntityClass;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityClassCalculatedNameType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('calculatedName' , 'textarea',  array(
                'label' => 'calculated_name.label',
                'attr' => array(  
                    'max_length' => '255',
                    )
                )
            )
            ->add('updateExistingDisplayName' , 'checkbox',  array(
                'label'        => 'entity_class.update.calculate_name.existing_name.label',
                'help_block'  => 'entity_class.update.calculate_name.existing_name.help',
                'required' => false,
                'mapped' => false,
                )
            )
            ->add('submit', 'submit', array(
                'label' => 'update.label',
                'attr' => array('class'=>'btn btn-primary btn-sm'),
                )
            )
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SL\CoreBundle\Entity\EntityClass\EntityClass',
            'method' => 'PUT',
            'attr' => array(
                'valid-target' => '', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'update',  
                ),
            'show_legend' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sl_core_entity_class_calculated_name';
    }
}
