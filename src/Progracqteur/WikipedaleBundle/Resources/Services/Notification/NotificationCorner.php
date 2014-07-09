<?php

namespace Progracqteur\WikipedaleBundle\Resources\Services\Notification;

use Symfony\Component\DependencyInjection\Container;

/**
 * This class receive all notifications processers ans senders
 * 
 * This is the main corner of the notification system.
 * 
 * This class gather :
 * - services tagged with 'progracqteur.wikipedale.notification.processor'. Those
 * should extends the abstract class NotificationProcessor. They are responsible of
 * searching for notifications into the database and give them to the transporters, 
 * which will be responsible, in turn, for sending them.
 * - services tagged with 'progracqteur.wikipedale.notification.transport', aka
 * the "transporters". Those should extend NotificationSender. 
 * 
 * NotificationCorner is executed into the wikipedale:notification:send command.
 * 
 * 
 * 
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class NotificationCorner {
    
    /**
     *
     * @var Symfony\Component\DependencyInjection\Container 
     */
    private $container;
    
    private $transporterIds = array();
    
    private $processorsIds = array();
    
    public function __construct(Container $container) {
        $this->container = $container;
    }

    
    public function addProcessorId($id) {
        $this->processorsIds[] = $id;
    }
    
    public function addTransporterId($id) {
        $this->transporterIds[] = $id;
    }
    
    public function getProcessors(){
        $a = array();
        
        foreach($this->processorsIds as $id) {
            $a[] = $this->container->get($id);
        }
        
        return $a;
    }
    
    public function getTransporters() {
        $a = array();
        
        foreach($this->transporterIds as $id) {
            $a[] = $this->container->get($id);
        }
        
        return $a;
    }
    
    /**
     * 
     * @param string $key
     * @return \Progracqteur\WikipedaleBundle\Resources\Services\NotificationProcessor
     */
    public function getProcessor($key) {
        foreach($this->getProcessors() as $processor) {
            if ($processor->getKey() === $key) {
                return $processor;
            }
        }
    }
}