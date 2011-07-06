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
                // Create default page
                $page = new Page();
                $page->setTitle("New Page");
                $page->setDefault(true);
                $page->setPath('/');
                $page->setPageType($this->defaultPageType);
                $page->setLocale($this->container->getParameter('session.default_locale'));
                $em->persist($page);
                $em->flush();
                //$em->refresh($page);
            } else {
                throw $this->createNotFoundException();
            }
        }
        $this->get('logger')->debug('DefaultPage: slug='.$page->getSlug()
            .', locale='.$page->getLocale()
            .', id='.$page->getId());
        $slug = $page->getSlug();
        //$em->clear();
        
        $this->get('request')->attributes->set('slug', $page->getSlug());
        //return $this->forward('lubo_content_manager.page_controller:showAction', array('slug' => $page->getSlug()));
        return $this->showAction($slug, $page);
    }
    
    /**
     * Show a page
     * @param $slug string The slug of the page to show
     */
    public function showAction($slug, $page=null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('LuboContentManagerBundle:Page');
        if (is_null($page) || !is_a($page, 'Lubo\ContentManagerBundle\Entity\Page')) {
            $page = $repo->findOneBySlug($slug);
        }
        if (!$page) {
            if ($this->container->getParameter('lubo_content_manager.editing_mode')) {
                $dql = "SELECT p, a, s "
                        ."FROM LuboContentManagerBundle:Page p "
                        ."LEFT JOIN p.areas a LEFT JOIN a.slots s "
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
        
        $areas = array();
        foreach ($em->getRepository("LuboContentManagerBundle:Area")->findAreasOfPage($page) as $area) {
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
