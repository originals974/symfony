<?php

namespace SL\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PropertyType extends AbstractType
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
                    'label' =>  'displayName',
                    'attr' => array(
                        'max_length' => '255',
                        )
                    )
                ) 
                ->add('fieldType' , 'entity',  array(
                    'class' =>  'SLCoreBundle:FieldType',
                    'property' => 'displayName',
                    'query_builder' => function(EntityRepository $er) {
                                            return $er->createQueryBuilder('ft')
                                                      ->join('ft.fieldCategory', 'fg')
                                                      ->where('ft.isEnabled = true')
                                                      ->orderBy('ft.displayOrder', 'ASC');
                                        },
                    'group_by' => 'fieldCategory.displayName',
                    'label' =>  'fieldType',
                    'attr' => array(
                        'class'       => 'col-lg-4'
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
            'data_class' => 'SL\CoreBundle\Entity\Property',
            'attr' => array(
                'class' => 'form-horizontal', 
                'valid-target' => 'accordionPropertyContent', 
                ),
            'show_legend' => false,
            )
        );

        $resolver->setRequired(array(
            'submit_label',
            'submit_color',
            'object_id',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'property';
    }
}
