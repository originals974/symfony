<?php
//TO COMPLETE
namespace SL\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SearchType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('searchField' , 'search')
            ->add('submit', 'submit', array(
                'label' => 'search',
                'attr' => array(
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
            'attr' => array(
                'id' => 'sl_corebundle_search',
                'method' => 'POST',
                'class' => 'form-inline', 
                'valid-data-target' => '#tree_view', 
                'no-valid-data-target' => '#search_error',
                ),
            'show_legend' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sl_corebundle_search';
    }
}
