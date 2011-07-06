<?php

namespace Lubo\ContentManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Lubo\ContentManagerBundle\Entity\Page;
use Lubo\ContentManagerBundle\Entity\Node;


class PagetreeController extends BaseController
{
    protected $defaultPageType;
    
    public function setDefaultPageType($name)
    {
        $this->defaultPageType = $name;
    }
    
    /**
     * Return the main Pagetree script
     */
    public function getMainScriptAction()
    {
        $response = $this->container->get('templating')
            ->renderResponse('LuboContentManagerBundle:Pagetree:main_script.js.twig', array(
                
            ));
        $response->headers->set('content-type', 'text/javascript');
        return $response;
    }
    
    public function getChildrenAction()
    {
        $request = $this->get("request");
        $path = $request->query->get('path');
        $repository = $this->getDoctrine()->getRepository('LuboContentManagerBundle:Page');
        $pages = $repository->getPageTree($path);
        
        $data = $this->fillChildren($pages, $path);
        return $this->getJsonResponse($data);
    }
    
    private function fillChildren($nodes, $basePath="")
    {
        $data = array();
        foreach ($nodes as $key => $node) {
            $id = is_array($node) ?
                    str_replace("/", "-", str_replace(" ", "_", strtolower($basePath."/".$key)))
                    : $node->getId();
            $path = ($basePath ?: "/").(is_array($node) ? $key : "");
            $data[] = array(
                "attr" => array(
                    "id" => "treenode_".$id,
                    "rel" => is_array($node) ? "node" : "page",
                    "data-path" => $path,
                    "data-url" => is_array($node) ?
                        "" : $this->get('router')->generate("page", array("slug" => $node->getSlug()), false),
                    "title" => "Path: ".$path.(is_array($node) ? "" : ", Slug: ".$node->getSlug()),
                ),
                "data" => is_array($node) ? $key : $node->getTitle(),
                "state" => is_array($node) ? "open" : "",
                "children" => is_array($node) ? $this->fillChildren($node, $basePath."/".$key) : array(),
            );
            if (is_a($node, "Lubo\ContentManagerBundle\Entity\Page") && $node->isDefault()) {
                $data[count($data) - 1]["attr"]["style"] = "font-weight:bold;";
            }
        }

        if ($basePath == "") {
            $data = array(
                "attr" => array(
                    "id" => "tree_root",
                    "rel" => "root",
                    "data-path" => "/",
                    "data-url" => "",
                    "title" => "Path: /"
                ),
                "data" => "Root",
                "state" => "open",
                "children" => $data,
            );
        }
        return $data;
    }
    
    public function createAction()
    {
        $request = $this->get("request");
        $position = intval($request->request->get("position"));
        $title = $request->request->get("title");
        $path = $request->request->get("path");
        $pageType = $request->request->get("page_type") ?: $this->defaultPageType;
        $defaultLocale = $this->container->getParameter('session.default_locale');
        
        // Create new node/page
        $page = new Page();
        $page->setPageType($pageType);
        $page->setTitle($title);
        $page->setPosition($position);
        $page->setPath($path);
        $page->setLocale($this->get('session')->getLocale());
        
        $this->getDoctrine()->getEntityManager()->persist($page);
        $this->getDoctrine()->getEntityManager()->flush();
        
        if ($this->get('session')->getLocale() != $defaultLocale) {
            $page->setTitle("New Page");
            $page->setLocale($defaultLocale);
            $this->getDoctrine()->getEntityManager()->persist($page);
            $this->getDoctrine()->getEntityManager()->flush();
        }
        
        $data = array(
            "status" => true,
            "id" => $page->getId(),
            "path" => $page->getPath(),
            "url" => $this->get('router')->generate("page", array("slug" => $page->getSlug()), false),
        );
        return $this->getJsonResponse($data);
    }
    
    public function removeAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->get('request')->request;
        if ($request->has('id')) {
            $id = $request->get('id');
            $obj = $em->find('LuboContentManagerBundle:Page', $id);
            $em->remove($obj);
            $em->flush();
        } else {
            $path = $request->get('path');
            $repo = $em->getRepository('LuboContentManagerBundle:Page');
            $repo->removeByPath($path);
        }
        
        $data = array("status" => true);
        return $this->getJsonResponse($data);
    }
    
    public function renameAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $id = intval($this->get("request")->request->get("id"));
        $title = $this->get("request")->request->get("title");
        
        $obj = $em->find('LuboContentManagerBundle:Page', $id);
        $obj->setTitle($title);
        $obj->setLocale($this->get('session')->getLocale());
        
        $em->persist($obj);
        $em->flush();
        $em->refresh($obj);
        
        $data = array(
            "status" => true,
            "url" => $this->get('router')->generate("page", array("slug" => $obj->getSlug()), false),
            "title" => "Slug: ".$obj->getSlug()
        );
        return $this->getJsonResponse($data);
    }
    
    public function moveAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $id = intval($this->get("request")->request->get("id"));
        $path = $this->get("request")->request->get("path");
        $position = intval($this->get("request")->request->get("position"));
        
        $page = $em->find('LuboContentManagerBundle:Page', $id);
        $page->setPath($path);
        $page->setPosition($position);
        $em->persist($page);
        $em->flush();
        
        $data = array("status" => true, "id" => $page->getId());
        return $this->getJsonResponse($data);
    }
    
    public function setDefaultAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $id = intval($this->get("request")->request->get("id"));
        
        $obj = $em->find("LuboContentManagerBundle:Page", $id);
        if (!$obj->isDefault()) {
            $old = $this->getDoctrine()->getRepository("LuboContentManagerBundle:Page")->findDefaultPage();
            $old->setDefault(false);
            $obj->setDefault(true);
            $em->persist($old);
            $em->persist($obj);
            $em->flush();
        }
        
        $data = array("status" => true);
        return $this->getJsonResponse($data);
    }
}
    