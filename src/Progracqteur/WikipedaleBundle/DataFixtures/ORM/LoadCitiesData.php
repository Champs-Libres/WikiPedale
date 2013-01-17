<?php

namespace Progracqteur\WikipedaleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of LoadCitiesData
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class LoadCitiesData extends AbstractFixture implements ContainerAwareInterface, 
        OrderedFixtureInterface 
{
    
    /**
     *
     * @var Symfony\Component\DependencyInjection\ContainerInterface 
     */
    private $container;
    
    public function getOrder() {
        return 1;
    }

    public function load(ObjectManager $manager) {
        /**
         * @var \Doctrine\ORM\EntityManager
         */
        $em = $this->container->get('doctrine.orm.entity_manager');
        
        $nbCities = $em->createQuery("SELECT count(c.id) from 
            ProgracqteurWikipedaleBundle:Management\City c")
                ->getSingleScalarResult();
        
        if ($nbCities == 0) {
            
            $conn = $em->getConnection();
            $r = $conn->executeUpdate("insert into cities 
                (id, name, codeprovince, polygon, slug, center)  
                select gid, nom, nurgcdl2, geog, '', 
                        ST_Centroid(ST_geomFromText(ST_AsText(geog))) 
                        from limites where nom is not null;");
            echo "$r cities added to the database \n";
            $r = $conn->executeUpdate("update cities set slug = getslug(name);");
            echo "$r slug updated in the database \n";
            
        } else {
            echo "$nbCities records as cities. No update done. \n";
        }
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }
}

