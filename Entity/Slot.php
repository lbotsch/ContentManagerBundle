<?php

namespace Lubo\ContentManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Lubo\ContentManagerBundle\Entity\Repository\SlotRepository")
 * @ORM\Table(name="slot")
 */
class Slot
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
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
    
    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Area", inversedBy="slots")
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $area;
    
    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;
    
    public function __construct()
    {
        $this->data = "";
    }
    
    /* Getters / Setters */
    
    public function getId()
    {
        return $this->id;
    }
    
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
    
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
    
    public function getLocale()
    {
        return $this->locale;
    }
    
    public function setArea(Area $area)
    {
        $this->area = $area;
    }
    
    public function getArea()
    {
        return $this->area;
    }
    
    public function setPosition($position)
    {
        $this->position = $position;
    }
    
    public function getPosition()
    {
        return $this->position;
    }
}
    