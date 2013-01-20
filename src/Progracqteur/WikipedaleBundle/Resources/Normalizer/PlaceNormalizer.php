<?php

namespace Progracqteur\WikipedaleBundle\Resources\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Progracqteur\WikipedaleBundle\Entity\Model\Place;
use Progracqteur\WikipedaleBundle\Resources\Geo\Point;
use Progracqteur\WikipedaleBundle\Entity\Model\Place\PlaceStatus;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\AddressNormalizer;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\UserNormalizer;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizingException;



/**
 * Description of PlaceNormalizer
 *
 * @author julien [at] fastre [point] info
 */
class PlaceNormalizer implements NormalizerInterface {
    
    /**
     *
     * @var Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService 
     */
    private $service;
    
    /**
     * Place being denormalized
     * (useful for recursive denormalization)
     * @var Progracqteur\WikipedaleBundle\Entity\Model\Place 
     */
    private $currentPlace;
    
    public function __construct(NormalizerSerializerService $service)
    {
        $this->service = $service;
    }
    
    
    public function denormalize($data, $class, $format = null) {
        
        if ($data['id'] === null){
            $p = new Place();
        }
        else {
            $p = $this->service->getManager()
                    ->getRepository('ProgracqteurWikipedaleBundle:Model\\Place')
                    ->find($data['id']);
            
            if ($p === null)
            {
                throw new \Exception("La place recherchée n'existe pas");
            }
        }
        
        $this->setCurrentPlace($p);
        
        if (isset($data['description']))
            $p->setDescription($data['description']);
        
        if (isset($data['geom'])) 
        {
            $point = Point::fromArrayGeoJson($data['geom']);
            $p->setGeom($point);
        }
        
        if (isset($data['addressParts']))
        {
            $addrNormalizer = $this->service->getAddressNormalizer();
            if ($addrNormalizer->supportsDenormalization($data['addressParts'], 
                    $class, 
                    $format));
            {
                $addr = $addrNormalizer->denormalize($data['addressParts'], 
                        $class, 
                        $format);
                $p->setAddress($addr);
            }
        }
        
        if (isset($data['creator']))
        {
            $userNormalizer = $this->service->getUserNormalizer();
            if ($userNormalizer->supportsDenormalization($data['creator'], 
                    $class, 
                    $format))
            {
                $u = $userNormalizer->denormalize($data['creator'], 
                        $class, 
                        $format);
                $p->setCreator($u);
            }
        }
        
        if (isset($data['statusBicycle']))
        {
            $p->setStatusBicycle($data['statusBicycle']);
        }
        
        if (isset($data['statusCity']))
        {
            $p->setStatusCity($data['statusCity']);
        }
        
        if (isset($data['accepted']))
        {
            $p->setAccepted($data['accepted']);
        }
        
        if (isset($data['statuses']))
        {
            foreach ($data['statuses'] as $key => $arrayStatus)
            {
                if (is_array($arrayStatus) 
                        && isset($arrayStatus['t']) 
                        && isset($arrayStatus['v']) 
                        )
                {
                    $status = new PlaceStatus();
                    $status->setType($arrayStatus['t'])
                            ->setValue($arrayStatus['v']);
                    $p->addStatus($status);
                }
            }
        }
        
        if (isset($data['categories']))
        {
            $arrayCategories = array();
            foreach($data['categories'] as $cat)
            {
                if ($this->service->getCategoryNormalizer()->supportsDenormalization($cat, $type, $format))
                {
                    $arrayCategories[] = $this->service->getCategoryNormalizer()->denormalize($cat, null);
                } else {
                    throw new NormalizingException('Could not denormalize category '.print_r($cat, true));
                }
            }
            
            foreach ($arrayCategories as $cat)
            {
                //TODO
            }
            
        }
        
        return $p;
    }
    
    /**
     * 
     * @param Progracqteur\WikipedaleBundle\Entity\Model\Place $object
     * @param string $format
     * @return array
     */
    public function normalize($object, $format = null) {
        $creator = $object->getCreator();
        $addrNormalizer = $this->service->getAddressNormalizer();
        $userNormalizer = $this->service->getUserNormalizer();
        
        //create an array with statuses
        $statuses = $object->getStatuses();
        $s = array();
        
        foreach ($statuses as $status)
        {
            $s[] = array('t' => $status->getType(), 'v' => $status->getValue());
        }
        
        //create an array with categories
        $categories = $object->getCategory();
        $c = array();
        
        foreach ($categories as $cat)
        {
            $c[] = $this->service->getCategoryNormalizer()->normalize($cat, $format);
        }
        
        return  array(
            'entity' => 'place',
            'description' => $object->getDescription(),
            'geom' => $object->getGeom()->toArrayGeoJson(),
            'id' => $object->getId(),
            'nbComm' => $object->getNbComm(),
            'nbVote' => $object->getNbVote(),
            'creator' => $userNormalizer->normalize($creator, $format),
            'addressParts' => $addrNormalizer->normalize($object->getAddress(), $format),
            'createDate' => $this->service->getDateNormalizer()->normalize($object->getCreateDate(), $format),
            'lastUpdate' => $this->service->getDateNormalizer()->normalize($object->getLastUpdate(), $format),
            'nbPhotos' => $object->getNbPhoto(),
            //'statusBicycle' => $object->getStatusBicycle(),
            //'statusCity' => $object->getStatusCity(),
            'accepted' => $object->isAccepted(),
            'statuses' => $s,
            'categories' => $c
            
        );
    }
    
    public function supportsDenormalization($data, $type, $format = null) 
    {
        if ($data['entity'] == 'place')
        {
            return true;
        } else 
        {
            return false;
        }
        
    }
    public function supportsNormalization($data, $format = null) {
        if ($data instanceof Place)
        {
            return true;
        } else
        {
            return false;
        }
    }
    
    private function setCurrentPlace(Place $place)
    {
        $this->currentPlace = $place;
    }
    
    /*
     * 
     * @return Progracqteur\WikipedaleBundle\Entity\Model\Place
     */
    public function getCurrentPlace()
    {
        return $this->currentPlace;
    }
}

