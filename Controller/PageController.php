<?php

namespace Lubo\ContentManagerBundle\Controller;

use Lubo\ContentManagerBundle\Entity\Page;
use Lubo\ContentManagerBundle\Entity\Node;

class PageController extends BaseController
{
    protected $pageTypes = array();
    protected $defaultPageType;
    protected $slotTypes = array();
    
    
    public function __construct($defaultPageType='Default')
    {
        $this->defaultPageType = $defaultPageType;
    }
    
    /**
     * Register page type
     * @param $name
     * @param $template
     */
    public function addPageType($name, $template)
    {
        $this->pageTypes[$name] = $template;
    }
    
    /**
     * Show the default page
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('LuboContentManagerBundle:Page');
        if (!($page = $repo->findDefaultPage())) {
            if ($this->container->getParameter('lubo_content_manager.editing_mode')) {
                // Create default pagetree
                $node = new Node();
                $node->setTitle("Default");
                $page = new Page();
                $page->setTitle("Page");
                $page->setDefault(true);
                $page->setPageType($this->defaultPageType);
                $page->setParent($node);
                $em->persist($node);
                $em->persist($page);
                $em->flush();
            } else {
                throw $this->createNotFoundException();
            }
        }
        $this->get('logger')->debug('DefaultPage: slug='.$page->getSlug()
            .', locale='.$page->getTranslatableLocale()
            .', id='.$page->getId());
        $this->get('request')->attributes->set('slug', $page->getSlug());
        return $this->forward('lubo_content_manager.page_controller:showAction', array('slug' => $page->getSlug()));
    }
    
    /**
     * Show a page
     * @param $slug string The slug of the page to show
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('LuboContentManagerBundle:Page');
        $page = $repo->findOneBySlug($slug);
        if (!$page || !is_a($page, 'Lubo\ContentManagerBundle\Entity\Page')) {
            if ($this->container->getParameter('lubo_content_manager.editing_mode')) {
                $dql = "SELECT p "
                        ."FROM LuboContentManagerBundle:Page p "
                        ."WHERE p.id = "
                            ."(SELECT t.foreignKey "
                                ."FROM StofDoctrineExtensionsBundle:Translation t "
                                ."WHERE t.content = :slug AND t.field = 'slug' "
                                ."AND t.objectClass = 'Lubo\ContentManagerBundle\Entity\Page')";
                $query = $em->createQuery($dql);
                $query->setParameter('slug', $slug);
                $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
                    'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
                $query->setMaxResults(1);
                try {
                   $page = $query->getSingleResult();
                   $page->setTitle('New Page');
                   $em->persist($page);
                   $em->flush();
                } catch (\Exception $e) {
                    $this->get('logger')->err($e->getMessage());
                    throw $this->createNotFoundException();
                }
            } else {
                throw $this->createNotFoundException();
            }
        }
        
        $logger = $this->get('logger');
        $logger->debug('Getting areas of page');
        $repo = $this->getDoctrine()->getRepository('LuboContentManagerBundle:Area');
        $res = $repo->findAreasOfPage($page);
        $logger->debug('Got areas of page');
        $areas = array();
        foreach ($res as $area) {
            $areas[$area->getName()] = $area;
        }
        
        return $this->render($this->pageTypes[$page->getPageType()], array(
            'title' => $page->getTitle(),
            'areas' => $areas,
            'page_id' => $page->getId(),
            'page_type' => $page->getPageType()
        ));
    }
}
