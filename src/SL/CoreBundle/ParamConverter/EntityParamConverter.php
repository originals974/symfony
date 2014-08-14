<?php

namespace SL\CoreBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\NoResultException;

class EntityParamConverter implements ParamConverterInterface
{
  protected $class;
  protected $em; 
  protected $repository;

  /**
  * Constructor
  *
  * @param string $class
  * @param RegistryInterface $registry
  *
  * @return void
  */ 
  public function __construct($class, RegistryInterface $registry)
  {
    $this->class = $class;
    $this->databaseEm = $registry->getManager('database');
  }

  /**
   * {@inheritDoc}
   */
  function supports(ParamConverter $configuration)
  {
    return $configuration->getClass() == $this->class;
  }

  /**
   * {@inheritDoc}
   */
  function apply(Request $request, ParamConverter $configuration)
  {
    $name = $configuration->getName();
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
    $request->attributes->set($name, $entity);

    if($selectMode == "all"){
      $filters->enable('softdeleteable');
    }

    return true;
  }

  /**
  * Find entity class
  * identified by $id
  *
  * @param integer $id
  *
  * @return Mixed 
  */ 
  function find($id)
  { 
    try {

      return $this->repository->findOneById($id);

    } catch (NoResultException $e) {

      return null;

    }
  }
}
