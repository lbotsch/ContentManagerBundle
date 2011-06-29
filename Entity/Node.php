<?php

namespace Lubo\ContentManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @ORM\Table(name="node")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"node" = "Node", "page" = "Page"})
 */
class Node
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @Gedmo\Sluggable
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length="100")
     */
    protected $title;
    
    /**
     * @Gedmo\Slug
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length="100", unique=true)
     */
    protected $slug;
    
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
     * @ORM\ManyToOne(targetEntity="Node", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;
    
    /**
     * @ORM\OneToMany(targetEntity="Node", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;
    
    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;
    
    
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }
    
    /* Getters / Setters */
    
    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }
    
    public function getSlug()
    {
        return $this->slug;
    }
    
    public function setParent(Node $parent)
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
    
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
    
    public function getTranslatableLocale()
    {
        return $this->locale;
    }
}
