<?php

namespace Lubo\ContentManagerBundle;

use Symfony\Component\DependencyInjection\ContainerAware;
use Lubo\ContentManagerBundle\Entity\Slot;

class SlotEngine extends ContainerAware
{
    protected $slotControllers = array();
    
    public function __construct()
    {
        
    }
    
    /**
     * Get ids of available SlotControllers
     * @return array [{id: SlotController id, name: SlotController name}, ...]
     */
    public function getAvailableSlotIds()
    {
        $slots = array();
        foreach ($this->slotControllers as $id => $slot) {
            $slots[] = array('id' => $id, 'name' => $slot->getName());
        }
        return $slots;
    }
    
    public function addSlotController(SlotControllerInterface $controller)
    {
        $this->slotControllers[$controller->getId()] = $controller;
    }
    
    /**
     * Get SlotController by id
     * @param string $id The SlotController id
     * @return SlotControllerInterface
     * @throws InvalidArgumentException When no SlotController was found
     */
    public function getSlotController($id)
    {
        if (!array_key_exists($id, $this->slotControllers)) {
            throw new \InvalidArgumentException('No SlotController with id \''.$id.'\' was registered.');
        }
        return $this->slotControllers[$id];
    }
    
    /**
     * Render a slot
     * @param Slot $slot Slot object
     * @param boolean $editTools Whether to include edit tools
     * @return string The rendered Slot
     * @throws InvalidArgumentException When no SlotController was found
     */
    public function render(Slot $slot, $editTools=false)
    {
        return $this->getSlotController($slot->getSlotType())->render($slot, $editTools);
    }
}
