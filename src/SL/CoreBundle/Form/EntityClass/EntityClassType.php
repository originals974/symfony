<?php

namespace SL\CoreBundle\Form\EntityClass;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityClassType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['method'] != 'DELETE'){

            $builder
                ->add('displayName' , 'text',  array(
                    'label' =>  'display_name.label',
                    'attr' => array(
                        'max_length' => '255',
                        )
                    )
                )
            ;
        }

        $builder->add('submit', 'submit', array(
            'label' => $options['submit_label'],
            'attr' => array(
                'class'=>'btn btn-'.$options['submit_color'].' btn-sm'
                ),
            )
        );
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SL\CoreBundle\Entity\EntityClass\EntityClass',
            'show_legend' => false,
            )
        );

        $resolver->setRequired(array(
            'submit_label',
            'submit_color',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sl_core_entity_class';
    }
}
