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
    protected $object;
    protected $entityClass;

    /**
     * Constructor
     */
    public function __construct (EntityManager $em, Object $object, $entityClass)
    {
        $this->em = $em; 
        $this->object = $object;
        $this->entityClass = $entityClass;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //Get object properties
        $parentObjectProperties = $this->em->getRepository('SLCoreBundle:Property')->findEnabledByParentObject($this->object);
        $objectProperties = $this->em->getRepository('SLCoreBundle:Property')->findEnabledByObject($this->object);
        
        if($parentObjectProperties == null) {

            if($objectProperties != null) {

                foreach ($objectProperties as $property) {

                    $formFieldOptions = $this->defineFormFieldOptions($property); 

                     $builder->add(
                        $property->getTechnicalName(),
                        $property->getFieldType()->getFormType(),  
                        $formFieldOptions
                    );
                }

            }
        }
        else {

            //Create parent Tab in order to display parent Object fields
            $parentTab = $builder->create('parentProperty', 'tab', array(
            'label' => 'property.parent',
            'icon' => 'pencil',
            'inherit_data' => true,
            ));

            foreach ($parentObjectProperties as $property) {
                
                $formFieldOptions = $this->defineFormFieldOptions($property); 

                $parentTab->add(
                    $property->getTechnicalName(), 
                    $property->getFieldType()->getFormType(),  
                    $formFieldOptions
                );
            }

            //Create children Tab in order to display Object field
            $childrenTab = $builder->create('childProperty', 'tab', array(
                'label' => 'property.child',
                'icon' => 'pencil',
                'inherit_data' => true,
            ));

            foreach ($objectProperties as $property) {

                $formFieldOptions = $this->defineFormFieldOptions($property); 

                 $childrenTab->add(
                    $property->getTechnicalName(),
                    $property->getFieldType()->getFormType(),  
                    $formFieldOptions
                );
            }

            $builder
                ->add($parentTab)
                ->add($childrenTab);
        }
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
        switch ($property->getFieldType()->getTechnicalName()) {
            case 'genemu_jquerydate':
                $formFieldOptions['widget'] = 'single_text';

                break;
            case 'entity':
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
            
            case 'data_list':

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
