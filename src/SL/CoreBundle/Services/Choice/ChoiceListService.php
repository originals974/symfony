<?php

namespace SL\CoreBundle\Services\Choice;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;  
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;

use SL\CoreBundle\Entity\Choice\ChoiceList;

/**
 * ChoiceList Service
 *
 */
class ChoiceListService
{
    private $em;
    private $translator;
    private $formFactory;
    private $router;

    /**
     * Constructor
     *
     * @param Doctrine\ORM\EntityManager $em
     * @param Symfony\Component\Translation\Translator $translator
     * @param Symfony\Component\Form\FormFactory $formFactory
     * @param Symfony\Component\Routing\Router $router
     *
     */
    public function __construct(EntityManager $em, Translator $translator, FormFactory $formFactory, Router $router)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
    * Create create form for $choiceList
    *
    * @param SL\CoreBundle\Entity\Choice\ChoiceList $choiceList
    *
    * @return Symfony\Component\Form\Form $form
    */
    public function createCreateForm(ChoiceList $choiceList)
    {   
        $form = $this->formFactory->create('sl_core_choice_list', $choiceList, array(
            'action' => $this->router->generate('choice_list_create'),
            'method' => 'POST',
            'attr' => array(
                'valid-target' => '', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'add',  
                ),
            'submit_label' => 'create',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
    * Create update form for $choiceList
    *
    * @param SL\CoreBundle\Entity\Choice\ChoiceList $choiceList
    *
    * @return Symfony\Component\Form\Form $form
    */
    public function createEditForm(ChoiceList $choiceList)
    {     
        $form = $this->formFactory->create('sl_core_choice_list', $choiceList, array(
            'action' => $this->router->generate('choice_list_update', array(
                'id' => $choiceList->getId(),
                )
            ),
            'method' => 'PUT',
            'attr' => array(
                'valid-target' => '', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'update', 
                ),
            'submit_label' => 'update',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
     * Create delete form for $choiceList
     *
     * @param SL\CoreBundle\Entity\Choice\ChoiceList $choiceList
     *
     * @return Symfony\Component\Form\Form $form
     */
    public function createDeleteForm(ChoiceList $choiceList)
    {
        $form = $this->formFactory->create('sl_core_choice_list', $choiceList, array(
            'action' => $this->router->generate('choice_list_delete', array(
                'id' => $choiceList->getId(),
                )
            ),
            'attr' => array(
                'valid-target' => '', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'delete',  
                ),
            'method' => 'DELETE',
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            )
        );

        return $form;
    }

   /**
     * Verify $choiceList could be delete
     *
     * @param SL\CoreBundle\Entity\Choice\ChoiceList $choiceList
     *
     * @return array $integrityError Title and error message
     */
    public function integrityControlBeforeDelete(ChoiceList $choiceList) 
    {
        $integrityError = null;

        //Check if choice list is not associated with an object property
        $property = $this->em->getRepository('SLCoreBundle:ListProperty')->findByChoiceList($choiceList);

        if($property != null){
            $title = $this->translator->trans('delete.error.title');
            $message = $this->translator->trans('delete.choice_list.reference.error.message');

            $integrityError = array(
                'title' => $title,
                'message' => $message,
                );

            return $integrityError;
        }

        return $integrityError; 
    }
}
