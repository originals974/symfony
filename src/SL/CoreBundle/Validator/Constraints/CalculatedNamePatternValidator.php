<?php

namespace SL\CoreBundle\Validator\Constraints;

//Symfony classes
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

            //Check if Properties associated to calculatedName pattern exist 
            foreach($patternArray as $key => $pattern) {
                
                if(strpos(strtolower($pattern), 'property') !== false){
                	$propertyFound = true; 

    	            $property = $this->em->getRepository('SLCoreBundle:Property')->findByTechnicalName($pattern);

    	            if($property == null) {
    	            	$this->context->addViolation($constraint->WrongPropertiesMessage);
    	            	break;
    	            }
                }
            }

            //Check if at least one Property is found in calculatedName pattern 
            if(!$propertyFound){
            	$this->context->addViolation($constraint->NoPropertyFoundMessage);
            }
        }
    }
}