<?php

namespace Progracqteur\WikipedaleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Resources\Geo\Point;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;
use Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportStatus;
use Progracqteur\WikipedaleBundle\Resources\Container\Hash;
use Progracqteur\WikipedaleBundle\Resources\Container\Address;
use Progracqteur\WikipedaleBundle\Entity\Management\UnregisteredUser;

/*
 * 1 seul status
 * notation tjs cem
 * tjs 1 manager
 */

class LoadReportData extends AbstractFixture implements OrderedFixtureInterface, \Symfony\Component\DependencyInjection\ContainerAwareInterface
{
    public function getOrder()
    {
        return 500;
    }
    
    /**
     *Cette fonction ajoute trois report à des endroits aléatoires
     * @param ObjectManager $manager 
     */
    public function load(ObjectManager $manager)
    {       
        $notations = array('gracq', "spw", "villedemons", 'cem', 'cem', 'cem', 'cem');
        $valuesNotations = array(-1,0,1,2,3);
        
        
        for ($i=0; $i < 20; $i++)
        {
            echo "Création du point $i (associé à un utilisateur) \n";
            
            $point = $this->getRandomPoint();
        
            $str = $this->createId();

            $report = new Report();
            $report->setCreator($this->getReference('user'));
            $report->setDescription($this->getLipsum(rand(10,60)));
            if (rand(0,5) > 3) //add randomly a moderator comment, or not
                $report->setModeratorComment($this->getLipsum(rand(10,90)));
            $report->setGeom($point);
            
            $add = $this->geolocate($point);
            
            $report->setAddress($add);
            
            //add a random category amongst the one loaded
            $cat_array = array('1', '2', '3');
            $rand = array_rand($cat_array);
            $cat_string_ref = 'cat'.$cat_array[$rand];
            echo "add $cat_string_ref \n";
            $report->setCategory($this->getReference('cat'.$cat_array[$rand]));
            
            //ajout un statut à toutes les report, sauf à quatre d'entre elles
            if ($i != 0 OR $i != 10 OR $i != 15 OR $i != 19)
            {
                $p = new ReportStatus();
                $p->setType($notations[array_rand($notations)])
                        ->setValue($valuesNotations[array_rand($valuesNotations)]);
                $report->addStatus($p);
                
                /*
                //ajoute un deuxième statut à une sur trois
                if ($i%3 == 0)
                {
                    $p = new ReportStatus();
                $p->setType($notations[array_rand($notations)])
                        ->setValue($valuesNotations[array_rand($valuesNotations)]);
                $report->addStatus($p);
                }
                */
            }
            
            //ajout d'un manager de voirie responsable à une report sur deux
            //if ((($i+2) % 2) === 0)
            //{
                $report->setManager($this->getReference('manager_mons'));
            //}
            
            $report->getChangeset()->setAuthor($this->getReference('user'));
            
            //add a type (little 4 more frequently)
            $type_array = array('long', 'short', 'medium', 'short', 'short', 'short');
            $rand = array_rand($type_array);
            $reportType = $this->getReference('type_'.$type_array[$rand]);
            $report->setType($reportType);

            echo "type de le signalement est ".$report->getType()->getLabel()." \n";
            
            $errors = $this->container->get('validator')->validate($report);
            if (count($errors) > 0)
            {
                $m = "";
                foreach ($errors as $error)
                {
                    $m .= $error->getMessage();
                }
                
                //ignore some errors
                if (!($m === "place.validation.message.onlyOneStatusAtATime"))
                    throw new \Exception("report invalide $m");
            }
            
            $manager->persist($report);
            
            $this->addReference("REPORT_FOR_REGISTERED_USER".$i, $report);
        }
    
        //ajout de reports avec utilisateurs non enregistré
        for ($i=0; $i<20; $i++)
        {
            echo "Création du point $i (utilisateur inconnu) \n";
            
            $report = new Report();
            $point = $this->getRandomPoint();
            $report->setGeom($point);

            $u = new UnregisteredUser();
            $u->setLabel('non enregistré '.$this->createId());
            $u->setEmail('test@fastre.info');
            $u->setIp('192.168.1.89');
            $u->setPhonenumber("012345678901");
            $report->setDescription($this->getLipsum(rand(10,60)));
            if (rand(0,5) > 3) //add randomly a moderator comment, or not
                $report->setModeratorComment($this->getLipsum(rand(10,90)));

            $report->setCreator($u);

            $report->setAddress($this->geolocate($point));
            
            //ajout un statut à toutes les signalement, sauf à quatre d'entre elles
            if ($i != 0 OR $i != 10 OR $i != 15 OR $i != 19)
            {
                $p = new ReportStatus();
                $p->setType($notations[array_rand($notations)])
                        ->setValue($valuesNotations[array_rand($valuesNotations)]);
                $report->addStatus($p);

                /*
                //ajoute un deuxième statut à une sur trois
                if ($i%3 == 0)
                {
                    $p = new ReportStatus();
                $p->setType($notations[array_rand($notations)])
                        ->setValue($valuesNotations[array_rand($valuesNotations)]);
                $report->addStatus($p);
                }
                */
            }

            print "jdsksdj - add";
            
            $report->getChangeset()->setAuthor($u);
            

            //add a random category amongst the one loaded
            $cat_array = array('1', '2', '3');
            $rand = array_rand($cat_array);
            $cat_string_ref = 'cat'.$cat_array[$rand];
            echo "add $cat_string_ref \n";
            echo "\n";
            echo "\n";
            echo $this->getReference('cat'.$cat_array[$rand]);

            $report->setCategory($this->getReference('cat'.$cat_array[$rand]));


            
            //add a type (little 4 more frequently)
            $type_array = array('short', 'medium', 'short', 'short', 'short');
            $rand = array_rand($type_array);
            $reportType = $this->getReference('type_'.$type_array[$rand]);
            $report->setType($reportType);
            
            $errors = $this->container->get('validator')->validate($report);
            if (count($errors) > 0)
            {
                $m = "";
                foreach ($errors as $error)
                {
                    $m .= $error->getMessage();
                }
                
                //ignore some errors
                if (!($m === "place.validation.message.onlyOneStatusAtATime"))
                    throw new \Exception("report invalide $m");
            }

            print "jdsksdj - 4";

            $manager->persist($report);
            
            $this->addReference("REPORT_FOR_UNREGISTERED_USER".$i, $report);
        }
        
        
        $manager->persist($report);
        
        $manager->flush();
    }
    
