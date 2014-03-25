<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services\Notification;

use Progracqteur\WikipedaleBundle\Resources\Services\Notification\NotificationFilter;
use Progracqteur\WikipedaleBundle\Resources\Security\ChangesetInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription;
use Progracqteur\WikipedaleBundle\Resources\Security\ChangeService;

/**
 * Filter notification which may be public, for user which are not MODERATOR and
 * MANAGER
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class NotificationFilterBySubscriptionPublicPlace implements NotificationFilter {
    
    
    public static $authorizedChangesToBeNotified = array(
        ChangeService::REPORT_ADDRESS,
        ChangeService::REPORT_ACCEPTED,
        ChangeService::REPORT_ADD_PHOTO,
        ChangeService::REPORT_ADD_VOTE,
        ChangeService::REPORT_DESCRIPTION,
        ChangeService::REPORT_GEOM,
        ChangeService::REPORT_MANAGER_ADD,
        ChangeService::REPORT_MANAGER_ALTER,
        ChangeService::REPORT_MODERATOR_COMMENT_ALTER,
        ChangeService::REPORT_STATUS
    );
    
    /**
     * Return true if the notification is within $authorizedChangesToBeNotified, 
     * and may be send. Return false instead.
     * 
     * @param \Progracqteur\WikipedaleBundle\Resources\Security\ChangesetInterface $changeset
     * @param \Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription $subscription
     * @return boolean
     */
    public function mayBeSend(ChangesetInterface $changeset, 
            NotificationSubscription $subscription) {
        
        $maybesend = true;
        
        //if on change may NOT be notified, the whole notification is blocked.
        foreach ($changeset as $change ) {
            if (! in_array($change->getType(), self::$authorizedChangesToBeNotified) ) {
                $maybesend = false;
            }
        }
        
        return $maybesend;
        
        
    }    
}

