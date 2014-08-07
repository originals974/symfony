<?php

namespace SL\CoreBundle\Form;

//Symfony classes
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager; 
use Symfony\Component\Translation\Translator;  

//Custom classes
use SL\CoreBundle\Services\EntityClassService;
use SL\CoreBundle\Entity\Property;

class FrontType extends AbstractType
{
    protected $em;
    protected $entityClass;
    protected $entityClassService;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param String $entityClass
     * @param EntityClassService $entityClassService
     * @param Translator $translator
     */
    public function __construct (EntityManager $em, $entityClass, EntityClassService $entityClassService, Translator $translator)
    {
        $this->em = $em; 
        $this->entityClass = $entityClass; 
        $this->entityClassService = $entityClassService; 
        $this->translator = $translator; 
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['method'] != 'DELETE'){

            $entityClasses = $this->entityClassService->getPath($options['entityClass']); 

            foreach($entityClasses as $entityClass){

                //Create one tab per entityClass
                $suffix = ($entityClass->getDeletedAt() !== null)?$this->translator->trans('deleted'):'';
                $tabLabel = $entityClass->getDisplayName().' '.$suffix;

                $tab = $builder->create($entityClass->getTechnicalName().'Property', 'tab', array(
                'label' => $tabLabel,
                'icon' => $entityClass->getIcon(),
                'inherit_data' => true,
                ));

                $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->fullFindById($entityClass->getId());

                foreach ($entityClass->getProperties() as $property) {
                    
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

                $fieldOptions['class'] = 'SLDataBundle:'.$property->getTargetEntityClass()->getTechnicalName();
                $fieldOptions['property'] = 'displayName';
                $fieldOptions['multiple'] = $property->isMultiple();

                break;
            
            case 'choice':

                $choice = array(); 
                $choiceItems = $this->em ->getRepository('SLCoreBundle:Choice\ChoiceItem')
                                            ->fullFindByChoiceList($property->getChoiceList());

                if($choiceItems) {
                    foreach($choiceItems as $choiceItem){
                        $choice[$choiceItem->getDisplayName()] = $choiceItem->getDisplayName();
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
            'entity_class',
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
