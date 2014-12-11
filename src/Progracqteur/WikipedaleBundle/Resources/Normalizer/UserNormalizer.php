<?php

namespace Progracqteur\WikipedaleBundle\Resources\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Progracqteur\WikipedaleBundle\Entity\Management\UnregisteredUser;
use Progracqteur\WikipedaleBundle\Resources\Normalizer\NormalizerSerializerService;
use Fastre\LibravatarBundle\Services\ServiceLibravatar;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Description of UserNormalizer
 *
 * @author julien [at] fastre [point] info
 * @author marc.ducobu@gmail.com
 */
class UserNormalizer implements NormalizerInterface
{
    private $service;
    private $addGroupsToNormalization = false;

    /**
     *
     * @var \Fastre\LibravatarBundle\Services\ServiceLibravatar 
     */
    private $libravatarService;

    /**
     * RoleHierarchy
     * @var Symfony\Component\Security\Core\Role\RoleHierarchy
     */
    private $roleHierarchy;

    const GROUPS = 'groups';

    public function __construct(NormalizerSerializerService $service, ServiceLibravatar $libravatarService, RoleHierarchy $roleHierarchy)
    {
        $this->service = $service;
        $this->libravatarService = $libravatarService;
        $this->roleHierarchy = $roleHierarchy;
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        //si la classe demandée n'est pas USER, il faut uniquement renvoyer un objet User existant,
        // ou un objet Unregistereduser
        if ($class === NormalizerSerializerService::REPORT_TYPE) {
            if ($data['id'] === null) {
                $u = $this->service->getReportNormalizer()->getCurrentReport()->getCreator();
                if ($u === null) {
                    $u = new UnregisteredUser();
                    if (isset($data['label']))
                        $u->setLabel($data['label']);

                    if (isset($data['email']))
                        $u->setEmail($data['email']);

                    if (isset($data['phonenumber']))
                        $u->setPhonenumber($data['phonenumber']);

                    $u->setIp($this->service->getRequest()->getClientIp());
                }
            } else {
                $u = $this->service->getManager()
                    ->getRepository('ProgracqteurWikipedaleBundle:Management\\User')
                    ->find($data['id']);

                if ($u === null) {
                    throw new \Exception("L'utilisateur n'a pas été trouvé dans la base de donnée");
                }
            }
        }
        return $u;
    }

    /**
     * 
     * @param \Progracqteur\WikipedaleBundle\Entity\Management\User $object peut aussi être \Progracqteur\WikipedaleBundle\Entity\Management\UnregisteredUser
     * @param string $format
     * @return type
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $userRoles = $object->getRoles();
        $userRoleObjects = array_map(function($r) { return new Role($r); }, $userRoles);
        $userAllRoleObjects = $this->roleHierarchy->getReachableRoles($userRoleObjects);
        $userAllRoles = array_map(function($r) { return $r->getRole(); }, $userAllRoleObjects);

        $a = array(
            'entity' => 'user',
            'id' => $object->getId(),
            'label' => $object->getLabel(),
            'nbComment' => $object->getNbComment(),
            'nbVote' => $object->getNbVote(),
            'roles' => $userAllRoles,
            'registered' => $object->isRegistered(),
            'avatar' => '' //$this->libravatarService->getUrl($object->getEmail()),   TODO : activate avatar when this will be ready ! 
        );

        if ($this->service->getSecurityContext()->isGranted(User::ROLE_SEE_USER_DETAILS)) {
            $a['email'] = $object->getEmail();
            $a['phonenumber'] = $object->getPhonenumber();
        }

        if ($this->addGroupsToNormalization) {
            $a[self::GROUPS] = array();
            foreach ($object->getGroups() as $group) {
                $a[self::GROUPS][] = $this->service->getGroupNormalizer()
                    ->normalize($group);
            }
        }
        return $a;
    }

    /**
     * add groups to normalization
     * 
     * permanent until the class is recreated
     * 
     * @param boolean $enable
     */
    public function addGroupsToNormalization($enable)
    {
        $this->addGroupsToNormalization = $enable;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return ($data['entity'] == 'user');
    }

    public function supportsNormalization($data, $format = null)
    {
        return ($data instanceof User);
    }

}
