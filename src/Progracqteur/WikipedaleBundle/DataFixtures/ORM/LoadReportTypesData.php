<?php

namespace Progracqteur\WikipedaleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Description of LoadPlaceTypesData
 *
 * @author Champs-Libres COOP
 */
class LoadReportTypesData extends AbstractFixture implements OrderedFixtureInterface
{    
    public function getOrder()
    {
        return 450;
    }

    public function load(ObjectManager $manager)
    {
        $types = array(
            'short' => 'petits problèmes',
            'long' => 'points noirs',
            'medium' => 'moyen terme'
        );
        
        foreach ($types as $key => $t )
        {
            echo "Loading $key type\n"; 
            $a = new ReportType();
            $a->setLabel($t);
            $manager->persist($a);
            
            $this->setReference('type_'.$key, $a);
        }
        
        $manager->flush();
    }
}

