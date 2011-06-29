<?php

namespace Lubo\ContentManagerBundle\Entity\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Lubo\ContentManagerBundle\Entity\Area;

class SlotRepository extends NestedTreeRepository
{
    /**
     * @throws Doctrine\ORM\NoResultException
     * @throws Doctrine\ORM\NonUniqueResultException
     */
    public function findVirtualSlot(Area $area)
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT s FROM LuboContentManagerBundle:VirtualSlot s"
            ." WHERE s.area = :area AND s.parent IS NULL"
        );
        return $query->setParameters(array(
            "area" => $area
        ))->getSingleResult();
    }
}
