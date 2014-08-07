<?php

namespace SL\CoreBundle\Form;

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
                'label' => 'calculatedName',
                'attr' => array(  
                    'max_length' => '255',
                    )
                )
            )
            ->add('updateExistingName' , 'checkbox',  array(
                'label'        => 'entity_class.update.calculate_name.existing_name.label',
                'help_block'  => 'entity_class.update.calculate_name.existing_name.help',
                'required' => false,
                'mapped' => false,
                )
            )
            ->add('submit', 'submit', array(
                'label' => 'update',
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
            'data_class' => 'SL\CoreBundle\Entity\EntityClass',
            'method' => 'PUT',
            'attr' => array(
                'valid-target' => '', 
                ),
            'show_legend' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'entity_class_calculated_name';
    }
}