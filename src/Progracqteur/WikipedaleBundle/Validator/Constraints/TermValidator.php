<?php

namespace Progracqteur\WikipedaleBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Check if the field has a valid term.
 * 
 * Terms are defined in parameters.yml (see example in parameters.dist.yml)
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class TermValidator extends ConstraintValidator {
    
    /**
     *
     * @var array 
     */
    private $report_type;
    
    private $message = "validators.term.not_valid_term";
    
    
    public function __construct($report_type) {
        $this->report_type = $report_type;
    }
    
    /**
     * 
     * @param \Progracqteur\WikipedaleBundle\Entity\Model\Place $place (not defined in interface)
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($place, Constraint $constraint) {
        //TODO term is now used in categories. This should be adapted.
        
        $valid_terms = array();
        
        foreach ($this->report_type as $target => $array) {
            //TODO : we work only for bike place now
            if ($target === "bike") {
                foreach ($array["terms"] as $term) {
                    $valid_terms[] = $term['key'];
                }
            }
        }
        
        if (in_array($place->getTerm(), $valid_terms)) {
            //this is ok !
        } else {
            //we have a problem :-)
            $this->context->addViolationAt('term', $this->message, 
                    array('%term%' => $place->getTerm()), null);
        }
        
    }
   
}

