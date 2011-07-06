<?php

namespace Lubo\ContentManagerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class AreaRepository extends EntityRepository
{
    public function findAreasOfPage($page, $lazy=false)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT a'.($lazy ? ' ': ', s ')
            .'FROM LuboContentManagerBundle:Area a '
            .($lazy ? '' : 'LEFT JOIN a.slots s ')
            .'WHERE a.page = :page OR (a.is_global = true AND a.page_type = :page_type) '
            .($lazy ? '' : 'ORDER BY s.position'));
        $query->setParameters(array(
            "page" => $page,
            "page_type" => $page->getPageType()
        ));
        $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $areas = $query->getResult();
        return $areas;
    }
    
    /**
     * @throws Doctrine\ORM\NoResultException
     * @throws Doctrine\ORM\NonUniqueResultException
     */
    public function findOneAreaOfPage($page, $name, $lazy=false)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT a'.($lazy ? ' ': ', s ')
            .'FROM LuboContentManagerBundle:Area a '
            .($lazy ? '' : 'JOIN a.slots s ')
            .'WHERE a.name = :name AND (a.page = :page OR (a.is_global = true AND a.page_type = :page_type))');
        $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        $area = $query->setParameters(array(
            "name" => $name,
            "page" => $page,
            "page_type" => $page->getPageType()
        ))->getSingleResult();
        return $area;
    }
}
