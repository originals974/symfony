<?php

namespace SL\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PropertyType extends AbstractType
{
    private $method; 

    public function __construct($method = null)
    {
        $this->method = $method; 
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->method != 'DELETE'){
            
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
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'property';
    }
}