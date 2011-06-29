<?php

namespace Lubo\ContentManagerBundle;

use Lubo\ContentManagerBundle\Entity\ContentSlot;

interface SlotControllerInterface
{
    /**
     * Return a unique SlotType Id
     * @return string
     */
    public function getId();
    
    /**
     * Return a descriptive SlotType Name
     * @return string
     */
    public function getName();
    
    /**
     * Render the slot
     * @param Slot $slot The slot object to render
     * @param boolean $editTools Whether to include edit tools
     * @return string the rendered slot
     */
    public function render(ContentSlot $slot, $editTools);
}
