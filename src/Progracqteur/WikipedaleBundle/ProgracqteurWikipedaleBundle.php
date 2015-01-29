<?php

namespace Progracqteur\WikipedaleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Progracqteur\WikipedaleBundle\Resources\Services\Notification\NotificationCompilerPass;
use Progracqteur\WikipedaleBundle\DependencyInjection\ModeratorFinderCompiler;

class ProgracqteurWikipedaleBundle extends Bundle
{
   public function build(ContainerBuilder $container)
   {
        parent::build($container);

       	$extension = $container->getExtension('security');
        $container->addCompilerPass(new NotificationCompilerPass());
        $container->addCompilerPass(new ModeratorFinderCompiler());
   }

}
