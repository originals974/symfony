<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;

//Custom classes
use SL\CoreBundle\Entity\DataList;
use SL\CoreBundle\Entity\DataListValue;

/**
 * DataListValue Service
 *
 */
class DataListValueService
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
    * Create datalistvalue form
    *
    * @param DataList $dataList Parent datalist
    * @param DataListValue $dataListValue
    *
    * @return Form $form
    */
    public function createCreateForm(DataList $dataList, DataListValue $dataListValue)
    {   
        $form = $this->formFactory->create('data_list_value', $dataListValue, array(
            'action' => $this->router->generate('data_list_value_create', array(
                'id' => $dataList->getId(),
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
    * Update datalistvalue form
    *
    * @param DataListValue $dataListValue
    *
    * @return Form $form
    */
    public function createEditForm(DataListValue $dataListValue)
    {
        $form = $this->formFactory->create('data_list_value', $dataListValue, array(
            'action' => $this->router->generate('data_list_value_update', array(
                'id' => $dataListValue->getId(),
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
     * Delete datalistvalue Form
     *
     * @param DataListValue $dataListValue
     *
     * @return Form $form
     */
    public function createDeleteForm(DataListValue $dataListValue)
    {
        $form = $this->formFactory->create('data_list_value', $dataListValue, array(
            'action' => $this->router->generate('data_list_value_delete', array(
                'id' => $dataListValue->getId(),
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
