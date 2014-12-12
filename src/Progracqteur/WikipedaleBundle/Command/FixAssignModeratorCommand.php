<?php


namespace Progracqteur\WikipedaleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Assign a moderator to all report that does not have a controller
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class FixAssignModeratorCommand extends ContainerAwareCommand
{
    public function configure() 
    {
        $this->setName('uello:assign_moderator')
              ->setDescription("Add the default moderator to all report which "
                    . "does not have a moderator.");
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $reports = $this->_getReportsWithoutModerators();
        $output->writeln("<info>Found ".count($reports)." reports without moderators</info>");
        
        /**
         * @var \Progracqteur\WikipedaleBundle\Resources\Services\Designator\ModeratorDesignator
         */
        $designator = $this->getContainer()
              ->get('progracqteur.wikipedale.moderator_designator');
        
        foreach($reports as $report) {
            $moderator = $designator->getModerator($report);
            if ($moderator !== NULL) {
                $report->setModerator($moderator);
                $output->writeln("<info>Set moderator '$moderator' to report "
                  . "".$report->getId()."</info>");
            } else {
               $output->writeln("<warning>No moderator found for report "
                     . "".$report->getId()."</warning>");
               $moderator = NULL;
            }
        }
        
        $this->getContainer()->get('doctrine.orm.entity_manager')->flush();
        
        $output->writeln("Saved ! Do not forget to execute \n\t "
              . "DELETE FROM pendingnotification; \nif you do not want to "
              . "send notifications about this changes");
    }
    
    /**
     * 
     * @return \Progracqteur\WikipedaleBundle\Entity\Model\Report[]
     */
    private function _getReportsWithoutModerators()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        return $em->getRepository('ProgracqteurWikipedaleBundle:Model\Report')
              ->findBy(array("moderator" => null));
        /*return $em->createQuery('SELECT r '
              . 'FROM ProgracqteurWikipedaleBundle:Model\Report r '
              . 'WHERE r.moderator IS NULL ')
              ->getResult();*/
    }
}
