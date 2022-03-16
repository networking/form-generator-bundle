<?php

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

trait FormTrait
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ArrayCollection;
     * @ORM\OneToMany(targetEntity="Networking\FormGeneratorBundle\Model\FormField",cascade={"persist", "remove"}, mappedBy="form", orphanRemoval=true)
     */
    protected $formFields;

    /**
     * @var ArrayCollection;
     * @ORM\OneToMany(targetEntity="Networking\FormGeneratorBundle\Model\FormData",cascade={ "remove"}, mappedBy="form", orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $formData;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}