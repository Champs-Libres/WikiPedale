<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services\Notification;

use Progracqteur\WikipedaleBundle\Resources\Services\Notification\NotificationFilter;
use Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription;
use Progracqteur\WikipedaleBundle\Resources\Security\ChangesetInterface;
use Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportTracking;

/**
 * This class filter the notification which may be send only to MODERATORS.
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class NotificationFilterBySubscriptionModerator implements NotificationFilter {
    
    private $listEvents;
    
    public function __construct($listEvents) {
        $this->listEvents = $listEvents;
    }
    
    
    /**
     * 
     * Return true if the notification is only for moderator, and the user associated
     * with the notification is a moderator, 
     * 
     * Return false instead.
     * 
     * @param \Progracqteur\WikipedaleBundle\Resources\Security\ChangesetInterface $changeset
     * @param \Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription $subscription
     * @return boolean
     */
    public function mayBeSend(ChangesetInterface $changeset, NotificationSubscription $subscription) {
        
        if ($subscription->getKind() !== NotificationSubscription::KIND_MODERATOR) {
            return false;
        }
        
        if ($changeset instanceof ReportTracking) {
            
            //block notification for you own modifications
            if ($changeset->getAuthor()->getId() === $subscription->getOwner()->getId())
            {
                echo get_class($this)." : Changeset was made by the notification's owner - STOP  \n";
                return false;
            }
            
            return true;
        } else {
            return false;
        }
    }    
}

