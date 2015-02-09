<?php

namespace Progracqteur\WikipedaleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\User;

/**
 * Set salt into password
 */
class FixPlainPasswordToHashCommand extends ContainerAwareCommand {
    protected function configure()
    {
        $this->setName('uello:fixPlainPasswordToHashCommand')
            ->setDescription("Password : plaintext -> hash (as in security.yml)");
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new Exception("Are you sure to use this method ? ", 1);
        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $users = $manager->getRepository('ProgracqteurWikipedaleBundle:Management\User')
            ->findAll();
        
        foreach ($users as $user) {
            $userPassword = $user->getPassword();
            $saltSize = strlen($user->getSalt());
            $plainPassword = substr($userPassword, 0, strlen($userPassword) - $saltSize -2);

            $encoder = $this->getContainer()->get('security.encoder_factory')->getEncoder($user);
            $encodedPass = $encoder->encodePassword($plainPassword, $user->getSalt());
            $user->setPassword($encodedPass);
        }
        $manager->flush();   
    }
}