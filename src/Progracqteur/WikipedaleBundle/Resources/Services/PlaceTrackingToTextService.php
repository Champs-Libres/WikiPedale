<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services;

use Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportTracking;
use Symfony\Component\Translation\Translator;
use Progracqteur\WikipedaleBundle\Resources\Security\ChangeService;
use Doctrine\ORM\EntityManager;

/**
 * This class transform a reportTracking entity into a text readable by
 * an human. 
 * 
 * The texts were discussed there : https://github.com/progracqteur/WikiPedale/issues/27
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class ReportTrackingToTextService {
    
    /**
     * the translator, stored by the constructor
     * @var Symfony\Component\Translation\Translator 
     */
    private $t;
    
    /**
     *
     * @var \Doctrine\ORM\EntityManager; 
     */
    private $em;
    
    
    
    public function __construct(Translator $translator, EntityManager $em) {
        $this->t = $translator;
        $this->em = $em;
    }
    
    /**
     * Return a string, which may be displayed to the user 
     * and described the changes.
     * The string is translatable.
     * 
     * 
     * @param \Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportTracking $reportTracking
     * @return string
     */
    public function toText(ReportTracking $reportTracking)
    {
        //domain of the messages :
        $domain = 'changes';
        
        //prepare common arguments for translator
        //try {
            $authorLabel = $reportTracking->getAuthor()->getLabel();
            //FIXME: this should not throw an error !
        //}
        //catch (\Exception $e) {$authorLabel = $this->t->trans('user.unknow', array(), $domain); }
        
        $reportName = $reportTracking->getReport()->getLabel();
        
        $args = array(
                '%author%' => $authorLabel,
                '%report%' => $reportName
            );
        
        
        //check if the report Tracking is a creation, return string if true
        if ($reportTracking->isCreation())
        {
            return $this->t->trans('report.is.created', $args, $domain);
        }
        
              
        //order the thanges to be founded by isset() function and array[key]
        $changes = $reportTracking->getChanges();
        $keyChanges = array(); 
        foreach ($changes as $change)
        {
            $keyChanges[$change->getType()] = $change;
        }
        
        //if the change is add a photo (do not consider other changes)
        if (isset($keyChanges[ChangeService::REPORT_ADD_PHOTO]))
        {
            return $this->t->trans('report.add.photo', $args, $domain);
        }
        
        //if the change concern the status of the report
        if (isset($keyChanges[ChangeService::REPORT_STATUS]))
        {
            $status = $keyChanges[ChangeService::REPORT_STATUS]->getNewValue();
            $args['%notation%'] = $status->getType();
            
            switch ($status->getValue())
            {
                case -1 : 
                    return $this->t->trans('report.status.rejected', $args, $domain);
                    break;
                case 0 :
                    return $this->t->trans('report.status.notReviewed', $args, $domain);
                    break;
                case 1 :
                    return $this->t->trans('report.status.takenIntoAccount', $args, $domain);
                    break;
                case 2 :
                    return $this->t->trans('report.status.inChange', $args, $domain);
                    break;
                case 3 :
                    return $this->t->trans('report.status.success', $args, $domain);
                    break;
            }
        }
        
        if (isset($keyChanges[ChangeService::REPORT_MANAGER_ADD]) 
                OR isset($keyChanges[ChangeService::REPORT_MANAGER_ALTER])) {
            
            if (isset($keyChanges[ChangeService::REPORT_MANAGER_ADD])) {
                $idGroupManager = $keyChanges[ChangeService::REPORT_MANAGER_ADD]->getNewValue();
            } else {
                $idGroupManager = $keyChanges[ChangeService::REPORT_MANAGER_ALTER]->getNewValue();
            }
            
            $groupManager = $this->em->getRepository('ProgracqteurWikipedaleBundle:Management\Group')
                    ->find($idGroupManager);
            
            $args['%group%'] = $groupManager->getName();
            
            return $this->t->trans('report.manager.new', $args, $domain);
            
        }
        
        //if the changes are other : 
        
        //count the changes
        $nb = count ($changes);
        
        //if only one : 
        if ($nb == 1)
        {
            $args['%change%'] = 
                 $this->getStringFromChangeType($changes[0]->getType());
            return $this->t->trans('report.change.one', $args, $domain);
        }
        
        if ($nb == 2)
        {
            $args['%change_%'] = 
                 $this->getStringFromChangeType($changes[0]->getType());
            $args['%change__%'] = 
                 $this->getStringFromChangeType($changes[1]->getType());
            return $this->t->trans('report.change.two', $args, $domain);
        }
        
        if ($nb > 2)
        {
            $args['%change0%'] = 
                 $this->getStringFromChangeType($changes[0]->getType());
            $args['%change1%'] = 
                 $this->getStringFromChangeType($changes[1]->getType());
            $more = $nb - 2;
            $args['%more%'] = $more;
            return $this->t->transChoice('report.change.more', $more, $args, $domain);
        }
        
        
        
        
    }
    
    
    private function getStringFromChangeType($type)
    {
        //domain of the translations
        $d = 'changes';
        
        switch ($type)
        {
            case ChangeService::REPORT_ADDRESS :
                return $this->t->trans('change.report.address' , array(), $d);
                break;
            case ChangeService::REPORT_DESCRIPTION:
                return $this->t->trans('change.report.description', array(), $d);
                break;
            case ChangeService::REPORT_GEOM:
                return $this->t->trans('change.report.geom', array(), $d);
                break;
            case ChangeService::REPORT_ADD_CATEGORY:
            case ChangeService::REPORT_REMOVE_CATEGORY:
                return $this->t->trans('change.report.category', array(), $d);
                break;
            case ChangeService::REPORT_REPORTTYPE_ALTER:
                return $this->t->trans('change.report.report_type', array(), $d);
            case ChangeService::REPORT_MODERATOR_COMMENT_ALTER:
                return $this->t->trans('change.report.moderator_comment', array(), $d);
            default:
                return $this->t->trans('change.report.other', array(), $d);
        }
    }
    
}

