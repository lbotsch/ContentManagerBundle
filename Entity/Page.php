<?php

namespace Lubo\ContentManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Lubo\ContentManagerBundle\Entity\Repository\PageRepository")
 * @ORM\Table(name="page")
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="text")
     * @Gedmo\SortableGroup
     */
    protected $path;
    
    /**
     * @ORM\Column(type="string", length="100")
     */
    protected $page_type;
    
    /**
     * @var boolean $default
     * 
     * @ORM\Column(type="boolean")
     */
    protected $is_default;
    
    /**
     * @var datetime $created_at
     * 
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created_at;
    
    /**
     * @var datetime $updated_at
     * 
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;
    
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
     * @ORM\OneToMany(targetEntity="Area", mappedBy="page")
     */
    protected $areas;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;
    
    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     */
    private $position;
    
    public function __construct()
    {
        $this->areas = new ArrayCollection();
        $this->is_default = false;
        $this->path = '/';
    }
    
    /* Getters / Setters */
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setPath($path)
    {
        $this->path = $path;
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function setPageType($page_type)
    {
        $this->page_type = $page_type;
    }
    
    public function getPageType()
    {
        return $this->page_type;
    }
    
    public function setDefault($default)
    {
        $this->is_default = $default;
    }
    
    public function isDefault()
    {
        return $this->is_default;
    }
    
    public function getCreatedAt()
    {
        return $this->created_at;
    }
    
    public function getUpdatedAt()
    {
        return $this->updated_at;
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
    
    public function getAreas()
    {
        return $this->areas;
    }
    
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
    
    public function getLocale()
    {
        return $this->locale;
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
