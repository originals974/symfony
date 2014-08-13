<?php

namespace SL\CoreBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;

class EntityClassParamConverter implements ParamConverterInterface
{
  protected $class;
  protected $em; 
  protected $repository;

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
    $name = $configuration->getName(); 
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

    $id = $request->attributes->get('entity_class_id');
    $entityClass = $this->find($id); 
    

    $request->attributes->set($name, $entityClass);

    if($selectMode == "all"){
      $filters->enable('softdeleteable');
    }

    return true;
  }

  function find($id)
  { 
    try {
      return $this->repository->fullFindById($id);
    } catch (NoResultException $e) {
      return null;
    }
  }
}
