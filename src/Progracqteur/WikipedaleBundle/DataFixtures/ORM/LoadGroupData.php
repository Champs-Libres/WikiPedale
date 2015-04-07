<?php

namespace Progracqteur\WikipedaleBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;



/**
 * Load Groups into the DB
 * 
 * A group is an association of user that can notate / moderate / manage reports
 * of a zone
 *
 * @author Champs-Libres COOP
 */
class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 250;
    }
    
    /*
     * Create a given number of groups for a given zone and a give type
     * 
     * @param $om The object manager.
     * @param $zoneSlug The slug of the zone
     * @param $nbr The number of groupes to create
     */
    private function loadGroups(ObjectManager $om, $zoneSlug, $type, $nbr)
    {
        for($i = 0; $i < $nbr; $i ++) {
            $groupName = $type.'-'.$zoneSlug.'-'.$i;
            echo 'Adding the '.$groupName." Group\n"
                ;
            $groupRoles = array();
            $group = new Group($groupName, $groupRoles);
            $group->setType($type)
                ->setNotation($this->getReference('notation_cem'))
                ->setZone($this->getReference('zone_'.$zoneSlug));
            $om->persist($group);
            $this->addReference('group_'.$groupName, $group);
        }
    }
    
    /*
     * Load groups into the DB
     */
    public function load(ObjectManager $om)
    {
        $zoneSlugWithManagerGroup = ['mons', 'namur', 'mons-ring'];
        
        foreach ($zoneSlugWithManagerGroup as $zoneSlug) {
            $this->loadGroups($om, $zoneSlug, Group::TYPE_MANAGER, 2);
        }
        
        foreach ($zoneSlugWithManagerGroup as $zoneSlug) {
            $this->loadGroups($om, $zoneSlug, Group::TYPE_MODERATOR, 2);
        }
          
        $om->flush();
    }
}
