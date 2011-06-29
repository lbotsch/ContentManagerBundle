<?php

namespace Lubo\ContentManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Lubo\ContentManagerBundle\Entity\Repository\PageRepository")
 */
class Page extends Node
{
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
     * @ORM\OneToMany(targetEntity="Area", mappedBy="page")
     */
    protected $areas;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;
    
    public function __construct()
    {
        $this->areas = new ArrayCollection();
        $this->default = false;
    }
    
    /* Getters / Setters */
    
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
    
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
}
