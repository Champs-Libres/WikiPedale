<?php

namespace Progracqteur\WikipedaleBundle\Entity\Management;

use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Resources\Container\Hash;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Description of UnregisteredUser
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class UnregisteredUser extends User{
    
    private $ip;
    
    const ROLE_UNREGISTERED = "UNREGISTERED";
    
    
    
    
    public function __construct()
    {
        parent::__construct();
        $this->confirmed = false;
    }
    
    public function isRegistered()
    {
        return false;
    }
    
    public function getIp()
    {
        return $this->ip;
    }
    
    public function setIp($ip)
    {
        $this->ip = $ip;
    }
    

    /**
     * Get confirmed
     *
     * @return boolean 
     */
    public function getConfirmed()
    {
        return false;
    }
    
    public static function fromHash(Hash $hash)
    {
        $u = new self();
        $u->setIp($hash->ip);
        $u->setEmail($hash->email);
        $u->setLabel($hash->label);
        $u->setPhonenumber($hash->phonenumber);
        
        $d = new \DateTime($hash->createdate);
        $u->setCreationDate($d);
        
        return $u;
    }
    
    public function toHash()
    {
        $h = new Hash();
        $h->ip = $this->getIp();
        $h->label = $this->getLabel();
        $h->email = $this->getEmail();
        $h->createdate = $this->getCreationDate();
        $h->phonenumber = $this->getPhonenumber();
        
        return $h;
    }
    
    public function equals(UserInterface $user)
    {
        if ($user->isRegistered()) {
            return false;
        }
        
        if ($user instanceof UnregisteredUser)
        {
            return $user->toHash()->equals($this->toHash());
        } else {
            return false;
        }
    }
    
    public function getRoles(){
        return array(self::ROLE_UNREGISTERED);
    }
    
}

