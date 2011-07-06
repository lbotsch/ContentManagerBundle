<?php

namespace Lubo\ContentManagerBundle\SlotController;

require_once(__DIR__ . "/markdown.php");

use Lubo\ContentManagerBundle\SlotControllerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Lubo\ContentManagerBundle\Entity\Slot;


class MarkdownController extends ContainerAware implements SlotControllerInterface
{
    /**
     * Return a unique SlotType Id
     * @return string
     */
    public function getId()
    {
        return 'markdown';
    }
    
    /**
     * Return a descriptive SlotType Name
     * @return string
     */
    public function getName()
    {
        return 'Markdown';
    }
    
    /**
     * Render the slot
     * @param Slot $slot The slot object to render
     * @param boolean $editTools Whether to include edit tools
     * @return string the rendered slot
     */
    public function render(Slot $slot, $editTools)
    {
        $data = $slot->getData();
        $md = new \Markdown_Parser();
        return $md->transform($data);
    }
    
    public function renderEditor()
    {
        return $this->container->get('templating')
            ->renderResponse('LuboContentManagerBundle:Slot:markdown_editor.js.twig', array(
                
            ))->getContent();
    }
    
    public function saveSlotAction()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $request = $this->container->get('request');
        $slotId = $request->request->get('id');
        $slotData = $request->request->get('data');
        
        $slot = $em->find('LuboContentManagerBundle:Slot', $slotId);
        assert(is_a($slot, 'Lubo\ContentManagerBundle\Entity\ContentSlot'));
        $slot->setData($slotData);
        $slot->setTranslatableLocale($this->container->get('session')->getLocale());
        $em->persist($slot);
        $em->flush();
        
        $html = '<article class="lubo-cm-etb-area-slot" data-slot-id="'.$slot->getId().'"'
            .'data-slot-type="'.$slot->getSlotType().'"><div class="lubo-cm-etb-area-slot-content">'
            . $this->render($slot, true)
            . '</div></article>';
        
        $data = array("status" => true, "html" => $html);
        return new Response(json_encode($data), 200,
            array('content-type' => 'application/json; charset=utf-8'));
    }
}
