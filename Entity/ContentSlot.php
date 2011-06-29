<?php

namespace Lubo\ContentManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Lubo\ContentManagerBundle\Entity\Repository\SlotRepository")
 */
class ContentSlot extends Slot
{
    /**
     * @ORM\Column(type="string", length="100")
     */
    protected $slot_type;
    
    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text")
     */
    protected $data;
    
    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;
    
    public function __construct()
    {
        parent::__construct();
        $this->data = "";
    }
    
    /* Getters / Setters */
    
    public function setSlotType($type)
    {
        $this->slot_type = $type;
    }
    
    public function getSlotType()
    {
        return $this->slot_type;
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
    