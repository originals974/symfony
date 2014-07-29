<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Form; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;  
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;

//Custom classes
use SL\CoreBundle\Entity\DataList;

/**
 * DataList Service
 *
 */
class DataListService
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
    * Create datalist form
    *
    * @param DataList $dataList
    *
    * @return Form $form
    */
    public function createCreateForm(DataList $dataList)
    {   
        $form = $this->formFactory->create('data_list', $dataList, array(
            'action' => $this->router->generate('data_list_create'),
            'method' => 'POST',
            'submit_label' => 'create',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
    * Update datalist form
    *
    * @param DataList $dataList
    *
    * @return Form $form
    */
    public function createEditForm(DataList $dataList)
    {     
        $form = $this->formFactory->create('data_list', $dataList, array(
            'action' => $this->router->generate('data_list_update', array(
                'id' => $dataList->getId(),
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
     * Delete datalist form
     *
     * @param DataList $dataList
     *
     * @return Form $form
     */
    public function createDeleteForm(DataList $dataList)
    {
        $form = $this->formFactory->create('data_list', $dataList, array(
            'action' => $this->router->generate('data_list_delete', array(
                'id' => $dataList->getId(),
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
     * Verify integrity of datalist before delete
     *
     * @param DataList $dataList Datalist to delete
     *
     * @return Array $integrityError Title and error message
     */
    public function integrityControlBeforeDelete(DataList $dataList) 
    {
        $integrityError = null;

        //Check if Datalist is not a property of an object
        $property = $this->em->getRepository('SLCoreBundle:ListProperty')->findByDataList($dataList);

        if($property != null){
            $title = $this->translator->trans('delete.error.title');
            $message = $this->translator->trans('delete.dataList.reference.error.message');

            $integrityError = array(
                'title' => $title,
                'message' => $message,
                );

            return $integrityError;
        }

        return $integrityError; 
    }
}
