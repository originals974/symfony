<?php
//TO COMPLETE
namespace SL\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SL\CoreBundle\Entity\Object;

class DeleteFrontType extends AbstractType
{
    protected $object;
    protected $entityClass;

    /**
     * Constructor
     */
    public function __construct (Object $object, $entityClass)
    {
        $this->object = $object;
        $this->entityClass = $entityClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'SL\\DataBundle\\Entity\\'.$this->object->getTechnicalName(),
            'data_class' => $this->entityClass,
            'show_legend' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sl_corebundle_'.$this->object->getTechnicalName();
    }
}
