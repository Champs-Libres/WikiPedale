<?php

namespace Progracqteur\WikipedaleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\User;

/**
 * Set salt into password
 */
class FixPasswordSaltCommand extends ContainerAwareCommand {
    
    protected function configure()
    {
        $this->setName('uello:ixPasswordSalt')
            ->setDescription("Fixe les password : set salt into password");
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $users = $manager->getRepository('ProgracqteurWikipedaleBundle:Management\User')
            ->findAll();
        
        foreach ($users as $user) {
            $userPassword = $user->getPassword();
            $userSalt = $user->getSalt();
            if(strpos($userPassword,$userSalt) === false) {
                $user->setPassword($userPassword.'{'.$userSalt.'}');
            }
        }
        $manager->flush();   
    }
}