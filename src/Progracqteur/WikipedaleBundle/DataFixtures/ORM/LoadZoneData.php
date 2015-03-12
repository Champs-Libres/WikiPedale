<?php

namespace Progracqteur\WikipedaleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\Zone;
use Progracqteur\WikipedaleBundle\Resources\Geo\Polygon;
use Progracqteur\WikipedaleBundle\Resources\Geo\Point;

/**
 * Load zones into the DB
 * 
 * Zones are associated with Notation / Moderation / Management Group used
 * to describe what can the users do
 * 
 * @author Champs-Libres COOP
 */
class LoadZoneData extends AbstractFixture implements OrderedFixtureInterface 
{
    public function getOrder()
    {
        return 100;
    }
    
    private $cityZones = [
        ['name' => 'Mons', 'slug' => 'mons', 
            'east' => 3.87, 'north' => 50.52, 'west' => 4.07, 'south' => 50.38,
            'url' => 'mons.be', 'desc' => 'Description de Mons'],
        ['name' => 'Namur', 'slug' => 'namur', 
            'east' => 4.74, 'north' => 50.53, 'west' => 5.01, 'south' => 50.39,
            'url' => 'namur.be', 'desc' => 'Description de Namur'],
        ['name' => 'La Louvière', 'slug' => 'la-louviere', 
            'east' => 4.09, 'north' => 50.54, 'west' => 4.28, 'south' => 50.41,
            'url' => 'lalouviere.be', 'desc' => 'Description de La Louvière'],
        ['name' => 'Charleroi', 'slug' => 'charleroi', 
            'east' => 4.32, 'north' => 50.48, 'west' => 4.59, 'south' => 50.35,
            'url' => 'charleroi.be', 'desc' => 'Description de Charleroi'],
        ['name' => 'Lima', 'slug' => 'lima',
            'east' => -5, 'north' => -10, 'west' => -4, 'south' => -12,
            'url' => 'lima.pu', 'desc' => 'Description de Lima'],
        ['name' => 'Paris', 'slug' => 'paris',
            'east' => 4, 'north' => 48, 'west' => 5, 'south' => 47,
            'url' => 'paris.be', 'desc' => 'Description de Paris'],
        ['name' => 'Anvers', 'slug' => 'anvers',
            'east' => 5, 'north' => 51, 'west' => 5.5, 'south' => 50.8,
            'url' => 'Anvers.be', 'desc' => 'Description de Anvers'],
        ['name' => 'Conakry', 'slug' => 'conakry',
            'east' => 4, 'north' => -20, 'west' => 5, 'south' => -21,
            'url' => 'mons.be', 'desc' => 'Description de Conakry'],
        ['name' => 'Portsmouth', 'slug' => 'portshmouth',
            'east' => 50, 'north' => -2, 'west' => 51, 'south' => -1,
            'url' => 'mons.be', 'desc' => 'Description de Portsmouth'],
        ['name' => 'Moddsles;cs', 'slug' => 'moddslescs',
            'east' => 22.1, 'north' => 22.1, 'west' => 22.0, 'south' => 22.0,
            'url' => 'mdfqns.be', 'desc' => 'Description de Mfdqons']
    ];

    public function load(ObjectManager $om)
    {        
        foreach ($this->cityZones as $i => $data) {
            echo 'Loading the '.$data['name']." zone\n"
                ;
            $zone = new Zone();
            $zone->setId($i);
            $zone->setName($data['name']);
            $zone->setSlug($data['slug']);
            $zone->setCodeProvince(7000);
            $zone->setType(Zone::TYPE_CITY);
            $zone->setDescription($data['desc']);
            $zone->setPolygon(new Polygon([
                ['lat' => $data['north'], 'lon' => $data['east']],
                ['lat' => $data['north'], 'lon' => $data['west']],
                ['lat' => $data['south'], 'lon' => $data['west']],
                ['lat' => $data['south'], 'lon' => $data['east']],
                ['lat' => $data['north'], 'lon' => $data['east']]]));
            $zone->setCenter(new Point(($data['east'] + $data['west'])/ 2,
                ($data['north'] + $data['south'])/2));
            
            $om->persist($zone);
            
            $this->addReference('zone_'.$zone->getSlug(), $zone);
        }
        $om->flush();
    }
}