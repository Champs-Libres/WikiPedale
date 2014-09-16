<?php

namespace Progracqteur\WikipedaleBundle\Resources\Normalizer;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/** 
 * Description of LightReportArrayNormalizer
 *
 * @author Champs-Libres COOP
 */
class LightReportArrayNormalizer implements NormalizerInterface {
    
   /**
    * 
    * @param \Progracqteur\WikipedaleBundle\Entity\Model\Report $object
    * @param string $format
    * @return array
    */
   public function normalize($report, $format = null, array $context = array()) {
      return  array(
            'type' => 'Feature',
            'geometry' => array(
               'type' => 'Point',
               'coordinates' => array($report->getGeom()->getLon(),$report->getGeom()->getLat())
               ),
            'properties' => array(
               'id' => $report->getId()
               )
            );
    }
    
    public function supportsNormalization($data, $format = null) {
        return $data instanceof Report;
    }
}