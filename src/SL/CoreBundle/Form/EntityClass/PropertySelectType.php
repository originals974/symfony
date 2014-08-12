<?php

namespace SL\CoreBundle\Form\EntityClass;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PropertySelectType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('formMode', 'choice', array(
                'label_render' => false,
                'expanded'     => true,
                'choices'      => array(
                    'default' => 'property', 
                    'entity' => 'entity_property',
                    'choice' => 'choice_property',
                    ),
                'widget_type'  => 'inline',
            )
        );
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array(
                'valid-target' => '', 
                'no-valid-target' => '',
                'mode' => '', 
                ),
            'show_legend' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sl_core_property_select';
    }
}
