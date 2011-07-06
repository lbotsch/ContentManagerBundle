<?php

namespace Lubo\ContentManagerBundle\SlotController;

use Lubo\ContentManagerBundle\SlotControllerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Lubo\ContentManagerBundle\Entity\Slot;


class MenuController extends ContainerAware implements SlotControllerInterface
{
    /**
     * Return a unique SlotType Id
     * @return string
     */
    public function getId()
    {
        return 'menu';
    }
    
    /**
     * Return a descriptive SlotType Name
     * @return string
     */
    public function getName()
    {
        return 'Menu';
    }
    
    /**
     * Render the slot
     * @param Slot $slot The slot object to render
     * @param boolean $editTools Whether to include edit tools
     * @return string the rendered slot
     */
    public function render(Slot $slot, $editTools)
    {
        $content = '';
        $nodes = array();
        if ($data = $slot->getData()) {
            $data = json_decode($data, true);
            $em = $this->container->get('doctrine')->getEntityManager();
            $dql = 'SELECT p FROM LuboContentManagerBundle:Page p WHERE p.path = :path';
            $query = $em->createQuery($dql);
            $query->setParameter('path', $data['path']);
            $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
            $nodes = $query->getResult();
        }
        
        if (count($nodes) == 0) {
            $content .= "Please configure the menu slot!";
        } else {
            $currentSlug = $this->container->get('request')->attributes->get('slug');
            $router = $this->container->get('router');
            $content .= '<nav class="clearfix"><ul>';
            foreach ($nodes as $item) {
                $content .= '<li'.($item->getSlug() == $currentSlug ? ' class="active"' : '').'>'
                            .'<a href="'. $router->generate('page', array('slug' => $item->getSlug())) .'">'
                            .$item->getTitle().'</a></li>';
            }
            $content .= '</ul></nav>';
        }
        
        return $content;
    }
    
    public function getPathsAction()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $res = $em->createQuery("SELECT DISTINCT p.path FROM LuboContentManagerBundle:Page p ORDER BY p.path")
                    ->getScalarResult();
        $paths = array();
        foreach ($res as $r) {
            $paths[] = $r['path'];
        }
        $data = array("status" => true, "paths" => $paths);
        
        return new Response(json_encode($data), 200,
            array('content-type' => 'application/json; charset=utf-8'));
    }
    
    public function saveSlotAction()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $request = $this->container->get('request');
        $slotId = $request->request->get('id');
        $path = $request->request->get('path');
        
        $slot = $em->find('LuboContentManagerBundle:Slot', $slotId);
        $slot->setData(json_encode(array("path" => $path)));
        $slot->setLocale($this->container->get('session')->getLocale());
        $em->persist($slot);
        $em->flush();
        
        $html = '<article class="lubo-cm-etb-area-slot" data-slot-id="'.$slot->getId().'"'
                .'data-slot-type="menu"><div class="lubo-cm-etb-area-slot-content">'.$this->render($slot, true)
                .'</div></article>';
        $data = array("status" => true, "html" => $html);
        return new Response(json_encode($data), 200,
            array('content-type' => 'application/json; charset=utf-8'));
    }
}
