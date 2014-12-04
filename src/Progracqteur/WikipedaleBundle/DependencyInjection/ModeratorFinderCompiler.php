<?php

namespace Progracqteur\WikipedaleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * add services tagged as moderatorFinder into the moderator_designator
 * service
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ModeratorFinderCompiler implements CompilerPassInterface
{
   
   const MODERATOR_FINDER_ID = 'progracqteur.wikipedale.moderator_designator';
   const MODERATOR_FINDER_TAG = 'moderatorFinder';
   const MODERATOR_FINDER_TAG_PRIORITY = 'priority';
   
   public function process(ContainerBuilder $container)
   {
      
      $definitionModeratorDesignator = $container->getDefinition(
            self::MODERATOR_FINDER_ID);
      
      $moderatorFinderServices = $container->findTaggedServiceIds(
            self::MODERATOR_FINDER_TAG);
      
      $priorities = array();
      
      foreach ($moderatorFinderServices as $id => $tagAttributes) {
         foreach ($tagAttributes as $attribute) {

            if (!is_numeric($attribute[self::MODERATOR_FINDER_TAG_PRIORITY])) {
               throw new \Exception('the priority attribute must be numeric, '
                     . 'check the priority attribute for service '.$id);
            }
            
            $priority = $attribute[self::MODERATOR_FINDER_TAG_PRIORITY];
            
            if (in_array($priority, $priorities)) {
               throw new \Exception('priority conflict in a tag moderatorFinder: '
                     . 'priorities must be unique. You have a conflict on service '
                     . $id.'.');
            }
            
            $definitionModeratorDesignator->addMethodCall(
                  'addModeratorFinder', 
                  array(new Reference($id), $priority)
                  );
         }
         
      }
      
      $definitionModeratorDesignator->addMethodCall('sortFinders');
   }

   

}
