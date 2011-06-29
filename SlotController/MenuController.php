<?php

namespace Lubo\ContentManagerBundle\SlotController;

use Lubo\ContentManagerBundle\SlotControllerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Lubo\ContentManagerBundle\Entity\ContentSlot;


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
    public function render(ContentSlot $slot, $editTools)
    {
        $content = '';
        $nodes = array();
        if ($data = $slot->getData()) {
            $data = json_decode($data, true);
            $em = $this->container->get('doctrine')->getEntityManager();
            $dql = 'SELECT n FROM LuboContentManagerBundle:Page n JOIN n.parent p WHERE p.id = :parent_id';
            $query = $em->createQuery($dql);
            $query->setParameter('parent_id', $data['parent']);
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
    
    public function getNodesAction()
    {
        $repo = $this->container->get('doctrine')->getRepository('LuboContentManagerBundle:Node');
        $data = array("status" => true, "nodes" => array());
        $nodes = $repo->findAll();
        foreach ($nodes as $node) {
            $data["nodes"][] = array("id" => $node->getId(), "title" => $node->getTitle());
        }
        return new Response(json_encode($data), 200,
            array('content-type' => 'application/json; charset=utf-8'));
    }
    
    public function saveSlotAction()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $request = $this->container->get('request');
        $slotId = $request->request->get('id');
        $menuParent = $request->request->get('parent');
        
        $slot = $em->find('LuboContentManagerBundle:ContentSlot', $slotId);
        if (!is_a($slot, 'Lubo\ContentManagerBundle\Entity\ContentSlot')) {
            throw new \LogicException("Slot (id: $slotId) is of type ".get_class($slot). " but should be ContentSlot!");
        }
        assert(is_a($slot, 'Lubo\ContentManagerBundle\Entity\ContentSlot'));
        $slot->setData(json_encode(array("parent" => $menuParent)));
        $slot->setTranslatableLocale($this->container->get('session')->getLocale());
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
