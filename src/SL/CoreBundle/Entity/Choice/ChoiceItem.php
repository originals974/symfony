<?php

namespace SL\CoreBundle\Entity\Choice;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;

/**
 * ChoiceItem
 *
 * @ORM\Table(name="choice_item",uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_index_choice_item_choice_list_id_technical_name", columns={"choiceList_id", "technical_name"})
 * })
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\Repository\ChoiceItemRepository")
 * @UniqueEntity(fields={"choiceList","displayName"})
 */
class ChoiceItem extends AbstractEntity
{
     /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=255)
     */
    private $icon = 'fa-minus';

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="SL\CoreBundle\Entity\Choice\ChoiceList", inversedBy="choiceItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $choiceList;

    /**
     * Set icon
     *
     * @param string $icon
     *
     * @return SL\CoreBundle\Entity\Choice\ChoiceItem
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string 
     */
    public function getIcon()
    {
        return $this->icon;
    }

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
