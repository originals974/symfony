<?php 

namespace SL\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CalculatedNamePattern extends Constraint
{
    public $WrongPropertiesMessage = 'La formule de calcul contient des propriétés non valides.';
    public $NoPropertyFoundMessage = 'La formule de calcul ne contient aucune propriété.';


    public function validatedBy()
	{
	    return 'calculated_name_pattern';
	}
}