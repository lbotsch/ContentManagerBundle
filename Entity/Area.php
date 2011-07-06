<?php

namespace Lubo\ContentManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity(repositoryClass="Lubo\ContentManagerBundle\Entity\Repository\AreaRepository")
 * @ORM\Table(name="area")
 */
class Area
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
    protected $name;
    
    /**
     * @var boolean $is_global
     * 
     * @ORM\Column(type="boolean")
     */
    protected $is_global;
    
    /**
     * @ORM\Column(type="string", length="100")
     */
    protected $page_type;
    
    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="areas")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $page;
    
    /**
     * @ORM\OneToMany(targetEntity="Slot", mappedBy="area")
     */
    protected $slots;
    
    public function __construct()
    {
        $this->is_global = false;
        $this->slots = new ArrayCollection();
    }
    
    /* Getters / Setters */
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setGlobal($is_global)
    {
        $this->is_global = $is_global;
    }
    
    public function isGlobal()
    {
        return $this->is_global;
    }
    
    public function setPageType($page_type)
    {
        $this->page_type = $page_type;
    }
    
    public function getPageType()
    {
        return $this->page_type;
    }
    
    public function setPage(Page $page)
    {
        $this->page = $page;
    }
    
    public function getPage()
    {
        return $this->page;
    }
    
    public function getSlots()
    {
        return $this->slots;
    }
}
