<?php

namespace Lubo\ContentManagerBundle\Entity\Repository;

use Gedmo\Sortable\Entity\Repository\SortableRepository;

class PageRepository extends SortableRepository
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
                $res->setLocale('en');
                $this->getEntityManager()->refresh($res);
            }
            return $res;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    public function findOneBySlug($slug, $lazy=false)
    {
        try {
            $dql = "SELECT p".($lazy ? "" : ", a, s")." FROM LuboContentManagerBundle:Page p ";
            if (!$lazy) {
                $dql .= "LEFT JOIN p.areas a LEFT JOIN a.slots s ";
            }
            $dql .= "WHERE p.slug = :slug";
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
    
    public function removeByPath($path)
    {
        $dql = "DELETE FROM LuboContentManagerBundle:Page p "
                . "WHERE p.path LIKE :path";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('path', $path."%");
        $query->getResult();
    }
    
    public function getPageTree($path="/")
    {
        $em = $this->getEntityManager();
        $dql = "SELECT p FROM LuboContentManagerBundle:Page p "
                ."WHERE p.path LIKE :path "
                ."ORDER BY p.path, p.position ";
        $query = $em->createQuery($dql);
        $query->setParameter('path', $path."%");
        $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        //echo $query->getSQL();
        $res = $query->getResult();
        $pages = array();
        
        foreach ($res as $page) {
            if (!$page->getTitle()) {
                // The title is not yet translated in the current locale
                $page->setTitle("New Page");
                $em->persist($page);
                $em->flush();
            }
            $path = $page->getPath();
            if ($path[0] == '/') $path = substr($path, 1);
            if ($path == "") {
                $pages[] = $page;
                continue;
            }
            $path = array_reverse(explode("/", $path));
            $tmp = array($page);
            foreach ($path as $p) {
                $tmp = array($p => $tmp);
            }
            $pages = array_merge_recursive($pages, $tmp);
        }
        return $pages;
    }
}
