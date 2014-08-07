<?php

namespace SL\CoreBundle\Entity\Choice;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;

/**
 * ChoiceList
 *
 * @ORM\Table(name="choice_list",uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_index_choice_list_technical_name", columns={"technical_name"})
 * })
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\Repository\ChoiceListRepository")
 * @UniqueEntity(fields={"displayName"})
 */
class ChoiceList extends AbstractEntity
{
    /**
     * @var Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="SL\CoreBundle\Entity\Choice\ChoiceItem", mappedBy="choiceList", cascade={"remove"})
     */
    private $choiceItems;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->choiceItems = new ArrayCollection();
    }

    /**
     * Add choiceItem
     *
     * @param SL\CoreBundle\Entity\Choice\ChoiceItem $choiceItem
     *
     * @return SL\CoreBundle\EntityChoiceList
     */
    public function addChoiceItem(ChoiceItem $choiceItem)
    {
        $this->choiceItems[] = $choiceItem;

        return $this;
    }

    /**
     * Remove choiceItem
     *
     * @param SL\CoreBundle\Entity\Choice\ChoiceItem $choiceItem
     *
     * @return void 
     */
    public function removeChoiceItem(ChoiceItem $choiceItem)
    {
        $this->choiceItems->removeElement($choiceItem);
    }

    /**
     * Get choiceItems
     *
     * @return Doctrine\Common\Collections\ArrayCollection
     */
    public function getChoiceItems()
    {
        return $this->choiceItems;
    }
}
