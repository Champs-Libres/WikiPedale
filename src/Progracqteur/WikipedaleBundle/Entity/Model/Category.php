<?php

namespace Progracqteur\WikipedaleBundle\Entity\Model;

use Symfony\Component\Validator\ExecutionContext;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Category for a report. The categories are used for classifying the report.
 *
 * A Category is either parent either children. A category is parent if and only if it has not parent, 
 * otherwise the category is childen.
 *
 * A Category is assigned with a term that help to classify the reports.
 */
class Category
{
    /**
     * @var integer $id Unique identifier of a category
     */
    private $id;

    /**
     * @var string $label Label of a category
     */
    private $label;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $children Collection of the children of the category (if the category is parent otherwise this collection is empty)
     */
    private $children;

    /**
     * @var Progracqteur\WikipedaleBundle\Entity\Model\Category $parent The parent of the category (if the category is children)
     */
    private $parent;
    
    /**
     *
     * @var double $order Display order used in the application.
     */
    protected $order;
    
    /**
     * @var boolean $used True if the  category is used.
     */
    private $used = true;
    
    /** 
     * @var string  $term The associated term.
     */
    private $term;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function __toString() {
        return $this->getLabel();
    }
    
    /**
     * Get the id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set label
     *
     * @param string $label The new label
     * @return Category
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get the label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Return an hierarchical view of the label :
     * - if the category is parent, the returned string is "The category label"
     * - if the category is children, the returned string is "Tha parent category label > The category label"
     * 
     * @return string
     */
    public function getHierarchicalLabel() 
    {        
        if ($this->isParent()) {
            return $this->getLabel();
        } else {
            return $this->getParent()->getLabel().' > '.$this->getLabel();
        }            
    }

    /**
     * Get the children
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }


    /**
     * Get the children
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    private function _addChildren(Category $newChildren)
    {
        $this->children->add($newChildren);
        return $this;
    }


    /**
     * Return True if the categrory has children
     *
     * @return boolean
     */

    public function hasChildren()
    {
        return ($this->getChildren()->count() !== 0);
    }

    /**
     * Set parent 
     *
     * @param Progracqteur\WikipedaleBundle\Entity\Model\Category $parent
     * @return Category
     */
    public function setParent(\Progracqteur\WikipedaleBundle\Entity\Model\Category $parent = null)
    {
        if ($parent === null) {
            if($this->parent->children->contains($this)) {
                $this->parent->children->remove($this);
            }
        } else {
            $parent->_addChildren($this);
        }
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent
     *
     * @return Progracqteur\WikipedaleBundle\Entity\Model\Category 
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * Returns True if the category has a parent
     *
     * @return boolean
     */
    public function hasParent()
    {
        return ($this->parent !== null);
    }

    /**
     * Retuns True if the category is parent
     *
     * @return boolean
     */
    public function isParent()
    {
        return ! ($this->hasParent());
    }

    /**
     *
     */
    public function isParentAChild(ExecutionContext $context)
    {
        if ($this->hasParent())
        {
            if ($this->getParent()->hasParent())
            {
                if ($this->getParent()->getParent()->hasParent()) {
                    $context->addViolationAtSubPath('parent', 'admin.category.form.parent.parent_has_parent', array(), $this->getParent());
                }
            }
        }
    }
    
    /**
     * Return True if a category is still in use
     * 
     * @return boolean
     */
    public function isUsed(){
        return $this->used;
    }
    
    /**
     * Set if a category may be in used or not
     * 
     * @param boolean $used
     */
    public function setUsed($used)
    {
        $this->used = $used;
    }
    
    /**
     * Set the Display Order
     * 
     * @param double $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }
    
    /**
     * Get the Display Order
     * 
     * @return double
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get the term
     *
     * @return string
     */
    public function getTerm() {
        return $this->term;
    }
    
    /**
     * Set the term
     *
     * @param string $term The new term
     */
    public function setTerm($term) {
        $this->term = $term;
    }
}