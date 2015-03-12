<?php

namespace Progracqteur\WikipedaleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\Notation;

/**
 * Description of LoadNotationData
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class LoadNotationData extends AbstractFixture implements OrderedFixtureInterface
{    
    public function getOrder()
    {
        return 200;
    }

    public function load(ObjectManager $manager)
    {
        $notationIds = array('cem');
        
        foreach ($notationIds as $id) {
            $n = new Notation($id);
            $n->setName("default");
            $manager->persist($n);
            echo "Loading the notation_".$n->getId()." notation\n";
            $this->addReference('notation_'.$n->getId(), $n);
        }
        
        $manager->flush();
    }
}

