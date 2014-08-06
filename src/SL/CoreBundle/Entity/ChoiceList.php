<?php

namespace SL\CoreBundle\Entity;

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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ChoiceItem", mappedBy="choiceList", cascade={"remove"})
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
     * @param ChoiceItem $choiceItem
     * @return ChoiceList
     */
    public function addChoiceItem(ChoiceItem $choiceItem)
    {
        $this->choiceItems[] = $choiceItem;

        return $this;
    }

    /**
     * Remove choiceItem
     *
     * @param ChoiceItem $choiceItem
     */
    public function removeChoiceItem(ChoiceItem $choiceItem)
    {
        $this->choiceItems->removeElement($choiceItem);
    }

    /**
     * Get choiceItems
     *
     * @return Collection 
     */
    public function getChoiceItems()
    {
        return $this->choiceItems;
    }
}
