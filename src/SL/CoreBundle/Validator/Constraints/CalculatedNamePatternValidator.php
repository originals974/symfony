<?php

namespace SL\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

class CalculatedNamePatternValidator extends ConstraintValidator
{
	private $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     *
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        if($value != null) {
            $patternArray = explode("%", $value);
            $propertyFound = false; 

            //Check if properties associated to calculated name pattern exist 
            foreach($patternArray as $key => $pattern) {
                
                if(strpos(strtolower($pattern), 'property') !== false){
                	$propertyFound = true; 

    	            $property = $this->em->getRepository('SLCoreBundle:EntityClass\Property')->findByTechnicalName($pattern);

    	            if($property == null) {
    	            	$this->context->addViolation($constraint->WrongPropertiesMessage);
    	            	break;
    	            }
                }
            }

            //Check if at least one property is found in calculated name pattern 
            if(!$propertyFound){
            	$this->context->addViolation($constraint->NoPropertyFoundMessage);
            }
        }
    }
}