<?php

namespace Lubo\ContentManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Lubo\ContentManagerBundle\Entity\Repository\SlotRepository")
 * @ORM\Table(name="slot")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"virtual" = "VirtualSlot", "content" = "ContentSlot"})
 */
abstract class Slot
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;
    
    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Slot", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;
    
    /**
     * @ORM\OneToMany(targetEntity="Slot", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;
    
    /**
     * @ORM\ManyToOne(targetEntity="Area", inversedBy="slots")
     * @ORM\JoinColumn(name="area_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $area;
    
    
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
    
    /* Getters / Setters */
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setParent(Slot $parent)
    {
        $this->parent = $parent;    
    }

    public function getParent()
    {
        return $this->parent;   
    }
    
    public function getChildren()
    {
        return $this->children;
    }
    
    public function setArea(Area $area)
    {
        $this->area = $area;
    }
    
    public function getArea()
    {
        return $this->area;
    }
}
