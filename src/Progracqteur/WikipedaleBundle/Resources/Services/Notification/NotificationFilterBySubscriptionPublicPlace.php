<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services\Notification;

use Progracqteur\WikipedaleBundle\Resources\Services\Notification\NotificationFilter;
use Progracqteur\WikipedaleBundle\Resources\Security\ChangesetInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription;
use Progracqteur\WikipedaleBundle\Resources\Security\ChangeService;

/**
 * Description of NotificationFilterBySubscriptionPublicPlace
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

