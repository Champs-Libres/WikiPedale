<?php

namespace Progracqteur\WikipedaleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load user into the db
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     *
     * @var Symfony\Component\DependencyInjection\ContainerInterface 
     */
    private $container;
    
    public function getOrder()
    {
        return 300;
    }
    
    /**
     * Loading the users into the DB.
     * 
     * @param ObjectManager $om
     */ 
    public function load(ObjectManager $om)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        
        echo "Loading the 'admin' USER (pwd 'admin')\n";
        $admin = new User(array("label" => "Robert Delieu", "username" => "admin",
            "password" => "admin", "email" => "RANDOM", "enable" => true, "phonenumber" => "RANDOM"));
        $admin->addRole(User::ROLE_ADMIN);
        $userManager->updateUser($admin);
        
        echo "Loading the 'user' USER (pwd 'user')\n";
        $user = new User(array("label" => "Arnaud Bobo", "username" => "user",
            "password" => "user", "email" => "RANDOM", "enable" => true, "phonenumber" => "RANDOM"));
        $userManager->updateUser($user);
        $this->addReference('user', $user);
        
        echo "Loading the 'moderator' USER (pwd 'moderator')\n";
        $moderator = new User(array("label" => "Monsieur Vélo Mons", "username" => "moderator",
            "password" => "moderator", "email" => "RANDOM", "enable" => true, "phonenumber" => "RANDOM"));
        $moderator->addGroup($this->getReference('group_MODERATOR-mons-0'));
        $userManager->updateUser($moderator);
        $this->addReference('moderator', $moderator);
        
        echo "Loading the 'manager' USER (pwd 'manager')\n";
        $manager = new User(array("label" => "Monsieur Travaux Mons", "username" => "manager",
            "password" => "manager", "email" => "RANDOM", "enable" => true, "phonenumber" => "RANDOM"));
        $manager->addGroup($this->getReference('group_MANAGER-mons-0'));
        $userManager->updateUser($manager);
        $this->addReference('manager', $manager);
        
        echo "Loading the 'manager1' USER (pwd 'manager1')\n";
        $manager1 = new User(array("label" => "Monsieur Travaux Region Mons", "username" => "manager1",
            "password" => "manager1", "email" => "RANDOM", "enable" => true, "phonenumber" => "RANDOM"));
        $manager1->addGroup($this->getReference('group_MANAGER-mons-1'));
        $userManager->updateUser($manager1);
        $this->addReference('manager1', $manager1);
        
        $om->flush();
    }
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    } 
}