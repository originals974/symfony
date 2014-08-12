<?php

namespace SL\CoreBundle\Entity\EntityClass;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use SL\CoreBundle\Entity\Choice\ChoiceList;

/**
 * PropertyChoice
 *
 * @ORM\Table(name="property_choice")
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\Repository\PropertyRepository")
 */
class PropertyChoice extends Property
{ 
    /**
     * @ORM\ManyToOne(targetEntity="SL\CoreBundle\Entity\Choice\ChoiceList")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $choiceList;

    /**
     * Set choiceList
     *
     * @param SL\CoreBundle\Entity\Choice\ChoiceList $choiceList
     *
     * @return SL\CoreBundle\Entity\Choice\ChoiceList
     */
    public function setChoiceList(ChoiceList $choiceList = null)
    {
        $this->choiceList = $choiceList;

        return $this;
    }

    /**
     * Get choiceList
     *
     * @return SL\CoreBundle\Entity\Choice\ChoiceList 
     */
    public function getChoiceList()
    {
        return $this->choiceList;
    }
}
