<?php

namespace Progracqteur\WikipedaleBundle\Entity\Management;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Progracqteur\WikipedaleBundle\Resources\Container\Hash;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription;
use Doctrine\Common\Collections\ArrayCollection;
use Progracqteur\WikipedaleBundle\Resources\Generator\StringGenerator;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;

/**
 * Progracqteur\WikipedaleBundle\Entity\Management\User
 */
class User extends BaseUser
{
    protected $email = '';

    /**
    * the way the user want to be publicly known
    * 
    * @var string $label
    */
    protected $label = '';

    /**
    *
    * @var string 
    */
    protected $phonenumber = "";

    /**
    * @var datetime $creationDate
    */
    protected $creationDate;

    /**
    * @var Progracqteur\WikipedaleBundle\Resources\Container\Hash $infos
    */
    private $infos;

    /**
    * @var integer $nbComment
    */
    private $nbComment = 0;

    /**
    * @var integer $nbVote
    */
    private $nbVote = 0;

    /**
    *
    * @var boolean 
    */
    private $virtual = false;

    /**
    *
    * @var Doctrine\Common\Collections\ArrayCollection
    */
    private $notificationSubscriptions;

    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
    * indicate if the user may create or alter categories on a report
    * 
    * @var string
    */
    const ROLE_CATEGORY = 'ROLE_CATEGORY'; 

    /**
    * indicate if the user may create or alter notation. 
    * The group which use it must be associated with a particular notation
    * 
    * @var string
    */
    const ROLE_NOTATION = 'ROLE_NOTATION';

    /**
    * @var string
    */
    const ROLE_MANAGER = 'ROLE_MANAGER';

    /**
    * @var string
    */
    const ROLE_MODERATOR = 'ROLE_MODERATOR';

    /**
    * indicate if the user may alter details of a little point
    * 
    * @var string
    */
    const ROLE_DETAILS_LITTLE = 'ROLE_DETAILS_LITTLE';

    /**
    * indicate if the user may alter details of a BIG point
    * 
    * @var string
    */
    const ROLE_DETAILS_BIG = 'ROLE_DETAILS_BIG';

    /**
    * 
    * indicate if the user may publish/unpublish a report
    * 
    * @var string 
    */
    const ROLE_PUBLISHED = 'ROLE_PUBLISHED';

    /**
    * indicate if the user may see email and personal
    * details of other users
    * 
    * @var string
    */
    const ROLE_SEE_USER_DETAILS = 'ROLE_SEE_USER_DETAILS';

    /**
    * indicate if the user may alter or modify the manager
    * of a report
    * 
    * @var string
    */
    const ROLE_MANAGER_ALTER = 'ROLE_MANAGER_ALTER';

    /**
    * indicate if the user may alter the report type
    */
    const ROLE_REPORTTYPE_ALTER = 'ROLE_PLACETYPE_ALTER';

    /**
    * indicate if the user may see unaccepted entities (like reports,
    *  photos, comments, etc.)
    */
    const ROLE_SEE_UNACCEPTED = 'ROLE_SEE_UNACCEPTED';

    /**
    * indicate if the user may alter the moderator's comment
    * the moderator's comment is inside a report: this is not a comment entity
    */
    const ROLE_MODERATOR_COMMENT_ALTER = 'ROLE_MODERATOR_COMMENT_ALTER';

    /**
    * indicate if the user may add and see private comments between
    * moderators and manager. This concern comment entity and not 
    * report's moderator comment
    */
    const ROLE_COMMENT_MODERATOR_MANAGER = 'ROLE_COMMENT_MODERATOR_MANAGER';

    /**
    * indicate if the user may alter the term.
    */

    const ROLE_REPORT_TERM = 'ROLE_PLACE_TERM';
   
   /**
    * indicate if the user/group may change the moderator
    */
   const ROLE_MODERATOR_ALTER = 'ROLE_MODERATOR_ALTER';
   
    /**
    * Creates a new user
    * @param array of parameters :
    * - 'label' => the label of the user (if equals to 'RANDOM' : generation of a random label)
    * - 'username' => the username of the user (if equals to 'RANDOM' : generation of a random label)
    * - 'password' => the  password of the user.
    * - 'email' => the email of the user (if equals to 'RANDOM' : genration of a random email)
    * - 'phonenumber' => the phonenumber of the user (if equals to 'RANDOM' : generation of a random phonumber)
    * @return  Progracqteur\WikipedaleBundle\Entity\Model\User
    */
    public function __construct($params = array())
    {
        parent::__construct();
        $this->setCreationDate(new \DateTime());
        $this->infos = new Hash();
        $this->notificationSubscriptions = new ArrayCollection();

        if (array_key_exists('label', $params)) {
            if($params['label'] === 'RANDOM') {
                $this->setLabel(StringGenerator::randomGenerate(10));
            } else {
                $this->setLabel($params['label']);
            }
        }

        if (array_key_exists('username', $params)) {
            if($params['username'] === 'RANDOM') {
                $this->setUsername(StringGenerator::randomGenerate(10));
            } else {
                $this->setUsername($params['username']);
            }
        }

        if (array_key_exists('password', $params)) {
            $this->setPlainPassword($params['password']);
        }

        if (array_key_exists('email', $params)) {
            if($params['email'] === 'RANDOM') {
                $this->setEmail((StringGenerator::randomGenerate(6) . '@blopblop.be'));
            } else {
                $this->setEmail($params['email']);
            }
        }

        if (array_key_exists('phonenumber', $params)) {
            if($params['phonenumber'] === 'RANDOM') {
                $this->setPhonenumber(StringGenerator::randomGenerate(10));
            } else {
                $this->setPhonenumber($params['phonenumber']);
            }
        }

        if(array_key_exists('enable', $params)) {
            $this->setEnabled($params['enable']);
        }
   }

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
    * Set label
    * @param string $label
    */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
    * Get label
    *
    * @return string 
    */
    public function getLabel()
    {
        return $this->label;
    }

