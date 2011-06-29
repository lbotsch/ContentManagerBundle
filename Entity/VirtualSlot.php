<?php

namespace Lubo\ContentManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Lubo\ContentManagerBundle\Entity\Repository\SlotRepository")
 */
class VirtualSlot extends Slot
{
    
}
    