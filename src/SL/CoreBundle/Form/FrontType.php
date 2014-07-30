<?php
//TO COMPLETE
namespace SL\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Entity\Property;
use Doctrine\ORM\EntityManager;   

class FrontType extends AbstractType
{
    protected $em;
    protected $entityClass;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param String $entityClass
     */
    public function __construct (EntityManager $em, $entityClass)
    {
        $this->em = $em; 
        $this->entityClass = $entityClass; 
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['method'] != 'DELETE'){
            $objects = $this->em->getRepository('SLCoreBundle:Object')->getPath($options['object']); 

            foreach($objects as $object){

                //Create one tab per object
                $tab = $builder->create($object->getTechnicalName().'Property', 'tab', array(
                'label' => $object->getDisplayName(),
                'icon' => $object->getIcon(),
                'inherit_data' => true,
                ));

                $object = $this->em->getRepository('SLCoreBundle:Object')->fullFindById($object->getId());

                foreach ($object->getProperties() as $property) {
                    
                    $fieldOptions = $this->getFieldConfiguration($property); 

                    $tab->add(
                        $property->getTechnicalName(), 
                        $property->getFieldType()->getFormType(),  
                        $fieldOptions
                    );
                }

                $builder->add($tab); 
            }
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
     * Get type and options of a form field
     *
     * @param Property $property Property used to create form field
     *
     * @return Array $fieldConfiguration
     */
    private function getFieldConfiguration(Property $property) {

        //Globals options
        $fieldOptions = array(
            'label' =>  $property->getDisplayName(),
            'required' => $property->isRequired(),
            'horizontal_input_wrapper_class' => 'col-lg-6',
            );

        //Complete or override options array depending to property field type
        switch ($property->getFieldType()->getFormType()) {
            case 'genemu_jquerydate':
                
                $fieldOptions['widget'] = 'single_text';

                break;
            case 'entity':

                $fieldOptions['class'] = 'SLDataBundle:'.$property->getTargetObject()->getTechnicalName();
                $fieldOptions['property'] = 'displayName';
                $fieldOptions['multiple'] = $property->isMultiple();

                break;
            
            case 'choice':

                $choice = array(); 
                $dataListValues = $this->em ->getRepository('SLCoreBundle:DataListValue')
                                            ->fullFindByDataList($property->getDataList());

                if($dataListValues) {
                    foreach($dataListValues as $dataListValue){
                        $choice[$dataListValue->getDisplayName()] = $dataListValue->getDisplayName();
                    }

                    $fieldOptions['choices'] = $choice;

                }
                $fieldOptions['multiple'] = $property->isMultiple();

                break;

            default:
                $fieldOptions['attr']['max_length'] = $property->getFieldType()->getLength(); 

                break; 
        }

        return $fieldOptions; 
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->entityClass,
            'show_legend' => false,
            )
        );

        $resolver->setRequired(array(
            'submit_label',
            'submit_color',
            'object',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'front';
    }
}
