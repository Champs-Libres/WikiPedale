<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services\Notification;

use Progracqteur\WikipedaleBundle\Entity\Management\Notification\PendingNotification;
use Progracqteur\WikipedaleBundle\Resources\Services\Notification\NotificationCorner;
use \Exception;

/**
 *
 * @author Julien Fastré <Julien arobase fastre POINT info>
 */
abstract class NotificationSender {
    
    /**
     *
     * @var \Progracqteur\WikipedaleBundle\Resources\Services\Notification\NotificationCorner 
     */
    private $notificationCorner;
    
    public function setNotificationCorner(NotificationCorner $corner) {
        $this->notificationCorner = $corner;
    }
    
    public function postProcess(PendingNotification $notification, Exception $exception = null) {
        $this->notificationCorner
                ->getProcessor($notification->getSubscription()->getKind())
                ->postSendingProcess($notification, $exception);
    }
    
    /**
     * Register a notificaiton which will be send later, when the function "send"
     * will be executed
     * 
     * @param PendingNotification $notification the notification to send
     */
    abstract public function addNotification(PendingNotification $notification);
    
    /**
     * This command really send the notification.
     */
    abstract public function send();
    
    /**
     * A string which will be an unique identifier for the sender. This key is
     * used by notificationCorner, NotificationProcessor, ...
     * 
     * @return string
     */
    abstract public function getKey();
    
    
}


