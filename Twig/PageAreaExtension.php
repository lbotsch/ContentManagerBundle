<?php

namespace Lubo\ContentManagerBundle\Twig;

use Lubo\ContentManagerBundle\Entity\Area;

class PageAreaExtension extends \Twig_Extension
{
    private $editorTools;
    private $slotEngine;
    
    public function __construct($slotEngine, $editorTools=false)
    {
        $this->slotEngine = $slotEngine;
        $this->editorTools = $editorTools;
    }
    
    public function getFunctions()
    {
        return array(
            'page_area' => new \Twig_Function_Method($this, 'createPageArea', array('needs_context' => true)),
        );
    }
    
    /**
     * @param string $name The area name
     * @param array $options (optional) An array of options
     *   {
     *     'global' => true     // This is a global area
     *     'allowed_slots' => ['id1', 'id2', ...],    // Whitelist
     *     'disallowed_slots' => ['id1', 'id2', ...]  // Blacklist
     *   }
     */
    public function createPageArea($context, $name, $options=array())
    {
        if (!array_key_exists("areas", $context)
                || !array_key_exists("page_id", $context)
                || !array_key_exists("page_type", $context)) {
            throw new \Twig_Error_Runtime("Twig Function page_area is only available in a PageType template!");
        }
        $areas = $context["areas"];
        $area_global = (array_key_exists("global", $options) && $options["global"]) ? "1" : "0";
        $page_id = $context["page_id"];
        $page_type = $context["page_type"];
        $content = "";
        if ($this->editorTools) {
            $content .= '<div class="lubo-cm-etb-area">';
            $allowedSlots = $this->slotEngine->getAvailableSlotIds();
            if (array_key_exists('allowed_slots', $options)
                    && is_array($options['allowed_slots'])) {
                $tmp = array();
                foreach ($allowedSlots as $s) {
                    if (in_array($s["id"], $options['allowed_slots']))
                        $tmp[] = $s;
                }
                $allowedSlots = $tmp;
            }
            if (array_key_exists('disallowed_slots', $options)
                    && is_array($options['disallowed_slots'])) {
                $tmp = array();
                foreach ($allowedSlots as $s) {
                    if (!in_array($s["id"], $options['disallowed_slots']))
                        $tmp[] = $s;
                }
                $allowedSlots = $tmp;
            }
            $allowedSlots = array_values($allowedSlots);
            
            $content .= '<div id="lubo-cm-etb-area-toolbar-'.$name.'" class="lubo-cm-etb-area-toolbar" '
                .'data-area-name="'.$name.'" data-area-global="'.$area_global.'" '
                .'data-page-id="'.$page_id.'" data-page-type="'.$page_type.'" '
                .'data-allowed-slots=\''.json_encode($allowedSlots).'\'></div>';
        }
        
        if (array_key_exists($name, $areas)
                && count($areas[$name]->getSlots()) > 0)
        {
            foreach ($areas[$name]->getSlots() as $slot) {
                if ($this->editorTools)
                    $content .= '<article class="lubo-cm-etb-area-slot" data-slot-id="'.$slot->getId().'"'
                        .'data-slot-type="'.$slot->getSlotType().'"><div class="lubo-cm-etb-area-slot-content">';
                else
                    $content .= '<article>';
                $content .= $this->slotEngine->render(
                    $slot,
                    $this->editorTools
                );
                if ($this->editorTools) $content .= '</div>';
                $content .= '</article>';
            }
        } elseif ($this->editorTools) {
            $content .= '<div class="lubo-cm-etb-area-notice"><em>This area has no content yet.</em></div>';
        }
        
        if ($this->editorTools) $content .= '</div>';
        
        return $content;
    }

    public function getName()
    {
        return "PageAreaExtension";
    }
}
