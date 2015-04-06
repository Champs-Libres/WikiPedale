<?php

namespace Progracqteur\WikipedaleBundle\Resources\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;
use Progracqteur\WikipedaleBundle\Entity\Model\Report\ReportStatus;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizingException;
use Progracqteur\WikipedaleBundle\Entity\Model\Comment;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

/**
 * Description of ReportNormalizer
 *
 * @author julien [at] fastre [point] info
 */
class ReportNormalizer implements NormalizerInterface, DenormalizerInterface
{    
    /** @var NormalizerSerializerService The normalizer service (for accessing
     * to each normalizers) */
    private $service;
    
    /**
     * Report being denormalized
     * (useful for recursive denormalization)
     * @var Progracqteur\WikipedaleBundle\Entity\Model\Report 
     */
    private $currentReport;
    
    const REPORT_TYPE = 'reporttype';
    const MODERATOR_COMMENT = 'moderatorComment';
    const COMMENTS = 'comments';
    const TERM = 'term';
    const MODERATOR = 'moderator';
    
    public function __construct(NormalizerSerializerService $service)
    {
        $this->service = $service;
    }
    
    public function denormalize($data, $class, $format = null, array $context = array())
    {    
        if ($data['id'] === null) {
            $report = new Report();
        }
        else {
            $report = $this->service->getManager()
                ->getRepository('ProgracqteurWikipedaleBundle:Model\\Report')
                ->find($data['id']);
            
            if (!$report) {
                throw new \Exception("Le signalement recherchée n'existe pas");
            }
        }
        
        $this->setCurrentReport($report);
        
        if (isset($data['description'])) {
            $report->setDescription($data['description']);
        }

        if (isset($data['drawnGeoJSON'])) {   
            $report->setDrawnGeoJSON(json_encode($data['drawnGeoJSON']));
        }
        
        if (isset($data['geom'])) {
            $geoService = $this->service->getContainer()
                ->get('progracqteur.wikipedale.geoservice');
            
            $point = $geoService->pointGeoJSONArray($data['geom']);
            $report->setGeom($point);
        }
        
        if (isset($data['addressParts'])) {
            $addrNormalizer = $this->service->getAddressNormalizer();
            if ($addrNormalizer->supportsDenormalization($data['addressParts'], 
                    $class, 
                    $format)) {
                $addr = $addrNormalizer->denormalize($data['addressParts'], 
                        $class, 
                        $format);
                $report->setAddress($addr);
            }
        }
        
        if (isset($data['creator'])) {
            $userNormalizer = $this->service->getUserNormalizer();
            if ($userNormalizer->supportsDenormalization($data['creator'], 
                    $class, 
                    $format)) {
                $u = $userNormalizer->denormalize($data['creator'], 
                        $class, 
                        $format);
                $report->setCreator($u);
            }
        }
        
        if (isset($data['accepted'])) {
            $report->setAccepted($data['accepted']);
        }
        
        if (isset($data['statuses'])) {
            foreach ($data['statuses'] as $key => $arrayStatus) {
                if (is_array($arrayStatus) 
                        && isset($arrayStatus['t']) 
                        && isset($arrayStatus['v']) 
                        ) {
                    $status = new ReportStatus();
                    $status->setType($arrayStatus['t'])
                            ->setValue($arrayStatus['v']);
                    $report->addStatus($status);
                }
            }
        }
        
        //TODO simplifier
        if (isset($data['category'])) {
            if ($this->service->getCategoryNormalizer()->supportsDenormalization($data['category'], $class, $format)) {
                $newCategory = $this->service->getCategoryNormalizer()->denormalize($data['category'], null);
                //var_dump($newCategory);
                $report->setCategory($newCategory);
            } else {
                throw new NormalizingException('Could not denormalize category '.print_r($cat, true));
            }
        }
        
        if (isset($data['manager'])) {
            if ($this->service->getGroupNormalizer()
                    ->supportsDenormalization($data['manager'], $class)) {
                $group = $this->service->getGroupNormalizer()->denormalize($data['manager'], $class);
                $report->setManager($group);
            } else {
                throw new NormalizingException('could not denormalize manager '.$data['manager']);
            }
            
            if ($data['manager'] === null) {
                $report->setManager(null);
            }
        }
        
        if (isset($data[self::MODERATOR])) {
            if ($this->service->getGroupNormalizer()
                ->supportsDenormalization($data[self::MODERATOR], $class)) {
                    $group = $this->service->getGroupNormalizer()->denormalize($data[self::MODERATOR], $class);
                    $report->setModerator($group);
                } else {
                    throw new NormalizingException('could not denormalize moderator '.$data[self::MODERATOR]);
                }
            
                if ($data[self::MODERATOR] === null) {
                    $report->setModerator(null);
                }
        }
        
        if (isset($data[self::REPORT_TYPE])) {
            if ($this->service
                    ->getReportTypeNormalizer()
                    ->supportsDenormalization($data[self::REPORT_TYPE], $class)) {
                $type = $this->service
                        ->getReportTypeNormalizer()
                        ->denormalize($data[self::REPORT_TYPE], $class);
                $report->setType($type);
            } else {
                throw new NormalizingException('could not denormalize reportType');
            }
        }
        
        if (isset($data[self::MODERATOR_COMMENT])) {
            $report->setModeratorComment($data[self::MODERATOR_COMMENT]);
        }
        
        return $report;
    }
    
