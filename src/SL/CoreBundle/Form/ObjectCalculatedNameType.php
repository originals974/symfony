<?php

namespace SL\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectCalculatedNameType extends AbstractType
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
                'required' => false,
                'attr' => array(  
                    'max_length' => '255',
                    )
                )
            )
            ->add('updateExistingName' , 'checkbox',  array(
                'label'        => 'object.update.calculate_name.existing_name.label',
                'help_block'  => 'object.update.calculate_name.existing_name.help',
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
            'data_class' => 'SL\CoreBundle\Entity\Object',
            'method' => 'PUT',
            'attr' => array(
                'class' => 'form-horizontal',
                ),
            'show_legend' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'object_calculated_name';
    }
}
