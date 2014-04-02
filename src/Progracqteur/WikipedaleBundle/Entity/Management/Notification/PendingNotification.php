<?php

namespace Progracqteur\WikipedaleBundle\Entity\Management\Notification;

use Doctrine\ORM\Mapping as ORM;

/**
 * PendingNotification
 */
class PendingNotification
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription
     */
    private $subscription;

    /**
     * @var \Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportTracking
     */
    private $reportTracking;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set subscription
     *
     * @param \Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription $subscription
     * @return PendingNotification
     */
    public function setSubscription(\Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription $subscription = null)
    {
        $this->subscription = $subscription;
    
        return $this;
    }

    /**
     * Get subscription
     *
     * @return \Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription 
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Set reportTracking
     *
     * @param \Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportTracking $reportTracking
     * @return PendingNotification
     */
    public function setreportTracking(\Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportTracking $reportTracking = null)
    {
        $this->reportTracking = $reportTracking;
    
        return $this;
    }

    /**
     * Get reportTracking
     *
     * @return \Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportTracking 
     */
    public function getreportTracking()
    {
        return $this->reportTracking;
    }
}