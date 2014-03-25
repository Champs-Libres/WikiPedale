<?php

namespace Progracqteur\WikipedaleBundle\Entity\Model\Report;

/**
 * This entity represent the statuses of the reports
 * Those are described by : 
 *    - a type ;
 *    - a value ;
 * 
 * the statuses were discussed there : https://github.com/progracqteur/WikiPedale/issues/37
 * 
 * 
 * 
 * 
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class ReportStatus {
    
    /**
     * the type of the status
     * @var string
     */
    private $type;
    
    /**
     * the value of the status
     * @var int
     */
    private $value = 0;
    
    /**
     * 
     * @return int
     */
    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * 
     * 
     * @return int
     */
    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
        return $this;
    }
    
    public function equals(ReportStatus $status)
    {
        return ($this->getType() ===  $status->getType() 
                && $this->getValue() === $status->getValue());
    }


    
}

