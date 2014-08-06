<?php

namespace SL\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class EntityVersionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['entity']; 
        $limit = $options['limit']; 

        $builder
            ->add('version', 'entity', array(
                'class' => 'SLDataBundle:LogEntry',
                'property' => 'version',
                'query_builder' => function(EntityRepository $er) use ($entity, $limit){
                                      return $er->findAllVersion($entity, $limit);
                                    },
                'label' =>  'form.update.version',
                'attr' => array(
                        'class'       => 'col-lg-4'
                    ) 
                )
            )
            ->add('submit', 'submit', array(
                'label' => 'validate',
                'attr' => array(
                    'enabled' =>false,
                    'class'=>'btn btn-primary btn-sm'
                    )
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
            'validation_groups' => false,
            'show_legend' => false,
            'method' => 'PUT',
            'attr' => array(
                'class' => 'form-inline',
                //'valid-target' => 'search_result', 
                'mode' => 'update',
                ),
            )
        );

        $resolver->setRequired(array(
            'entity',
            'limit',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sl_core_entity_version';
    }
}