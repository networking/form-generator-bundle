<?php

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

trait FormFieldTrait
{

    /**
     * @var Form
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Networking\FormGeneratorBundle\Model\Form", inversedBy="formFields")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $form;


    public function __clone()
    {
        $this->id = null;
    }

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