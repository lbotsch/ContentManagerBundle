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
    
    public function getChildrenAction()
    {
        $request = $this->get("request");
        $parentId = intval($request->query->get('id'));
        $repository = $this->getDoctrine()->getRepository('LuboContentManagerBundle:Node');
        if ($parentId < 0) {
            // Get root nodes
            $nodes = $repository->getRootNodes();
        } else {
            $nodes = $repository->children($repository->find($parentId), true);
        }
        // Todo: include the whole tree
        $data = array();
        foreach ($nodes as $node) {
            $data[] = array(
                "attr" => array(
                    "id" => "treenode_".$node->getId(),
                    "rel" => is_a($node, "Lubo\ContentManagerBundle\Entity\Page") ? "page" : "node",
                    "data-url" => is_a($node, "Lubo\ContentManagerBundle\Entity\Page") ?
                        $this->get('router')->generate("page", array("slug" => $node->getSlug()), false) : "",
                    "title" => "Slug: ".$node->getSlug(),
                ),
                "data" => $node->getTitle(),
                "state" => (count($node->getChildren()) > 0 ? "open" : ""),
                "children" => $this->fillChildren($node),
            );
            if (is_a($node, "Lubo\ContentManagerBundle\Entity\Page") && $node->isDefault()) {
                $data[count($data) - 1]["attr"]["style"] = "font-weight:bold;";
            }
        }
        
        return $this->getJsonResponse($data);
    }
    
    private function fillChildren($node)
    {
        $children = array();
        foreach ($node->getChildren() as $child) {
            $children[] = array(
                "attr" => array(
                    "id" => "treenode_".$child->getId(),
                    "rel" => is_a($child, "Lubo\ContentManagerBundle\Entity\Page") ? "page" : "node",
                    "data-url" => is_a($child, "Lubo\ContentManagerBundle\Entity\Page") ?
                        $this->get('router')->generate("page", array("slug" => $child->getSlug()), false) : "",
                    "title" => "Slug: ".$child->getSlug(),
                ),
                "data" => $child->getTitle(),
                "state" => (count($child->getChildren()) > 0 ? "open" : ""),
                "children" => array($this->fillChildren($child)),
            );
            if (is_a($child, "Lubo\ContentManagerBundle\Entity\Page") && $child->isDefault()) {
                $children[count($children) - 1]["attr"]["style"] = "font-weight:bold;";
            }
        }
        return $children;
    }
    
    public function createAction()
    {
        $request = $this->get("request");
        $parentId = intval($request->request->get("parent"));
        // Assume we always create as last child!
        $position = intval($request->request->get("position"));
        $title = $request->request->get("title");
        $type = $request->request->get("type");
        $parent = null;
        if ($parentId > 0)
            $parent = $this->getDoctrine()
                ->getRepository("LuboContentManagerBundle:Node")
                ->find($parentId);
        
        // Create new node/page
        $obj = null;
        if ($type == "page") {
            $obj = new Page();
            $obj->setPageType($this->defaultPageType);
        } else $obj = new Node();
        
        $obj->setTitle($title);
        $obj->setParent($parent);
        $obj->setTranslatableLocale($this->get('session')->getLocale());
        
        $this->getDoctrine()->getEntityManager()->persist($obj);
        $this->getDoctrine()->getEntityManager()->flush();
        
        $data = array(
            "status" => true,
            "id" => $obj->getId(),
            "url" => is_a($obj, "Lubo\ContentManagerBundle\Entity\Page") ?
                $this->get('router')->generate("page", array("slug" => $obj->getSlug()), false) : ""
        );
        return $this->getJsonResponse($data);
    }
    
    public function removeAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $id = $this->get("request")->request->get("id");
        
        $obj = $em->find('LuboContentManagerBundle:Node', $id);
        $em->remove($obj);
        $em->flush();
        
        $data = array("status" => true);
        return $this->getJsonResponse($data);
    }
    
    public function renameAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $id = intval($this->get("request")->request->get("id"));
        $title = $this->get("request")->request->get("title");
        
        $obj = $em->find('LuboContentManagerBundle:Node', $id);
        $obj->setTitle($title);
        $obj->setTranslatableLocale($this->get('session')->getLocale());
        
        $em->persist($obj);
        $em->flush();
        
        $data = array(
            "status" => true,
            "url" => is_a($obj, "Lubo\ContentManagerBundle\Entity\Page") ?
                $this->get('router')->generate("page", array("slug" => $obj->getSlug()), false) : "",
            "title" => $obj->getSlug()
        );
        return $this->getJsonResponse($data);
    }
    
    public function moveAction()
    {
        $data = array("status" => false);
        return $this->getJsonResponse($data);
    }
    
    public function setDefaultAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $id = intval($this->get("request")->request->get("id"));
        
        $obj = $em->find("LuboContentManagerBundle:Node", $id);
        if (is_a($obj, "Lubo\ContentManagerBundle\Entity\Page") && !$obj->isDefault()) {
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
    