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
                ->add('parent', 'entity', array(
                    'label' =>  'object.parent',
                    'required' => false,
                    'class' => 'SLCoreBundle:Object',
                    'property' => 'displayName',
                    'query_builder' => function(EntityRepository $er){
                                          return $er->findParentObject();
                                        },
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
            'data_class' => 'SL\CoreBundle\Entity\Object',
            'attr' => array(
                'class' => 'form-horizontal',
                'valid-target' => 'accordionSubObjectContent',  
                ),
            'show_legend' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'object';
    }
}
