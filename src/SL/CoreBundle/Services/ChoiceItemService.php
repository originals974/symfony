<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;

//Custom classes
use SL\CoreBundle\Entity\ChoiceList;
use SL\CoreBundle\Entity\ChoiceItem;

/**
 * ChoiceItem Service
 *
 */
class ChoiceItemService
{
    private $formFactory;
    private $router;

    /**
     * Constructor
     *
     * @param FormFactory $formFactory
     * @param Router $router
     *
     */
    public function __construct(FormFactory $formFactory, Router $router)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

     /**
    * Create choice item form
    *
    * @param ChoiceList $choiceList Parent choice list
    * @param ChoiceItem $choiceItem
    *
    * @return Form $form
    */
    public function createCreateForm(ChoiceList $choiceList, ChoiceItem $choiceItem)
    {   
        $form = $this->formFactory->create('choice_item', $choiceItem, array(
            'action' => $this->router->generate('choice_item_create', array(
                'id' => $choiceList->getId(),
                )
            ),
            'method' => 'POST',
            'submit_label' => 'create',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }


    /**
    * Update choice item form
    *
    * @param ChoiceItem $choiceItem
    *
    * @return Form $form
    */
    public function createEditForm(ChoiceItem $choiceItem)
    {
        $form = $this->formFactory->create('choice_item', $choiceItem, array(
            'action' => $this->router->generate('choice_item_update', array(
                'id' => $choiceItem->getId(),
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
     * Delete choice item Form
     *
     * @param ChoiceItem $choiceItem
     *
     * @return Form $form
     */
    public function createDeleteForm(ChoiceItem $choiceItem)
    {
        $form = $this->formFactory->create('choice_item', $choiceItem, array(
            'action' => $this->router->generate('choice_item_delete', array(
                'id' => $choiceItem->getId(),
                )
            ),
            'method' => 'DELETE',
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            )
        );

        return $form;
    }   
}
