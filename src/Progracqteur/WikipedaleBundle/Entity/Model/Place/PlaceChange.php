<?php

namespace Progracqteur\WikipedaleBundle\Entity\Model\Place;

use Progracqteur\WikipedaleBundle\Resources\Security\ChangeInterface;

/**
 * Description of PlaceChange
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class PlaceChange implements ChangeInterface{
    
    private $type;
    
    public function __construct($type)
    {
        $this->type = $type;
    }
    
    public function getType() {
        return $this->type;
    }
}

