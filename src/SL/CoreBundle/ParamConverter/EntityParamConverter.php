<?php

namespace SL\CoreBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\RegistryInterface;

class EntityParamConverter implements ParamConverterInterface
{
  protected $class;
  protected $em; 
  protected $repository;

  public function __construct($class, RegistryInterface $registry)
  {
    $this->class = $class;
    $this->databaseEm = $registry->getManager('database');
  }

  function supports(ParamConverter $configuration)
  {
    return $configuration->getClass() == $this->class;
  }

  function apply(Request $request, ParamConverter $configuration)
  {
    $options = $configuration->getOptions();
    $id = $request->attributes->get('entity_id');
    $classNamespace = $request->attributes->get('class_namespace'); 
    $this->repository = $this->databaseEm->getRepository($classNamespace);
   
    if(isset($options['select_mode'])) {
      $selectMode = $options['select_mode'];
    } 
    else{
      $selectMode = 'not_deleted';
    }

    if($selectMode == "all"){
      $filters = $this->databaseEm->getFilters();
      $filters->disable('softdeleteable');
    }
    $entity = $this->find($id); 
    $request->attributes->set($configuration->getName(), $entity);

    if($selectMode == "all"){
      $filters->enable('softdeleteable');
    }

    return true;
  }

  function find($id)
  { 
    try {
      return $this->repository->findOneById($id);
    } catch (NoResultException $e) {
      return null;
    }
  }
}