    /**
     * 
     * @param \Progracqteur\WikipedaleBundle\Entity\Model\Report $object
     * @param string $format
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $addrNormalizer = $this->service->getAddressNormalizer();
        $userNormalizer = $this->service->getUserNormalizer();
        $categoryNomalizer = $this->service->getCategoryNormalizer();
        
        //create an array with statuses
        $statuses = $object->getStatuses();
        $s = array();
        
        foreach ($statuses as $status) {
            $s[] = array('t' => $status->getType(), 'v' => $status->getValue());
        }
        
        if ($object->getManager() !== null) {
            $manager = $this->service->getGroupNormalizer()->normalize($object->getManager());
        } else {
            $manager = null;
        }
        
        if ($object->getModerator() !== null) {
           $moderator = $this->service->getGroupNormalizer()
                 ->normalize($object->getModerator());
        } else {
           $moderator = null;
        }
        
        if ($object->getType() !== null) {
            $reportType = $this->service
                    ->getReportTypeNormalizer()
                    ->normalize($object->getType());
        } else {
            $reportType = null;
        }
        
        $nbComments = array(
            'moderator_manager' => $object->getNbComments(Comment::TYPE_MODERATOR_MANAGER),
            'public' => $object->getNbComments(Comment::TYPE_PUBLIC)
            );
        
        return  array(
            'entity' => 'report',
            'description' => $object->getDescription(),
            'geom' => array(
                'type' => 'Point',
                'coordinates' => [$object->getGeom()->getLongitude(), $object->getGeom()->getLatitude()]
            ),
            'drawnGeoJSON' => $object->getDrawnGeoJSON(),
            'id' => $object->getId(),
            'nbComm' => $object->getNbComm(),
            'nbVote' => $object->getNbVote(),
            'creator' => $userNormalizer->normalize($object->getCreator(), $format),
            'addressParts' => $addrNormalizer->normalize($object->getAddress(), $format),
            'createDate' => $this->service->getDateNormalizer()->normalize($object->getCreateDate(), $format),
            'lastUpdate' => $this->service->getDateNormalizer()->normalize($object->getLastUpdate(), $format),
            'nbPhotos' => $object->getNbPhoto(),
            'accepted' => $object->isAccepted(),
            'statuses' => $s,
            'category' => $categoryNomalizer->normalize($object->getCategory(), $format),
            'manager' => $manager,
            self::MODERATOR => $moderator,
            self::REPORT_TYPE => $reportType,
            self::MODERATOR_COMMENT => $object->getModeratorComment(),
            self::COMMENTS => $nbComments,
        );
    }
    
    public function supportsDenormalization($data, $type, $format = null) 
    {
        if ($data['entity'] == 'report') {
            return true;
        } else {
            return false;
        }
        
    }

    public function supportsNormalization($data, $format = null)
    {
        if ($data instanceof Report) {
            return true;
        } else {
            return false;
        }
    }
    
    private function setCurrentReport(Report $report)
    {
        $this->currentReport = $report;
    }
    
    /*
     * 
     * @return Progracqteur\WikipedaleBundle\Entity\Model\Report
     */
    public function getCurrentReport()
    {
        return $this->currentReport;
    }
}