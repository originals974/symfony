<?php
namespace SL\CoreBundle\Entity;

/**
 * Search
 *
 */
class Search
{
    /**
     * @var string
     *
     */
    private $searchField;


    /**
     * Set fieldSearch
     *
     * @param string $fieldSearch
     * @return Search
     */
    public function setSearchField($searchField)
    {
        $this->searchField = $searchField;

        return $this;
    }

    /**
     * Get fieldSearch
     *
     * @return string 
     */
    public function getSearchField()
    {
        return $this->searchField;
    }
}
