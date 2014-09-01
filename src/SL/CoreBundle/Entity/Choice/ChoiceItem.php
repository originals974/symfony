<?php

namespace SL\CoreBundle\Entity\Choice;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;

/**
 * ChoiceItem
 *
 * @ORM\Table(name="sl_core_choice_item",uniqueConstraints={
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
     * @ORM\ManyToOne(targetEntity="ChoiceList", inversedBy="choiceItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $choiceList;

    /**
     * Constructor
     */
    public function __construct(ChoiceList $choiceList)
    {
        $this->setChoiceList($choiceList);
        $choiceList->addChoiceItem($this); 
    }

    /**
     * Set icon
     *
     * @param string $icon
     *
     * @return ChoiceItem
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
     * @param ChoiceList $choiceList
     *
     * @return ChoiceList
     */
    public function setChoiceList(ChoiceList $choiceList)
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
