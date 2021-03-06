<?php

/*
 *  Uello is a reporting tool. This file is part of Uello.
 * 
 *  Copyright (C) 2015, Champs-Libres Cooperative SCRLFS,
 *  <http://www.champs-libres.coop>, <info@champs-libres.coop>
 * 
 *  Uello is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Uello is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Uello.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Progracqteur\WikipedaleBundle\Resources\Security;

use Progracqteur\WikipedaleBundle\Entity\Management\User;
use Doctrine\ORM\EntityManager;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;
use Progracqteur\WikipedaleBundle\Resources\Services\GeoService;
use Progracqteur\WikipedaleBundle\Resources\Services\ReachableRoleService;

/**
 * Description of ChangeService
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class ChangeService {
    
    const REPORT_CREATION = 100;
    const REPORT_DESCRIPTION = 1050;
    const REPORT_ADDRESS = 1060;
    const REPORT_GEOM = 1070;
    const REPORT_COMMENT_MODERATOR_MANAGER_ADD = 110;
    const REPORT_ADD_VOTE = 120;
    const REPORT_ADD_PHOTO = 130;
    const REPORT_REMOVE_PHOTO = 135;
    const REPORT_STATUS = 141;
    const REPORT_STATUS_BICYCLE = 140;
    const REPORT_STATUS_Zone = 150;
    const REPORT_CREATOR = 160;
    const REPORT_ACCEPTED = 170;
    const REPORT_CHECK = 173;
    const REPORT_ADD_CATEGORY = 180; //depreciate
    const REPORT_REMOVE_CATEGORY = 181; //depreciate
    const REPORT_CATEGORY = 182;
    const REPORT_MANAGER_ADD = 190;
    const REPORT_MANAGER_ALTER = 193;
    const REPORT_MANAGER_REMOVE = 198;
    const REPORT_REPORTTYPE_ALTER = 200;
    const REPORT_MODERATOR_COMMENT_ALTER = 210;
    const REPORT_DRAWINGS = 220;
    const REPORT_TERM = 220;
    const REPORT_CREATOR_CONFIRMATION = 1600;
    const REPORT_MODERATOR_ALTER = 230;
    
    
    /**
     *
     * @var Symfony\Component\Security\Core\SecurityContext 
     */
    private $securityContext;
    
    /**
     *
     * @var Doctrine\ORM\EntityManager 
     */
    private $em;
    
    /**
     *
     * @var Progracqteur\WikipedaleBundle\Resources\Services\GeoService 
     */
    private $geoService;
    
    /**
     *
     * @var \Progracqteur\WikipedaleBundle\Resources\Services\ReachableRoleService 
     */
    private $reachableRoles;
    
    /**
     * default type and term.
     * Must be defined in app/config/parameters.yml
     * 
     * @var string
     */
    private $defaultTypeTerm;
    
    public function __construct($securityContext, 
            EntityManager $em, 
            GeoService $geoService, 
            ReachableRoleService $reachableRoles,
            $defaultTypeTerm)
    {
        $this->securityContext = $securityContext;
        $this->em = $em;
        $this->geoService = $geoService;
        $this->reachableRoles = $reachableRoles;
        $this->defaultTypeTerm = $defaultTypeTerm;
    }
    
    public function checkChangesAreAllowed(ChangeableInterface $object) 
    {
        /**
         * @var Progracqteur\WikipedaleBundle\Entity\Management\User
         */
        $author = $object->getChangeset()->getAuthor();
            
    
        //s'il s'agit d'une création
        if ($object->getChangeset()->isCreation() === true 
                OR
            $object->getChangeset()->isCreation() === null
                )
        {
            if ($object instanceof Report) {
                // si l'utilisateur est authentifié, il ne peut créer un objet pour un autre
                if ( $author->isRegistered()) {
                    if (! $object->getCreator()->equals($author)) {
                        throw ChangeException::mayNotCreateEntityForAnotherUser(10);
                    }
                } else { //si l'utilisateur n'est pas enregistré, alors il ne peut 
                    //pas créer d'objet pour un utilisateur enregistré
                    if ( $object->getCreator()->isRegistered() === true ) {
                        throw ChangeException::mayNotCreateEntityForAnotherUser(100);
                    }
                }
            }
        }
        
        foreach ($object->getChangeset() as $change) {
            switch ($change->getType()) {
                //pour les objets Report
                //case self::REPORT_ADD_COMMENT : 
                case self::REPORT_DRAWINGS:
                    continue;
                    break;
                case self::REPORT_CREATION:
                    continue; //tout le monde peut ajouter un emplacement
                    break;
                case self::REPORT_ADD_VOTE :
                    continue; //tout le monde peut ajouter un commentaire ou un vote
                    break;
                case self::REPORT_CREATOR : 
                    if (!$object->getChangeset()->isCreation()) {
                        throw ChangeException::param('creator');
                    }
                    break;
                case self::REPORT_COMMENT_MODERATOR_MANAGER_ADD:
                    continue; //checked by the controller CommentController
                    break;
                case self::REPORT_ACCEPTED:
                    if ($object->getChangeset()->isCreation()) {
                        //must be accepted except if the user is not registered
                        continue;
                    }
                     
                    if ($this->securityContext->isGranted(User::ROLE_PUBLISHED)) {
                        continue;
                    } else {
                        throw ChangeException::param('accepted');
                    }
                case self::REPORT_ADDRESS : 
                    //allowed on creation
                    if ($object->getChangeset()->isCreation()) {
                        continue;
                    }
                     
                    if ($this->securityContext->isGranted(User::ROLE_DETAILS_LITTLE) 
                        || $this->securityContext->isGranted(User::ROLE_DETAILS_BIG)) {
                        continue;
                    } else {
                        throw ChangeException::param('address');
                    }
                case self::REPORT_DESCRIPTION :
                    //allowed on creation
                    if ($object->getChangeset()->isCreation()) {
                        continue;
                    }
                     
                    if ($this->securityContext->isGranted(User::ROLE_DETAILS_LITTLE) 
                        || $this->securityContext->isGranted(User::ROLE_DETAILS_BIG)) {
                        continue;
                    } else {
                        throw ChangeException::param('description');
                    }
                case self::REPORT_GEOM:
                    //allowed on creation
                    if ($object->getChangeset()->isCreation()) {
                        continue;
                    }
                     
                    if ($this->securityContext->isGranted(User::ROLE_DETAILS_LITTLE) 
                        || $this->securityContext->isGranted(User::ROLE_DETAILS_BIG)) {
                        continue;
                    } else {
                        throw ChangeException::param('geom');
                    }
                case self::REPORT_STATUS:                     
                    if (!($object instanceof Report)) {
                        throw new \Exception('Unexpected object : expecting Report, receiving '
                            .get_class($object));
                    }
                     
                    $groups = $this->securityContext->getToken()->getUser()->getGroups();
                    $hasRight = false;
                    $d = '';
                     
                    foreach ($groups as $group) {
                        $d.= $group->getName()."\n";
                        if ($this->reachableRoles->hasRole(User::ROLE_NOTATION, $group) 
                            && $group->getNotation()->getId() === $change->getNewValue()->getType()) {
                            $d.= 'match role and ID';
                            if (
                                $this->geoService->covers(
                                    $group->getZone()->getPolygon(), 
                                    $object->getGeom())
                            ) {
                                $hasRight = true; 
                                break;
                            } else {
                                 $d .= 'geographic request fail';
                            }
                        } else {
                            $d.= 'does not match role and id';
                        }
                    }
                     
                    if ($hasRight) {
                        continue;
                    } else {
                        throw ChangeException::param('status');
                    }
                     
                     /*
                     $dql = "select g from ProgracqteurWikipedaleBundle:Management\Group g 
                         JOIN g.Zone c JOIN g.notation n 
                         WHERE 
                                EXISTS (select h from ProgracqteurWikipedaleBundle:Management\User u
                                           JOIN u.groups h
                                           WHERE u = :author)
                            AND
                                n.id like :notationId 
                            AND
                                COVERS(c.polygon, :geomString) = true
                            ";
                     
                     $q = $this->em->createQuery($dql);
                     $q->setParameters(array(
                         'geomString' => $geomString,
                         'author' => $author,
                         'notationId' => $change->getNewValue()->getType()
                     ));
                     
                     $groups = $q->getResult();
                     
                     if (count($groups) === 0)
                     {
                         throw ChangeException::param('Status '.$change->getnewValue()->getType().' no rights ');
                     }
                     
                     //filter by role
                     $groups_notation = array();
                     foreach ($groups as $group)
                     {
                         if ($group->hasRole('ROLE_NOTATION'))
                         {
                             $groups_notation[] = $group;
                         }
                     }
                     
                     if (count($groups_notation) > 0)
                     {
                         continue;
                     } else 
                     {
                         throw ChangeException::param('Status '.$change->getnewValue()->getType().' no rights ');
                     }*/
                    break;
                case self::REPORT_ADD_PHOTO:
                    //l'ajout de photo est réglé dans le controleur PhotoController
                    continue;
                    break;
                case self::REPORT_REMOVE_PHOTO:
                    //la modification des photos est réglé dans le controleur PhotoController
                    continue;
                    break;
                case self::REPORT_CATEGORY :
                    //allowed for everybody on creation, only for group 
                    //"ROLE_CATEGORY" later
                    if ($object->getChangeset()->isCreation()) {
                        continue;
                    }
                     
                    if ($this->securityContext->isGranted(User::ROLE_CATEGORY)) {
                        //TODO: wheen we will need it, check if the user may add the category
                        //with specific term using report_type
                        continue;
                    } else {
                        throw ChangeException::param('add category ');
                    }
                    break;
                case self::REPORT_ADD_CATEGORY: // DEPRECIATE
                    //allowed for everybody on creation, only for group 
                    //"ROLE_CATEGORY" later
                    if ($object->getChangeset()->isCreation()) {
                        continue;
                    }
                     
                    if ($this->securityContext->isGranted(User::ROLE_CATEGORY)) {
                        //TODO: wheen we will need it, check if the user may add the category
                        //with specific term using report_type
                        continue;
                    } else {
                        throw ChangeException::param('add category ');
                    }
                    break;
                case self::REPORT_REMOVE_CATEGORY: // DEPRECIATE
                    //allowed only for ROLE_CATEGORY
                    if ($this->securityContext->isGranted(User::ROLE_CATEGORY)) {
                        continue;
                    } else {
                        throw ChangeException::param('remove category');
                    }
                    break;
                case self::REPORT_MANAGER_ADD:
                case self::REPORT_MANAGER_ALTER:
                case self::REPORT_MANAGER_REMOVE:
                    if ($this->securityContext->isGranted(User::ROLE_MANAGER_ALTER)) {
                        continue;
                    } else {
                        throw ChangeException::param('manager');
                    }
                    break;
                case self::REPORT_REPORTTYPE_ALTER:
                    if ($this->securityContext->isGranted(User::ROLE_REPORTTYPE_ALTER)) {
                        continue;
                    } else {
                        throw ChangeException::param('report_type');
                    }
                    break;
                case self::REPORT_MODERATOR_COMMENT_ALTER:
                    if ($this->securityContext
                            ->isGranted(User::ROLE_MODERATOR_COMMENT_ALTER))
                    {
                        continue;
                    } else {
                        throw ChangeException::param('moderator_comment');
                    }
                    break;
                case self::REPORT_TERM:
                    
                    if ($object->getChangeset()->isCreation()) {
                        //it must be to default term
                        //TODO add support for other things than bike...
                        $ar = explode('.', $this->defaultTypeTerm);

                        
                        if ($change->getNewValue() == $ar[1]) {
                            //this is ok !
                            continue; 
                        } else {
                            throw ChangeException::param('term_not_default');
                        }
                    } elseif ($this->securityContext->isGranted(User::ROLE_REPORT_TERM)) {
                        //OK ! 
                        continue;
                    } else {
                        throw ChangeException::param('term');
                    }
                    break;
                case self::REPORT_MODERATOR_ALTER:
                    if ($object->getChangeset()->isCreation()) {
                        continue;
                    } else {
                        if ($this->securityContext
                            ->isGranted(User::ROLE_MODERATOR_ALTER)) {
                            continue;
                        } else {
                            throw ChangeException::param('moderator');
                        }
                    }
                default:
                    throw ChangeException::param('inconnu - '.$change->getType());
            }
        }
        return true;
    }
}