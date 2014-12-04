<?php

namespace Progracqteur\WikipedaleBundle\Entity\Model\Report;

use Progracqteur\WikipedaleBundle\Resources\Security\ChangesetInterface;
use Progracqteur\WikipedaleBundle\Resources\Container\Hash;
use Progracqteur\WikipedaleBundle\Resources\Security\ChangeService;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Entity\Management\UnregisteredUser;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;
use Progracqteur\WikipedaleBundle\Resources\Geo\Point;
use Progracqteur\WikipedaleBundle\Resources\Container\Address;

/**
 * ReportTracking store changes on Report instances.
 * 
 * ReportTracking is iterable: every element of an iteration is an instance of 
 * ReportChange
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class ReportTracking implements ChangesetInterface {
    
    private $id;
    
    private $author;
    
    private $details;
    
    private $types = array();
    private $values = array();
    
    private $isCreation = false;
    
    private $date;
    
    /**
     *
     * @var Progracqteur\WikipedaleBundle\Entity\Model\Report
     */
    private $report;
    
    public function __construct(Report $report)
    {
        $this->details = new Hash;
        $this->report = $report;
        $this->date = new \DateTime();
    }
    
    /**
     * 
     * @return Progracqteur\WikipedaleBundle\Entity\Model\Report
     */
    public function getReport()
    {
        return $this->report;
    }
    
    /**
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function getIdUUID()
    {
        return dechex($this->getId());
    }
    
    public function addChange($type, $newValue, $options = array())
    {
        if (!in_array($type, $this->types))
        {
            //pour le suivi des changements par Security
            $this->types[] = $type;
            $this->values[] = $newValue;
            
            //pour l'enregistrement dans la base de donnée
            
            if ($this->details->has('changes') === false)
            {
                $this->details->changes = new Hash();
            }
            
            //transformation de newValue si nécessaire
            //et traitement de creation
            switch ($type)
            {
                case ChangeService::REPORT_CREATOR:
                    //Il ne faut rien faire: report creator n'est normalement pas permis
                    break;
                case ChangeService::REPORT_ADD_PHOTO:
                    $newValue = $newValue->getFileName();
                    break;
                case ChangeService::REPORT_CREATION:
                    $this->setCreation(true);
                    //il n'y a pas d'autrs modifs à effectuer
                    break;
                case ChangeService::REPORT_GEOM:
                    $newValue = $newValue->toGeoJson();
                    break;
                case ChangeService::REPORT_ADDRESS:
                    $newValue = json_encode($newValue->toArray());
                    break;
                case ChangeService::REPORT_STATUS:
                    $a = array('type' => $newValue->getType(),
                        'value' => $newValue->getValue());
                    $newValue = json_encode($a);
                    break;
                case ChangeService::REPORT_REPORTTYPE_ALTER:
                    $newValue = $newValue->getId();
                    break;
                case ChangeService::REPORT_CATEGORY:
                    $newValue = $newValue->getId();
                    break;
                case ChangeService::REPORT_ADD_CATEGORY:
                case ChangeService::REPORT_REMOVE_CATEGORY:
                    $ids = array();
                    foreach ($newValue as $category)
                    {
                        $ids[]['id'] = $category->getId();
                    }
                    $newValue = json_encode($ids);
                    break;
                case ChangeService::REPORT_MODERATOR_COMMENT_ALTER:
                    $newValue = $newValue;
                    break;
                case ChangeService::REPORT_MANAGER_ADD:
                case ChangeService::REPORT_MANAGER_ALTER:
                case ChangeService::REPORT_MANAGER_REMOVE:
                    $newValue = $newValue->getId();
                    break;
                case ChangeService::REPORT_COMMENT_MODERATOR_MANAGER_ADD:
                    $newValue = $newValue->getId();
                    break;
                case ChangeService::REPORT_TERM:
                    $newValue = $newValue;
                    break;
                case ChangeService::REPORT_MODERATOR_ALTER:
                    $newValue = $newValue->getId();
                    break;
                //default:
                    //rien à faire
            }
            
            $this->details->changes->{$type} = $newValue;
            
            
            
        }
        
        
    }
    
    /**
     * return true if the changeset concern a creation of a report
     * @return boolean
     */
    public function isCreation() {
        if ($this->isCreation === NULL) {
            return true;
        }
        return $this->isCreation;
    }
    
    public function setCreation($boolean) {
        $this->isCreation = $boolean;
    }
    
    private $proxyAuthor;
    
    public function getAuthor() {
        
        if ($this->proxyAuthor !== null)
        {
            return $this->proxyAuthor;
        }
        
        if ($this->author !== null)
            return $this->author;
        else {
            if ($this->details->has('author')){
                $u = UnregisteredUser::fromHash($this->details->author);
                return $u;
            } else
                return null;
        }
    }
    
    public function setAuthor(User $user) {
        $this->proxyAuthor = $user;
        
        if ($user instanceof UnregisteredUser)
        {
            $this->details->author = $user->toHash();
        } else if ($user instanceof User) {
            $this->author = $user;
        }    
    }
    
    public function getDate()
    {
        return $this->date;
    }
    
    
    /**
     * get all the changes into an array of 
     * Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportChange
     * 
     * @return array
     */
    public function getChanges()
    {
        $a = array();
        
        foreach ($this as $changes)
        {
            $a[] = $changes;
        }
        
        return $a;
    }
    
    
    
    // fonctions pour l'implémentation de Iterable
    private $intTypes = 0;
    
    /**
     * 
     * @return \Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportChange
     */
    public function current() {
        $prop = $this->types[$this->intTypes];
        $val = $this->values[$this->intTypes];
        return new ReportChange($prop, $val);
    }
        
    public function key() {
        return $this->intTypes;
    }
    
    public function next() {
        $this->intTypes++;
    }
    
    public function rewind() {
        $this->intTypes = 0;
    }
    
    public function valid() {
        
        if ($this->details->has("changes")=== true && count($this->types) === 0)
        {
            $this->prepareIterationFromHash();
        }
        
        return isset($this->types[$this->intTypes]);
    }
    
    /**
     * this function prepare the class for iteration. It transforms the hash
     * into ReportChanges elements, ready to be iterated one by one.
     */
    private function prepareIterationFromHash()
    {
        $a = $this->details->changes->toArray();
        
        foreach ($a as $key => $value)
        {
            $this->types[] = $key;
            
            switch ($key)
            {
                case ChangeService::REPORT_GEOM:
                    $newValue = Point::fromGeoJson($value);
                    break;
                case ChangeService::REPORT_ADDRESS:
                    $a = json_decode($value);
                    $newValue = Address::fromArray($a);
                    break;
                case ChangeService::REPORT_STATUS:
                    $a = json_decode($value); 
                    $status = new ReportStatus();
                    $status->setType($a->type)->setValue($a->value);
                    $newValue = $status;
                    break;
                case ChangeService::REPORT_ADD_CATEGORY: //DEPRECIATE
                case ChangeService::REPORT_REMOVE_CATEGORY: //DEPRECIATEs
                    $newValue = $json_decode($value);
                    break;
                default: //REPORT_CATEGORY, REPORT_REPORTTYPE_ALTER, REPORT_CREATION, REPORT_ADD_PHOTO, REPORT_CREATOR
                    $newValue = $value;
                    break;
            }

            //for debugging in case of message "Notice: Undefined variable: 
            //newValue in /home/user/public_html/uello21/src/Progracqteur/WikipedaleBundle/Entity/Model/Report/ReportTracking.php 
            //line 295"
            //try {
                $this->values[] = $newValue;
            //} catch (\Exception $e) {
            //    echo $key ." ".$e->getMessage();
            //}
        }
    }
    
    public function checkIfEmpty() {
        $this->getReport()->checkEmptyReportTracking();
    }

}

