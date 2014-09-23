<?php

namespace SL\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityManager; 
use Symfony\Component\Translation\Translator;  

use SL\CoreBundle\Services\EntityClass\EntityClassService;
use SL\CoreBundle\Entity\EntityClass\Property;
use SL\DataBundle\Form\DocumentType; 

class EntityType extends AbstractType
{
    protected $em;
    protected $entityClassService;
    protected $translator;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param EntityClassService $entityClassService
     * @param Translator $translator
     */
    public function __construct (EntityManager $em, EntityClassService $entityClassService, Translator $translator)
    {
        $this->em = $em; 
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

            $mainEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->find($options['entity_class_id']); 
            $entityClasses = $this->entityClassService->getPath($mainEntityClass); 

            foreach($entityClasses as $entityClass){

                //Create one tab per entityClass
                $suffix = ($entityClass->getDeletedAt() !== null)?$this->translator->trans('deleted.title'):'';
                $tabLabel = $entityClass->getDisplayName().' '.$suffix;

                $tab = $builder->create($entityClass->getTechnicalName().'Property', 'tab', array(
                    'label' => $tabLabel,
                    'icon' => $entityClass->getIcon(),
                    'inherit_data' => true,
                    )
                );

                if($entityClass->isDocument() && $entityClass === reset($entityClasses)){
                    $tab->add(
                        'document', 
                        new DocumentType(),  
                        array(
                            'label' =>  'main_document.label',
                            'required' => true,
                            'horizontal_input_wrapper_class' => 'col-lg-6',
                        )
                    );
                }

                foreach ($entityClass->getProperties() as $property) {
                    
                    $fieldOptions = $this->getFieldOptions($property); 

                    if($property->getFieldType()->getFormType() === "file"){

                        $tab->add(
                            $property->getTechnicalName(), 
                            new DocumentType(),  
                            $fieldOptions
                        );
                    }
                    else{

                        $tab->add(
                            $property->getTechnicalName(), 
                            $property->getFieldType()->getFormType(),  
                            $fieldOptions
                        );
                    }
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
     * Get form options for $property
     *
     * @param Property $property
     *
     * @return array $fieldOptions
     */
    private function getFieldOptions(Property $property) {

        //Globals options
        $fieldOptions = array(
            'label' =>  $property->getDisplayName(),
            'required' => $property->isRequired(),
            'horizontal_input_wrapper_class' => 'col-lg-6',
            );

        //Complete $fieldOptions depending to property field type
        switch ($property->getFieldType()->getFormType()) {
            case 'genemu_jquerydate':
                
                $fieldOptions['widget'] = 'single_text';

                break;
            case 'entity':

                $fieldOptions['class'] = 'SLDataBundle:'.$property->getTargetEntityClass()->getTechnicalName();
                $fieldOptions['property'] = 'displayName';
                $fieldOptions['multiple'] = $property->isMultiple();

                break;
            case 'file':

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
            'show_legend' => false,
            )
        );

        $resolver->setRequired(array(
            'submit_label',
            'submit_color',
            'entity_class_id',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sl_core_entity';
    }
}
