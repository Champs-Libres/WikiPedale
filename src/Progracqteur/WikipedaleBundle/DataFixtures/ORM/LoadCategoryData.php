<?php

namespace Progracqteur\WikipedaleBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Entity\Management\Group;
use Progracqteur\WikipedaleBundle\Entity\Management\Notation;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjction\ContainerInterface;
use Progracqteur\WikipedaleBundle\Entity\Model\Category;

/**
 * Description of LoadCategoryData
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class LoadCategoryData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
   private $nextParentOrder = 0.1;
   private $nextChildrenOrder = 0.11;
   private $actualParent;

   /**
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    private $container;
    
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null) {
        $this->container = $container;
    }

   /**
    * Loads the category
    */
   public function load(ObjectManager $manager) 
   {
      $manager->persist($this->newParent("Revêtement"));
      $rd = $this->newChildren("Revêtement dégradé (trous, soulèvement, mal réparé)");
      $manager->persist($rd);
      $this->addReference('cat1', $rd);
      $manager->persist($this->newChildren("Accumulation d'eau en cas de pluie",'medium'));
      $manager->persist($this->newChildren("Bordure saillante",'medium'));

      $manager->persist($this->newParent("Signalisation"));
      $manager->persist($this->newChildren("Signalisation manquante ou peu claire (balisage, panneau disparu...)"));
      $se = $this->newChildren("Signalisation erronée");
      $manager->persist($se);
      $this->addReference('cat2', $se);
      $manager->persist($this->newChildren("Marquages au sol effacés",'medium'));
      $manager->persist($this->newChildren("Marquages au sol incorrects",'medium'));
      
      $manager->persist($this->newParent("Obstacles"));
      $manager->persist($this->newChildren("De la végétation envahit le cheminement cyclable"));
      $manager->persist($this->newChildren("Des déchets jonchent la voirie (débris de verre, graviers, terre, ...)"));
      $manager->persist($this->newChildren("Des objets créent des obstacles sur le cheminement cyclable (poubelle, encombrant)"));
      $manager->persist($this->newChildren("Des véhicules sont régulièrement stationnés sur le cheminement cyclable"));
      
      $manager->persist($this->newParent("Sécurité"));
      $ed = $this->newChildren("Éclairage public défectueux");
      $manager->persist($ed);
      $this->addReference('cat3', $ed);

      $manager->persist($this->newParent("Chantiers"));
      $manager->persist($this->newChildren("Pas de prise en compte des cyclistes pour la durée du chantier (déviation, ...)"));
      $manager->persist($this->newChildren("Signalisation ou balises lumineuses du chantier manquantes/insuffisantes"));
      $manager->persist($this->newChildren("Aménagements cyclables disparus ou endommagés après les travaux"));
      
      $manager->persist($this->newParent("Parkings"));

      $manager->persist($this->newParent("Stationnement vélo défectueux, abîmé ou non replacé"));

      $manager->persist($this->newParent("Autre"));
      $manager->persist($this->newChildren("Autre"));
     
      $manager->flush(); 
   }

   public function getOrder()
   {
      return 400;
   }

      /**
    * Create a new category
    */
   private function newCategory($label, $order,  $parent=null, $term='short')
   {
      echo "$label - $order - $parent - $term \n";
      $cat = new Category();
      $cat->setLabel($label);
      $cat->setOrder($order);
      if($parent) {
         $cat->setParent($parent);
      }
      $cat->setTerm($term);
      return $cat;
   }

   /**
    * Create a new category (parent)
    */
   private function newParent($label)
   {
      echo "newParent : " . $label ."\n";
      echo $this->nextParentOrder;
      $this->actualParent = $this->newCategory($label,$this->nextParentOrder);
      $this->nextChildrenOrder = $this->nextParentOrder + 0.01;
      $this->nextParentOrder = $this->nextParentOrder + 0.1;
      return $this->actualParent;
   }

   /**
    * Create a new category (children)
    */
   private function newChildren($label,$term='short')
   {
      echo "newChildren : " . $label ."\n";
      $ret = $this->newCategory($label, $this->nextChildrenOrder,  $this->actualParent, $term);
      $this->nextChildrenOrder = $this->nextChildrenOrder + 0.01;
      return $ret;
   }
}