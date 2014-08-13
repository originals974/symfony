<?php

namespace SL\CoreBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

class EntityParamConverter implements ParamConverterInterface
{
  protected $class;
  protected $em; 
  //protected $repository;

  public function __construct($class, EntityManager $em)
  {
    $this->class = $class;
    $this->em = $em; 
    $this->repository = $em->getRepository($class);
  }

  function supports(ParamConverter $configuration)
  {
    return $configuration->getClass() == $this->class;
  }

  function apply(Request $request, ParamConverter $configuration)
  {
    $options = $configuration->getOptions();

    if(isset($options['select_mode'])) {
      $selectMode = $options['select_mode'];
    } 
    else{
      $selectMode = 'not_deleted';
    }

    if($selectMode == "all"){
      $filters = $this->em->getFilters();
      $filters->disable('softdeleteable');
    }

    $id = $request->attributes->get('entity_id');
    //$childClass = $request->attributes->get('class');

    //$entity = $this->em->getRepository($childClass)->find($id);

    $entity = $this->repository->find($id);

    $request->attributes->set($configuration->getName(), $entity);

    if($selectMode == "all"){
      $filters->enable('softdeleteable');
    }

    return true;
  }
}
