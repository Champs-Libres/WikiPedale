<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services\Notification;

use Progracqteur\WikipedaleBundle\Resources\Services\Notification\NotificationSender;
use Progracqteur\WikipedaleBundle\Entity\Management\Notification\PendingNotification;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription;

/**
 * You must implements this class for creating a new kind of notification.
 * 
 * Then, you must register the implemented class as a tagged service, with the key :
 * 'progracqteur.wikipedale.notification.processor'.
 * 
 * The NotificationProcessor class will be called by the Notification command-line command.
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
abstract class NotificationProcessor {
    
    protected $transporters = array();
    
    /**
     * Register a new transporter.
     * 
     * @param \Progracqteur\WikipedaleBundle\Resources\Services\Notification\NotificationSender $sender
     */
    public function addTransporter(NotificationSender $transporter) {
        if (in_array($transporter->getKey(), $this->acceptTransporter())) {
            $this->transporters[] = $transporter;
        }
    }
    
    /**
     * Return the transporter with the given key
     * 
     * @param string $key
     * @return \Progracqteur\WikipedaleBundle\Resources\Services\Notification\NotificationSender
     */
    protected function getTransporter($key) {
        foreach($this->transporters as $transporter ){
            if ($transporter->getKey() === $key) {
                return $transporter;
            }
        }
    }
    
    /**
     * 
     * Prepare notifications with the given frequency, 
     * and give them to the appropriate NotificationSender.
     * 
     * @param int $frequency
     */
    abstract public function process($frequency);
    
    /**
     * This method must be executed after all the notification have been
     * sent by the NotificationSender.  
     * 
     * Typically, the aim of this method is to register the status of the notification
     * to "sent" (or to delete them from the database); i.e. executing the 
     * function $objectManager->flush()
     */
    abstract public function finishProcess();
    
    /**
     * and identifier of the NotificationProcessor
     * 
     * @return string
     */
    abstract public function getKey();
    
    /**
     * An array of string, each string must be the key of a NotificationSender.
     * 
     * @see NotificationSender::getKey
     * 
     * @return string[]
     */
    abstract public function acceptTransporter();
    
    /**
     * This method is used by the transporter to inform the current processor 
     * (the instance wich extends this class) of the result of the sending of each
     * individual $notification.
     * 
     * If the notification fail, an exception is catched and added to the $exception
     * parameter.
     * 
     * @param PendingNotification $notification the notification wich has been recently sent
     * @param \Exception $exception|null the exception thrown, if any. 
     */
    abstract public function postSendingProcess(PendingNotification $notification, \Exception $exception = null);
    
    /**
     * The aim of this method is to check the user's right to create this kind of 
     * notification. It helps ACL and controllers.
     * 
     * 
     * 
     * @return bool
     */
    abstract public function mayBeCreated(User $user);
    
    /**
     * Get the form to create/update the parameters of this notification.
     * 
     * @return \Symfony\Component\Form\FormTypeInterface
     */
    abstract public function getForm(User $user, NotificationSubscription $notification);
    
    /**
     * Get the template to render the form given by the getForm method.
     * 
     * 
     * @return string The name of the twig template
     */
    abstract public function getFormTemplate();
}

