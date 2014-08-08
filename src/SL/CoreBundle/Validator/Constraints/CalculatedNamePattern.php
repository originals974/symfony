<?php 

namespace SL\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CalculatedNamePattern extends Constraint
{
    //public $WrongPropertiesMessage = 'La formule de calcul contient des propriétés non valides.';
   	//public $NoPropertyFoundMessage = 'La formule de calcul ne contient aucune propriété.';

    public $WrongPropertiesMessage = 'calculated_name.wrong_properties.message';
    public $NoPropertyFoundMessage = 'calculated_name.not_found_property.message';

    public function validatedBy()
	{
	    return 'calculated_name_pattern';
	}
}