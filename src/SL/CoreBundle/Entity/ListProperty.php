<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ListProperty
 *
 * @ORM\Table(name="list_property")
 * @ORM\Entity(repositoryClass="PropertyRepository")
 */
class ListProperty extends Property
{ 
    /**
     * @ORM\ManyToOne(targetEntity="ChoiceList")
     * @Assert\NotBlank()
     */
    private $choiceList;


    /**
     * Set choiceList
     *
     * @param ChoiceList $choiceList
     * @return ChoiceList
     */
    public function setChoiceList(ChoiceList $choiceList = null)
    {
        $this->choiceList = $choiceList;

        return $this;
    }

    /**
     * Get choiceList
     *
     * @return ChoiceList 
     */
    public function getChoiceList()
    {
        return $this->choiceList;
    }
}
