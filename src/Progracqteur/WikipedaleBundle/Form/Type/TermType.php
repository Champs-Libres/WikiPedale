<?php

namespace Progracqteur\WikipedaleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Create type based on term defined in parameters.place.type (in parameters.yml)
 *
 * @author julien
 */
class TermType extends AbstractType {
    
    private $report_type;
    
    public function __construct($report_type) {
        $this->report_type = $report_type;
    }
    
    

    
    public function getName() {
        return 'term';
    }
    
    public function getParent() {
        return 'choice';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        
        $valid_terms = array();
        
        foreach ($this->report_type as $target => $array) {
            //TODO : we work only for bike place now
            if ($target === "bike") {
                foreach ($array["terms"] as $term) {
                    $valid_terms[$term['key']] = $term['label'];
                }
            }
        }
        
        $resolver->setDefaults(array( 
           'choices' => $valid_terms,
           'empty_value' => 'report_type.form.type.term.choose'
        ));
    }

}
