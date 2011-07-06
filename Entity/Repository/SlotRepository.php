<?php

namespace Lubo\ContentManagerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Lubo\ContentManagerBundle\Entity\Area;
use Gedmo\Sortable\Entity\Repository\SortableRepository;

class SlotRepository extends SortableRepository
{
    public function getAreaSlots(Area $area)
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT s FROM LuboContentManagerBundle:Slot s"
            ." WHERE s.area = :area ORDER BY s.position"
        );
        return $query->setParameters(array(
            "area" => $area
        ))->getResult();
    }
}