    /**
     * La fonction renvoie un point aléatoire dans la région de Mons
     * @return \Progracqteur\WikipedaleBundle\Resources\Geo\Point 
     */
    private function getRandomPoint()
    {
        //la latitude et la longitude est défini aléatoirement, et dans la région de MOns
        $lat = rand(504500, 504570)/10000;
        $lon = rand(39400, 39620)/10000;
    
        return Point::fromLonLat($lon, $lat);
    }
    
    //cette partie du code sert à créer des chaines de caractères aléatoires
    private $n = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

    private $z = array(6);

    public function createId() {
  
        $s = '';
        $d = array_rand($this->z);
        $dd = $this->z[$d];

        for ($i = 0; $i < $dd; $i++) {
     
            $o = array_rand($this->n);
            $s .= $this->n[$o];
        }

        return $s;
  }
  
  
  private function geolocate(Point $point)
  {
      $a = new Address();

        //si la chaine est vide, retourne le hash
        
        $dom = new \DOMDocument();
        
        $lat = $point->getLat();
        $lon = $point->getLon();
        
        //$ch = curl_init();
        
         $url = "http://open.mapquestapi.com/nominatim/v1/reverse?format=xml&lat=$lat&lon=$lon";
        
        echo "Recherche de l'adresse à l'url suivante : $url \n";
        
        $dom->load($url);
        $docs = $dom->getElementsByTagName('addressparts');
        
        $doc = $docs->item(0);

        if ($dom->hasChildNodes())
        {
            foreach ($doc->childNodes as $node)
            {
                $v = $node->nodeValue;
                
                switch ($node->nodeName) {
                    case Address::CITY_DECLARATION :
                        $a->setCity($v);
                        break;
                    case Address::ADMINISTRATIVE_DECLARATION :
                        $a->setAdministrative($v);
                        break;
                    case Address::COUNTY_DECLARATION :
                        $a->setCounty($v);
                        break;
                    case Address::STATE_DISTRICT_DECLARATION :
                        $a->setStateDistrict($v);
                        break;
                    case Address::STATE_DECLARATION :
                        $a->setState($v);
                        break;
                    case Address::COUNTRY_DECLARATION :
                        $a->setCountry($v);
                        break;
                    case Address::COUNTRY_CODE_DECLARATION :
                        $a->setCountryCode($v);
                        break;
                    case Address::ROAD_DECLARATION : 
                        $a->setRoad($v);
                            break;
                    case Address::PUBLIC_BUILDING_DECLARATION :
                        $a->setPublicBuilding($v);
                        break;
                }
            }
        }
        
        return $a;
  }
  
  
  private $cacheLipsum = array();
  
  /**
   * Source: http://blog.ergatides.com/2011/08/16/simple-php-one-liner-to-generate-random-lorem-ipsum-lipsum-text/#ixzz2OSncsP22
   * 
   * @param type $amount
   * @param type $what
   * @param type $start
   */
    private function getLipsum($amount = 1, $what = 'words', $start = 0)
    {
        
        //for performance reason: set a cache of previous lipsum
        //use the cache if we got more than three strings available, 
        //except 2 times on 10: create a new one
        if (count($this->cacheLipsum) < 3 OR rand(0,10) === 9) //% 4 === 0 )
        {
            $str = simplexml_load_file("http://www.lipsum.com/feed/xml?amount=$amount&what=$what&start=$start")->lipsum;
            $this->cacheLipsum[] = $str;
            return $str;
        }
            
        
        return $this->cacheLipsum[array_rand($this->cacheLipsum)];
    }

    /**
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    private $container;
    
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null) {
        $this->container = $container;
    }
}

