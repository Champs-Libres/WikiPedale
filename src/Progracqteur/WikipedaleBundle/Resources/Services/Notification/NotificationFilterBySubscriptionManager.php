<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services\Notification;

use Progracqteur\WikipedaleBundle\Resources\Services\Notification\NotificationFilter;
use Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription;
use Progracqteur\WikipedaleBundle\Resources\Security\ChangesetInterface;
use Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportTracking;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;

/**
 * This class filter all notification which may be send by Managers (ROLE_MANAGER).
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class NotificationFilterBySubscriptionManager implements NotificationFilter {
    
    private $listEvents;
    
    public function __construct($listEvents) {
        $this->listEvents = $listEvents;
    }
    
    /**
     * 
     * Return true if the notification is only for manager, and the user associated
     * with the notification is a manager, 
     * 
     * Return false instead.
     * 
     * @param \Progracqteur\WikipedaleBundle\Resources\Security\ChangesetInterface $changeset
     * @param \Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription $subscription
     * @return boolean
     */
    public function mayBeSend(ChangesetInterface $changeset, NotificationSubscription $subscription) {
        
        if ($subscription->getKind() !== NotificationSubscription::KIND_MANAGER) {
            return false;
        }
        
        if ($changeset instanceof ReportTracking) {
            
            //block notification for you own modifications
            if ($changeset->getAuthor()->getId() === $subscription->getOwner()->getId())
            {
                echo get_class($this)." : Changeset was made by the notification's owner - STOP  \n";
                return false;
            }
            
            
            //check if the subscriber is the manager of the report
            
            if ($changeset->getReport()->getManager() === null){
                return false;
            }
            $groups = $subscription->getOwner()->getGroups();
            
            $groupsManagerIds = array();
            
            foreach($groups as $group) {
                if ($group->getType() === Group::TYPE_MANAGER) {
                    $groupsManagerIds[] = $group->getId();
                }
            }
            
            
            
            if (in_array($changeset->getReport()->getManager()->getId(), $groupsManagerIds)) {
                return true;
            } else {
                return false;
            }
            
        } else {
            return false;
        }
    }    
}

