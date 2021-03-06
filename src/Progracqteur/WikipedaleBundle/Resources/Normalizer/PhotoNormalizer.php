<?php

namespace Progracqteur\WikipedaleBundle\Resources\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;
use Progracqteur\WikipedaleBundle\Entity\Model\Photo;

/**
 * Description of PhotoNormalizer
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class PhotoNormalizer implements NormalizerInterface {
    
    /**
     *
     * @var Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService 
     */
    private $normalizerService;


    public function __construct(NormalizerSerializerService $service)
    {
        $this->normalizerService = $service;
    }
    
    
    public function denormalize($data, $class, $format = null, array $context = array()) {
        
    }

    public function normalize($object, $format = null, array $context = array()) {
        return array(
            'entity' => 'photo',
            'webPath' => $object->getWebPath(),
            'fullFileName' => $object->getFile(),
            'width' => $object->getWidth(),
            'height' => $object->getHeight(),
            'legend' => $object->getLegend(),
            'creator'=> $this->normalizerService->getUserNormalizer()->normalize($object->getCreator(), $format),
            'reportId' => $object->getReport()->getId(),
            'published' => $object->getPublished(),
            'filename' => $object->getFileName(),
            'photoType' => $object->getPhotoType(),
            'createDate' => $this->normalizerService->getDateNormalizer()->normalize($object->getCreateDate(), $format)
        );
    }

    public function supportsDenormalization($data, $type, $format = null) {
        return false;
    }

    public function supportsNormalization($data, $format = null) {
        if ($data instanceof Photo)
            return true;
        else
            return false;
    }
}

