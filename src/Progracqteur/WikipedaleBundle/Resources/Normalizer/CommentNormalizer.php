<?php

namespace Progracqteur\WikipedaleBundle\Resources\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Progracqteur\WikipedaleBundle\Entity\Model\Comment;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizingException;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\UserNormalizer;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\ReportNormalizer;

/**
 * Description of CommentNormalizer
 *
 * @author julien [at] fastre [point] info & marcducobu [at] gmail [point] com
 */

class CommentNormalizer implements NormalizerInterface, DenormalizerInterface
{    
    /**
     *
     * @var Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService 
     */
    private $service;
    
    /**
     * Comment being denormalized
     * (useful for recursive denormalization)
     * @var Progracqteur\WikipedaleBundle\Entity\Model\Comment
     */
    private $currentComment;
    
    public function __construct(NormalizerSerializerService $service)
    {
        $this->service = $service;
    }
    
    public function denormalize($data, $class, $format = null, array $context = array()) 
    {
        //TODO à adapter lorsque le json envoyé sera corrigé
    	/*if ($data['id'] === null)
        {*/
        $p = new Comment();
        /*}
        else 
            {
                $p = $this->service->getManager()
                        ->getRepository('ProgracqteurWikipedaleBundle:Model\\Comment')
                        ->find($data['id']);

                if ($p === null)
                {
                    throw new \Exception("Le commentaire recherché n'existe pas");
                }
            }*/

        if (isset($data['reportId'])) {
            $report = $this->service->getManager()
                ->getRepository('ProgracqteurWikipedaleBundle:Model\\Report')
                ->find($data['reportId']);
            
            if ($report === null) {
                throw new \Exception("report with id ".$data['reportId']." not found");
            }
            
            $p->setReport($report);
        }

        if (isset($data['text'])) {
            $p->setContent($data['text']);
        }

        if (isset($data['creator'])) {
                $userNormalizer = $this->service->getUserNormalizer();
                if ($userNormalizer->supportsDenormalization($data['creator'], 
                        $class, 
                        $format)) {
                    $u = $userNormalizer->denormalize($data['creator'], 
                            $class, 
                            $format);
                    $p->setCreator($u);
                }
            }

        if (isset($data['published'])) {
            $p->setPublished($data['published']);
        }
            
        if (isset($data['type'])) {
            switch($data['type']) {
                case 'moderator_manager' :
                    $p->setType(Comment::TYPE_MODERATOR_MANAGER);
                    break;
                default :
                    $p->setType(Comment::TYPE_PUBLIC);
                    break;
            }
        }

        return $p;
    }

  	/**
     * 
     * @param \Progracqteur\WikipedaleBundle\Entity\Model\Comment $object
     * @param string $format
     * @return array
     */
    public function normalize($object, $format = null, array $context = array()) {    
        return  array(
            'entity' => 'comment',
            'id' => $object->getId(),
            'text' => $object->getContent(),
            'published' => $object->getPublished(),
            'creationDate' => $this->service->getDateNormalizer()->normalize($object->getCreationDate(), $format),
            'createDate' => $this->service->getDateNormalizer()->normalize($object->getCreationDate(), $format),
            'creator' => $this->service->getUserNormalizer()->normalize($object->getCreator(), $format),
            'reportId' => $object->getReport()->getId(),
            'type' => $object->getType() 
        );
    }
    
    public function supportsNormalization($data, $format = null) {
        if ($data instanceof Comment) {
            return true;
        } else {
            return false;
        }
    }

    public function supportsDenormalization($data, $type, $format = null) 
    {
        if ($data['entity'] === 'comment') {
            return true;
        } else  {
            return false;
        }
        
    }
    
    private function setCurrentComment(Comment $comment)
    {
        $this->currentComment = $comment;
    }
    
    /*
     * 
     * @return Progracqteur\WikipedaleBundle\Entity\Model\Comment
     */
    public function getCurrentComment()
    {
        return $this->currentComment;
    }   
}