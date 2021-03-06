<?php

namespace Progracqteur\WikipedaleBundle\Entity\Management;

use FOS\UserBundle\Entity\Group as BaseGroup;
use Progracqteur\WikipedaleBundle\Entity\Management\Zone;
use Symfony\Component\Validator\ExecutionContext;
use Doctrine\Common\Collections\ArrayCollection;
use Progracqteur\WikipedaleBundle\Entity\Model\Report;

/**
 * A group is a team/group of users that can NOTATE / MANAGE / MODERATE
 * reports of a given zone. 
 */
class Group extends BaseGroup
{
    /**
     * @var Progracqteur\WikipedaleBundle\Management\Zone The zone relative to
     * the group.
     */
    private $zone;
    
    /**
     * @var Progracqteur\WikipedaleBundle\Management\Notation The notition 
     * relative to the group/.
     */
    private $notation;
    
    /**
     * @var string The type of the group. The type can be 'NOTATION' if XXX,
     * 'MODERATOR' if the group permits moderation, and 'MANAGER' of the group
     * permits management
     */
    private $type;
    
    /**
     * The reports that are moderated by the group.
     * 
     * @var \Doctrine\Common\Collections\ArrayCollection;
     * 
     * @todo adding $reportsAsManager that are manager by the group;
     */
    private $reportsAsModerator;
    
    /**
     * @var string
     */
    const TYPE_NOTATION = 'NOTATION';
    /**
     * @var string
     */
    const TYPE_MANAGER = 'MANAGER';
    /**
     * @var string
     */
    const TYPE_MODERATOR = 'MODERATOR';

    /**
     * return an array of each valid types
     * 
     * @return array
     */
    public static function getExistingTypes()
    {
        return array(self::TYPE_MANAGER, self::TYPE_MODERATOR, self::TYPE_NOTATION);
    }

    public function __construct($name = '', $roles = array())
    {
        parent::__construct($name, $roles);
        $this->reportsAsModerator = new ArrayCollection();
    }

    /**
     * Set Zone
     *
     * @param \Progracqteur\WikipedaleBundle\Entity\Management\Zone $Zone
     * @return Group
     */
    public function setZone(Zone $zone = null)
    {
        $this->zone = $zone;
        return $this;
    }

    /**
     * 
     * @return \Progracqteur\WikipedaleBundle\Entity\Management\Zone
     */
    public function getZone()
    {
        return $this->zone;
    }

    public function setType($type)
    {
        $this->type = $type;
        //TODO this should be moved to security.yml
        $this->setRoles(array());

        switch ($type) {
            case self::TYPE_MODERATOR :
                $this->addRole(User::ROLE_MODERATOR_COMMENT_ALTER)
                    ->addRole(User::ROLE_MODERATOR);
            case self::TYPE_MANAGER :
                $this->addRole(User::ROLE_NOTATION)
                    ->addRole(User::ROLE_CATEGORY)
                    ->addRole(User::ROLE_DETAILS_LITTLE)
                    ->addRole(User::ROLE_PUBLISHED)
                    ->addRole(User::ROLE_SEE_USER_DETAILS)
                    ->addRole(User::ROLE_MANAGER_ALTER)
                    ->addRole(User::ROLE_SEE_UNACCEPTED)
                    ->addRole(User::ROLE_COMMENT_MODERATOR_MANAGER)
                ;
                break;
            case self::TYPE_NOTATION:
                $this->addRole(User::ROLE_NOTATION)
                    ->addRole(User::ROLE_CATEGORY)
                    ->addRole(User::ROLE_DETAILS_LITTLE)
                    ->addRole(User::ROLE_SEE_USER_DETAILS)
                    ->addRole(User::ROLE_SEE_UNACCEPTED)
                ;
                break;
        }

        switch ($type) {
            case self::TYPE_MANAGER:
                $this->addRole(User::ROLE_MANAGER);
                break;
        }


        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * Set notation
     *
     * @param \Progracqteur\WikipedaleBundle\Management\Notation $notation
     * @return Group
     */
    public function setNotation(\Progracqteur\WikipedaleBundle\Entity\Management\Notation $notation = null)
    {
        $this->notation = $notation;
        return $this;
    }

    /**
     * Get notation
     *
     * @return \Progracqteur\WikipedaleBundle\Management\Notation 
     */
    public function getNotation()
    {
        return $this->notation;
    }

    /**
     * list of reports where the group is moderator
     * 
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getReportsAsModerator()
    {
        return $this->reportsAsModerator;
    }

    /**
     * 
     * @param \Doctrine\Common\Collections\ArrayCollection $reportsAsModerator
     */
    public function setReportsAsModerator(ArrayCollection $reportsAsModerator)
    {
        $this->reportsAsModerator = $reportsAsModerator;
    }

    /**
     * 
     * @param \Progracqteur\WikipedaleBundle\Entity\Model\Report $report
     * @return Group 
     */
    public function addReportsAsModerator(Report $report)
    {
        $this->reportsAsModerator->add($report);
        return $this;
    }

    public function __toString()
    {
        return $this->getName() . ' ("' . $this->getNotation() . '" à ' . $this->getZone() . ')';
    }

    public function isValidType(ExecutionContext $context)
    {
        $a = self::getExistingTypes();

        if (!in_array($this->getType(), $a)) {
            $propertyPath = $context->getPropertyPath() . 'Type';
            $context->setPropertyPath($propertyPath);
            $context->addViolation('group.type.invalid', array(), $this->getType());
        }
    }

    public function isValidNotation(ExecutionContext $context)
    {
        if ($this->hasRole(User::ROLE_NOTATION)) {
            if ($this->getNotation() === null) {
                $context->addViolationAtSubPath('Notation', 'group.notation.not_null', array(), null);
                return;
            }
        }

        if ($this->getType() === self::TYPE_MODERATOR) {
            if ($this->getNotation()->getId() !== 'cem') {
                $context->addViolationAtSubPath('Notation', 'group.notation.must_be_cem', array(), $this->getNotation()->getId());
                return;
            }
        }
    }
}
