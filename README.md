== Installation  ==
Installer le code dans /src/Progracqteur/WikipedaleBundle/
Modifier /app/config/routing.yml en y ajoutant :

ProgracqteurWikipedaleBundle:
    resource: "@ProgracqteurWikipedaleBundle/Resources/config/routing.yml"
    prefix:   /

Modifier /app/AppKernel.php en ajoutant au tableau $bundles l'�l�ment new Progracqteur\WikipedaleBundle\ProgracqteurWikipedaleBundle() :
$bundles = array(
	    ... autres �l�ments ...
	    new Progracqteur\WikipedaleBundle\ProgracqteurWikipedaleBundle(),
            ... autres �l�ments ...
        );


== Documentation ==
Voir wiki pour le moment...