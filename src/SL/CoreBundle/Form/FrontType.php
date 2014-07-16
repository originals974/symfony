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
            $lastObject = array_pop($objects); 
            array_unshift($objects, $lastObject); 

            foreach($objects as $object){

                //Create parent Tab in order to display parent Object fields
                $tab = $builder->create($object->getTechnicalName().'Property', 'tab', array(
                'label' => $object->getDisplayName(),
                'icon' => $object->getIcon(),
                'inherit_data' => true,
                ));

                foreach ($object->getProperties() as $property) {
                    
                    $formFieldOptions = $this->defineFormFieldOptions($property); 

                    $tab->add(
                        $property->getTechnicalName(), 
                        $property->getFieldType()->getFormType(),  
                        $formFieldOptions
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
     * Define options of form field 
     *
     * @param Property $property Property used to create form field
     *
     * @return Array $formFieldOptions Options array of Property
     */
    private function defineFormFieldOptions(Property $property) {

        //Globals options
        $formFieldOptions = array(
            'label' =>  $property->getDisplayName(),
            'required' => $property->isRequired(),
            'horizontal_input_wrapper_class' => 'col-lg-6',
            );

        //Complet or override options array depending to property field type
        switch ($property->getFieldType()->getFormType()) {
            case 'genemu_jquerydate':
                $formFieldOptions['widget'] = 'single_text';

                break;
            case 'collection':
                $formFieldOptions['type'] = 'entity';
                $formFieldOptions['allow_add'] = true;
                $formFieldOptions['allow_delete'] = true;
                $formFieldOptions['prototype'] = true;

                $widgetButtonOption = array(
                    'label' => "", 
                    'icon' => "plus",
                    'attr' => array(
                        'class' => 'btn btn-success btn-xs',
                        )
                    );
                $formFieldOptions['widget_add_btn'] = $widgetButtonOption;


                $specificOptions = array(
                    'class' =>  'SLDataBundle:'.$property->getTargetObject()->getTechnicalName(),
                    'property' => 'displayName',
                    'label_render' => false,
                    'required' => $property->isRequired(),
                    'horizontal_input_wrapper_class' => 'col-lg-6',
                    'widget_remove_btn' => array(
                        'wrapper_div' => false,
                        'label' => "", 
                        'icon' => "times", 
                        'attr' => array(
                            'class' => 'btn btn-danger btn-xs'
                            )
                        ),
                    );
                $formFieldOptions['options'] = $specificOptions;
            
                break;
            
            case 'choice':

                $choice = array(); 
                $dataListValues = $this->em ->getRepository('SLCoreBundle:DataLIstValue')
                                            ->findEnabledByDataList($property->getDataList());

                if($dataListValues) {
                    foreach($dataListValues as $dataListValue){
                        $choice[$dataListValue->getTechnicalName()] = $dataListValue->getDisplayName();
                    }

                    $formFieldOptions['choices'] = $choice;

                }

                break;

            default:
                 $formFieldOptions['attr']['max_length'] = $property->getFieldType()->getLength(); 

                break; 
        }

        return $formFieldOptions; 
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->entityClass,
            'attr' => array(
                'class' => 'form-horizontal',
                'valid-target' => '',  
                'no-valid-target' => 'ajax-modal',
                ),
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
