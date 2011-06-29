<?php

namespace Lubo\ContentManagerBundle\Entity\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class PageRepository extends NestedTreeRepository
{
    public function findDefaultPage()
    {
        try {
            $dql = "SELECT p FROM LuboContentManagerBundle:Page p WHERE p.is_default = true";
            $query = $this->getEntityManager()->createQuery($dql);
            $query->setMaxResults(1);
            $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
            $res = $query->getSingleResult();
            if ($res->getSlug() == "") {
                $res->setTranslatableLocale('en');
                $this->getEntityManager()->refresh($res);
            }
            return $res;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    public function findOneBySlug($slug)
    {
        try {
            $dql = "SELECT p FROM LuboContentManagerBundle:Page p "
                    ."WHERE p.slug = :slug";
            $query = $this->getEntityManager()->createQuery($dql);
            $query->setMaxResults(1);
            $query->setParameter('slug', $slug);
            $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
            return $query->getSingleResult();
        } catch (\Exception $e) {
            return null;
        }
    }
}
