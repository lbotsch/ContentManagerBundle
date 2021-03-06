<?php

namespace Lubo\ContentManagerBundle\SlotController;

use Lubo\ContentManagerBundle\SlotControllerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Lubo\ContentManagerBundle\Entity\Slot;


class RawTextController extends ContainerAware implements SlotControllerInterface
{
    /**
     * Return a unique SlotType Id
     * @return string
     */
    public function getId()
    {
        return 'raw_text';
    }
    
    /**
     * Return a descriptive SlotType Name
     * @return string
     */
    public function getName()
    {
        return 'Raw Text';
    }
    
    /**
     * Render the slot
     * @param Slot $slot The slot object to render
     * @param boolean $editTools Whether to include edit tools
     * @return string the rendered slot
     */
    public function render(Slot $slot, $editTools)
    {
        return $slot->getData();
    }
    
    public function renderEditor()
    {
        return $this->container->get('templating')
            ->renderResponse('LuboContentManagerBundle:Slot:raw_text_editor.js.twig', array(
                
            ))->getContent();
    }
    
    public function saveSlotAction()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $request = $this->container->get('request');
        $slotId = $request->request->get('id');
        $slotData = $request->request->get('data');
        
        $slot = $em->find('LuboContentManagerBundle:Slot', $slotId);
        $slot->setData($slotData);
        $slot->setLocale($this->container->get('session')->getLocale());
        $em->persist($slot);
        $em->flush();
        
        $data = array("status" => true, "html" => $slotData);
        return new Response(json_encode($data), 200,
            array('content-type' => 'application/json; charset=utf-8'));
    }
}
