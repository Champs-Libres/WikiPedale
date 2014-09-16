<?php

namespace Progracqteur\WikipedaleBundle\Resources\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Progracqteur\WikipedaleBundle\Resources\Container\NormalizedExceptionResponse;

/**
 * Description of NormalizedExceptionResponseNormalizer
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class NormalizedExceptionResponseNormalizer implements NormalizerInterface {
    
    public function denormalize($data, $class, $format = null, array $context = array()) {
        
    }
    
    public function normalize($object, $format = null, array $context = array()) {
        return array(
            'error' => true,
            'message' => $object->getException()->getMessage()
        );
    }
    
    public function supportsDenormalization($data, $type, $format = null) {
        return false;
    }
    
    public function supportsNormalization($data, $format = null) {
        return ($data instanceof NormalizedExceptionResponse);
    }
}

