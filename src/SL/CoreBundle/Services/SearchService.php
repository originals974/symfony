<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router; 

//Custom classes
use SL\CoreBundle\Entity\Search;

/**
 * Search Service
 *
 */
class SearchService
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
    * Search entity form
    *
    * @param Search $search
    *
    * @return Form $form
    */
    public function createSearchForm(Search $search = null)
    {
        $form = $this->formFactory->create('sl_core_search', $search, array(
            'action' => $this->router->generate('search'),
            'method' => 'POST',
            'attr' => array(
                'id' => 'sl_corebundle_search',
                'class' => 'form-inline',
                'valid-target' => 'search_result', 
                'mode' => 'search',
                ),
            )
        );

        return $form;
    }
}
