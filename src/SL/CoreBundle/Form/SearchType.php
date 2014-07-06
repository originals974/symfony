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
            ->add('searchField' , 'search',  array(
                'label' =>  'searchField',
                'horizontal_input_wrapper_class' => 'col-lg-8',
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
                'id' => 'sl_corebundle_search',
                'method' => 'POST',
                'class' => 'well form-inline', 
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
