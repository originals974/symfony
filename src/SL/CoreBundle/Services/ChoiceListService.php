<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Form;   
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;

//Custom classes
use SL\CoreBundle\Entity\ChoiceList;

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
     * @param EntityManager $em
     * @param Translator $translator
     * @param FormFactory $formFactory
     * @param Router $router
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
    * Create choice list form
    *
    * @param ChoiceList $choiceList
    *
    * @return Form $form
    */
    public function createCreateForm(ChoiceList $choiceList)
    {   
        $form = $this->formFactory->create('choice_list', $choiceList, array(
            'action' => $this->router->generate('choice_list_create'),
            'method' => 'POST',
            'submit_label' => 'create',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
    * Update choice list form
    *
    * @param ChoiceList $choiceList
    *
    * @return Form $form
    */
    public function createEditForm(ChoiceList $choiceList)
    {     
        $form = $this->formFactory->create('choice_list', $choiceList, array(
            'action' => $this->router->generate('choice_list_update', array(
                'id' => $choiceList->getId(),
                )
            ),
            'method' => 'PUT',
            'submit_label' => 'update',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
     * Delete choice list form
     *
     * @param ChoiceList $choiceList
     *
     * @return Form $form
     */
    public function createDeleteForm(ChoiceList $choiceList)
    {
        $form = $this->formFactory->create('choice_list', $choiceList, array(
            'action' => $this->router->generate('choice_list_delete', array(
                'id' => $choiceList->getId(),
                )
            ),
            'method' => 'DELETE',
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            )
        );

        return $form;
    }

   /**
     * Verify integrity of choice list before delete
     *
     * @param ChoiceList $choiceList ChoiceList to delete
     *
     * @return Array $integrityError Title and error message
     */
    public function integrityControlBeforeDelete(ChoiceList $choiceList) 
    {
        $integrityError = null;

        //Check if choice list is not a property of an object
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