    /**
    * Set creationDate
    *
    * @param datetime $creationDate
    */
    protected function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
    * Get creationDate
    *
    * @return datetime 
    */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
    * Set confirmed
    *
    * @param boolean $confirmed
    */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
        return $this;
    }

    /**
    * Get confirmed
    *
    * @return boolean 
    */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
    * 
    * @param string $phonenumber
    * @return \Progracqteur\WikipedaleBundle\Entity\Management\User
    */
    public function setPhonenumber($phonenumber)
    {
        $this->phonenumber = $phonenumber;
        return $this;
    }

    /**
    * 
    * @return string
    */
    public function getPhonenumber()
    {
        return $this->phonenumber;
    }    

    /**
    * Set infos
    *
    * @param Progracqteur\WikipedaleBundle\Resources\Container\Hash $infos
    */
    public function setInfos(Hash $infos)
    {
        $this->infos = $infos;
        return $this;
    }

    /**
    * Get infos
    *
    * @return Progracqteur\WikipedaleBundle\Resources\Container\Hash 
    */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
    * Set nbComment
    *
    * 
    */
    public function increaseNbComment()
    {
        $this->nbComment++;
        return $this;
    }

    /**
    * Get nbComment
    *
    * @return integer 
    */
    public function getNbComment()
    {
        return $this->nbComment;
    }

    /**
    * Set nbVote
    *
    * 
    */
    public function increaseNbVote($nbVote)
    {
        $this->nbVote++;
        return $this;
    }

    /**
    * Get nbVote
    *
    * @return integer 
    */
    public function getNbVote()
    {
        return $this->nbVote;
    }

    public function isRegistered()
    {
        return true;
    }

    public function setVirtual($virtual) {
        $this->virtual = $virtual;
        $this->setLocked($virtual);
        return $this;
    }

    public function isVirtual() {
        return $this->virtual;
    }

    public function isVirtualConsistant(\Symfony\Component\Validator\ExecutionContextInterface $context) {
        if ($this->isVirtual() === true ) {
            if ($this->isLocked() === false ) {
                $context->addViolationAt('locked', 
                    "admin.profile_user.inconsistent_virtual_lock", array(), null);
            }
        }
    }

    /**
    * 
    * @return Doctrine\Common\Collections\ArrayCollection
    */
    public function getNotificationSubscriptions()
    {
        return $this->notificationSubscriptions;
    }

    public function equals(UserInterface $user) {
        if ($user instanceof UnregisteredUser)
            return false;
        else {
            return $user->getId() === $this->getId();
        }
    }

    /**
    * 
    * @param \Progracqteur\WikipedaleBundle\Entity\Management\NotificationSubscription $notification
    * @return \Progracqteur\WikipedaleBundle\Entity\Management\User
    */
    public function addNotificationSubscription(NotificationSubscription $notification)
    {
        $this->notificationSubscriptions->add($notification);
        return $this;
    }

    public function removeNotificationSubscription(NotificationSubscription $notification)
    {
        foreach($this->notificationSubscriptions as $key => $not) {
            if ($not->getId() === $notification->getId()) {
                $this->notificationSubscriptions->remove($key);
                return $this;
            }
        }

        return $this;
    }

    public function addGroup(\FOS\UserBundle\Model\GroupInterface $group)
    { 
      if ( ! $this->getGroups()->contains($group)) {
            parent::addGroup($group);

            //add a notification subscription if group of type moderator or manager
            switch ($group->getType()) {
                case Group::TYPE_MANAGER:
                    $notification = new NotificationSubscription();
                    $notification->setKind(NotificationSubscription::KIND_MANAGER);
                    break;
                case Group::TYPE_MODERATOR:
                    $notification = new NotificationSubscription();
                    $notification->setKind(NotificationSubscription::KIND_MODERATOR);
                    break;
                default:
                    $notification = null;
                break;
            }

            if ($notification !== null) {
                $notification->setFrequency(NotificationSubscription::FREQUENCY_MINUTELY)
                    ->setGroup($group)
                    ->setOwner($this)
                    ->setZone($group->getZone())
                ;
                $this->addNotificationSubscription($notification);
            }
        }
      
        return $this;
    }

    public function removeGroup(\FOS\UserBundle\Model\GroupInterface $group)
    {
        if ($this->getGroups()->contains($group)) {
            parent::removeGroup($group);
         
            //remove the notification if group of type moderator or manager
            if (in_array($group->getType(), 
                array( Group::TYPE_MANAGER or Group::TYPE_MODERATOR))) {
                foreach ($this->getNotificationSubscriptions() as $notification) {
                    if ($notification->hasGroup() && 
                       $notification->getGroup()->getId() === $group->getId()) {
                        $this->removeNotificationSubscription($notification);
                    }
                }
            }
        }
    }  

}

