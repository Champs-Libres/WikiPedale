<?php


namespace Progracqteur\WikipedaleBundle\Resources\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Entity\Management\UnregisteredUser;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;




/**
 * Description of UserNormalizer
 *
 * @author julien [at] fastre [point] info
 */
class UserNormalizer implements NormalizerInterface
{
    
    private $service;
    
    public function __construct(NormalizerSerializerService $service)
    {
        $this->service = $service;
    }
    
    
    public function denormalize($data, $class, $format = null) {
        //si la classe demandée n'est pas USER, il faut uniquement renvoyer un objet User existant,
        // ou un objet Unregistereduser
        if ($class === NormalizerSerializerService::PLACE_TYPE)
        {
            if ($data['id'] === null)
            {          
                $u = $this->service->getPlaceNormalizer()->getCurrentPlace()->getCreator();
                if ($u === null) {
                    $u = new UnregisteredUser();
                    if (isset($data['label']))
                        $u->setLabel($data['label']);

                    if (isset($data['email']))
                        $u->setEmail($data['email']);

                    $u->setIp($this->service->getRequest()->getClientIp());
                }

            } else {

                $u = $this->service->getManager()
                        ->getRepository('ProgracqteurWikipedaleBundle:Management\\User')
                        ->find($data['id']);

                if ($u === null)
                {
                    throw new \Exception("L'utilisateur n'a pas été trouvé dans la base de donnée");
                }
            }

            
        }
        
        return $u;
    }
    
    public function normalize($object, $format = null) {
        
        $a =  array(
            'entity' => 'user',
            'id' => $object->getId(),
            'label' => $object->getLabel(),
            'nbComment' => $object->getNbComment(),
            'nbVote' => $object->getNbVote(),
            'roles' => $object->getRoles(),
            'registered' => $object->isRegistered()
        );
        
        if (
                $this->service->getSecurityContext()->isGranted(User::ROLE_SEE_USER_DETAILS)
                )
        {
            $a['email'] = $object->getEmail();
            $a['phonenumber'] = $object->getPhonenumber();
        }
        

        return $a;
        
    }
    public function supportsDenormalization($data, $type, $format = null) {
        if ($data['entity'] == 'user')
        {
            return true;
        } else
        {
            return false;
        }
        
        
    }
    public function supportsNormalization($data, $format = null) {
        if ($data instanceof User)
        {
            return true;
        } else {
            return false;
        }
    }
}

