<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Form; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;  

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

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param Translator $translator
     *
     */
    public function __construct(EntityManager $em, Translator $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
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
