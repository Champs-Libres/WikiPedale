<?php

namespace Progracqteur\WikipedaleBundle\Entity\Model\Report;

/**
 * Type of the report
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class ReportType {
    
    /**
     *
     * @var int 
     */
    private $id;
    
    /**
     *
     * @var string 
     */
    private $label;
    
    /**
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * 
     * @return string
     */
    public function getLabel() 
    {
        return $this->label;
    }
    
    /**
     * 
     * @param string $label
     * @return \Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportType
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }
}

