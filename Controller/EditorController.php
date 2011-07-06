<?php

namespace Lubo\ContentManagerBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Lubo\ContentManagerBundle\Entity\Slot;
use Lubo\ContentManagerBundle\Entity\Area;
use Doctrine\ORM\NoResultException;

class EditorController extends BaseController
{
    protected $styles = array();
    protected $scripts = array();
    
    /**
     * 
     */
    public function addStyle($url)
    {
        $this->styles[] = $url;
    }
    
    public function addScript($url)
    {
        $this->scripts[] = $url;
    }
    
    public function toolbarAction()
    {
        $response = $this->container->get('templating')
            ->renderResponse('LuboContentManagerBundle:Editor:toolbar.html.twig', array(
            'scripts' => $this->scripts,
            'styles' => $this->styles,
        ));
        $response->headers->set('content-type', 'text/javascript');
        return $response;
    }
    
    public function createSlotAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $slotType = $this->get("request")->request->get("slot_type");
        $areaName = $this->get("request")->request->get("area_name");
        $areaGlobal = $this->get("request")->request->get("global") == "1";
        $pageId = $this->get("request")->request->get("page_id");
        $slotEngine = $this->get('lubo_content_manager.slot_engine');
        
        $page = $em->find('LuboContentManagerBundle:Page', $pageId);
        
        try {
            $area = $this->getDoctrine()->getRepository('LuboContentManagerBundle:Area')
                    ->findOneAreaOfPage($page, $areaName, true);
        } catch(NoResultException $e) {
            // Create area if it does not exist yet
            $area = new Area();
            $area->setName($areaName);
            $area->setPageType($page->getPageType());
            if ($areaGlobal) {
                $area->setGlobal(true);
            } else {
                $area->setPage($page);
            }
            $em->persist($area);
            $em->flush();
        }
        
        $slot = new Slot();
        $slot->setSlotType($slotType);
        $slot->setArea($area);
        $slot->setData("");
        $slot->setLocale($this->get('session')->getLocale());
        
        $em->persist($slot);
        $em->flush();
        
        $html = '<article class="lubo-cm-etb-area-slot" data-slot-id="'.$slot->getId().'"'
                .'data-slot-type="'.$slot->getSlotType().'">'
                .'<div class="lubo-cm-etb-area-slot-content">'
                . $slotEngine->render($slot, true)
                . '</div></article>';
        
        $data = array('status' => true, 'html' => $html);
        return $this->getJsonResponse($data);
    }

    public function deleteSlotAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $slotId = intval($this->get("request")->request->get("id"));
        $slot = $em->find('LuboContentManagerBundle:Slot', $slotId);
        if ($slot) {
            $em->remove($slot);
            $em->flush();
        }
        
        $data = array("status" => true);
        return $this->getJsonResponse($data);
    }
}
