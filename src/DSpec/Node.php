<?php

namespace DSpec;

class Node
{
    protected $parent;
    protected $title;

    /**
     * @return null|Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Node
     * @return Node
     */
    public function setParent(Node $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return array
     */
    public function getAncestors()
    {
        $ancestors = array($this);
        $parent = $this->getParent();

        while ($parent) {
            array_unshift($ancestors, $parent);
            $parent = $parent->getParent();
        }

        return $ancestors;
    }

    /**
     *
     */
    public function getTitle()
    {
        return $this->title;
    }



}
