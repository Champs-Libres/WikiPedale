<?php

namespace Progracqteur\WikipedaleBundle\Entity\Model\Report;

use Progracqteur\WikipedaleBundle\Resources\Security\ChangeInterface;

/**
 * The elements of the report which has been changed, with the type and the new
 * values. 
 * 
 * Types are stored in instances of
 *    Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportTracking
 * 
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class ReportChange implements ChangeInterface{
    
    private $type;
    private $value = null;
    
    public function __construct($type, $newValue = null)
    {
        $this->type = $type;
        $this->value = $newValue;
    }
    
    /**
     * The type of the change.
     * 
     * Types are stored in Progracqteur\WikipedaleBundle\Resources\Security\ChangeService
     * 
     * @return int
     */
    public function getType() {
        return $this->type;
    }
    
    /**
     * The type of getValue is :
     * if type is ..... type of getValue is ....
     * 
     * 
     * 
     * If the type is :
     * - REPORT_STATUS : the new value is an instance of Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportStatus
     * - REPORT_PHOTO: a string which represent the filename of the new picture
     * - REPORT_ADDRESS : Progracqteur\WikipedaleBundle\Resources\Container\Address
     * - REPORT_DESCRIPTION = string
     * - REPORT_GEOM = CrEOF\Spatial\PHP\Types\Geometry\Point;
     * - REPORT_ADD_COMMENT = not implemented;
     * - REPORT_ADD_VOTE = not implemented;
     * - REPORT_ADD_PHOTO = string of the filename;
     * - REPORT_REMOVE_PHOTO = not implemented;
     * - REPORT_STATUS_BICYCLE = deprecated;
     * - REPORT_STATUS_Zone = deprecated;
     * - REPORT_CREATOR = this should not happen;
     * - REPORT_ACCEPTED = boolean;
     * - REPORT_CATEGORY = the new id of the category
     * - REPORT_ADD_CATEGORY = DEPRECIATE :  array of id of categories after the changes were made; 
     * - REPORT_REMOVE_CATEGORY =  DEPRECIATE : array of id of categories afther the changes were made;
     * - REPORT_REPORTTYPE_ALTER = id of the new reporttype
     * -REPORT_MANAGER_ADD ou REPORT_MANAGER_ALTER ou REPORT_MANAGER_REMOVE: id of the manager's group
     * 
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->value;
    }
}

