<?php

namespace SL\CoreBundle\Form;

//Symfony classes
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

//Custom classes
use SL\CoreBundle\Entity\Object;

class ObjectType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['method'] != 'DELETE'){

            $object = $options['object']; 

            $builder
                ->add('displayName' , 'text',  array(
                    'label' =>  'displayName',
                    'attr' => array(
                        'max_length' => '255',
                        )
                    )
                )
                ->add('parent', 'entity', array(
                    'disabled' => $options['disabled_parent_field'], 
                    'label' =>  'object.parent',
                    'required' => false,
                    'class' => 'SLCoreBundle:Object',
                    'property' => 'displayName',
                    'query_builder' => function(EntityRepository $er) use ($object){
                                          return $er->findParentObject($object);
                                        },
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
            'data_class' => 'SL\CoreBundle\Entity\Object',
            'attr' => array(
                'valid-target' => '',  
                ),
            'show_legend' => false,
            )
        );

        $resolver->setRequired(array(
            'submit_label',
            'submit_color',
            'disabled_parent_field',
            'object'
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'object';
    }
}
