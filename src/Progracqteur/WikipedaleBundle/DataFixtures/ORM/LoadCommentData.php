<?php

namespace Progracqteur\WikipedaleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Progracqteur\WikipedaleBundle\Entity\Model\Comment;

/**
 * Load Comments data into the DB 
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class LoadCommentData extends AbstractFixture implements OrderedFixtureInterface
{
   public function getOrder()
   {
      return 700;
   }

   public function load(ObjectManager $manager)
   {    
      for ($i = 0; $i < 20; $i ++) {       
         $creators = array(
            $this->getReference('moderator'),
            $this->getReference('manager'),
            $this->getReference('manager1')
         );

         //create 1,2 or 3 random comment for 1/3 report
         if ($i%3 === 0) {
            $nb = rand(1,3);
              
            echo "Create $nb comments for index $i registered user \n";
              
            //if a place exist with this id...
            if ($this->hasReference('REPORT_FOR_REGISTERED_USER'.$i)) {
               $report = $this->getReference('REPORT_FOR_REGISTERED_USER'.$i);   
            } else  {
               echo "No report recorded with index $i \n";
               continue;
            }
              
            for ($j = 0; $j < $nb; $j++) {
               $c = new Comment();
                  
               $c->setContent($this->getLipsum(rand(10,40)))
                  ->setCreator($creators[array_rand($creators)])
                  ->setReport($report)
                  ->setType(Comment::TYPE_MODERATOR_MANAGER);

               $c->getReport()->getChangeset()->setAuthor($c->getCreator());
                  
               $manager->persist($c);
                  
               echo "comment $j created ! \n";
            }
              
              
            $nb = rand(1,3);
              
            //if a place exist with this id...
            try 
            {
               $report = $this->getReference('REPORT_FOR_UNREGISTERED_USER'.$i);
               
            } catch (\Exception $e) 
            {
               echo "No report recorded with index $i \n";
               continue;
            }

            for ($j = 0; $j < $nb; $j++) {
               $c = new Comment();
               $c->setContent($this->getLipsum(rand(10,40)))
                  ->setCreator($creators[array_rand($creators)])
                  ->setReport($report)
                  ->setType(Comment::TYPE_MODERATOR_MANAGER);

               $c->getReport()->getChangeset()->setAuthor($c->getCreator());

               $manager->persist($c);
            }
         }
      }
      $manager->flush();
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
      if (count($this->cacheLipsum) < 3 OR rand(0,10) === 9) {
         $str = simplexml_load_file("http://www.lipsum.com/feed/xml?amount=$amount&what=$what&start=$start")->lipsum;
         $this->cacheLipsum[] = $str;
         return $str;
      }
           
      return $this->cacheLipsum[array_rand($this->cacheLipsum)];
   }
}

