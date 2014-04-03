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
     * @param \Progracqteur\WikipedaleBundle\Entity\Model\Report $report (not defined in interface)
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($report, Constraint $constraint) {
        //TODO term is now used in categories. This should be adapted.
        
        $valid_terms = array();
        
        foreach ($this->report_type as $target => $array) {
            //TODO : we work only for bike report now
            var_dump($target);
            var_dump($array);
            if ($target === "bike") {
                foreach ($array["terms"] as $term) {
                    $valid_terms[] = $term['key'];
                }
            }
        }

        echo "-------------\n";
        var_dump($valid_terms);
        echo "-------------\n";
        echo $report->getTerm();
        echo "--------------\n";
        //var_dump($report->getCategory());
        echo $report->getCategory()[0]->getTerm();
        echo "-------------\n";
        
        if (in_array($report->getCategory()->getTerm(), $valid_terms)) {
            //this is ok !
        } else {
            //we have a problem :-)
            $this->context->addViolationAt('term', $this->message, 
                    array('%term%' => $report->getTerm()), null);
        }
        
    }
   
}

