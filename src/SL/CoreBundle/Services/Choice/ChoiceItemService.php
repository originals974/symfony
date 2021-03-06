<?php

namespace SL\CoreBundle\Services\Choice;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;

use SL\CoreBundle\Entity\Choice\ChoiceItem;

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
    * Create create form for $choiceItem
    *
    * @param ChoiceItem $choiceItem
    *
    * @return Form $form
    */
    public function createCreateForm(ChoiceItem $choiceItem)
    {   
        $form = $this->formFactory->create('sl_core_choice_item', $choiceItem, array(
            'action' => $this->router->generate('choice_item_create', array(
                'id' => $choiceItem->getChoiceList()->getId(),
                )
            ),
            'method' => 'POST',
            'attr' => array(
                'valid-target' => 'choice-item-panel-body', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'add',  
                ),
            'submit_label' => 'create.label',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
    * Create update form for $choiceItem
    *
    * @param ChoiceItem $choiceItem
    *
    * @return Form $form
    */
    public function createEditForm(ChoiceItem $choiceItem)
    {
        $form = $this->formFactory->create('sl_core_choice_item', $choiceItem, array(
            'action' => $this->router->generate('choice_item_update', array(
                'choice_list_id' => $choiceItem->getChoiceList()->getId(),
                'id' => $choiceItem->getId(),
                )
            ),
            'method' => 'PUT',
            'attr' => array(
                'valid-target' => 'choice-item-panel-body', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'update', 
                ),
            'submit_label' => 'update.label',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
     * Create delete form for $choiceItem
     *
     * @param ChoiceItem $choiceItem
     *
     * @return Form $form
     */
    public function createDeleteForm(ChoiceItem $choiceItem)
    {
        $form = $this->formFactory->create('sl_core_choice_item', $choiceItem, array(
            'action' => $this->router->generate('choice_item_delete', array(
                'choice_list_id' => $choiceItem->getChoiceList()->getId(),
                'id' => $choiceItem->getId(),
                )
            ),
            'method' => 'DELETE',
            'attr' => array(
                'valid-target' => 'choice-item-panel-body', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'delete',  
                ),
            'submit_label' => 'delete.label',
            'submit_color' => 'danger',
            )
        );

        return $form;
    }   
}
