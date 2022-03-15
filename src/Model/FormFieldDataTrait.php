<?php

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;

trait FormFieldDataTrait
{

    /**
     * @var FormData
     *
     * @ORM\ManyToOne(targetEntity="Networking\FormGeneratorBundle\Model\FormData", inversedBy="formFields")
     * @ORM\JoinColumn(name="form_data_id", referencedColumnName="id", onDelete="CASCADE")
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